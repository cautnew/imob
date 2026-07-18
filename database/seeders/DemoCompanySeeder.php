<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Provisions the demo real-estate company ("Horizonte Imóveis") used to
 * showcase the system to prospective clients, together with its staff users.
 *
 * Creating the Company triggers CompanyObserver, which provisions the
 * default roles (Administrador, Corretor, Financeiro, Atendente,
 * Proprietário, Inquilino) and financial transaction categories.
 */
class DemoCompanySeeder extends Seeder
{
    /**
     * Slug of the demo company, used by the other Demo*Seeder classes to locate it.
     */
    public const string COMPANY_SLUG = 'horizonte-imoveis';

    /**
     * Staff users provisioned for the demo company, beyond its Administrador owner.
     *
     * @var list<array{name: string, email: string, role: string}>
     */
    public const array STAFF = [
        ['name' => 'Marina Alves', 'email' => 'marina.corretora@horizonte.imob', 'role' => 'Corretor'],
        ['name' => 'Rafael Souza', 'email' => 'rafael.corretor@horizonte.imob', 'role' => 'Corretor'],
        ['name' => 'Juliana Prado', 'email' => 'juliana.financeiro@horizonte.imob', 'role' => 'Financeiro'],
        ['name' => 'Bruno Lima', 'email' => 'bruno.atendente@horizonte.imob', 'role' => 'Atendente'],
    ];

    public function run(): void
    {
        $company = Company::create([
            'name' => 'Horizonte Imóveis',
            'slug' => self::COMPANY_SLUG,
            'document' => '12.345.678/0001-90',
            'phone' => '(11) 4002-8922',
            'address' => 'Av. Paulista, 1000 - Bela Vista, São Paulo/SP',
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

        $admin = User::factory()->for($company)->create([
            'name' => 'Ana Beatriz Costa',
            'email' => 'admin@horizonte.imob',
            'is_owner' => true,
        ]);
        $admin->assignRole('Administrador');

        foreach (self::STAFF as $member) {
            $user = User::factory()->for($company)->create([
                'name' => $member['name'],
                'email' => $member['email'],
                'is_owner' => false,
            ]);
            $user->assignRole($member['role']);
        }
    }
}
