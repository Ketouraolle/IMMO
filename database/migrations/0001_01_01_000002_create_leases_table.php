<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('rent_amount', 12, 2);
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->string('document_path')->nullable(); // uploaded signed lease PDF
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
