<?php

declare(strict_types=1);

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_type', 20)
                ->default(AccountType::Customer->value);

            $table->string('account_status', 20)
                ->default(AccountStatus::Active->value);

            $table->timestamp('last_login_at')->nullable();

            $table->index(
                ['account_type', 'account_status'],
                'users_account_type_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_account_type_status_index');

            $table->dropColumn([
                'account_type',
                'account_status',
                'last_login_at',
            ]);
        });
    }
};
