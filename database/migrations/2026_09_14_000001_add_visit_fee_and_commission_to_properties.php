<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('visit_fee', 12, 2)->default(0)->after('monthly_rent');
            $table->decimal('commission_rate', 5, 2)->default(0)->after('visit_fee'); // percentage of rent collected
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['visit_fee', 'commission_rate']);
        });
    }
};
