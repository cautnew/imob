<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\TeamMemberStoreRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * List the members of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        abort_unless($request->user()->is_owner, 403);

        return Inertia::render('team/index', [
            'members' => $request->user()->company->users()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'is_owner']),
        ]);
    }

    /**
     * Add a new member to the authenticated user's company.
     */
    public function store(TeamMemberStoreRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'company_id' => $request->user()->company_id,
            'is_owner' => false,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Membro adicionado com sucesso.')]);

        return to_route('team.index');
    }
}
