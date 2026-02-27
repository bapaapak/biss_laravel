<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Show Login Form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle Login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Laravel default Auth expects 'email', but we use 'username'.
        // We can manually attempt.
        // Also need to check if legacy passwords use bcrypt (they do, based on db_biss.sql).

        $user = User::where('username', $request->username)->first();

        // Check if user exists and password matches
        if ($user && Hash::check($request->password, $user->password)) {
            
            // Manual Login
            Auth::login($user);

            // Redirect to intended page or Dashboard
            // Since we haven't built Dashboard yet, we can go to PR index
            return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully.');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    // Handle Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
