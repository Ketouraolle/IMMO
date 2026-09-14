<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A date the admin opens for visits on a property, with the time window visitors can pick from.
        Schema::create('visit_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_slots');
    }
};
