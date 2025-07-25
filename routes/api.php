<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/user/token/{token}', function (Request $request, $token) {

    // Verify the token
    $token = DB::table('token')->where('token', $token)->first();

    // Get the user associated with the token
    if (!$token) {
        return response()->json(['message' => 'Token not found'], 404);
    }

    // Get the expeation date time
    $expiresAt = $token->expires_at;
    if (now()->greaterThan($expiresAt)) {
        DB::table('token')->where('token', $token->token)->delete();
        return response()->json(['message' => 'Token has expired'], 401);
    }

    $userID = $token->user_id;
    $user = \App\Models\User::find($userID);

    return response()->json([
        'user' => $user,
    ], 200)
    ->header('Content-Type', 'application/json');
});
