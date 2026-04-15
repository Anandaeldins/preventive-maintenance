<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

  public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        $role = Auth::user()->role;

        // 🔥 redirect sesuai role
        if ($role == 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role == 'teknisi') {
            return redirect()->route('teknisi.dashboard');
        } elseif ($role == 'kepala_ro') {
            return redirect()->route('kepalaro.dashboard');
        } elseif ($role == 'pusat') {
            return redirect()->route('pusat.dashboard');
        }

        // fallback
        return redirect('/');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah',
    ]);
}


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}