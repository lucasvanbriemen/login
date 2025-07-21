<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('login');
})->name('login');


Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // User authenticated successfully

        $request->session()->regenerate();

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
