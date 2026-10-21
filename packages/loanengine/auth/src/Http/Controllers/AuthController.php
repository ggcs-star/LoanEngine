<?php

namespace LoanEngine\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('loanengine-auth::login');
    }

    public function showRegister(): View
    {
        return view('loanengine-auth::register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'channel_name' => ['required', 'string', 'max:120'],
            'channel_url' => ['nullable', 'url', 'max:2048'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'address' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'mobile.required' => 'Please enter your 10-digit mobile number.',
            'mobile.regex' => 'Mobile number must contain exactly 10 digits.',
            'channel_url.url' => 'Enter a valid URL, including https://.',
        ]);

        User::create([
            'name' => trim($validated['name']),
            'channel_name' => trim($validated['channel_name']),
            'channel_url' => $validated['channel_url'] ?? null,
            'email' => strtolower($validated['email']),
            'mobile' => $validated['mobile'],
            'address' => trim($validated['address']),
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        return redirect()->route('auth.login')
            ->with('status', 'Account created successfully. Please sign in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = strtolower($credentials['email']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();

        return redirect()->intended(
            $user->role === 'admin'
                ? route('admin.dashboard')
                : route('user.dashboard')
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
