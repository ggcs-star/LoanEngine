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

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            [],
            [
                'site_name' => config('app.name', 'LoanEngine'),
                'tagline' => 'Simple. Secure. Reliable.',

                // Black, white and grey theme defaults
                'primary_color' => '#262626',
                'primary_hover_color' => '#404040',
                'accent_color' => '#525252',

                'sidebar_color' => '#262626',
                'sidebar_text_color' => '#ffffff',

                'page_background_color' => '#f5f5f5',
                'card_color' => '#ffffff',
                'text_color' => '#262626',
                'border_color' => '#e5e5e5',
                'button_text_color' => '#ffffff',
            ]
        );
    }

}
