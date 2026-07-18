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
        Schema::create('lessees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Dados pessoais
            $table->string('name');
            $table->date('birth_date')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('occupation')->nullable();

            // Documentos
            $table->string('document', 18);
            $table->string('rg', 20)->nullable();
            $table->string('rg_issuer', 20)->nullable();

            // Contato
            $table->string('phone', 20);
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();

            // Endereço
            $table->string('zip_code', 9);
            $table->string('street');
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state', 2);

            // Renda
            $table->decimal('monthly_income', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'document']);
            $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessees');
    }
};
