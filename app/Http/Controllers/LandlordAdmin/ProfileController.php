<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('landlord')->user();
        return view('landlord-admin.profile.index', compact('user'));
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::guard('landlord')->user();

        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:landlord.landlord_users,email,' . $user->id,
        ], [
            'name.required'  => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.unique'   => 'Cette adresse e-mail est déjà utilisée.',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success_info', 'Informations mises à jour.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('landlord')->user();

        $request->validate([
            'current_password'      => 'required',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'        => 'La confirmation ne correspond pas.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])
                         ->with('tab', 'password');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success_password', 'Mot de passe modifié avec succès.');
    }
}
