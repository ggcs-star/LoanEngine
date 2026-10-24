<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('user.profile.show', [
            'user' => $request->user(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('user.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile' => [
                'nullable',
                'string',
                'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
            'city' => ['nullable', 'string', 'max:100'],
            'employment_type' => [
                'nullable',
                Rule::in([
                    'salaried',
                    'self_employed',
                    'business',
                    'student',
                    'unemployed',
                    'other',
                ]),
            ],
            'annual_income' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000000',
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->mobile = $validated['mobile'] ?? null;
        $user->city = $validated['city'] ?? null;
        $user->employment_type = $validated['employment_type'] ?? null;
        $user->annual_income = $validated['annual_income'] ?? null;

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            if ($user->profile_photo_path) {
                Storage::disk('public')->delete(
                    $user->profile_photo_path
                );
            }

            $user->profile_photo_path = $path;
        }

        $user->save();

        return redirect()
            ->route('user.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);

            $user->profile_photo_path = null;
            $user->save();
        }

        return redirect()
            ->route('user.profile.edit')
            ->with('success', 'Profile photo removed successfully.');
    }
}
