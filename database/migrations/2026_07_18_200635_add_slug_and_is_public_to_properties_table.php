<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
            $table->boolean('is_public')->default(false)->after('status');
        });

        $usedPerCompany = [];

        DB::table('properties')->orderBy('company_id')->orderBy('id')->get(['id', 'company_id', 'title'])
            ->each(function ($property) use (&$usedPerCompany) {
                $base = Str::slug($property->title) ?: 'imovel';
                $slug = $base;
                $i = 2;

                $usedPerCompany[$property->company_id] ??= [];

                while (in_array($slug, $usedPerCompany[$property->company_id], true)) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }

                $usedPerCompany[$property->company_id][] = $slug;

                DB::table('properties')->where('id', $property->id)->update(['slug' => $slug]);
            });

        Schema::table('properties', function (Blueprint $table) {
            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'is_public', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->dropIndex(['company_id', 'is_public', 'status']);
            $table->dropColumn(['slug', 'is_public']);
        });
    }
};
