<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel enums are varchar + CHECK constraint on pgsql; drop it so status can take the new values.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE visit_requests DROP CONSTRAINT IF EXISTS visit_requests_status_check');
        }

        Schema::table('visit_requests', function (Blueprint $table) {
            $table->string('status', 20)->default('new')->change();
        });

        DB::table('visit_requests')->where('status', 'contacted')->update(['status' => 'confirmed']);
        DB::table('visit_requests')->where('status', 'closed')->update(['status' => 'completed']);

        Schema::table('visit_requests', function (Blueprint $table) {
            $table->foreignId('visit_slot_id')->nullable()->after('property_id')->constrained()->nullOnDelete();
            $table->date('visit_date')->nullable()->after('message');
            $table->time('visit_time')->nullable()->after('visit_date');
            $table->decimal('fee_amount', 12, 2)->default(0)->after('visit_time');
            $table->string('payment_option', 20)->nullable()->after('fee_amount'); // pay_now | pay_at_visit
            $table->string('payment_status', 20)->default('unpaid')->after('payment_option'); // unpaid | paid | not_required
            $table->string('payment_method', 30)->nullable()->after('payment_status');
            $table->string('transaction_ref')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('transaction_ref');
        });

        // Legacy leads had no fee attached.
        DB::table('visit_requests')->whereNull('payment_option')->update(['payment_status' => 'not_required']);
    }

    public function down(): void
    {
        Schema::table('visit_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('visit_slot_id');
            $table->dropColumn(['visit_date', 'visit_time', 'fee_amount', 'payment_option', 'payment_status', 'payment_method', 'transaction_ref', 'paid_at']);
        });

        DB::table('visit_requests')->where('status', 'confirmed')->update(['status' => 'contacted']);
        DB::table('visit_requests')->whereIn('status', ['completed', 'cancelled'])->update(['status' => 'closed']);
    }
};
