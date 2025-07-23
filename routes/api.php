<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {

    if (!$request->user()) {
        return response()->json(['message' => 'Unauthenticated'], 200);
    }

    return $request->user();
});
