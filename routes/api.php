<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AtividadeController;
use App\Http\Controllers\API\ColaboradorController;
use App\Http\Controllers\API\GastoController;
use App\Http\Controllers\API\RoteiroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui é onde você pode registrar as rotas da API para sua aplicação.
| Essas rotas são carregadas pelo RouteServiceProvider e todas elas
| serão atribuídas ao grupo de middleware "api".
|
*/

// Rotas públicas de autenticação
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/registrar', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/entrar', [AuthController::class, 'login']);

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    // Rotas de autenticação
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/perfil', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/sair', [AuthController::class, 'logout']);
    
    // Rotas para Roteiros
    Route::apiResource('/roteiros', RoteiroController::class);

    // Rotas para Colaboradores
    Route::apiResource('/colaboradores', ColaboradorController::class);
    Route::post('/colaboradores/adicionar-roteiro', [ColaboradorController::class, 'adicionarAoRoteiro']);
    Route::post('/colaboradores/remover-roteiro', [ColaboradorController::class, 'removerDoRoteiro']);

    // Rotas para Gastos
    Route::get('/roteiros/{roteiro_id}/gastos', [GastoController::class, 'index']);
    Route::post('/roteiros/{roteiro_id}/gastos', [GastoController::class, 'store']);
    Route::get('/roteiros/{roteiro_id}/gastos/{id}', [GastoController::class, 'show']);
    Route::put('/roteiros/{roteiro_id}/gastos/{id}', [GastoController::class, 'update']);
    Route::delete('/roteiros/{roteiro_id}/gastos/{id}', [GastoController::class, 'destroy']);
    Route::patch('/roteiros/{roteiro_id}/gastos/{id}/aprovar', [GastoController::class, 'aprovarGasto']);
    
    // Rotas para Atividades
    Route::get('/roteiros/{roteiro_id}/atividades', [AtividadeController::class, 'index']);
    Route::post('/roteiros/{roteiro_id}/atividades', [AtividadeController::class, 'store']);
    Route::get('/roteiros/{roteiro_id}/atividades/{id}', [AtividadeController::class, 'show']);
    Route::put('/roteiros/{roteiro_id}/atividades/{id}', [AtividadeController::class, 'update']);
    Route::delete('/roteiros/{roteiro_id}/atividades/{id}', [AtividadeController::class, 'destroy']);
}); 