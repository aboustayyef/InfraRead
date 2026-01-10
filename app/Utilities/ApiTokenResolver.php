<?php

namespace App\Utilities;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenResolver
{
    public static function resolveForUser(User $user, string $tokenName = 'spa-token'): string
    {
        $token = config('infraread.api_token');

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken && $accessToken->tokenable) {
                return $token;
            }
        }

        return $user->createToken($tokenName)->plainTextToken;
    }
}
