<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * The global catalog of permissions available to every company.
     *
     * @var list<string>
     */
    public const array PERMISSIONS = [
        'usuarios.visualizar',
        'usuarios.criar',
        'usuarios.editar',
        'usuarios.excluir',
        'papeis.visualizar',
        'papeis.criar',
        'papeis.editar',
        'papeis.excluir',
        'permissoes.visualizar',
        'caracteristicas.visualizar',
        'caracteristicas.criar',
        'caracteristicas.editar',
        'caracteristicas.excluir',
        'atributos.visualizar',
        'atributos.criar',
        'atributos.editar',
        'atributos.excluir',
    ];

    /**
     * Seed the application's permission catalog.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
