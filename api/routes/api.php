<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\EnumController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
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
        | Permissions Routes (sem middleware de permission)
        |--------------------------------------------------------------------------
        */
        Route::get('permissions', [PermissionController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Rotas protegidas por permissão
        |--------------------------------------------------------------------------
        */
        Route::group(['middleware' => ['permission']], function () {

            /*
            |--------------------------------------------------------------------------
            | Users System Routes
            |--------------------------------------------------------------------------
            */
            Route::put('users/change-active/{id}', [UserController::class, 'changeActive'])->name('users.change-active');
            Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
            Route::post('users/bulk-change-active', [UserController::class, 'bulkChangeActive'])->name('users.bulk-change-active');
            Route::apiResource('users', UserController::class);

            /*
            |--------------------------------------------------------------------------
            | Goals Routes
            |--------------------------------------------------------------------------
            */
            Route::put('goals/change-active/{id}', [GoalController::class, 'changeActive'])->name('goals.change-active');
            Route::post('goals/bulk-delete', [GoalController::class, 'bulkDelete'])->name('goals.bulk-delete');
            Route::post('goals/bulk-change-active', [GoalController::class, 'bulkChangeActive'])->name('goals.bulk-change-active');
            Route::post('goals/bulk-change-status', [GoalController::class, 'bulkChangeStatus'])->name('goals.bulk-change-status');
            Route::get('goals/kanban', [GoalController::class, 'kanban'])->name('goals.kanban');
            Route::patch('goals/{id}/status', [GoalController::class, 'updateStatus'])->name('goals.update-status');
            Route::apiResource('goals', GoalController::class);

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
            | Reports Routes
            |--------------------------------------------------------------------------
            */
            Route::prefix('reports')->group(function () {
                Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
                Route::get('/full-dashboard', [ReportController::class, 'fullDashboard'])->name('reports.dashboard');
                Route::get('/upcoming-deadlines', [ReportController::class, 'upcomingDeadlines'])->name('reports.dashboard');
                Route::get('/recent-activity', [ReportController::class, 'recentActivity'])->name('reports.dashboard');
                Route::get('/top-performers', [ReportController::class, 'topPerformers'])->name('reports.dashboard');
                Route::get('/goals/summary', [ReportController::class, 'goalsSummary'])->name('reports.goals');
                Route::get('/goals/by-status', [ReportController::class, 'goalsByStatus'])->name('reports.goals');
                Route::get('/goals/by-user', [ReportController::class, 'goalsByUser'])->name('reports.goals');
                Route::get('/goals/timeline', [ReportController::class, 'goalsTimeline'])->name('reports.goals');
                Route::get('/users/summary', [ReportController::class, 'usersSummary'])->name('reports.users');
                Route::get('/export/goals/pdf', [ReportController::class, 'exportGoalsPdf'])->name('reports.export-pdf');
                Route::get('/export/goals/excel', [ReportController::class, 'exportGoalsExcel'])->name('reports.export-excel');
                Route::get('/export/users/pdf', [ReportController::class, 'exportUsersPdf'])->name('reports.export-pdf');
                Route::get('/export/users/excel', [ReportController::class, 'exportUsersExcel'])->name('reports.export-excel');
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
});
