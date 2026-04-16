<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Throwable;

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

        try {
            // Laravel default Auth expects 'email', but we use 'username'.
            // We can manually attempt.
            $user = User::where('username', $request->username)->first();

            // Check if user exists and password matches
            if ($user && Hash::check($request->password, $user->password)) {
                // Manual Login
                Auth::login($user);

                // Redirect to intended page or Dashboard
                return redirect()->intended(route('dashboard'))->with('success', 'Logged in successfully.');
            }
        } catch (Throwable $e) {
            Log::error('Login failed due to server/database issue', [
                'username' => $request->username,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'username' => 'Login gagal karena koneksi server/database bermasalah. Silakan coba lagi.',
            ])->onlyInput('username');
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
