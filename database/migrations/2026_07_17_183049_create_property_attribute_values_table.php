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
        Schema::create('property_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_attribute_option_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'property_attribute_id'], 'prop_attr_val_prop_id_prop_attr_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_attribute_values');
    }
};
