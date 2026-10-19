<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LoanEngine | Account</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="brand">LoanEngine</div>
            <h1>You are logged in</h1>
            <p class="muted">Your account authentication is working.</p>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button class="btn" type="submit">Logout</button>
            </form>
        </section>
    </main>
</body>
</html>
