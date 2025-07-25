<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user/token/{token}', function (Request $request, $token) {

    return response()->json([
        'message' => 'Token received',
        'token' => $token,
    ]);

    // Verify the token
    $token = DB::table('token')->where('token', $token)->first();

    dd($token);
});
