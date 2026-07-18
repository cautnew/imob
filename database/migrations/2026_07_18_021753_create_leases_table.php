<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lessee_id')->constrained()->cascadeOnDelete();

            // Vigência do contrato
            $table->date('start_date');
            $table->date('end_date');

            // Aluguel e reajuste
            $table->decimal('rent_amount', 12, 2);
            $table->string('adjustment_index');
            $table->unsignedSmallInteger('adjustment_interval_months')->default(12);
            $table->date('last_adjustment_date')->nullable();

            // Renovação
            $table->string('renewal_type');

            // Situação
            $table->string('status')->default('ativo');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
