<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            DemoCompanySeeder::class,
            DemoCatalogSeeder::class,
            DemoPeopleSeeder::class,
            DemoPropertySeeder::class,
            DemoLeaseSeeder::class,
            DemoFinancialSeeder::class,
        ]);
    }
}
