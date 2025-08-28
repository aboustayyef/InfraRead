<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * AuthController - Handles authentication bridging between session and API tokens
 *
 * This controller provides endpoints to facilitate the migration from session-based
 * authentication to API token authentication. It allows the frontend to seamlessly
 * obtain API tokens while maintaining existing session-based authentication.
 *
 * Teaching Note: This is a bridge pattern that allows gradual migration from one
 * authentication system to another without breaking existing functionality.
 */
class AuthController extends Controller
{
    /**
     * Generate an API token from an existing authenticated session.
     *
     * This endpoint allows the frontend to obtain an API token when the user
     * is already logged in via session authentication. This enables a smooth
     * transition from session-based to token-based API calls.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateSessionToken(Request $request): JsonResponse
    {
        // Ensure the user is authenticated via session
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        $user = auth()->user();

        // Generate a new API token for this session
        // We'll use a descriptive name that includes timestamp for identification
        $tokenName = 'frontend-session-' . now()->format('Y-m-d-H-i-s');

        // Create the token
        $token = $user->createToken($tokenName);

        return response()->json([
            'message' => 'API token generated successfully',
            'data' => [
                'token' => $token->plainTextToken,
                'token_name' => $tokenName,
                'expires_at' => null, // Tokens don't expire by default
                'abilities' => ['*'] // Full access for single-user app
            ]
        ], 200);
    }

    /**
     * Validate current authentication state
     * Used for debugging auth issues in Phase 5A
     */
    public function validateAuth(Request $request)
    {
        // Get session information
        $sessionId = $request->session()->getId();
        $sessionData = $request->session()->all();

        // Check web authentication (session-based)
        $webAuth = auth('web')->check();
        $webUser = auth('web')->user();

        // Check sanctum authentication (token-based)
        $sanctumAuth = auth('sanctum')->check();
        $sanctumUser = auth('sanctum')->user();

        // Check request headers
        $headers = [
            'authorization' => $request->header('Authorization'),
            'x-csrf-token' => $request->header('X-CSRF-Token'),
            'x-requested-with' => $request->header('X-Requested-With'),
            'referer' => $request->header('Referer'),
            'user-agent' => $request->header('User-Agent'),
        ];

        return response()->json([
            'debug' => [
                'session_id' => $sessionId,
                'session_keys' => array_keys($sessionData),
                'session_auth' => isset($sessionData['login_web_' . sha1('web')]),
                'csrf_token' => csrf_token(),
                'request_ip' => $request->ip(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
            ],
            'authentication' => [
                'web' => [
                    'authenticated' => $webAuth,
                    'user_id' => $webUser ? $webUser->id : null,
                    'user_email' => $webUser ? $webUser->email : null,
                ],
                'sanctum' => [
                    'authenticated' => $sanctumAuth,
                    'user_id' => $sanctumUser ? $sanctumUser->id : null,
                    'user_email' => $sanctumUser ? $sanctumUser->email : null,
                ]
            ],
            'headers' => $headers,
            'cookies' => array_keys($request->cookies->all()),
            'overall_authenticated' => $webAuth || $sanctumAuth,
            'recommended_action' => $webAuth ? 'session_valid' : 'needs_login',
        ]);
    }

    /**
     * Get information about the current user's API tokens.
     *
     * This can be useful for token management in the admin interface.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTokenInfo(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        // Get user's tokens (excluding the actual token values for security)
        $tokens = $user->tokens()->select('id', 'name', 'abilities', 'last_used_at', 'created_at')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return response()->json([
            'message' => 'Token information retrieved',
            'data' => [
                'token_count' => $tokens->count(),
                'tokens' => $tokens->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at?->toISOString(),
                        'created_at' => $token->created_at->toISOString(),
                        'is_current' => false // We'd need the current token ID to determine this
                    ];
                })
            ]
        ], 200);
    }
}
