<?php

use App\Exceptions\ApiException;
use App\Exceptions\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'cookie.to.token' => \App\Http\Middleware\CookieToTokenMiddleware::class,
            'is.active' => \App\Http\Middleware\ActiveMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Aplicar middleware globalmente para API
        $middleware->api(prepend: [
            \App\Http\Middleware\CookieToTokenMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport(ValidationException::class);
        $exceptions->render(function (ValidationException $e, Request $request) {

            if ($request->wantsJson()) {
                // Força o status 422 diretamente
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 422,
                    'errors' => $e->getErrorData(),
                ], 422);
            }
        });

        $exceptions->render(function (ApiException $e, Request $request) {
            if ($e instanceof ValidationException) {
                return null;
            }

            if ($request->wantsJson()) {
                return response()->json($e->render(), $e->getStatusCode());
            }
        });

        $exceptions->render(function (LaravelValidationException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autenticado',
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recurso não encontrado',
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                ], 403);
            }
        });

        // Tratamento para TypeError (erros de tipo do PHP)
        $exceptions->render(function (\TypeError $e, Request $request) {
            if ($request->wantsJson()) {
                // Em produção, retorna mensagem genérica
                if (app()->environment('production')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao processar requisição',
                    ], 500);
                }

                // Em desenvolvimento, retorna detalhes para debug
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de tipo: '.$e->getMessage(),
                ], 500);
            }
        });

        // Tratamento genérico para outras exceções
        $exceptions->render(function (\Exception $e, Request $request) {
            if ($request->wantsJson()) {
                // Se for uma exceção customizada que já tem render
                if (method_exists($e, 'render')) {
                    return $e->render();
                }

                // Em produção, retorna mensagem genérica
                if (app()->environment('production')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro interno do servidor',
                    ], 500);
                }

                // Em desenvolvimento, retorna mais detalhes
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Erro interno do servidor',
                ], 500);
            }
        });
    })->create();
