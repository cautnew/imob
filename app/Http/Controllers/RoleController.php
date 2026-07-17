<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List the roles of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        return Inertia::render('roles/index', [
            'roles' => $request->user()->company->roles()
                ->withCount('users')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show the form to create a new role.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('roles/create', [
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(RoleStoreRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
            'company_id' => $request->user()->company_id,
        ]);

        $role->syncPermissions(Permission::whereIn('id', $request->validated('permissions', []))->get());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Papel criado com sucesso.')]);

        return to_route('roles.index');
    }

    /**
     * Show the form to edit an existing role.
     */
    public function edit(Role $role): Response
    {
        $this->authorize('update', $role);

        return Inertia::render('roles/edit', [
            'role' => $role->only('id', 'name'),
            'assignedPermissions' => $role->permissions()->pluck('id'),
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update an existing role.
     */
    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->validated('name')]);

        $role->syncPermissions(Permission::whereIn('id', $request->validated('permissions', []))->get());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Papel atualizado com sucesso.')]);

        return to_route('roles.index');
    }

    /**
     * Delete an existing role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        abort_if($role->users()->exists(), 422, __('Não é possível excluir um papel em uso.'));

        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Papel removido com sucesso.')]);

        return to_route('roles.index');
    }
}
