<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the administrator's read-only profile.
     */
    public function show(Request $request): View
    {
        return view('admin.profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the profile edit form.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Validate and update the administrator's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],

            'mobile' => [
                'nullable',
                'string',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'mobile')->ignore($user->getKey()),
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'remove_photo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'mobile.regex' => 'Enter a valid 10-digit mobile number.',
            'mobile.unique' => 'This mobile number is already registered.',
            'email.unique' => 'This email address is already registered.',
            'profile_photo.image' => 'Please select a valid image.',
            'profile_photo.mimes' => 'Photo must be JPG, PNG or WebP.',
            'profile_photo.max' => 'Photo size must not exceed 2 MB.',
        ]);

        $oldPhoto = $user->profile_photo_path;
        $newPhoto = null;

        /*
         * Store the new image before updating the database.
         * The old image is retained until the database update succeeds.
         */
        try {
            if ($request->hasFile('profile_photo')) {
                $newPhoto = $request->file('profile_photo')
                    ->store('profile-photos', 'public');

                if (! $newPhoto) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'profile_photo' => 'Unable to save the uploaded photo. Please try again.',
                        ]);
                }
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->mobile = $validated['mobile'] ?? null;
            $user->city = $validated['city'] ?? null;

            if ($newPhoto !== null) {
                $user->profile_photo_path = $newPhoto;
            } elseif (
                $request->boolean('remove_photo')
                && ! $request->hasFile('profile_photo')
            ) {
                $user->profile_photo_path = null;
            }

            $user->save();
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('public')->delete($newPhoto);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'profile' => 'Your profile could not be saved. Please try again.',
                ]);
        }

        /*
         * Delete the old image only after the profile was saved.
         */
        $photoWasReplaced = $newPhoto !== null;
        $photoWasRemoved = $request->boolean('remove_photo')
            && ! $request->hasFile('profile_photo');

        if (
            $oldPhoto
            && ($photoWasReplaced || $photoWasRemoved)
            && $oldPhoto !== $user->profile_photo_path
        ) {
            try {
                Storage::disk('public')->delete($oldPhoto);
            } catch (Throwable $exception) {
                // Keep the successful profile update; log cleanup failure.
                report($exception);
            }
        }

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
