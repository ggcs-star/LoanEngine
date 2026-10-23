<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        // Website branding
        'site_name',
        'tagline',
        'meta_description',
        'meta_keywords',

        // Contact details
        'contact_email',
        'contact_phone',
        'contact_address',

        // Social media
        'facebook_url',
        'instagram_url',
        'linkedin_url',

        // Logo and website icons
        'logo_path',
        'dark_logo_path',
        'favicon_path',
        'apple_icon_path',
        'android_icon_path',

        // Theme colours
        'primary_color',
        'primary_hover_color',
        'accent_color',
        'sidebar_color',
        'sidebar_text_color',
        'page_background_color',
        'card_color',
        'text_color',
        'border_color',
        'button_text_color',
    ];

    /**
     * Get or create the current website settings.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            [],
            [
                'site_name' => config('app.name', 'LoanEngine'),
                'tagline' => 'Simple. Secure. Reliable.',
                'primary_color' => '#2563eb',
                'primary_hover_color' => '#1d4ed8',
                'accent_color' => '#0ea5e9',
                'sidebar_color' => '#ffffff',
                'sidebar_text_color' => '#1f2937',
                'page_background_color' => '#f3f4f6',
                'card_color' => '#ffffff',
                'text_color' => '#111827',
                'border_color' => '#e5e7eb',
                'button_text_color' => '#ffffff',
            ]
        );
    }
}
