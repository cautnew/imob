<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List the users of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('users/index', [
            'users' => $request->user()->company->users()
                ->with('roles:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'is_owner']),
        ]);
    }

    /**
     * Show the form to create a new user.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/create', [
            'roles' => $request->user()->company->roles()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'company_id' => $request->user()->company_id,
            'is_owner' => false,
        ]);

        $user->syncRoles(Role::whereIn('id', $request->validated('roles', []))->get());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuário criado com sucesso.')]);

        return to_route('users.index');
    }

    /**
     * Show the form to edit an existing user.
     */
    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('users/edit', [
            'user' => $user->only('id', 'name', 'email', 'is_owner'),
            'assignedRoles' => $user->roles()->pluck('id'),
            'roles' => $request->user()->company->roles()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $user->update(array_filter([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ], fn ($value) => $value !== null));

        if ($request->user()->id !== $user->id) {
            $user->syncRoles(Role::whereIn('id', $request->validated('roles', []))->get());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuário atualizado com sucesso.')]);

        return to_route('users.index');
    }

    /**
     * Delete an existing user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Usuário removido com sucesso.')]);

        return to_route('users.index');
    }
}
