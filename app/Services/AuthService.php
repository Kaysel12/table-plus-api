<?php

namespace App\Services;

use App\Exceptions\AuthException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function login(array $credentials)
    {
        if (!$token = Auth::attempt($credentials)) {
            throw AuthException::invalidCredentials();
        }
        // Guardar en password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $credentials['email']],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        return response()->json([
            'access_token' => $token ,
            'token_type' => 'bearer',
        ]);
    }
}
