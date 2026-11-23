<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClientAreaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('preference');

        return Inertia::render('client/ClientArea', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'emailVerifiedAt' => $user->email_verified_at?->toIso8601String(),
                'createdAt' => $user->created_at?->toIso8601String(),
                'hasPassword' => $user->hasPassword(),
                'theme' => $user->preferredTheme(),
                'locale' => $user->preferredLocale(),
            ],
            'showOnboarding' => (bool) $request->session()->get('show_onboarding', false),
            'onboardingCompleted' => (bool) $request->session()->get('onboarding_completed', false),
            'status' => session('status'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        $wantsPasswordUpdate = $request->filled('password');
        if ($wantsPasswordUpdate) {
            if ($user->hasPassword()) {
                $rules['current_password'] = ['required', 'string'];
            }
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        if ($wantsPasswordUpdate && $user->hasPassword()) {
            if (! Hash::check((string) $validated['current_password'], (string) $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The current password is incorrect.',
                ]);
            }
        }

        $attributes = [
            'name' => trim($validated['name']),
        ];

        if ($wantsPasswordUpdate) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($attributes)->save();

        return back()->with('status', 'Profile updated successfully.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'locale' => ['required', 'string', 'in:en,id'],
        ]);

        $user->preference()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => $validated['theme'],
                'locale' => $validated['locale'],
            ],
        );

        app()->setLocale($validated['locale']);

        return back()->with(
            'status',
            $validated['locale'] === 'id'
                ? 'Preferensi berhasil disimpan.'
                : 'Preferences saved successfully.',
        );
    }

    public function completeOnboarding(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];

        $wantsPassword = $request->filled('password');
        if ($wantsPassword) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $attributes = [
            'name' => trim($validated['name']),
        ];

        if ($wantsPassword) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        $user->forceFill($attributes)->save();

        $request->session()->forget('show_onboarding');
        $request->session()->flash('onboarding_completed', true);

        return redirect()->route('client.area');
    }

    public function skipOnboarding(Request $request): RedirectResponse
    {
        $request->session()->forget('show_onboarding');

        return redirect()->route('client.area');
    }
}
