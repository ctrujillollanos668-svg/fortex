<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiDepositController;
use App\Http\Controllers\Api\ApiPlanController;
use App\Http\Controllers\Api\ApiRewardController;
use App\Http\Controllers\Api\ApiTeamController;
use App\Http\Controllers\Api\ApiWithdrawalController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Sin Token)
Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::post('/auth/register', [ApiAuthController::class, 'register']);

// Rutas Protegidas (Requieren Bearer Token)
Route::middleware(['api.auth'])->group(function () {
    // Perfil y Datos del Usuario
    Route::get('/user/profile', [ApiAuthController::class, 'profile']);

    // Planes VIP (Catálogo y Mis Planes)
    Route::get('/plans', [ApiPlanController::class, 'catalog']);
    Route::get('/user/plans', [ApiPlanController::class, 'myPlans']);
    Route::post('/user/plans/buy', [ApiPlanController::class, 'buy']);
    Route::post('/user/plans/claim/{id}', [ApiPlanController::class, 'claimDaily']);

    // Recargas (Depósitos)
    Route::get('/deposits/info', [ApiDepositController::class, 'info']);
    Route::post('/deposits/submit', [ApiDepositController::class, 'submit']);

    // Retiros
    Route::get('/withdrawals/info', [ApiWithdrawalController::class, 'info']);
    Route::post('/withdrawals/submit', [ApiWithdrawalController::class, 'submit']);

    // Mi Red y Comisiones
    Route::get('/team/info', [ApiTeamController::class, 'info']);

    // Ruleta VIP
    Route::get('/rewards/roulette', [ApiRewardController::class, 'status']);
    Route::post('/rewards/roulette/spin', [ApiRewardController::class, 'spin']);
});
