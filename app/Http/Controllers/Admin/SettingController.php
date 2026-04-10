<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'logo'             => AppSetting::get('logo'),
            'carousel_image_1' => AppSetting::get('carousel_image_1'),
            'carousel_image_2' => AppSetting::get('carousel_image_2'),
            'carousel_image_3' => AppSetting::get('carousel_image_3'),
            'carousel_caption_1' => AppSetting::get('carousel_caption_1', ''),
            'carousel_caption_2' => AppSetting::get('carousel_caption_2', ''),
            'carousel_caption_3' => AppSetting::get('carousel_caption_3', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'logo'             => ['nullable', 'image', 'max:2048'],
            'carousel_image_1' => ['nullable', 'image', 'max:10240'],
            'carousel_image_2' => ['nullable', 'image', 'max:10240'],
            'carousel_image_3' => ['nullable', 'image', 'max:10240'],
            'carousel_caption_1' => ['nullable', 'string', 'max:120'],
            'carousel_caption_2' => ['nullable', 'string', 'max:120'],
            'carousel_caption_3' => ['nullable', 'string', 'max:120'],
        ]);

        // ── Logo ───────────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            $old = AppSetting::get('logo');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('logo')->store('settings', 'public');
            AppSetting::set('logo', $path);
        }

        if ($request->boolean('delete_logo')) {
            $old = AppSetting::get('logo');
            if ($old) Storage::disk('public')->delete($old);
            AppSetting::remove('logo');
        }

        // ── Images carousel ────────────────────────────────────────────────
        foreach ([1, 2, 3] as $i) {
            $field = "carousel_image_{$i}";

            if ($request->hasFile($field)) {
                $old = AppSetting::get($field);
                if ($old) Storage::disk('public')->delete($old);

                $path = $request->file($field)->store('settings', 'public');
                AppSetting::set($field, $path);
            }

            if ($request->boolean("delete_carousel_{$i}")) {
                $old = AppSetting::get($field);
                if ($old) Storage::disk('public')->delete($old);
                AppSetting::remove($field);
                AppSetting::remove("carousel_caption_{$i}");
            }

            $captionField = "carousel_caption_{$i}";
            if ($request->filled($captionField)) {
                AppSetting::set($captionField, $request->input($captionField));
            } elseif (!$request->boolean("delete_carousel_{$i}")) {
                AppSetting::remove($captionField);
            }
        }

        return back()->with('swal_success', 'Paramètres enregistrés.');
    }
}
