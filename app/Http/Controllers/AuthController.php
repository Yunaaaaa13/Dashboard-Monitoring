<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.overview');
        }

        $users = User::orderBy('id')->get();
        return view('auth.login', compact('users'));
    }

    /**
     * Proses login standar
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');

        // Check if login input is email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.overview'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . ' (' . strtoupper(Auth::user()->role) . ')!');
        }

        // Fallback check username
        if ($fieldType === 'email') {
            if (Auth::attempt(['username' => $loginInput, 'password' => $password], $remember)) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard.overview'))
                    ->with('success', 'Selamat datang, ' . Auth::user()->name . ' (' . strtoupper(Auth::user()->role) . ')!');
            }
        }

        return back()->withErrors([
            'login' => 'Username/Email atau Password yang Anda masukkan salah.',
        ])->withInput($request->only('login'));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.overview')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Login cepat sesuai akun / role yang dipilih (Quick Login)
     */
    public function demoLogin($identifier)
    {
        if (is_numeric($identifier)) {
            $user = User::find($identifier);
        } else {
            $user = User::where('role', $identifier)->first();
        }

        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'Akun pengguna tersebut tidak ditemukan.']);
        }

        Auth::login($user);
        return redirect()->route('dashboard.overview')
            ->with('success', 'Selamat datang, ' . $user->name . ' (' . strtoupper($user->role) . ')!');
    }
}
