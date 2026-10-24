
@extends('layouts.admin')

@section('title', 'Edit Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-profile.css') }}">
@endpush

@section('content')
@php
    $photoUrl = $user->profile_photo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path)
        : null;
@endphp

<div class="admin-profile-page">

    {{-- Page heading --}}
    <div class="admin-profile-heading">
        <div class="admin-profile-heading-copy">
            <span class="admin-profile-eyebrow">ACCOUNT SETTINGS</span>
            <h1>Edit Profile</h1>
            <p>Update your personal information and profile photo.</p>
        </div>

        <a href="{{ route('admin.profile.show') }}"
           class="admin-profile-button admin-profile-button-light">
            ← Back to Profile
        </a>
    </div>

    {{-- Validation summary --}}
    @if ($errors->any())
        <div class="admin-profile-alert admin-profile-alert-error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.profile.update') }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        {{-- Profile photo --}}
        <section class="admin-profile-card">

            <div class="admin-profile-card-heading">
                <div class="admin-profile-section-title">
                    <span class="admin-profile-section-icon">01</span>
                    <div>
                        <h2>Profile Photo</h2>
                        <p>Choose a clear photo for your administrator account.</p>
                    </div>
                </div>
            </div>

            <div class="admin-profile-photo-editor">

                <div class="admin-profile-avatar admin-profile-avatar-large"
                     id="profilePhotoPreview">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Current profile photo">
                    @else
                        <span>
                            {{ strtoupper(substr($user->name ?: 'A', 0, 1)) }}
                        </span>
                    @endif
                </div>

                <div class="admin-profile-photo-controls">

                    <label for="profile_photo" class="admin-profile-field-label">
                        Upload new photo
                    </label>

                    <input
                        type="file"
                        id="profile_photo"
                        name="profile_photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <small class="admin-profile-help">
                        JPG, PNG or WebP. Maximum file size: 2 MB.
                    </small>

                    @error('profile_photo')
                        <small class="admin-profile-field-error">
                            {{ $message }}
                        </small>
                    @enderror

                    @if ($photoUrl)
                        <label class="admin-profile-remove-photo">
                            <input
                                type="checkbox"
                                name="remove_photo"
                                value="1"
                                @checked(old('remove_photo'))
                                id="removeProfilePhoto"
                            >
                            <span>Remove current profile photo</span>
                        </label>
                    @endif

                </div>
            </div>
        </section>

        {{-- Personal information --}}
        <section class="admin-profile-card">

            <div class="admin-profile-card-heading">
                <div class="admin-profile-section-title">
                    <span class="admin-profile-section-icon">02</span>
                    <div>
                        <h2>Personal Information</h2>
                        <p>Manage your name, email address and contact details.</p>
                    </div>
                </div>
            </div>

            <div class="admin-profile-form-grid">

                <div class="admin-profile-field">
                    <label for="name">
                        Full Name <span class="admin-profile-required">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="120"
                        autocomplete="name"
                        required
                    >

                    @error('name')
                        <small class="admin-profile-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="admin-profile-field">
                    <label for="email">
                        Email Address <span class="admin-profile-required">*</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >

                    @error('email')
                        <small class="admin-profile-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="admin-profile-field">
                    <label for="mobile">Phone Number</label>

                    <input
                        type="tel"
                        id="mobile"
                        name="mobile"
                        value="{{ old('mobile', $user->mobile) }}"
                        inputmode="numeric"
                        pattern="[0-9]{10}"
                        minlength="10"
                        maxlength="10"
                        autocomplete="tel"
                        placeholder="Enter 10-digit mobile number"
                    >

                    <small class="admin-profile-help">
                        Enter exactly 10 digits, without country code or spaces.
                    </small>

                    @error('mobile')
                        <small class="admin-profile-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="admin-profile-field">
                    <label for="city">City</label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        value="{{ old('city', $user->city) }}"
                        maxlength="100"
                        autocomplete="address-level2"
                        placeholder="Enter your city"
                    >

                    @error('city')
                        <small class="admin-profile-field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- Account information --}}
        <section class="admin-profile-card">

            <div class="admin-profile-card-heading">
                <div class="admin-profile-section-title">
                    <span class="admin-profile-section-icon">03</span>
                    <div>
                        <h2>Account Information</h2>
                        <p>Your administrator access details.</p>
                    </div>
                </div>
            </div>

            <div class="admin-profile-details">

                <div class="admin-profile-detail">
                    <span class="admin-profile-detail-label">Account Role</span>
                    <strong>Administrator</strong>
                </div>

                <div class="admin-profile-detail">
                    <span class="admin-profile-detail-label">Member Since</span>
                    <strong>
                        {{ $user->created_at?->format('d M Y') ?? 'Not available' }}
                    </strong>
                </div>

            </div>

            <p class="admin-profile-help admin-profile-role-note">
                Your administrator role cannot be changed from this page.
            </p>
        </section>

        {{-- Form actions --}}
        <div class="admin-profile-form-actions">
            <a href="{{ route('admin.profile.show') }}"
               class="admin-profile-button admin-profile-button-light">
                Cancel
            </a>

            <button type="submit" class="admin-profile-button">
                Save Changes →
            </button>
        </div>

    </form>
</div>

{{-- Local image preview; actual upload is handled by Laravel --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('profilePhotoPreview');
    const removeCheckbox = document.getElementById('removeProfilePhoto');

    if (!input || !preview) return;

    let previewUrl = null;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
            input.value = '';
            alert('Choose a JPG, PNG or WebP image up to 2 MB.');
            return;
        }

        if (previewUrl) URL.revokeObjectURL(previewUrl);

        previewUrl = URL.createObjectURL(file);
        preview.replaceChildren();

        const image = document.createElement('img');
        image.src = previewUrl;
        image.alt = 'New profile photo preview';
        preview.appendChild(image);

        if (removeCheckbox) {
            removeCheckbox.checked = false;
        }
    });

    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function () {
            if (!removeCheckbox.checked) return;

            input.value = '';
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            preview.replaceChildren();

            const initials = document.createElement('span');
            initials.textContent = @json(strtoupper(substr($user->name ?: 'A', 0, 1)));
            preview.appendChild(initials);
        });
    }
});
</script>
@endsection
