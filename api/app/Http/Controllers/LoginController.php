<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RecoverPasswordRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CookieManager;
use App\Services\UserService;
use App\Traits\ExceptionHandlerTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController
{
    use ExceptionHandlerTrait;

    private const ACCESS_TOKEN_NAME = 'access-token';
    private const REFRESH_TOKEN_NAME = 'refresh-token';

    protected CookieManager $cookieManager;
    protected UserService $userService;
    protected AuditService $auditService;

    public function __construct(CookieManager $cookieManager, UserService $userService, AuditService $auditService)
    {
        $this->cookieManager = $cookieManager;
        $this->userService = $userService;
        $this->auditService = $auditService;
    }

    private function generateTokens($user): array
    {
        // Revoke all existing tokens
        if($user->id > 3) $user->tokens()->delete();

        // Create new tokens
        $accessToken = $user->createToken(
            self::ACCESS_TOKEN_NAME,
            ['*'],
            Carbon::now()->addMinutes(CookieManager::ACCESS_TOKEN_EXPIRY)
        );

        $refreshToken = $user->createToken(
            self::REFRESH_TOKEN_NAME,
            ['*'],
            Carbon::now()->addMinutes(CookieManager::REFRESH_TOKEN_EXPIRY)
        );

        return [
            'access'  => $accessToken->plainTextToken,
            'refresh' => $refreshToken->plainTextToken,
        ];
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ]);

            // Buscar usuário diretamente pelo email
            $user = User::where('email', $credentials['email'])->first();

            // Verificar se usuário existe e senha está correta
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                // Log failed login attempt
                $this->auditService->logAuth(
                    AuditLog::ACTION_LOGIN_FAILED,
                    "Tentativa de login falhou para o email '{$credentials['email']}'",
                    $credentials['email']
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                ], 401);
            }

            $tokens = $this->generateTokens($user);

            // Log successful login
            Auth::login($user);
            $this->auditService->logAuth(
                AuditLog::ACTION_LOGIN,
                "Usuário '{$user->name}' realizou login",
                $user->email
            );

            return response()->json([
                'user'    => $user,
                'success' => true,
                'message' => 'Login realizado com sucesso',
            ])
                ->withCookie($this->cookieManager->createAccessTokenCookie($tokens['access']))
                ->withCookie($this->cookieManager->createRefreshTokenCookie($tokens['refresh']));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar login: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            // Log logout before revoking tokens
            $this->auditService->logAuth(
                AuditLog::ACTION_LOGOUT,
                "Usuário '{$user->name}' realizou logout",
                $user->email
            );

            // Revoke all tokens for the user
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ])
            ->withCookie($this->cookieManager->forgetAccessTokenCookie())
            ->withCookie($this->cookieManager->forgetRefreshTokenCookie());
    }

    public function recoverPassword(RecoverPasswordRequest $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $data = $request->all();
            $this->userService->recoverPassword($data);

            // Log password reset
            $this->auditService->logPasswordReset(null, $data['email'] ?? null);

            return $this->successResponse(null, 'Senha alterada com sucesso');

        }, 'Erro ao buscar registros');

    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {

            $data = $request->all();
            $this->userService->forgotPassword($data);

            return $this->successResponse(null, 'Foi enviado um link para redefinição de senha para o e-mail informado');

        }, 'Erro ao buscar registros');

    }
}
