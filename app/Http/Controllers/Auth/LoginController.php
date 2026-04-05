<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $name = Auth::user()->name;
            return redirect()->intended(route('dashboard'))
                ->with('swal_success', "Bienvenue, {$name} ! Vous êtes connecté.");
        }

        return back()
            ->withErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.'])
            ->with('swal_error', 'Identifiants incorrects. Vérifiez votre email et mot de passe.')
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $name = Auth::user()?->name;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('swal_info', $name ? "{$name}, vous avez été déconnecté." : 'Vous avez été déconnecté.');
    }
}
