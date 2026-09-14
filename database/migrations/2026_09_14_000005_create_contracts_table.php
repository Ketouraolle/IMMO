<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->text('special_conditions')->nullable();
            $table->longText('body')->nullable();      // rendered HTML snapshot the tenant reads and signs
            $table->string('body_hash', 64)->nullable(); // sha256 of body, fixed when sent
            $table->string('status', 20)->default('draft'); // draft | sent | signed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->longText('signature_data')->nullable(); // PNG data URI drawn by the tenant
            $table->string('signer_ip', 45)->nullable();
            $table->text('signer_user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
