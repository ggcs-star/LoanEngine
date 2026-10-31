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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use LoanEngine\Auth\Support\AuthActivityLogger;
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

        $createdUser = User::create([
            'name' => trim($validated['name']),
            'channel_name' => trim($validated['channel_name']),
            'channel_url' => $validated['channel_url'] ?? null,
            'email' => strtolower($validated['email']),
            'mobile' => $validated['mobile'],
            'address' => trim($validated['address']),
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

AuthActivityLogger::record(
    'register_success',
    (int) $createdUser->getAuthIdentifier(),
    $createdUser->email,
    ['status_code' => 201],
    $request
);
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
AuthActivityLogger::record(
    'login_failed',
    null,
    $credentials['email'],
    ['status_code' => 401],
    $request
);            return back()
                ->withErrors(['email' => 'The email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();
AuthActivityLogger::record(
    'login_success',
    $user ? (int) $user->getAuthIdentifier() : null,
    $user?->email,
    ['status_code' => 200],
    $request
);
        return redirect()->intended(
            $user->role === 'admin'
                ? route('admin.dashboard')
                : route('user.dashboard')
        );
    }
    
    public function showForgotPassword()
    {
        return view('loanengine-auth::forgot-password');
    }

    public function sendResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        try {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if (!$user) {
                return back()
                    ->withInput(['email' => $email])
                    ->withErrors([
                        'email' => 'This email is not registered with LoanEngine.',
                    ]);
            }

            $existing = DB::table('password_reset_otps')
                ->where('email', $email)
                ->first();

            if (
                $existing &&
                Carbon::parse($existing->last_sent_at)->gt(now()->subMinute())
            ) {
                return back()
                    ->withInput(['email' => $email])
                    ->withErrors([
                        'email' => 'Please wait 60 seconds before requesting another OTP.',
                    ]);
            }

            $otp = (string) random_int(100000, 999999);

            DB::table('password_reset_otps')->updateOrInsert(
                ['email' => $email],
                [
                    'otp_hash' => Hash::make($otp),
                    'expires_at' => now('UTC')->addMinutes(5),
                    'last_sent_at' => now('UTC'),
                    'attempts' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            try {
                Mail::raw(
                    "Your LoanEngine password reset OTP is: {$otp}\n\n"
                    . "This code expires in 5 minutes. Do not share it with anyone.",
                    function ($message) use ($email) {
                        $message->to($email)
                            ->subject('LoanEngine - Password Reset OTP');
                    }
                );
            } catch (\Throwable $exception) {
                report($exception);

                DB::table('password_reset_otps')
                    ->where('email', $email)
                    ->delete();

                return back()
                    ->withInput(['email' => $email])
                    ->withErrors([
                        'email' => 'Unable to send OTP. Please check your email configuration and try again later.',
                    ]);
            }

            $request->session()->forget([
                'password_reset_email',
                'password_reset_verified_email',
                'password_reset_verified_at',
                'password_reset_token',
            ]);

            $request->session()->put('password_reset_email', $email);

            return redirect()
                ->route('password.otp.form')
                ->with('status', 'OTP sent successfully to your registered email.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput(['email' => $email])
                ->withErrors([
                    'email' => 'Something went wrong. Please try again later.',
                ]);
        }
    }

    public function showVerifyResetOtp(Request $request)
    {
        if (!$request->session()->has('password_reset_email')) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Please enter your registered email first.',
                ]);
        }

        return view('loanengine-auth::verify-reset-otp');
    }



    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower(trim(
            (string) $request->session()->get('password_reset_email')
        ));

        if ($email === '') {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Your verification session expired. Please request a new OTP.',
                ]);
        }

        try {
            $result = DB::transaction(function () use ($email, $validated) {
                $record = DB::table('password_reset_otps')
                    ->where('email', $email)
                    ->lockForUpdate()
                    ->first();

                if (!$record) {
                    return 'missing';
                }

                // Compare expiry timestamps consistently in UTC.
                $expiresAt = Carbon::parse($record->expires_at, 'UTC');

                if (now('UTC')->greaterThanOrEqualTo($expiresAt)) {
                    DB::table('password_reset_otps')
                        ->where('email', $email)
                        ->delete();

                    return 'expired';
                }

                if ((int) $record->attempts >= 5) {
                    DB::table('password_reset_otps')
                        ->where('email', $email)
                        ->delete();

                    return 'attempts';
                }

                $enteredOtp = trim((string) $validated['otp']);

                if (!Hash::check($enteredOtp, (string) $record->otp_hash)) {
                    DB::table('password_reset_otps')
                        ->where('email', $email)
                        ->increment('attempts');

                    return 'invalid';
                }

                // OTP is consumed only after successful verification.
                DB::table('password_reset_otps')
                    ->where('email', $email)
                    ->delete();

                return 'success';
            });

            if ($result === 'invalid') {
                return back()->withErrors([
                    'otp' => 'Incorrect OTP. Please enter the latest code from your email.',
                ]);
            }

            if ($result === 'expired') {
                $request->session()->forget('password_reset_email');

                return redirect()
                    ->route('password.request')
                    ->withErrors([
                        'email' => 'Your OTP has expired. Please request a new one.',
                    ]);
            }

            if ($result === 'attempts') {
                $request->session()->forget('password_reset_email');

                return redirect()
                    ->route('password.request')
                    ->withErrors([
                        'email' => 'Maximum OTP attempts reached. Please request a new OTP.',
                    ]);
            }

            if ($result === 'missing') {
                $request->session()->forget('password_reset_email');

                return redirect()
                    ->route('password.request')
                    ->withErrors([
                        'email' => 'OTP record not found. Please request a new one.',
                    ]);
            }

            $request->session()->put('password_reset_verified_email', $email);
            $request->session()->put('password_reset_verified_at', now()->timestamp);
            $request->session()->put('password_reset_token', Str::random(64));
            $request->session()->forget('password_reset_email');

            return redirect()
                ->route('password.reset.form')
                ->with('status', 'Email verified successfully. You can now reset your password.');

        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'otp' => 'OTP verification failed. Please check the application log.',
            ]);
        }
    }



    public function showResetPassword(Request $request)
    {
        $verifiedAt = $request->session()->get('password_reset_verified_at');
        $verifiedEmail = $request->session()->get('password_reset_verified_email');
        $resetToken = $request->session()->get('password_reset_token');

        if (
            !$verifiedEmail ||
            !$resetToken ||
            !$verifiedAt ||
            now()->timestamp - (int) $verifiedAt > 600
        ) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_verified_email',
                'password_reset_verified_at',
                'password_reset_token',
            ]);

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Your password reset session expired. Please start again.',
                ]);
        }

        return view('loanengine-auth::reset-password');
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $email = $request->session()->get('password_reset_verified_email');
        $verifiedAt = $request->session()->get('password_reset_verified_at');
        $resetToken = $request->session()->get('password_reset_token');

        if (
            !$email ||
            !$resetToken ||
            !$verifiedAt ||
            now()->timestamp - (int) $verifiedAt > 600
        ) {
            $request->session()->forget([
                'password_reset_email',
                'password_reset_verified_email',
                'password_reset_verified_at',
                'password_reset_token',
            ]);

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Your password reset session expired. Please verify a new OTP.',
                ]);
        }

        try {
            $updated = DB::transaction(function () use ($email, $validated) {
                $user = User::whereRaw('LOWER(email) = ?', [$email])
                    ->lockForUpdate()
                    ->first();

                if (!$user) {
                    return false;
                }

                $user->password = Hash::make($validated['password']);
                $user->setRememberToken(Str::random(60));
                $user->save();

                return true;
            });

            if (!$updated) {
                return redirect()
                    ->route('password.request')
                    ->withErrors([
                        'email' => 'Unable to find this account. Please start again.',
                    ]);
            }

            $request->session()->forget([
                'password_reset_email',
                'password_reset_verified_email',
                'password_reset_verified_at',
                'password_reset_token',
            ]);

            return redirect()
                ->route('auth.login')
                ->with('status', 'Password reset successfully. Please sign in with your new password.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'password' => 'Unable to reset your password right now. Please try again.',
            ]);
        }
    }


    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
AuthActivityLogger::record(
    'logout_success',
    $user ? (int) $user->getAuthIdentifier() : null,
    $user?->email,
    ['status_code' => 200],
    $request
);
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

}
