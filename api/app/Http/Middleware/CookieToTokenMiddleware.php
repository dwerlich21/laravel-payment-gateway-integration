<?php

namespace App\Http\Middleware;

use App\Services\CookieManager;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CookieToTokenMiddleware
{
    protected CookieManager $cookieManager;

    public function __construct(CookieManager $cookieManager)
    {
        $this->cookieManager = $cookieManager;
    }
    public function handle(Request $request, Closure $next): Response
    {
        // Não processar cookies em rotas de autenticação (login seta seus próprios cookies)
        if ($request->is('*/login') || $request->is('login')) {
            return $next($request);
        }

        // Verificar se há Authorization header
        if (!$request->hasHeader('Authorization')) {
            $accessToken = $this->cookieManager->getAccessTokenFromRequest($request);
            $refreshToken = $this->cookieManager->getRefreshTokenFromRequest($request);

            // Debug: Log dos cookies recebidos
//            Log::debug('CookieToTokenMiddleware - Cookies recebidos', [
//                'access_token' => $accessToken ? substr($accessToken, 0, 20) . '...' : 'null',
//                'refresh_token' => $refreshToken ? substr($refreshToken, 0, 20) . '...' : 'null',
//                'all_cookies' => array_keys($request->cookies->all()),
//            ]);

            $cookiesToAdd = [];

            if ($accessToken) {
                $validToken = $this->checkAccessToken($accessToken, $refreshToken);
//                Log::debug('CookieToTokenMiddleware - Resultado checkAccessToken', [
//                    'valid' => $validToken ? 'sim' : 'não',
//                ]);

                if ($validToken) {
                    $request->headers->set('Authorization', 'Bearer ' . $validToken);
//                    Log::debug('CookieToTokenMiddleware - Authorization header setado', [
//                        'header' => 'Bearer ' . substr($validToken, 0, 20) . '...',
//                    ]);

                    // Renovar cookies com os mesmos tokens mas tempo de validade atualizado
                    $cookiesToAdd[] = $this->cookieManager->createAccessTokenCookie($accessToken);
                    if ($refreshToken) {
                        $cookiesToAdd[] = $this->cookieManager->createRefreshTokenCookie($refreshToken);
                    }
                }
            } else if ($refreshToken) {
                // Se não há access token mas há refresh token, tentar criar novo access token
                $newAccessToken = $this->createNewAccessToken($refreshToken);

                if ($newAccessToken) {
                    $request->headers->set('Authorization', 'Bearer ' . $newAccessToken);

                    // Adicionar os cookies com os tokens
                    $cookiesToAdd[] = $this->cookieManager->createAccessTokenCookie($newAccessToken);
                    $cookiesToAdd[] = $this->cookieManager->createRefreshTokenCookie($refreshToken);
                }
            }

            // Se temos cookies para adicionar, adiciona-los na resposta
            if (!empty($cookiesToAdd)) {
                $response = $next($request);

                // StreamedResponse (usado em exports) não suporta withCookie()
                // Usar Cookie::queue() para esses casos
                if ($response instanceof StreamedResponse) {
                    foreach ($cookiesToAdd as $cookie) {
                        Cookie::queue($cookie);
                    }
                } else {
                    foreach ($cookiesToAdd as $cookie) {
                        $response = $response->withCookie($cookie);
                    }
                }

                return $response;
            }
        }

        return $next($request);
    }

    private function getTokenRecord(?string $plainToken, string $name = null)
    {
        if (!$plainToken || !str_contains($plainToken, '|')) {
//            Log::debug('getTokenRecord - Token inválido ou sem pipe', ['token' => $plainToken ? 'presente' : 'null']);
            return null;
        }

        try {
            // Sanctum armazena o hash do token, não o texto plano
            [$id, $token] = explode('|', $plainToken, 2);

            if (!is_numeric($id) || empty($token)) {
//                Log::debug('getTokenRecord - ID não numérico ou token vazio', ['id' => $id]);
                return null;
            }

            $query = PersonalAccessToken::where('id', $id);

            if ($name) {
                $query->where('name', $name);
            }

            $tokenRecord = $query->first();

//            Log::debug('getTokenRecord - Busca no banco', [
//                'id' => $id,
//                'name' => $name,
//                'found' => $tokenRecord ? 'sim' : 'não',
//                'hash_match' => $tokenRecord ? (hash_equals($tokenRecord->token, hash('sha256', $token)) ? 'sim' : 'não') : 'n/a',
//            ]);

            // Verificar se o hash do token corresponde
            if ($tokenRecord && hash_equals($tokenRecord->token, hash('sha256', $token))) {
                return $tokenRecord;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar token record: ' . $e->getMessage());
            return null;
        }
    }

    private function checkAccessToken($accessToken, $refreshToken)
    {
        try {
            $now = Carbon::now();
            $tokenRecord = $this->getTokenRecord($accessToken, 'access-token');

            if (!$tokenRecord) {
                return null;
            }

//            Log::debug('checkAccessToken - Token encontrado', [
//                'expires_at' => $tokenRecord->expires_at ? $tokenRecord->expires_at->toDateTimeString() : 'null',
//                'is_expired' => $tokenRecord->expires_at ? ($tokenRecord->expires_at->isPast() ? 'sim' : 'não') : 'n/a',
//                'now' => $now->toDateTimeString(),
//            ]);

            // Verificar se o token expirou
            if ($tokenRecord->expires_at && $tokenRecord->expires_at->isPast()) {
//                Log::debug('checkAccessToken - Token expirado, tentando refresh');
                // Tentar renovar com refresh token
                return $this->checkRefreshToken($refreshToken, $accessToken);
            }

            // Atualizar last_used_at e renovar expiração do access token
            $tokenRecord->update([
                'last_used_at' => $now,
                'expires_at' => $now->copy()->addMinutes(10)
            ]);

            // Também renovar o refresh token se existir
            if ($refreshToken) {
                $refreshTokenRecord = $this->getTokenRecord($refreshToken, 'refresh-token');
                if ($refreshTokenRecord) {
                    $refreshTokenRecord->update([
                        'last_used_at' => $now,
                        'expires_at' => $now->copy()->addDay()
                    ]);
                }
            }

            return $accessToken; // Retornar o token original (plain text)

        } catch (\Exception $e) {
            Log::error('Erro ao verificar access token: ' . $e->getMessage());
            return null;
        }
    }

    private function checkRefreshToken($refreshToken, $accessToken)
    {
        try {
            if (!$refreshToken) {
                return null;
            }

            $now = Carbon::now();
            $refreshTokenRecord = $this->getTokenRecord($refreshToken, 'refresh-token');

            if (!$refreshTokenRecord) {
                return null;
            }

            if ($refreshTokenRecord->expires_at && $refreshTokenRecord->expires_at->isPast()) {
                return null;
            }

            // Buscar o access token record para atualizar
            $accessTokenRecord = $this->getTokenRecord($accessToken, 'access-token');

            if (!$accessTokenRecord) {
                return null;
            }

            // Renovar o access token
            $accessTokenRecord->update([
                'last_used_at' => $now,
                'expires_at' => $now->copy()->addMinutes(10)
            ]);

            // Atualizar o refresh token também
            $refreshTokenRecord->update([
                'last_used_at' => $now,
                'expires_at' => $now->copy()->addDay()
            ]);

            // IMPORTANTE: Retornar o access token original após renovação
            return $accessToken;

        } catch (\Exception $e) {
            Log::error('Erro ao verificar refresh token: ' . $e->getMessage());
            return null;
        }
    }

    private function createNewAccessToken($refreshToken)
    {
        try {
            if (!$refreshToken || !str_contains($refreshToken, '|')) {
                return null;
            }

            $now = Carbon::now();
            $refreshTokenRecord = $this->getTokenRecord($refreshToken, 'refresh-token');

            if (!$refreshTokenRecord) {
                return null;
            }

            if ($refreshTokenRecord->expires_at && $refreshTokenRecord->expires_at->isPast()) {
                return null;
            }

            // Obter o usuário associado ao refresh token
            $user = $refreshTokenRecord->tokenable;

            if (!$user) {
                return null;
            }

            // Criar novo access token
            $newAccessToken = $user->createToken(
                'access-token',
                ['*'],
                $now->copy()->addMinutes(CookieManager::ACCESS_TOKEN_EXPIRY)
            );

            // Atualizar o refresh token
            $refreshTokenRecord->update([
                'last_used_at' => $now,
                'expires_at' => $now->copy()->addMinutes(CookieManager::REFRESH_TOKEN_EXPIRY)
            ]);

            return $newAccessToken->plainTextToken;

        } catch (\Exception $e) {
            Log::error('Erro ao criar novo access token: ' . $e->getMessage());
            return null;
        }
    }
}
