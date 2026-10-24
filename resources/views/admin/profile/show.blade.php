
@extends('layouts.admin')

@section('title', 'My Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-profile.css') }}">
@endpush

@section('content')
@php
    $photoUrl = $user->profile_photo_path
        ? url('storage/' . $user->profile_photo_path)
        : null;
@endphp

<div class="admin-profile-page">

    {{-- Page heading --}}
    <div class="admin-profile-heading">
        <div class="admin-profile-heading-copy">
            <span class="admin-profile-eyebrow">ACCOUNT SETTINGS</span>
            <h1>My Profile</h1>
            <p>View and manage your administrator account information.</p>
        </div>

        <a href="{{ route('admin.profile.edit') }}"
           class="admin-profile-button">
            <span aria-hidden="true">✎</span>
            Edit Profile
        </a>
    </div>

    {{-- Success message --}}
    @if (session('success'))
        <div class="admin-profile-alert admin-profile-alert-success"
             role="status">
            <span class="admin-profile-alert-icon" aria-hidden="true">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="admin-profile-alert admin-profile-alert-error" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Profile summary --}}
    <section class="admin-profile-card admin-profile-summary">

        <div class="admin-profile-avatar">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}"
                     alt="{{ $user->name }}'s profile photo">
            @else
                <span>
                    {{ strtoupper(substr($user->name ?: 'A', 0, 1)) }}
                </span>
            @endif
        </div>

        <div class="admin-profile-summary-info">
            <span class="admin-profile-badge">
                <span class="admin-profile-status-dot"></span>
                Administrator Account
            </span>

            <h2>{{ $user->name ?: 'Administrator' }}</h2>
            <p>{{ $user->email ?: 'No email available' }}</p>

            <span class="admin-profile-member">
                Member since
                {{ $user->created_at?->format('d M Y') ?? 'Not available' }}
            </span>
        </div>

        <!-- <a href="{{ route('admin.profile.edit') }}"
           class="admin-profile-button admin-profile-button-light">
            Edit Details
        </a> -->
    </section>

    {{-- Personal information --}}
    <section class="admin-profile-card">

        <div class="admin-profile-card-heading">
            <div class="admin-profile-section-title">
                <span class="admin-profile-section-icon" aria-hidden="true">01</span>
                <div>
                    <h2>Personal Information</h2>
                    <p>Your registered contact and account details.</p>
                </div>
            </div>

            <span class="admin-profile-number">01 / 01</span>
        </div>

        <div class="admin-profile-details">

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">Full Name</span>
                <strong>{{ $user->name ?: 'Not provided' }}</strong>
            </div>

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">Email Address</span>
                <strong>{{ $user->email ?: 'Not provided' }}</strong>
            </div>

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">Phone Number</span>
                <strong>{{ $user->mobile ?: 'Not provided' }}</strong>
            </div>

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">City</span>
                <strong>{{ $user->city ?: 'Not provided' }}</strong>
            </div>

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">Account Role</span>
                <strong class="admin-profile-role">Administrator</strong>
            </div>

            <div class="admin-profile-detail">
                <span class="admin-profile-detail-label">Email Verification</span>
                <strong>
                    {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                </strong>
            </div>

        </div>
    </section>

    {{-- Bottom action --}}
    <div class="admin-profile-bottom">
        <p>Keep your account information up to date.</p>

        <!-- <a href="{{ route('admin.profile.edit') }}"
           class="admin-profile-button">
            Edit Profile
            <span aria-hidden="true">→</span>
        </a> -->
    </div>

</div>
@endsection
