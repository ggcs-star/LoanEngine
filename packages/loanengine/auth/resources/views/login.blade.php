
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

    <form
        method="POST"
        action="{{ route('auth.login.submit') }}"
        class="auth-form"
        id="loginForm"
    >
        @csrf

        {{-- Browser and device information --}}
        <input
            type="hidden"
            name="client_device_info"
            id="client_device_info"
            value=""
        >

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
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
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

        <button type="submit" class="btn-primary" id="loginButton">
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

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('loginForm');
    const deviceInput = document.getElementById('client_device_info');
    const loginButton = document.getElementById('loginButton');

    if (!form || !deviceInput) {
        return;
    }

    let submitting = false;

    function detectDeviceType(userAgent) {
        if (/iPad|Tablet/i.test(userAgent)) {
            return 'tablet';
        }

        if (/Mobi|Android|iPhone|iPod/i.test(userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    async function collectDeviceInfo() {
        const ua = navigator.userAgent || '';

        const info = {
            user_agent: ua,
            platform: navigator.platform || null,
            device_type: detectDeviceType(ua),
            browser_platform: null,
            platform_version: null,
            device_model: null,
            browser_version: null,
            mobile: null,
            language: navigator.language || null,
            languages: Array.isArray(navigator.languages)
                ? navigator.languages
                : [],
            screen_width: window.screen ? window.screen.width : null,
            screen_height: window.screen ? window.screen.height : null,
            timezone: null
        };

        try {
            info.timezone =
                Intl.DateTimeFormat().resolvedOptions().timeZone || null;
        } catch (error) {
            info.timezone = null;
        }

        try {
            if (navigator.userAgentData) {
                const data = await navigator.userAgentData.getHighEntropyValues([
                    'platform',
                    'platformVersion',
                    'model',
                    'uaFullVersion'
                ]);

                info.browser_platform = data.platform || null;
                info.platform_version = data.platformVersion || null;
                info.device_model = data.model || null;
                info.browser_version = data.uaFullVersion || null;
                info.mobile = data.mobile;
            }
        } catch (error) {
            console.warn('Extended device information unavailable.');
        }

        return info;
    }

    form.addEventListener('submit', async function (event) {
        if (submitting) {
            return;
        }

        event.preventDefault();

        if (loginButton) {
            loginButton.disabled = true;
            loginButton.textContent = 'Please wait...';
        }

        try {
            const deviceInfo = await collectDeviceInfo();

            deviceInput.value = JSON.stringify(deviceInfo);
        } catch (error) {
            deviceInput.value = JSON.stringify({
                user_agent: navigator.userAgent || '',
                platform: navigator.platform || null,
                device_type: detectDeviceType(navigator.userAgent || '')
            });
        }

        submitting = true;

        // Native submission avoids triggering this submit listener again.
        HTMLFormElement.prototype.submit.call(form);
    });
})();
</script>
@endpush
