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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $reserved = config('public-portal.reserved_slugs', []);
        $used = [];

        DB::table('companies')->orderBy('id')->get(['id', 'name'])->each(function ($company) use (&$used, $reserved) {
            $base = Str::slug($company->name) ?: 'empresa';
            $slug = $base;
            $i = 2;

            while (in_array($slug, $used, true) || in_array($slug, $reserved, true)) {
                $slug = "{$base}-{$i}";
                $i++;
            }

            $used[] = $slug;

            DB::table('companies')->where('id', $company->id)->update(['slug' => $slug]);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
