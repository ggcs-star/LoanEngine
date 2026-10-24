
@extends('user.layouts.app')

@section('title', 'Edit Profile')
@section('page_heading', 'Edit Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-profile.css') }}">
@endpush

@section('content')
@php
    $photoUrl = $user->profile_photo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path)
        : null;
@endphp

<div class="profile-page">

    {{-- Page heading --}}
    <div class="profile-heading">
        <div>
            <span class="profile-eyebrow">ACCOUNT SETTINGS</span>
            <h2>Edit Profile</h2>
            <p>Manage your profile photo, personal information and financial details.</p>
        </div>

        <!-- <a href="{{ route('user.profile.show') }}"
           class="profile-button profile-button-secondary">
            Cancel
        </a> -->
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="profile-alert profile-alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success message --}}
    @if (session('success'))
        <div class="profile-alert profile-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Main update form --}}
    <form
        id="updateProfileForm"
        method="POST"
        action="{{ route('user.profile.update') }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        {{-- Profile photo --}}
        <section class="profile-card profile-photo-editor">

            <div class="profile-photo-preview" id="profilePhotoPreview">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Current profile photo">
                @else
                    <span>
                        {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                    </span>
                @endif
            </div>

            <div class="profile-photo-controls">
                <span class="profile-photo-title">Profile Photo</span>

                <p>
                    Choose a photo to personalize your LoanEngine account.
                </p>

                <div class="profile-photo-actions">
                    <label for="profile_photo" class="profile-upload-button">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path
                                d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14.5v4A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5v-4"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Choose Photo
                    </label>

                    @if ($photoUrl)
                        <button
                            type="submit"
                            form="removeProfilePhotoForm"
                            class="profile-remove-button"
                            onclick="return confirm('Are you sure you want to remove your profile photo?')"
                        >
                            Remove Photo
                        </button>
                    @endif
                </div>

                <input
                    type="file"
                    id="profile_photo"
                    name="profile_photo"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
                >

                <small>JPG, PNG or WebP · Maximum 2 MB</small>

                @error('profile_photo')
                    <small class="profile-error">{{ $message }}</small>
                @enderror
            </div>
        </section>

        {{-- Personal information --}}
        <section class="profile-card">
            <div class="profile-card-heading">
                <div>
                    <h3>Personal Information</h3>
                    <p>Your registered contact details and personal information.</p>
                </div>

                <span class="profile-section-number">01</span>
            </div>

            <div class="profile-form-grid">

                <div class="profile-field">
                    <label for="name">Full Name <span>*</span></label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        maxlength="120"
                        autocomplete="name"
                        required
                    >

                    @error('name')
                        <small class="profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="email">Email Address <span>*</span></label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >

                    @error('email')
                        <small class="profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="mobile">Mobile Number</label>

                    <input
                        id="mobile"
                        name="mobile"
                        type="tel"
                        inputmode="numeric"
                        autocomplete="tel-national"
                        value="{{ old('mobile', $user->mobile) }}"
                        placeholder="Enter 10-digit mobile number"
                        minlength="10"
                        maxlength="10"
                        pattern="[6-9][0-9]{9}"
                        title="Enter a valid 10-digit Indian mobile number starting with 6, 7, 8 or 9"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        aria-describedby="mobile-help mobile-error"
                    >

                    <small id="mobile-help">
                        Enter 10 digits, starting with 6, 7, 8 or 9.
                    </small>

                    @error('mobile')
                        <small id="mobile-error" class="profile-error">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="city">City</label>

                    <input
                        id="city"
                        name="city"
                        type="text"
                        value="{{ old('city', $user->city) }}"
                        maxlength="100"
                        autocomplete="address-level2"
                        placeholder="Enter your city"
                    >

                    @error('city')
                        <small class="profile-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- Financial profile --}}
        <section class="profile-card">
            <div class="profile-card-heading">
                <div>
                    <h3>Financial Profile</h3>
                    <p>Tell us about your employment and annual income.</p>
                </div>

                <span class="profile-section-number">02</span>
            </div>

            <div class="profile-form-grid">

                <div class="profile-field">
                    <label for="employment_type">Employment Type</label>

                    <select id="employment_type" name="employment_type">
                        <option value="">Select employment type</option>

                        @foreach ([
                            'salaried' => 'Salaried',
                            'self_employed' => 'Self-employed',
                            'business' => 'Business Owner',
                            'student' => 'Student',
                            'unemployed' => 'Unemployed',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('employment_type', $user->employment_type) === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @error('employment_type')
                        <small class="profile-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="profile-field">
                    <label for="annual_income">Annual Income (₹)</label>

                    <input
                        id="annual_income"
                        name="annual_income"
                        type="number"
                        min="0"
                        max="1000000000"
                        step="1"
                        value="{{ old('annual_income', $user->annual_income) }}"
                        placeholder="Enter your annual income"
                    >

                    <small>Enter your annual income in Indian rupees.</small>

                    @error('annual_income')
                        <small class="profile-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- Form actions --}}
        <div class="profile-form-actions">
            <a href="{{ route('user.profile.show') }}"
               class="profile-button profile-button-secondary">
                Cancel
            </a>

            <button
                type="submit"
                class="profile-button profile-button-primary"
                id="saveProfileButton"
            >
                Save Changes
            </button>
        </div>

    </form>

    {{-- Separate remove-photo form: must stay outside the main form --}}
    @if ($photoUrl)
        <form
            id="removeProfilePhotoForm"
            method="POST"
            action="{{ route('user.profile.photo.destroy') }}"
            hidden
        >
            @csrf
            @method('DELETE')
        </form>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('profilePhotoPreview');

    if (!input || !preview) {
        return;
    }

    let previewUrl = null;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {
            input.value = '';
            alert('Please select a JPG, PNG or WebP image.');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            input.value = '';
            alert('Profile photo must be 2 MB or smaller.');
            return;
        }

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        previewUrl = URL.createObjectURL(file);

        const image = document.createElement('img');
        image.src = previewUrl;
        image.alt = 'Selected profile photo preview';

        preview.replaceChildren(image);
    });
});
</script>
@endpush
