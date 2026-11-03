<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active accounts may sign in.
        if (Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        $user = User::where('email', $credentials['email'])->first();
        $message = $user && ! $user->is_active && Hash::check($credentials['password'], $user->password)
            ? 'Your account has been deactivated. Please contact the administrator.'
            : 'These credentials do not match our records.';

        return back()->withErrors(['email' => $message])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            // Only these two roles can be chosen. "admin" is rejected here and can never be self-assigned.
            'account_type' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_OWNER])],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User(collect($data)->only(['name', 'email', 'phone', 'password'])->all());
        $user->role = $data['account_type'];
        $user->is_active = true;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome to AutoKonek, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out.');
    }
}
