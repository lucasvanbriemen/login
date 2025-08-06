<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public const TOKEN_EXPIRY_DAYS = 1;
    public const COOKIE_EXPIRY_DAYS = 10;
    public const TOKEN_LENGTH = 60;

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $token = Str::random(self::TOKEN_LENGTH);

            DB::table('token')->insert([
                'token' => $token,
                'user_id' => Auth::id(),
                'expires_at' => now()->addDays(self::TOKEN_EXPIRY_DAYS),
            ]);

            $cookieExpiry = time() + (self::COOKIE_EXPIRY_DAYS * 24 * 60 * 60);
            setcookie('auth_token', $token, $cookieExpiry, '/', config('session.domain'));

            return response()->json([
                'success' => true,
                'message' => 'Login successful!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid login credentials!',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        setcookie('auth_token', '', time() - 3600, '/', config('session.domain'));

        return response()->json([
            'message' => 'Successfully logged out!',
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful!',
        ]);
    }
}