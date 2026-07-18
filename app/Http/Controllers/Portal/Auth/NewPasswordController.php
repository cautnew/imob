<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Lessee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    use PasswordValidationRules;

    /**
     * Show the portal's reset-password form.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('portal/auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Reset the lessee's password.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $status = Password::broker('lessees')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Lessee $lessee, string $password): void {
                $lessee->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return to_route('portal.login')->with('status', __($status));
    }
}
