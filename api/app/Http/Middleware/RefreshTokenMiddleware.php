<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenMiddleware
{
    private const ACCESS_TOKEN_NAME = 'access-token';

    private const REFRESH_TOKEN_NAME = 'refresh-token';

    private const ACCESS_TOKEN_EXPIRY = 10; // minutes

    private const REFRESH_TOKEN_EXPIRY = 1440; // minutes (1 day)

    private const TOKEN_REFRESH_THRESHOLD = 2; // minutes before expiry to refresh

    public function handle(Request $request, Closure $next): Response
    {
        // Only process if we have an authenticated user via cookie
        $accessToken = $request->cookie('access_token');
        if (! $accessToken) {
            return $next($request);
        }

        $response = $next($request);

        try {
            // Check if access token needs refresh
            $tokenHash = hash('sha256', explode('|', $accessToken)[1] ?? '');
            $token = PersonalAccessToken::where('token', $tokenHash)
                ->where('name', self::ACCESS_TOKEN_NAME)
                ->first();

            if (! $token) {
                return $response;
            }

            // Check if token is about to expire
            $expiresAt = Carbon::parse($token->expires_at);
            $shouldRefresh = $expiresAt->diffInMinutes(now()) <= self::TOKEN_REFRESH_THRESHOLD;

            if ($shouldRefresh) {
                // Try to refresh using refresh token
                $refreshToken = $request->cookie('refresh_token');
                if ($refreshToken) {
                    $refreshTokenHash = hash('sha256', explode('|', $refreshToken)[1] ?? '');
                    $refreshTokenRecord = PersonalAccessToken::where('token', $refreshTokenHash)
                        ->where('name', self::REFRESH_TOKEN_NAME)
                        ->where('expires_at', '>', now())
                        ->first();

                    if ($refreshTokenRecord) {
                        $user = $refreshTokenRecord->tokenable;

                        // Delete old access token
                        $token->delete();

                        // Create new access token
                        $newAccessToken = $user->createToken(
                            self::ACCESS_TOKEN_NAME,
                            ['*'],
                            Carbon::now()->addMinutes(self::ACCESS_TOKEN_EXPIRY)
                        );

                        // Add new cookie to response
                        $response->withCookie(Cookie::make(
                            'access_token',
                            $newAccessToken->plainTextToken,
                            self::ACCESS_TOKEN_EXPIRY,
                            '/',  // path
                            null, // domain
                            true, // secure
                            true, // httpOnly
                            false, // raw
                            'lax' // sameSite
                        ));
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the request
        }

        return $response;
    }
}
