<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * List the global catalog of permissions.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Permission::class);

        return Inertia::render('permissions/index', [
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
