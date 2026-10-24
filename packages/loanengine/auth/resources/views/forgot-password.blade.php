
@extends('loanengine-auth::layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <a href="{{ route('auth.login') }}" class="back-link">
        ← Back to login
    </a>

    <div class="forgot-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">
            <rect x="5" y="10" width="14" height="11" rx="2"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            <circle cx="12" cy="15" r="1"/>
        </svg>
    </div>

    <h1>Forgot password?</h1>

    <p class="auth-subtitle">
        Enter your registered email address to receive a secure verification code.
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

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="Enter your registered email"
                autocomplete="email"
                maxlength="255"
                required
                autofocus
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-primary">
            Send OTP →
        </button>
    </form>

    <div class="auth-switch auth-switch-spaced">
        Remember your password?
        <a href="{{ route('auth.login') }}">Sign in</a>
    </div>
@endsection
