<?php

namespace LoanEngine\Auth\Http\Controllers\Api;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use LoanEngine\Auth\Http\Requests\LoginRequest;
use LoanEngine\Auth\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        abort_unless(config('loanengine-auth.registration_enabled'), 404);
        $model = config('auth.providers.users.model');

        $user = $model::query()->create([
            'name' => $request->string('name')->trim()->toString(),
            'email' => mb_strtolower($request->string('email')->trim()->toString()),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => 'user',
        ]);

        event(new Registered($user));
        $token = $user->createToken(config('loanengine-auth.token_name'), ['*'], now()->addMinutes(config('loanengine-auth.token_expiration_minutes')));

        return response()->json([
            'message' => 'Account created successfully.',
            'user' => $user,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $model = config('auth.providers.users.model');
        $email = mb_strtolower($request->string('email')->trim()->toString());
        $user = $model::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
        }

        $token = $user->createToken(config('loanengine-auth.token_name'), ['*'], now()->addMinutes(config('loanengine-auth.token_expiration_minutes')));

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(['user' => request()->user()]);
    }

    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }
}
