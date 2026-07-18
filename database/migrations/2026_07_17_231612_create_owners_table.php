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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Dados básicos
            $table->string('name');
            $table->string('document', 18);

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

            // Dados bancários
            $table->string('bank_name')->nullable();
            $table->string('bank_agency', 20)->nullable();
            $table->string('bank_account', 20)->nullable();
            $table->string('bank_account_type')->nullable();
            $table->string('pix_key')->nullable();

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
        Schema::dropIfExists('owners');
    }
};
