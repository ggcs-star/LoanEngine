<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Authentication') | LoanEngine</title>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v=4">
</head>
<body>
    <main class="auth-layout">

        <aside class="auth-visual">
            
        <a href="{{ route('auth.login') }}" class="guest-brand">
            <img
                src="{{ asset('images/loanengine-logo.png') }}"
                alt="LoanEngine"
                class="guest-brand-logo"
            >
        </a>


            <div class="auth-visual-content">
                <span class="auth-eyebrow">LOAN MANAGEMENT PLATFORM</span>

                <h2>
                    Simplify Your<br>
                    <span>Loan Operations.</span>
                </h2>

                <p class="auth-visual-copy">
                    Loan Engine helps you manage applications, approvals,
                    disbursals, and repayments — all in one powerful platform.
                </p>

                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="auth-feature-icon">▤</div>
                        <div>
                            <strong>Manage Applications</strong>
                            <p>Track and process loan applications from start to finish.</p>
                        </div>
                    </div>

                    <div class="auth-feature">
                        <div class="auth-feature-icon">ϟ</div>
                        <div>
                            <strong>Faster Approvals</strong>
                            <p>Automate workflows and reduce turnaround time.</p>
                        </div>
                    </div>

                    <div class="auth-feature">
                        <div class="auth-feature-icon">▥</div>
                        <div>
                            <strong>Real-time Insights</strong>
                            <p>Get detailed reports and analytics for better decisions.</p>
                        </div>
                    </div>

                    <div class="auth-feature">
                        <div class="auth-feature-icon">⬟</div>
                        <div>
                            <strong>Secure &amp; Reliable</strong>
                            <p>Your data is protected with advanced security controls.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-visual-footer">
                Simple. Secure. Reliable.
            </div>
        </aside>

        <section class="auth-main">
            <div class="auth-panel">
                <div class="auth-mobile-brand">LoanEngine</div>

                @yield('content')
            </div>
        </section>

    </main>
    
<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-password-toggle]');

    if (!button) return;

    const inputId = button.dataset.passwordToggle;
    const input = document.getElementById(inputId);

    if (!input) return;

    const shouldShow = input.type === 'password';

    input.type = shouldShow ? 'text' : 'password';
    button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
    button.setAttribute('aria-pressed', String(shouldShow));
});
</script>

</body>
</html>