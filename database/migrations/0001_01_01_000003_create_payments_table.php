<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete(); // tenant who initiated it, null if admin entered it directly
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete(); // admin who validated/recorded it
            $table->string('receipt_number')->nullable()->unique(); // only set once approved
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('period_covered')->nullable();
            $table->enum('method', ['cash', 'bank_transfer', 'mobile_money', 'other'])->default('bank_transfer');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
