<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\LesseeRegisterRequest;
use App\Models\Lessee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredLesseeController extends Controller
{
    /**
     * Generic, enumeration-safe error shown for every failure case below —
     * never reveals whether the document, the email, or their combination
     * was the problem, nor whether the lessee has already registered.
     */
    private const string GENERIC_ERROR = 'Não foi possível concluir o cadastro com os dados informados. '
        .'Verifique o CPF e o e-mail cadastrados junto à imobiliária.';

    /**
     * Show the portal registration form.
     */
    public function create(): Response
    {
        return Inertia::render('portal/auth/register');
    }

    /**
     * Match the submitted document + email against an existing, password-less
     * Lessee record (created by staff) and let the lessee set their password.
     */
    public function store(LesseeRegisterRequest $request): RedirectResponse
    {
        $normalizedDocument = preg_replace('/\D/', '', $request->string('document')->value());

        $candidates = Lessee::where('email', $request->string('email')->value())->get();

        $matches = $candidates->filter(
            fn (Lessee $lessee) => preg_replace('/\D/', '', $lessee->document) === $normalizedDocument
        );

        if ($matches->count() !== 1 || $matches->first()->password !== null) {
            throw ValidationException::withMessages([
                'document' => self::GENERIC_ERROR,
                'email' => self::GENERIC_ERROR,
            ]);
        }

        $lessee = $matches->first();

        DB::transaction(function () use ($lessee, $request): void {
            $lessee->forceFill(['password' => $request->string('password')->value()])->save();
        });

        Auth::guard('lessee')->login($lessee);

        $request->session()->regenerate();

        return to_route('portal.dashboard');
    }
}
