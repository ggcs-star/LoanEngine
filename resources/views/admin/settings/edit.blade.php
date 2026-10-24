
@extends('layouts.admin')

@section('title', 'Website Settings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-settings.css') }}">

    <style>
        .le-settings .le-settings__section {
            margin-bottom: 24px;
        }

        .le-settings .le-settings__section-header {
            background: var(--card-color, #fff);
        }

        .le-settings .le-settings__section-body {
            background: var(--card-color, #fff);
        }

        .le-settings .le-settings__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .le-settings .le-settings__field--full {
            grid-column: 1 / -1;
        }

        .le-settings .le-settings__color-field {
            min-width: 0;
        }

        .le-settings .le-settings__color-field input[type="color"] {
            width: 48px;
            min-width: 48px;
            height: 42px;
            padding: 3px;
        }

        .le-settings .le-settings__color-value {
            overflow-wrap: anywhere;
        }

        .le-settings .le-settings__upload {
            height: 100%;
            padding: 18px;
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 12px;
            background: var(--card-color, #fff);
        }

        .le-settings .le-settings__upload-title {
            display: block;
            margin-bottom: 12px;
            font-weight: 650;
            font-size: 14px;
        }

        .le-settings .le-settings__image-preview {
            min-height: 120px;
        }

        .le-settings .le-settings__image-preview img {
            max-width: 100%;
            max-height: 90px;
            object-fit: contain;
        }

        .le-settings .le-settings__image-preview--icon img {
            width: 64px;
            height: 64px;
        }

        .le-settings .le-settings__empty-preview {
            color: #6b7280;
            font-size: 13px;
        }

        .le-settings .le-settings__preview {
            color: var(--text-color, #111827);
        }

        .le-settings .le-settings__preview-button {
            color: var(--button-text-color, #fff);
            background: var(--primary-color, #2563eb);
        }

        .le-settings .le-settings__preview-button:hover {
            color: var(--button-text-color, #fff);
            background: var(--primary-hover-color, #1d4ed8);
        }

        .le-settings .le-settings__preview-accent {
            color: var(--accent-color, #0ea5e9);
            font-weight: 650;
        }

        .le-settings .le-settings__actions {
            margin-bottom: 24px;
        }

        .le-settings .le-settings__button:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .le-settings .le-settings__grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .le-settings .le-settings__field--full {
                grid-column: auto;
            }
        }
    </style>
@endpush

@section('content')
<div class="le-settings">

    {{-- Page heading --}}
    <div class="le-settings__header">
        <div>
            <h1 class="le-settings__title">Website Settings</h1>
            <p class="le-settings__subtitle">
                Manage your website branding, logos, SEO and global theme colours.
            </p>
        </div>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div class="le-settings__alert le-settings__alert--success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="le-settings__alert le-settings__alert--error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="websiteSettingsForm"
        action="{{ route('admin.settings.update') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        {{-- General and SEO --}}
        <section class="le-settings__section">
            <div class="le-settings__section-header">
                <div class="le-settings__section-icon" aria-hidden="true">⚙</div>
                <div>
                    <h2 class="le-settings__section-title">General & SEO</h2>
                    <p class="le-settings__section-description">
                        Configure your website name and search engine details.
                    </p>
                </div>
            </div>

            <div class="le-settings__section-body">
                <div class="le-settings__grid">

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="site_name">
                            Website Name <span>(Optional)</span>
                        </label>

                        <input
                            id="site_name"
                            type="text"
                            name="site_name"
                            value="{{ old('site_name', $settings->site_name) }}"
                            maxlength="100"
                            placeholder="Enter website name (optional)"
                        >
                    </div>

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="tagline">
                            Website Tagline
                        </label>
                        <input
                            id="tagline"
                            type="text"
                            name="tagline"
                            value="{{ old('tagline', $settings->tagline) }}"
                            maxlength="200"
                            placeholder="Simple. Secure. Reliable."
                        >
                    </div>

                    <div class="le-settings__field le-settings__field--full">
                        <label class="le-settings__label" for="meta_description">
                            Meta Description
                        </label>
                        <textarea
                            id="meta_description"
                            name="meta_description"
                            rows="3"
                            maxlength="320"
                            placeholder="Describe your website for search engines..."
                        >{{ old('meta_description', $settings->meta_description) }}</textarea>
                        <small class="le-settings__help">
                            A concise description of your website.
                        </small>
                    </div>

                    <div class="le-settings__field le-settings__field--full">
                        <label class="le-settings__label" for="meta_keywords">
                            Meta Keywords
                        </label>
                        <input
                            id="meta_keywords"
                            type="text"
                            name="meta_keywords"
                            value="{{ old('meta_keywords', $settings->meta_keywords) }}"
                            maxlength="500"
                            placeholder="finance, loans, EMI calculator"
                        >
                    </div>

                </div>
            </div>
        </section>

        {{-- Logo and icons --}}
        <section class="le-settings__section">
            <div class="le-settings__section-header">
                <div class="le-settings__section-icon" aria-hidden="true">▣</div>
                <div>
                    <h2 class="le-settings__section-title">Logo & Website Icons</h2>
                    <p class="le-settings__section-description">
                        Upload your brand logo and icons used by the website.
                    </p>
                </div>
            </div>

            <div class="le-settings__section-body">
                @php
                    $uploads = [
                        'logo' => [
                            'label' => 'Main Logo',
                            'column' => 'logo_path',
                            'accept' => '.png,.jpg,.jpeg,.webp',
                            'help' => 'PNG, JPG or WebP. Maximum 2 MB.',
                            'icon' => false,
                        ],
                        'dark_logo' => [
                            'label' => 'Dark Logo',
                            'column' => 'dark_logo_path',
                            'accept' => '.png,.jpg,.jpeg,.webp',
                            'help' => 'Logo designed for dark backgrounds. Maximum 2 MB.',
                            'icon' => false,
                        ],
                        'favicon' => [
                            'label' => 'Favicon',
                            'column' => 'favicon_path',
                            'accept' => '.png,.ico',
                            'help' => 'PNG or ICO. Maximum 512 KB.',
                            'icon' => true,
                        ],
                        'apple_icon' => [
                            'label' => 'Apple Touch Icon',
                            'column' => 'apple_icon_path',
                            'accept' => '.png,.jpg,.jpeg,.webp',
                            'help' => 'Prefer a square 180 × 180 image.',
                            'icon' => true,
                        ],
                        'android_icon' => [
                            'label' => 'Android Icon',
                            'column' => 'android_icon_path',
                            'accept' => '.png,.jpg,.jpeg,.webp',
                            'help' => 'Prefer a square 192 × 192 or larger image.',
                            'icon' => true,
                        ],
                    ];
                @endphp

                <div class="le-settings__grid">
                    @foreach($uploads as $field => $upload)
                        <div class="le-settings__field">
                            <div class="le-settings__upload">

                                <label
                                    class="le-settings__upload-title"
                                    for="{{ $field }}"
                                >
                                    {{ $upload['label'] }}
                                </label>

                                <div class="le-settings__image-preview {{ $upload['icon'] ? 'le-settings__image-preview--icon' : '' }}">
                                    @if($settings->{$upload['column']})
                                        <img
                                            src="{{ asset('storage/' . $settings->{$upload['column']}) }}"
                                            alt="{{ $upload['label'] }} preview"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="le-settings__empty-preview">
                                            No image uploaded
                                        </span>
                                    @endif
                                </div>

                                <input
                                    id="{{ $field }}"
                                    type="file"
                                    name="{{ $field }}"
                                    accept="{{ $upload['accept'] }}"
                                >

                                <small class="le-settings__help">
                                    {{ $upload['help'] }}
                                </small>

                                <div
                                    class="le-settings__help"
                                    data-file-name="{{ $field }}"
                                    aria-live="polite"
                                ></div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Theme colours --}}
        <section class="le-settings__section">
            <div class="le-settings__section-header">
                <div class="le-settings__section-icon" aria-hidden="true">◉</div>
                <div>
                    <h2 class="le-settings__section-title">Theme Colours</h2>
                    <p class="le-settings__section-description">
                        Choose the shared colours for buttons, sidebars, cards and text.
                    </p>
                </div>
            </div>

            <div class="le-settings__section-body">

                @php
                    $colors = [
                        'primary_color' => [
    'label' => 'Primary / Button',
    'default' => '#262626',
],
'primary_hover_color' => [
    'label' => 'Button Hover',
    'default' => '#404040',
],
'accent_color' => [
    'label' => 'Accent',
    'default' => '#525252',
],
'button_text_color' => [
    'label' => 'Button Text',
    'default' => '#ffffff',
],
'sidebar_color' => [
    'label' => 'Sidebar Background',
    'default' => '#262626',
],
'sidebar_text_color' => [
    'label' => 'Sidebar Text',
    'default' => '#ffffff',
],
'page_background_color' => [
    'label' => 'Page Background',
    'default' => '#f5f5f5',
],
'card_color' => [
    'label' => 'Card Background',
    'default' => '#ffffff',
],
'text_color' => [
    'label' => 'Main Text',
    'default' => '#262626',
],
'border_color' => [
    'label' => 'Borders',
    'default' => '#e5e5e5',
],
                    ];
                @endphp

                <div class="le-settings__grid">
                    @foreach($colors as $field => $color)
                        @php
                            $colorValue = old(
                                $field,
                                $settings->{$field} ?: $color['default']
                            );

                            if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $colorValue)) {
                                $colorValue = $color['default'];
                            }
                        @endphp

                        <div class="le-settings__field">
                            <label
                                class="le-settings__label"
                                for="{{ $field }}"
                            >
                                {{ $color['label'] }}
                            </label>

                            <div class="le-settings__color-field">
                                <input
                                    type="color"
                                    id="{{ $field }}"
                                    name="{{ $field }}"
                                    value="{{ $colorValue }}"
                                    data-color-picker
                                    aria-label="{{ $color['label'] }}"
                                >

                                <span
                                    class="le-settings__color-value"
                                    data-color-value="{{ $field }}"
                                >{{ $colorValue }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Live theme preview --}}
                <div class="mt-4">
                    <h3 class="le-settings__section-title mb-3">
                        Theme Preview
                    </h3>

                    <div class="le-settings__preview" id="themePreview">
                        <div class="le-settings__preview-card">
                            <p class="mb-2">
                                <strong>LoanEngine Dashboard</strong>
                            </p>

                            <p class="mb-3">
                                This preview shows how your selected colours may look.
                            </p>

                            <p class="le-settings__preview-accent mb-3">
                                Accent text preview
                            </p>

                            <button
                                type="button"
                                class="le-settings__preview-button"
                            >
                                Primary Button
                            </button>
                        </div>
                    </div>

                    <small class="le-settings__help">
                        Preview changes are temporary until you save the settings.
                    </small>
                </div>
            </div>
        </section>

        {{-- Contact details --}}
        <section class="le-settings__section">
            <div class="le-settings__section-header">
                <div class="le-settings__section-icon" aria-hidden="true">✉</div>
                <div>
                    <h2 class="le-settings__section-title">Contact Details</h2>
                    <p class="le-settings__section-description">
                        Set the official contact information for your website.
                    </p>
                </div>
            </div>

            <div class="le-settings__section-body">
                <div class="le-settings__grid">

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="contact_email">
                            Contact Email
                        </label>
                        <input
                            id="contact_email"
                            type="email"
                            name="contact_email"
                            value="{{ old('contact_email', $settings->contact_email) }}"
                            maxlength="255"
                            placeholder="support@example.com"
                        >
                    </div>

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="contact_phone">
                            Contact Phone
                        </label>
                        <input
                            id="contact_phone"
                            type="text"
                            name="contact_phone"
                            value="{{ old('contact_phone', $settings->contact_phone) }}"
                            maxlength="30"
                            placeholder="+91..."
                        >
                    </div>

                    <div class="le-settings__field le-settings__field--full">
                        <label class="le-settings__label" for="contact_address">
                            Business Address
                        </label>
                        <textarea
                            id="contact_address"
                            name="contact_address"
                            rows="3"
                            maxlength="500"
                            placeholder="Enter your business address"
                        >{{ old('contact_address', $settings->contact_address) }}</textarea>
                    </div>

                </div>
            </div>
        </section>

        {{-- Social media --}}
        <section class="le-settings__section">
            <div class="le-settings__section-header">
                <div class="le-settings__section-icon" aria-hidden="true">↗</div>
                <div>
                    <h2 class="le-settings__section-title">Social Media</h2>
                    <p class="le-settings__section-description">
                        Add your official social media profile URLs.
                    </p>
                </div>
            </div>

            <div class="le-settings__section-body">
                <div class="le-settings__grid">

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="facebook_url">
                            Facebook URL
                        </label>
                        <input
                            id="facebook_url"
                            type="url"
                            name="facebook_url"
                            value="{{ old('facebook_url', $settings->facebook_url) }}"
                            maxlength="255"
                            placeholder="https://facebook.com/yourpage"
                        >
                    </div>

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="instagram_url">
                            Instagram URL
                        </label>
                        <input
                            id="instagram_url"
                            type="url"
                            name="instagram_url"
                            value="{{ old('instagram_url', $settings->instagram_url) }}"
                            maxlength="255"
                            placeholder="https://instagram.com/yourprofile"
                        >
                    </div>

                    <div class="le-settings__field">
                        <label class="le-settings__label" for="linkedin_url">
                            LinkedIn URL
                        </label>
                        <input
                            id="linkedin_url"
                            type="url"
                            name="linkedin_url"
                            value="{{ old('linkedin_url', $settings->linkedin_url) }}"
                            maxlength="255"
                            placeholder="https://linkedin.com/company/yourpage"
                        >
                    </div>

                </div>
            </div>
        </section>

        {{-- Save actions --}}
        <div class="le-settings__actions">
            <a
                href="{{ route('admin.dashboard') }}"
                class="le-settings__button le-settings__button--secondary"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="le-settings__button le-settings__button--primary"
                id="saveSettingsButton"
            >
                <span>Save Website Settings</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('websiteSettingsForm');

    if (!form) {
        return;
    }

    // Update the hex label when a colour is selected.
    form.querySelectorAll('[data-color-picker]').forEach(function (picker) {
        const label = form.querySelector(
            '[data-color-value="' + picker.name + '"]'
        );

        function updateColor() {
            if (label) {
                label.textContent = picker.value.toUpperCase();
            }

            updatePreview();
        }

        picker.addEventListener('input', updateColor);
        updateColor();
    });

    // Live preview of the selected theme colours.
    function updatePreview() {
        const preview = document.getElementById('themePreview');

        if (!preview) {
            return;
        }

        const getColor = function (name, fallback) {
            const input = form.elements.namedItem(name);
            return input && input.value ? input.value : fallback;
        };

        preview.style.setProperty(
            '--page-background-color',
            getColor('page_background_color', '#f3f4f6')
        );

        preview.style.setProperty(
            '--card-color',
            getColor('card_color', '#ffffff')
        );

        preview.style.setProperty(
            '--border-color',
            getColor('border_color', '#e5e7eb')
        );

        preview.style.setProperty(
            '--text-color',
            getColor('text_color', '#111827')
        );

        preview.style.setProperty(
            '--primary-color',
            getColor('primary_color', '#2563eb')
        );

        preview.style.setProperty(
            '--primary-hover-color',
            getColor('primary_hover_color', '#1d4ed8')
        );

        preview.style.setProperty(
            '--button-text-color',
            getColor('button_text_color', '#ffffff')
        );

        preview.style.setProperty(
            '--accent-color',
            getColor('accent_color', '#0ea5e9')
        );
    }

    // Preview a selected image before uploading it.
    const allowedExtensions = {
        logo: ['png', 'jpg', 'jpeg', 'webp'],
        dark_logo: ['png', 'jpg', 'jpeg', 'webp'],
        favicon: ['png', 'ico'],
        apple_icon: ['png', 'jpg', 'jpeg', 'webp'],
        android_icon: ['png', 'jpg', 'jpeg', 'webp']
    };

    Object.keys(allowedExtensions).forEach(function (fieldName) {
        const input = form.elements.namedItem(fieldName);
        const filename = form.querySelector(
            '[data-file-name="' + fieldName + '"]'
        );

        if (!input) {
            return;
        }

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) {
                if (filename) {
                    filename.textContent = '';
                }
                return;
            }

            const file = input.files[0];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions[fieldName].includes(extension)) {
                input.value = '';

                if (filename) {
                    filename.textContent = 'Unsupported file type.';
                }

                return;
            }

            if (filename) {
                filename.textContent = 'Selected: ' + file.name;
            }

            if (fieldName === 'favicon' && extension === 'ico') {
                return;
            }

            const previewContainer = input
                .closest('.le-settings__upload')
                ?.querySelector('.le-settings__image-preview');

            if (!previewContainer) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                let image = previewContainer.querySelector('img');

                if (!image) {
                    image = document.createElement('img');
                    image.alt = 'Selected ' + fieldName + ' preview';

                    previewContainer.replaceChildren(image);
                }

                image.src = event.target.result;
            };

            reader.readAsDataURL(file);
        });
    });

    updatePreview();

    // Prevent accidental double submissions.
    form.addEventListener('submit', function () {
        const button = document.getElementById('saveSettingsButton');

        if (button) {
            button.disabled = true;
            button.querySelector('span').textContent = 'Saving...';
        }
    });
});
</script>
@endpush
