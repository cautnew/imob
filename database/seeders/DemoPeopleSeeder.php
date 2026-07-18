<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Lessee;
use App\Models\Owner;
use Illuminate\Database\Seeder;

/**
 * Provisions the demo company's proprietários (owners) and inquilinos (lessees).
 */
class DemoPeopleSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('slug', DemoCompanySeeder::COMPANY_SLUG)->firstOrFail();

        Owner::factory()->for($company)->count(8)->create();

        Lessee::factory()->for($company)->count(4)->create();
        Lessee::factory()->for($company)->withPassword()->count(4)->create();
    }
}
