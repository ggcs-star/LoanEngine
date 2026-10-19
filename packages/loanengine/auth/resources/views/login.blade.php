@extends('loanengine-auth::layouts.guest')

@section('title', 'Login')

@section('content')
    <h1>Welcome to Loan Engine</h1>

    <p class="auth-subtitle">
        Sign in to access your dashboard and continue managing your lending operations.
    </p>

    <div class="auth-tabs">
        <a class="auth-tab is-active" href="{{ route('auth.login') }}">
            Sign In
        </a>
        <a class="auth-tab" href="{{ route('auth.register') }}">
            Register
        </a>
    </div>

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

    <form method="POST"
          action="{{ route('auth.login.submit') }}"
          class="auth-form">
        @csrf

        <div class="field">
            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="Enter your email address"
                autocomplete="email"
                required
                autofocus
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        
        <div class="field">
            <label for="password">Password</label>

            <div class="password-wrapper">
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
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


        <div class="form-options">
            <label class="check-label" for="remember">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    value="1"
                    @checked(old('remember'))
                >
                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            Sign In
        </button>
    </form>

    <div class="auth-divider">
        <span></span>
        <p>Secure access to your workspace</p>
        <span></span>
    </div>

    <div class="auth-switch">
        Don't have an account?
        <a href="{{ route('auth.register') }}">Register here</a>
    </div>
@endsection