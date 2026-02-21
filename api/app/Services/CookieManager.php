<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class CookieManager
{
    // Nomes dos cookies
    public const ACCESS_TOKEN_COOKIE = 'access_token';
    public const REFRESH_TOKEN_COOKIE = 'refresh_token';
    
    // Tempos de expiração em minutos
    public const ACCESS_TOKEN_EXPIRY = 10; // 10 minutos
    public const REFRESH_TOKEN_EXPIRY = 1440; // 1 dia (24 horas)
    
    /**
     * Cria um cookie de token com as configurações padrão de segurança
     *
     * @param string $name Nome do cookie
     * @param string $token Valor do token
     * @param int $minutes Tempo de expiração em minutos
     * @return SymfonyCookie
     */
    public function createTokenCookie(string $name, string $token, int $minutes): SymfonyCookie
    {
        return Cookie::make(
            $name,
            $token,
            $minutes,
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure', true),
            config('session.http_only', true),
            false,    // raw
            config('session.same_site', 'lax')
        );
    }
    
    /**
     * Cria um cookie de access token
     *
     * @param string $token
     * @return SymfonyCookie
     */
    public function createAccessTokenCookie(string $token): SymfonyCookie
    {
        return $this->createTokenCookie(
            self::ACCESS_TOKEN_COOKIE, 
            $token, 
            self::ACCESS_TOKEN_EXPIRY
        );
    }
    
    /**
     * Cria um cookie de refresh token
     *
     * @param string $token
     * @return SymfonyCookie
     */
    public function createRefreshTokenCookie(string $token): SymfonyCookie
    {
        return $this->createTokenCookie(
            self::REFRESH_TOKEN_COOKIE, 
            $token, 
            self::REFRESH_TOKEN_EXPIRY
        );
    }
    
    /**
     * Remove o cookie de access token
     *
     * @return SymfonyCookie
     */
    public function forgetAccessTokenCookie(): SymfonyCookie
    {
        // Criar cookie com valor vazio e tempo expirado para garantir remoção
        return Cookie::make(
            self::ACCESS_TOKEN_COOKIE,
            '',
            -1,       // tempo negativo para expirar imediatamente
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure', true),
            config('session.http_only', true),
            false,    // raw
            config('session.same_site', 'lax')
        );
    }
    
    /**
     * Remove o cookie de refresh token
     *
     * @return SymfonyCookie
     */
    public function forgetRefreshTokenCookie(): SymfonyCookie
    {
        // Criar cookie com valor vazio e tempo expirado para garantir remoção
        return Cookie::make(
            self::REFRESH_TOKEN_COOKIE,
            '',
            -1,       // tempo negativo para expirar imediatamente
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure', true),
            config('session.http_only', true),
            false,    // raw
            config('session.same_site', 'lax')
        );
    }
    
    /**
     * Renova os cookies com os mesmos tokens mas com novos tempos de expiração
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @return array Array com os cookies renovados
     */
    public function renewTokenCookies(string $accessToken, string $refreshToken): array
    {
        return [
            $this->createAccessTokenCookie($accessToken),
            $this->createRefreshTokenCookie($refreshToken)
        ];
    }
    
    /**
     * Obtém o access token do request
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function getAccessTokenFromRequest($request): ?string
    {
        return $request->cookie(self::ACCESS_TOKEN_COOKIE);
    }
    
    /**
     * Obtém o refresh token do request
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function getRefreshTokenFromRequest($request): ?string
    {
        return $request->cookie(self::REFRESH_TOKEN_COOKIE);
    }
}