<?php

use App\Models\Company;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exercises the BelongsToCompany trait/CompanyScope against a throwaway
 * table, without depending on any real business model (none exist yet).
 */
function belongsToCompanyTestModel(): Model
{
    return new class extends Model
    {
        use BelongsToCompany;

        protected $table = 'belongs_to_company_test_items';

        protected $fillable = ['name'];
    };
}

beforeEach(function () {
    Schema::create('belongs_to_company_test_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('belongs_to_company_test_items');
});

test('creating a record auto-fills company_id from the current tenant', function () {
    $company = Company::factory()->create();
    app()->instance('currentCompany', $company);

    $item = belongsToCompanyTestModel()::create(['name' => 'Item A']);

    expect($item->company_id)->toBe($company->id);
});

test('queries are scoped to the current tenant', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    app()->instance('currentCompany', $companyA);
    belongsToCompanyTestModel()::create(['name' => 'Belongs to A']);

    app()->instance('currentCompany', $companyB);
    belongsToCompanyTestModel()::create(['name' => 'Belongs to B']);

    expect(belongsToCompanyTestModel()::pluck('name')->all())->toBe(['Belongs to B']);

    app()->instance('currentCompany', $companyA);

    expect(belongsToCompanyTestModel()::pluck('name')->all())->toBe(['Belongs to A']);
});

test('without a bound tenant, no scope is applied', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    app()->instance('currentCompany', $companyA);
    belongsToCompanyTestModel()::create(['name' => 'Belongs to A']);

    app()->instance('currentCompany', $companyB);
    belongsToCompanyTestModel()::create(['name' => 'Belongs to B']);

    app()->forgetInstance('currentCompany');

    expect(belongsToCompanyTestModel()::count())->toBe(2);
});
