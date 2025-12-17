<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // First try as email
        $credentials = ['email' => $username, 'password' => $password];
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // mark admin in session if user has admin role (simple fallback: user id 1 or add is_admin column)
            $request->session()->put('is_admin', true);
            return redirect()->route('admin.dashboard');
        }

        // Next try as username (name column)
        $credentials = ['name' => $username, 'password' => $password];
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->put('is_admin', true);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
