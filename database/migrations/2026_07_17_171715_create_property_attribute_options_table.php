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
        Schema::create('property_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['property_attribute_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_attribute_options');
    }
};
