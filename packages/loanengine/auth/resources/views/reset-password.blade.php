
@extends('loanengine-auth::layouts.guest')

@section('title', 'Reset Password')

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

    <h1>Reset password</h1>

    <p class="auth-subtitle">
        Create a new password for your LoanEngine account.
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

    <form method="POST" action="{{ route('password.reset.update') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="password">New Password</label>

            <div class="password-wrapper">
                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Enter new password"
                    required
                    autofocus
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
            <label for="password_confirmation">Confirm New Password</label>

            <div class="password-wrapper">
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    minlength="8"
                    autocomplete="new-password"
                    placeholder="Re-enter new password"
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

            @error('password_confirmation')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-primary">
            Update Password →
        </button>
    </form>

    <div class="auth-switch auth-switch-spaced">
        Remember your password?
        <a href="{{ route('auth.login') }}">Sign in</a>
    </div>
@endsection
