<?php

namespace LoanEngine\Auth\Http\Controllers\Web;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use LoanEngine\Auth\Http\Requests\LoginRequest;
use LoanEngine\Auth\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('loanengine-auth::auth.login');
    }

    public function showRegister(): View
    {
        abort_unless(config('loanengine-auth.registration_enabled'), 404);
        return view('loanengine-auth::auth.register');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(config('loanengine-auth.redirect_after_login', '/dashboard'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(config('loanengine-auth.registration_enabled'), 404);
        $model = config('auth.providers.users.model');

        $user = $model::query()->create([
            'name' => $request->string('name')->trim()->toString(),
            'email' => mb_strtolower($request->string('email')->trim()->toString()),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => 'customer',
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect(config('loanengine-auth.redirect_after_register', '/dashboard'));
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('loanengine-auth.login');
    }
}
