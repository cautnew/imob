<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\LesseeLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedLesseeSessionController extends Controller
{
    /**
     * Show the portal login form.
     */
    public function create(): Response
    {
        return Inertia::render('portal/auth/login');
    }

    /**
     * Authenticate the lessee and start a portal session.
     */
    public function store(LesseeLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return to_route('portal.dashboard');
    }

    /**
     * Log the lessee out of the portal.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('lessee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('portal.login');
    }
}
