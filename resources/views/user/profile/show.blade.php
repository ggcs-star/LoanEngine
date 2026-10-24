
@extends('user.layouts.app')

@section('title', 'My Profile')
@section('page_heading', 'My Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-profile.css') }}">
@endpush

@section('content')
<div class="profile-page">

    <div class="profile-heading">
        <div>
            <span class="profile-eyebrow">ACCOUNT SETTINGS</span>
            <h2>My Profile</h2>
            <p>View your personal information and financial profile.</p>
        </div>

        <a href="{{ route('user.profile.edit') }}"
           class="profile-button profile-button-primary">
            Edit Profile
        </a>
    </div>

    @php
        $photoUrl = $user->profile_photo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path)
            : null;
    @endphp

    <section class="profile-card profile-summary">
        <div class="profile-photo">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="Profile photo">
            @else
                <span>
                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                </span>
            @endif
        </div>

        <div class="profile-summary-details">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
            <span class="profile-role">User Account</span>
        </div>
    </section>

    <section class="profile-card">
        <div class="profile-card-heading">
            <div>
                <h3>Personal Information</h3>
                <p>Your registered contact details.</p>
            </div>
        </div>

        <div class="profile-detail-grid">
            <div class="profile-detail">
                <span>Full Name</span>
                <strong>{{ $user->name ?: 'Not provided' }}</strong>
            </div>

            <div class="profile-detail">
                <span>Email Address</span>
                <strong>{{ $user->email ?: 'Not provided' }}</strong>
            </div>

            <div class="profile-detail">
                <span>Phone Number</span>
                <strong>{{ $user->mobile ?: 'Not provided' }}</strong>
            </div>

            <div class="profile-detail">
                <span>City</span>
                <strong>{{ $user->city ?: 'Not provided' }}</strong>
            </div>
        </div>
    </section>

    <section class="profile-card">
        <div class="profile-card-heading">
            <div>
                <h3>Financial Profile</h3>
                <p>Your employment and annual income information.</p>
            </div>
        </div>

        <div class="profile-detail-grid">
            <div class="profile-detail">
                <span>Employment Type</span>
                <strong>
                    {{ $user->employment_type
                        ? ucwords(str_replace('_', ' ', $user->employment_type))
                        : 'Not provided' }}
                </strong>
            </div>

            <div class="profile-detail">
                <span>Annual Income</span>
                <strong>
                    {{ $user->annual_income !== null
                        ? '₹' . number_format($user->annual_income)
                        : 'Not provided' }}
                </strong>
            </div>
        </div>
    </section>

    <!-- <div class="profile-bottom-actions">
        <a href="{{ route('user.profile.edit') }}"
           class="profile-button profile-button-primary">
            Edit Profile
        </a>
    </div> -->

</div>
@endsection
