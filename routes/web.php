<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('login');
})->name('login');


Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // User authenticated successfully

        $request->session()->regenerate();

        $token = Str::random(60);
        // store the token in the database
        DB::table('token')->insert([
            'token' => $token,
            'user_id' => Auth::id(),
            'expires_at' => now()->addDay(), // Example expiration time
        ]);

        // Store the token as a cookie
        setcookie('auth_token', $token, time() + 3600, '/', '.lucasvanbriemen.nl');

        return response()->json([
            'message' => 'Login successful!',
        ]);
    } else {
        return response()->json([
            'message' => 'Invalid login credentials!',
        ], 401);
    }
});

Route::post('/logout', function () {
    auth()->logout(); // For session-based authentication

    return response()->json([
        'message' => 'Successfully logged out!',
    ]);
});

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => 'required',
        'email' => 'required',
        'password' => 'required',
    ]);

    $user = \App\Models\User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
    ]);

    Auth::login($user);
});
