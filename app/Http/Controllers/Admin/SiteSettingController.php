<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $settings = SiteSetting::current();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = SiteSetting::current();

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],

            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_address' => ['nullable', 'string', 'max:500'],

            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],

            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'primary_hover_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sidebar_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'page_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'card_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'border_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_text_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'dark_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'mimes:png,ico', 'max:512'],
            'apple_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'android_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ]);

        $uploads = [
            'logo' => 'logo_path',
            'dark_logo' => 'dark_logo_path',
            'favicon' => 'favicon_path',
            'apple_icon' => 'apple_icon_path',
            'android_icon' => 'android_icon_path',
        ];

        foreach ($uploads as $input => $column) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $path = $file->store('site-settings', 'public');

                if ($settings->{$column}) {
                    Storage::disk('public')->delete($settings->{$column});
                }

                $validated[$column] = $path;
            }
        }

        unset(
            $validated['logo'],
            $validated['dark_logo'],
            $validated['favicon'],
            $validated['apple_icon'],
            $validated['android_icon']
        );

        $settings->fill($validated);
        $settings->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Website settings updated successfully.');
    }
}
