<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Helpers\ImageHelper;
use Throwable;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        try {
            return view('admin.profile.show', [
                'user' => $request->user(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Admin profile display failed.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function edit(Request $request): View
    {
        try {
            return view('admin.profile.edit', [
                'user' => $request->user(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Admin profile edit form loading failed.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function update(Request $request): RedirectResponse
    {
        try {
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

            try {                
                if ($request->hasFile('profile_photo')) {
                    $newPhoto = ImageHelper::store(
                        $request->file('profile_photo'),
                        'profile-photos',
                        $validated['name']
                    );
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
                    try {
                        Storage::disk('public')->delete($newPhoto);
                    } catch (Throwable $cleanupException) {
                        Log::error('Failed to clean up uploaded profile photo.', [
                            'exception' => $cleanupException,
                        ]);
                    }
                }

                report($exception);

                return back()
                    ->withInput()
                    ->withErrors([
                        'profile' => 'Your profile could not be saved. Please try again.',
                    ]);
            }

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
                    report($exception);
                }
            }

            return redirect()
                ->route('admin.profile.show')
                ->with('success', 'Profile updated successfully.');
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }
}
