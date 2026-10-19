@extends('loanengine-auth::layouts.guest')

@section('title', 'Register')

@section('content')
    <h1>Create your account</h1>

    <p class="auth-subtitle">
        Register to get started with LoanEngine.
    </p>

    <div class="auth-tabs">
        <a class="auth-tab" href="{{ route('auth.login') }}">Sign In</a>
        <a class="auth-tab is-active" href="{{ route('auth.register') }}">Register</a>
    </div>

    @if ($errors->any())
        <div class="notice notice-error" role="alert">
            Please correct the errors below.
        </div>
    @endif

    <form method="POST"
          action="{{ route('auth.register.submit') }}"
          class="auth-form">

        @csrf

        <div class="field">
            <label for="name">Full Name</label>

            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                autocomplete="name"
                maxlength="100"
                required
            >

            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-grid-2">
            <div class="field">
                <label for="channel_name">Channel Name</label>

                <input
                    id="channel_name"
                    name="channel_name"
                    type="text"
                    value="{{ old('channel_name') }}"
                    placeholder="Enter channel name"
                    maxlength="150"
                    required
                >

                @error('channel_name')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="channel_url">
                    Channel URL <span class="optional">(Optional)</span>
                </label>

                <input
                    id="channel_url"
                    name="channel_url"
                    type="url"
                    value="{{ old('channel_url') }}"
                    placeholder="https://example.com"
                    maxlength="2048"
                >

                @error('channel_url')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="auth-grid-2">
            <div class="field">
                <label for="email">Email Address</label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    autocomplete="email"
                    maxlength="255"
                    required
                >

                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="mobile">Mobile Number</label>

                <div class="phone-input">
                    <span class="phone-prefix">+91</span>

                    <input
                        id="mobile"
                        name="mobile"
                        type="tel"
                        value="{{ old('mobile') }}"
                        placeholder="10-digit number"
                        autocomplete="tel-national"
                        inputmode="numeric"
                        pattern="[6-9][0-9]{9}"
                        maxlength="10"
                        required
                    >
                </div>

                @error('mobile')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="field">
            <label for="address">Full Address</label>

            <textarea
                id="address"
                name="address"
                placeholder="Enter your full address"
                autocomplete="street-address"
                maxlength="1000"
                required
            >{{ old('address') }}</textarea>

            @error('address')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        
<div class="auth-grid-2">
    <div class="field">
        <label for="password">Password</label>

        <div class="password-wrapper">
            <input
                id="password"
                name="password"
                type="password"
                placeholder="Create a password"
                autocomplete="new-password"
                minlength="8"
                required
            >

            <button
                type="button"
                class="password-toggle"
                data-password-toggle="password"
                aria-label="Show password"
                aria-pressed="false"
            >
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        @error('password')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="field">
        <label for="password_confirmation">Confirm Password</label>

        <div class="password-wrapper">
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                placeholder="Confirm your password"
                autocomplete="new-password"
                minlength="8"
                required
            >

            <button
                type="button"
                class="password-toggle"
                data-password-toggle="password_confirmation"
                aria-label="Show password"
                aria-pressed="false"
            >
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>
    </div>
</div>


        <button type="submit" class="btn-primary">
            Create Account
        </button>
    </form>

    <div class="auth-switch auth-switch-spaced">
        Already registered?
        <a href="{{ route('auth.login') }}">Sign in</a>
    </div>
@endsection