<?php

namespace App\Observers;

use App\Enums\TransactionType;
use App\Models\Company;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompanyObserver
{
    /**
     * The default roles provisioned for every new company.
     *
     * @var list<string>
     */
    public const array DEFAULT_ROLES = [
        'Administrador',
        'Corretor',
        'Financeiro',
        'Atendente',
        'Proprietário',
        'Inquilino',
    ];

    /**
     * The default financial transaction categories provisioned for every new company.
     *
     * @var list<array{name: string, type: TransactionType}>
     */
    public const array DEFAULT_TRANSACTION_CATEGORIES = [
        ['name' => 'Aluguel', 'type' => TransactionType::Income],
        ['name' => 'Multas', 'type' => TransactionType::Income],
        ['name' => 'Juros', 'type' => TransactionType::Income],
        ['name' => 'IPTU', 'type' => TransactionType::Expense],
        ['name' => 'Condomínio', 'type' => TransactionType::Expense],
        ['name' => 'Manutenção', 'type' => TransactionType::Expense],
        ['name' => 'Seguro', 'type' => TransactionType::Expense],
    ];

    /**
     * Provision the default roles and financial categories for a newly created company.
     */
    public function created(Company $company): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        foreach (self::DEFAULT_ROLES as $name) {
            Role::create(['name' => $name, 'guard_name' => 'web']);
        }

        Role::findByName('Administrador', 'web')->syncPermissions(Permission::all());

        foreach (self::DEFAULT_TRANSACTION_CATEGORIES as $category) {
            $company->transactionCategories()->create([
                'name' => $category['name'],
                'type' => $category['type'],
            ]);
        }
    }
}
