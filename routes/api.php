<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user/token/{token}', function (Request $request, $token) {


    // Get the token from the request
    $token = $request->input('token');

    // Verify the token
    $token = DB::table('token')->where('token', $token)->first();

    dd($token);
});
