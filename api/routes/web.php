<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/**
 * PING de teste da aplicação
 */
Route::get('/', function () {
    echo 'PING';
});

Route::get('users/perfil/{id}', [UserController::class, 'perfil']);
