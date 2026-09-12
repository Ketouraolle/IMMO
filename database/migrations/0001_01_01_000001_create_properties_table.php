<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('type')->default('apartment');
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->enum('status', ['vacant', 'occupied', 'maintenance'])->default('vacant');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
