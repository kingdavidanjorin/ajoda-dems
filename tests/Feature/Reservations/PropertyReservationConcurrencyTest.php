<?php

declare(strict_types=1);

namespace Tests\Feature\Reservations;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PropertyStatus;
use App\Enums\ReservationStatus;
use App\Models\House;
use App\Models\PropertyReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class PropertyReservationConcurrencyTest extends TestCase
{
    private const DATABASE = 'ajoda_dems_concurrency_test';

    public function test_two_simultaneous_approvals_cannot_approve_same_house_twice(): void
    {
        if (getenv('RUN_MYSQL_CONCURRENCY_TEST') !== '1') {
            $this->markTestSkipped(
                'Set RUN_MYSQL_CONCURRENCY_TEST=1 to run the isolated MySQL concurrency test.',
            );
        }

        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.mysql.database');

        try {
            /*
             * Force this test onto the isolated database.
             */
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.database' => self::DATABASE,
            ]);

            DB::purge('mysql');
            DB::setDefaultConnection('mysql');

            /*
             * HARD SAFETY CHECK:
             * Never continue unless Laravel is really connected
             * to the dedicated concurrency-test database.
             */
            $this->assertSame(
                self::DATABASE,
                DB::connection()->getDatabaseName(),
                'Safety check failed: refusing to run concurrency test against another database.',
            );

            $firstCustomer = User::factory()->create([
                'account_type' => AccountType::Customer->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $secondCustomer = User::factory()->create([
                'account_type' => AccountType::Customer->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $firstReviewer = User::factory()->create([
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $secondReviewer = User::factory()->create([
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $house = House::factory()->create([
                'status' => PropertyStatus::Available->value,
            ]);

            $firstReservation = PropertyReservation::query()->create([
                'reference' => sprintf(
                    'AJD-RACE-%s-A',
                    Str::upper(Str::random(12)),
                ),
                'house_id' => $house->id,
                'customer_id' => $firstCustomer->id,
                'status' => ReservationStatus::PendingReview->value,
            ]);

            $secondReservation = PropertyReservation::query()->create([
                'reference' => sprintf(
                    'AJD-RACE-%s-B',
                    Str::upper(Str::random(12)),
                ),
                'house_id' => $house->id,
                'customer_id' => $secondCustomer->id,
                'status' => ReservationStatus::PendingReview->value,
            ]);

            /*
             * Both subprocesses wait for the same start timestamp
             * so their approval attempts hit MySQL almost together.
             */
            $startAt = microtime(true) + 1.5;

            $environment = [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'mysql',
                'DB_DATABASE' => self::DATABASE,
                'AJODA_CONCURRENCY_START' => (string) $startAt,
            ];

            $worker = <<<'PHP'
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->make(
    Illuminate\Contracts\Console\Kernel::class
)->bootstrap();

config([
    'database.default' => 'mysql',
    'database.connections.mysql.database' =>
        getenv('DB_DATABASE'),
]);

Illuminate\Support\Facades\DB::purge('mysql');
Illuminate\Support\Facades\DB::setDefaultConnection('mysql');

if (
    Illuminate\Support\Facades\DB::connection()
        ->getDatabaseName()
    !== 'ajoda_dems_concurrency_test'
) {
    fwrite(
        STDERR,
        'SAFETY FAILURE: wrong database'
    );

    exit(90);
}

$startAt = (float) getenv('AJODA_CONCURRENCY_START');

while (microtime(true) < $startAt) {
    usleep(1000);
}

$reservation = App\Models\PropertyReservation::query()
    ->findOrFail((int) $argv[1]);

$reviewer = App\Models\User::query()
    ->findOrFail((int) $argv[2]);

try {
    app(
        App\Services\Reservations\PropertyReservationService::class
    )->approve(
        $reservation,
        $reviewer,
        'Concurrency security test.',
    );

    fwrite(STDOUT, 'APPROVED');

    exit(0);
} catch (
    Illuminate\Validation\ValidationException $exception
) {
    fwrite(
        STDOUT,
        'REJECTED:'.json_encode($exception->errors())
    );

    exit(10);
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        get_class($exception).': '.$exception->getMessage()
    );

    exit(20);
}
PHP;

            $firstProcess = new Process(
                [
                    PHP_BINARY,
                    '-r',
                    $worker,
                    (string) $firstReservation->id,
                    (string) $firstReviewer->id,
                ],
                base_path(),
                $environment,
            );

            $secondProcess = new Process(
                [
                    PHP_BINARY,
                    '-r',
                    $worker,
                    (string) $secondReservation->id,
                    (string) $secondReviewer->id,
                ],
                base_path(),
                $environment,
            );

            /*
             * Launch both BEFORE waiting for either one.
             */
            $firstProcess->start();
            $secondProcess->start();

            $firstProcess->wait();
            $secondProcess->wait();

            $exitCodes = [
                $firstProcess->getExitCode(),
                $secondProcess->getExitCode(),
            ];

            sort($exitCodes);

            $diagnostics = sprintf(
                "First process:\n%s\n%s\n\nSecond process:\n%s\n%s",
                $firstProcess->getOutput(),
                $firstProcess->getErrorOutput(),
                $secondProcess->getOutput(),
                $secondProcess->getErrorOutput(),
            );

            /*
             * Exactly one approval must succeed.
             * The competing approval must be rejected by the
             * transactional reservation logic.
             */
            $this->assertSame(
                [0, 10],
                $exitCodes,
                $diagnostics,
            );

            $firstReservation->refresh();
            $secondReservation->refresh();
            $house->refresh();

            $reservations = PropertyReservation::query()
                ->where('house_id', $house->id)
                ->get();

            $this->assertSame(
                1,
                $reservations
                    ->where(
                        'status',
                        ReservationStatus::Approved,
                    )
                    ->count(),
                'Exactly one reservation should be approved.',
            );

            $this->assertSame(
                1,
                $reservations
                    ->where(
                        'status',
                        ReservationStatus::Rejected,
                    )
                    ->count(),
                'The competing reservation should be rejected.',
            );

            $this->assertSame(
                PropertyStatus::TemporarilyReserved,
                $house->status,
            );

            $this->assertNotNull(
                $house->reserved_until,
            );

            /*
             * A double approval could also produce duplicate
             * Available -> TemporarilyReserved transitions.
             */
            $this->assertSame(
                1,
                DB::table('house_status_histories')
                    ->where('house_id', $house->id)
                    ->where(
                        'from_status',
                        PropertyStatus::Available->value,
                    )
                    ->where(
                        'to_status',
                        PropertyStatus::TemporarilyReserved->value,
                    )
                    ->count(),
            );
        } finally {
            DB::disconnect('mysql');

            config([
                'database.default' => $originalDefault,
                'database.connections.mysql.database' =>
                $originalDatabase,
            ]);

            DB::purge('mysql');

            if (is_string($originalDefault)) {
                DB::setDefaultConnection($originalDefault);
            }
        }
    }
}
