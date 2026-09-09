<?php

declare(strict_types=1);

namespace Tests\Feature\Purchases;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Models\House;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

final class PurchaseConcurrencyTest extends TestCase
{
    private const DATABASE = 'ajoda_dems_concurrency_test';

    public function test_two_simultaneous_purchase_initiations_cannot_control_same_house(): void
    {
        if (getenv('RUN_MYSQL_CONCURRENCY_TEST') !== '1') {
            $this->markTestSkipped(
                'Set RUN_MYSQL_CONCURRENCY_TEST=1 to run the isolated MySQL concurrency test.',
            );
        }

        $originalDefault = config('database.default');
        $originalDatabase = config(
            'database.connections.mysql.database',
        );

        try {
            config([
                'database.default' => 'mysql',
                'database.connections.mysql.database' =>
                self::DATABASE,
            ]);

            DB::purge('mysql');
            DB::setDefaultConnection('mysql');

            /*
             * HARD SAFETY CHECK:
             * Do not write anything unless this is the
             * dedicated concurrency-test database.
             */
            $this->assertSame(
                self::DATABASE,
                DB::connection()->getDatabaseName(),
                'Safety check failed: refusing to run against another database.',
            );

            $firstCustomer = User::factory()->create([
                'account_type' => AccountType::Customer->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $secondCustomer = User::factory()->create([
                'account_type' => AccountType::Customer->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $firstSalesOfficer = User::factory()->create([
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $secondSalesOfficer = User::factory()->create([
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Active->value,
            ]);

            $house = House::factory()->create([
                'status' =>
                PropertyStatus::TemporarilyReserved->value,
                'reserved_until' => now()->addHours(72),
            ]);

            /*
             * We deliberately create two approved reservations
             * for the same house to stress-test PurchaseService.
             *
             * Normal reservation approval logic should already
             * prevent this state, but the purchase layer should
             * still defend itself independently.
             */
            $firstReservation =
                PropertyReservation::query()->create([
                    'reference' => sprintf(
                        'AJD-PUR-RACE-%s-A',
                        Str::upper(Str::random(10)),
                    ),
                    'house_id' => $house->id,
                    'customer_id' => $firstCustomer->id,
                    'status' =>
                    ReservationStatus::Approved->value,
                    'approved_at' => now(),
                    'expires_at' => now()->addHours(72),
                ]);

            $secondReservation =
                PropertyReservation::query()->create([
                    'reference' => sprintf(
                        'AJD-PUR-RACE-%s-B',
                        Str::upper(Str::random(10)),
                    ),
                    'house_id' => $house->id,
                    'customer_id' => $secondCustomer->id,
                    'status' =>
                    ReservationStatus::Approved->value,
                    'approved_at' => now(),
                    'expires_at' => now()->addHours(72),
                ]);

            /*
             * Both subprocesses wait for the same timestamp
             * before calling PurchaseService::initiate().
             */
            $startAt = microtime(true) + 1.5;

            $environment = [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'mysql',
                'DB_DATABASE' => self::DATABASE,
                'AJODA_CONCURRENCY_START' =>
                (string) $startAt,
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

$reservation =
    App\Models\PropertyReservation::query()
        ->findOrFail((int) $argv[1]);

$salesOfficer =
    App\Models\User::query()
        ->findOrFail((int) $argv[2]);

try {
    app(
        App\Services\Purchases\PurchaseService::class
    )->initiate(
        $reservation,
        $salesOfficer,
        '50000000.00',
        App\Enums\PaymentPlanType::Outright,
        'Concurrency security test.',
    );

    fwrite(STDOUT, 'PURCHASE_CREATED');

    exit(0);
} catch (
    Illuminate\Validation\ValidationException $exception
) {
    fwrite(
        STDOUT,
        'REJECTED:'.json_encode(
            $exception->errors()
        )
    );

    exit(10);
} catch (Throwable $exception) {
    fwrite(
        STDERR,
        get_class($exception).': '.
        $exception->getMessage()
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
                    (string) $firstSalesOfficer->id,
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
                    (string) $secondSalesOfficer->id,
                ],
                base_path(),
                $environment,
            );

            /*
             * Launch both before waiting for either process.
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
             * Exactly one request should succeed.
             * The competing request should be rejected.
             */
            $this->assertSame(
                [0, 10],
                $exitCodes,
                $diagnostics,
            );

            $firstReservation->refresh();
            $secondReservation->refresh();
            $house->refresh();

            $purchases = Purchase::query()
                ->where('house_id', $house->id)
                ->get();

            $this->assertCount(
                1,
                $purchases,
                'Only one purchase may exist for the house.',
            );

            $purchase = $purchases->first();

            $this->assertNotNull($purchase);

            $this->assertSame(
                PurchaseStatus::AwaitingPayment,
                $purchase->status,
            );

            $this->assertSame(
                $house->id,
                $purchase->active_house_id,
            );

            /*
             * Exactly one reservation should have been converted.
             */
            $this->assertSame(
                1,
                PropertyReservation::query()
                    ->where('house_id', $house->id)
                    ->where(
                        'status',
                        ReservationStatus::Converted->value,
                    )
                    ->count(),
            );

            $this->assertSame(
                1,
                PropertyReservation::query()
                    ->where('house_id', $house->id)
                    ->where(
                        'status',
                        ReservationStatus::Approved->value,
                    )
                    ->count(),
            );

            $this->assertSame(
                PropertyStatus::SaleProcessing,
                $house->status,
            );

            $this->assertNull(
                $house->reserved_until,
            );

            /*
             * There must only be one transition into
             * SaleProcessing for this house.
             */
            $this->assertSame(
                1,
                DB::table('house_status_histories')
                    ->where('house_id', $house->id)
                    ->where(
                        'from_status',
                        PropertyStatus::TemporarilyReserved->value,
                    )
                    ->where(
                        'to_status',
                        PropertyStatus::SaleProcessing->value,
                    )
                    ->count(),
            );

            /*
             * Database-level defence:
             * only one non-null active_house_id may exist.
             */
            $this->assertSame(
                1,
                Purchase::query()
                    ->where(
                        'active_house_id',
                        $house->id,
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
                DB::setDefaultConnection(
                    $originalDefault,
                );
            }
        }
    }
}
