<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Check if admin is editing another user
        if ($request->has('user_id') && Auth::user()->is_admin) {
            $user = User::findOrFail($request->user_id);
        } else {
            $user = $request->user();
        }

        return view('edit_profiel', [
            'user' => $user,
        ]);
    }

    /**
     * Display a user's public profile.
     */
    public function show(User $user): View
    {
        return view('Profiel_page', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Check if admin is updating another user
        if ($request->has('user_id') && Auth::user()->is_admin) {
            $user = User::findOrFail($request->user_id);
        } else {
            $user = $request->user();
        }

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                \Storage::disk('public')->delete($user->image);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('profile-photos', 'public');
            $data['image'] = $imagePath;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Redirect based on who is updating
        if ($request->has('user_id') && Auth::user()->is_admin) {
            return Redirect::route('profile.public', $user)->with('success', 'User profile updated successfully!');
        } else {
            return Redirect::route('profile.public', $user)->with('success', 'Profile updated successfully!');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
