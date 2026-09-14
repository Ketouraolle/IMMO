<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_method_check');
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('method', 30)->default('orange_money')->change();
            $table->string('transaction_ref')->nullable()->after('method');
            // Snapshot at approval time so later rate changes don't rewrite history.
            $table->decimal('commission_rate', 5, 2)->default(0)->after('amount');
            $table->decimal('commission_amount', 12, 2)->default(0)->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_ref', 'commission_rate', 'commission_amount']);
        });
    }
};
