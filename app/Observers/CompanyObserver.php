<?php

namespace App\Observers;

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
     * Provision the default roles for a newly created company.
     */
    public function created(Company $company): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        foreach (self::DEFAULT_ROLES as $name) {
            Role::create(['name' => $name, 'guard_name' => 'web']);
        }

        Role::findByName('Administrador', 'web')->syncPermissions(Permission::all());
    }
}
