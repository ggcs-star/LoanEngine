
@extends('loanengine-auth::layouts.guest')

@section('title', 'Verify OTP')

@section('content')
    <a href="{{ route('password.request') }}" class="back-link">
        ← Change email
    </a>

    <div class="forgot-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m4 7 8 6 8-6"/>
        </svg>
    </div>

    <h1>Verify your email</h1>

    <p class="auth-subtitle">
        Enter the 6-digit verification code sent to your registered email address.
    </p>

    @if (session('status'))
        <div class="notice notice-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice notice-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.otp.verify') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="otp">Verification Code</label>
            <input
                id="otp"
                name="otp"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                minlength="6"
                maxlength="6"
                autocomplete="one-time-code"
                placeholder="Enter 6-digit OTP"
                required
                autofocus
            >
            @error('otp')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-primary">
            Verify OTP →
        </button>
    </form>

    <div class="auth-switch auth-switch-spaced">
        Remember your password?
        <a href="{{ route('auth.login') }}">Sign in</a>
    </div>
@endsection
