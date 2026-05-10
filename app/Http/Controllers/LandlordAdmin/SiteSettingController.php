<?php

namespace App\Http\Controllers\LandlordAdmin;

use App\Http\Controllers\Controller;
use App\Models\LandlordSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /** Clés texte attendues dans le formulaire. */
    private const TEXT_KEYS = [
        // Identité
        'site_name', 'site_tagline', 'site_description',
        // Contact
        'contact_email', 'contact_phone', 'contact_address', 'contact_whatsapp',
        // Réseaux sociaux
        'social_facebook', 'social_linkedin', 'social_twitter',
        // Landing page — hero
        'hero_title', 'hero_subtitle',
        // Footer
        'footer_tagline',
        // Maintenance
        'maintenance_message',
    ];

    public function index()
    {
        $s = LandlordSetting::all()->pluck('value', 'key')->toArray();
        return view('landlord-admin.settings.index', compact('s'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'    => 'required|string|max:80',
            'contact_email'=> 'nullable|email|max:120',
            'logo'         => 'nullable|image|max:2048',
            'og_image'     => 'nullable|image|max:4096',
        ], [
            'site_name.required' => 'Le nom du site est obligatoire.',
            'logo.image'         => 'Le logo doit être une image.',
            'og_image.image'     => 'L\'image de partage doit être une image.',
        ]);

        // — Champs texte ————————————————————————————————————————————
        foreach (self::TEXT_KEYS as $key) {
            LandlordSetting::set($key, $request->input($key, ''));
        }

        // — Maintenance mode (checkbox) ——————————————————————————————
        LandlordSetting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');

        // — Logo ——————————————————————————————————————————————————————
        if ($request->hasFile('logo')) {
            $old = LandlordSetting::get('logo_path');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('logo')->store('landlord', 'public');
            LandlordSetting::set('logo_path', $path);
        }

        // — Image OG (partage réseaux sociaux) ————————————————————————
        if ($request->hasFile('og_image')) {
            $old = LandlordSetting::get('og_image_path');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('og_image')->store('landlord', 'public');
            LandlordSetting::set('og_image_path', $path);
        }

        // — Suppression logo ——————————————————————————————————————————
        if ($request->boolean('remove_logo')) {
            $old = LandlordSetting::get('logo_path');
            if ($old) Storage::disk('public')->delete($old);
            LandlordSetting::remove('logo_path');
        }

        return back()->with('success', 'Paramètres du site enregistrés avec succès.');
    }
}
