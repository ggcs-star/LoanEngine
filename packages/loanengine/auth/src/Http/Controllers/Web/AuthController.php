<?php

namespace LoanEngine\Auth\Http\Controllers\Web;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use LoanEngine\Auth\Http\Requests\LoginRequest;
use LoanEngine\Auth\Http\Requests\RegisterRequest;
use LoanEngine\Auth\Support\AuthActivityLogger;

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
        $credentials = $request->safe()->only([
            'email',
            'password',
        ]);

        if (! Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            AuthActivityLogger::record(
                $request,
                'login_failed',
                null,
                $request->input('email'),
                401
            );

            return back()
                ->withErrors(['email' => __('auth.failed')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        AuthActivityLogger::record(
            $request,
            'login_success',
            $user->getAuthIdentifier(),
            $user->email,
            200,
            [
                'login_method' => 'web',
                'remember' => $request->boolean('remember'),
                'device_info' => $this->getClientDeviceInfo($request),
            ]
        );

        return redirect()->intended(
            config('loanengine-auth.redirect_after_login', '/dashboard')
        );
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        abort_unless(config('loanengine-auth.registration_enabled'), 404);

        $model = config('auth.providers.users.model');

        $user = $model::query()->create([
            'name' => $request->string('name')->trim()->toString(),

            'email' => mb_strtolower(
                $request->string('email')->trim()->toString()
            ),

            'password' => Hash::make(
                $request->string('password')->toString()
            ),

            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        AuthActivityLogger::record(
            $request,
            'register_success',
            $user->getAuthIdentifier(),
            $user->email,
            201,
            [
                'registration_method' => 'web',
                'device_info' => $this->getClientDeviceInfo($request),
            ]
        );

        return redirect(
            config('loanengine-auth.redirect_after_register', '/dashboard')
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if ($user) {
            AuthActivityLogger::record(
                $request,
                'logout',
                $user->getAuthIdentifier(),
                $user->email,
                200,
                [
                    'logout_method' => 'web',
                    'device_info' => $this->getClientDeviceInfo($request),
                ]
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    /**
     * Read browser-provided device information.
     */
    private function getClientDeviceInfo(Request $request): array
    {
        $info = $request->input('client_device_info');

        if (is_string($info)) {
            $decoded = json_decode($info, true);

            if (is_array($decoded)) {
                $info = $decoded;
            }
        }

        if (! is_array($info)) {
            return [];
        }

        return [
            'platform' => $info['browser_platform'] ?? null,
            'platform_version' => $info['platform_version'] ?? null,
            'device_model' => $info['device_model'] ?? null,
            'browser_version' => $info['browser_version'] ?? null,
            'device_type' => $info['device_type'] ?? null,
            'mobile' => $info['mobile'] ?? null,
            'language' => $info['language'] ?? null,
            'timezone' => $info['timezone'] ?? null,
            'screen_width' => $info['screen_width'] ?? null,
            'screen_height' => $info['screen_height'] ?? null,
        ];
    }
}
