<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\EnumController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/recover-password', [LoginController::class, 'recoverPassword']);
    Route::post('/forgot-password', [LoginController::class, 'forgotPassword']);

    /*
    |--------------------------------------------------------------------------|
    | Enum Routes                                                              |
    |--------------------------------------------------------------------------|
    */
    Route::get('enums', [EnumController::class, 'index']);
    Route::get('enums/{enum}', [EnumController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Rotas públicas — vitrine de produtos
    |--------------------------------------------------------------------------
    */
    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    /*
    |--------------------------------------------------------------------------
    | Rotas públicas — Checkout
    |--------------------------------------------------------------------------
    */
    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::put('checkout/{order}/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');

    /*
    |--------------------------------------------------------------------------
    | Webhooks — recebimento de notificações dos gateways
    |--------------------------------------------------------------------------
    */
    Route::prefix('webhooks')->middleware('throttle:60,1')->group(function () {
        Route::post('stripe', [WebhookController::class, 'handleStripe'])->name('webhooks.stripe');
        Route::post('asaas', [WebhookController::class, 'handleAsaas'])->name('webhooks.asaas');
    });

    Route::group([
        'middleware' => [
            'cookie.to.token',
            'auth:sanctum',
            'is.active',
        ],
    ], function ($router) {

        Route::post('/logout', [LoginController::class, 'logout']);
        Route::get('/me', [UserController::class, 'getUser']);

        /*
        |--------------------------------------------------------------------------
        | Orders — requer autenticação para efetuar pagamento
        |--------------------------------------------------------------------------
        */
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

        /*
        |--------------------------------------------------------------------------
        | Rotas protegidas por permissão
        |--------------------------------------------------------------------------
        */

        /*
        |--------------------------------------------------------------------------
        | Users System Routes
        |--------------------------------------------------------------------------
        */
        Route::apiResource('users', UserController::class);

        /*
        |--------------------------------------------------------------------------
        | Notifications Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::post('/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | Audit Logs Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [AuditController::class, 'index'])->name('audit-logs.index');
            Route::get('/actions', [AuditController::class, 'actions'])->name('audit-logs.index');
            Route::get('/model-types', [AuditController::class, 'modelTypes'])->name('audit-logs.index');
            Route::get('/stats', [AuditController::class, 'stats'])->name('audit-logs.stats');
            Route::get('/{id}', [AuditController::class, 'show'])->name('audit-logs.show');
        });
    });
});
