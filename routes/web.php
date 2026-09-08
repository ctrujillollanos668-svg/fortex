<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Cliente\ClientDepositController;
use App\Http\Controllers\Cliente\ClientPlanController;
use App\Http\Controllers\Cliente\ClientTeamController;
use App\Http\Controllers\Cliente\ClientWithdrawalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return app(\App\Http\Controllers\DashboardController::class)->index();
})->name('home');

// Rutas de Cliente
Route::middleware(['auth'])->group(function () {
    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Planes VIP (Catálogo para Comprar) & Mis Planes (Comprados y Reclamos)
    Route::get('/plans', [ClientPlanController::class, 'index'])->name('cliente.plans.index');
    Route::get('/my-plans', [ClientPlanController::class, 'myPlans'])->name('cliente.plans.my-plans');
    Route::post('/plans/{id}/buy', [ClientPlanController::class, 'buy'])->name('cliente.plans.buy');
    Route::post('/plans/{id}/claim', [ClientPlanController::class, 'claimDaily'])->name('cliente.plans.claim');

    // Recargas / Depósitos
    Route::get('/deposit', [ClientDepositController::class, 'index'])->name('cliente.deposits.index');
    Route::post('/deposit', [ClientDepositController::class, 'store'])->name('cliente.deposits.store');

    // Retiros
    Route::get('/withdraw', [ClientWithdrawalController::class, 'index'])->name('cliente.withdrawals.index');
    Route::post('/withdraw', [ClientWithdrawalController::class, 'store'])->name('cliente.withdrawals.store');

    // Red de Referidos / Equipo
    Route::get('/my-team', [ClientTeamController::class, 'index'])->name('cliente.team.index');

    // Dinámicas Interactivas (Ruleta de la Suerte & Sobre Rojo)
    Route::match(['get', 'post'], '/rewards/roulette', [\App\Http\Controllers\Cliente\RewardController::class, 'spin'])->name('cliente.rewards.spin');
    Route::match(['get', 'post'], '/rewards/red-packet', [\App\Http\Controllers\Cliente\RewardController::class, 'claimRedPacket'])->name('cliente.rewards.red-packet');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas del Panel de Administrador
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications/check', [AdminDashboardController::class, 'checkNotifications'])->name('notifications.check');

    // Gestión de Depósitos / Recargas
    Route::get('/deposits', [AdminDepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposit-image/{id}', [AdminDepositController::class, 'showImage'])->name('deposits.image');
    Route::get('/deposit-img/{id}', [AdminDepositController::class, 'showImage'])->name('deposit.image');
    Route::post('/deposits/{id}/approve', [AdminDepositController::class, 'approve'])->name('deposits.approve');
    Route::post('/deposits/{id}/reject', [AdminDepositController::class, 'reject'])->name('deposits.reject');

    // Gestión de Retiros
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Gestión de Planes VIP
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{id}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::post('/plans/{id}/toggle', [AdminPlanController::class, 'toggle'])->name('plans.toggle');
    Route::post('/plans/{id}/toggle-home', [AdminPlanController::class, 'toggleHome'])->name('plans.toggle-home');
    Route::delete('/plans/{id}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');

    // Gestión de Usuarios / Clientes
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{id}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('users.adjustBalance');
    Route::post('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::post('/users/{id}/password', [AdminUserController::class, 'updatePassword'])->name('users.updatePassword');

    // Configuración y CRUD de Métodos de Pago y Códigos QR (Nequi, Daviplata, Bancolombia, etc.)
    Route::get('/payment-methods', [\App\Http\Controllers\Admin\AdminPaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [\App\Http\Controllers\Admin\AdminPaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::put('/payment-methods/{id}', [\App\Http\Controllers\Admin\AdminPaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::post('/payment-methods/{id}/toggle', [\App\Http\Controllers\Admin\AdminPaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
    Route::delete('/payment-methods/{id}', [\App\Http\Controllers\Admin\AdminPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    
    // Alias de settings hacia payment-methods
    Route::get('/settings', function() { return redirect()->route('admin.payment-methods.index'); })->name('settings.index');
});

// Manejador de imágenes subidas para Wasmer / Cloud (garantiza entrega 100% libre de enlaces rotos)
Route::get('/uploads/{folder}/{filename}', function ($folder, $filename) {
    $path = public_path('uploads/' . $folder . '/' . $filename);
    if (!file_exists($path)) {
        $storagePath = storage_path('app/public/' . $folder . '/' . $filename);
        if (file_exists($storagePath)) {
            return response()->file($storagePath);
        }
        abort(404);
    }
    return response()->file($path);
})->where('folder', '[a-zA-Z0-9_\-]+')->where('filename', '[a-zA-Z0-9_\-\.]+');

Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $path = storage_path('app/public/' . $folder . '/' . $filename);
    if (!file_exists($path)) {
        $publicPath = public_path('uploads/' . $folder . '/' . $filename);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }
        abort(404);
    }
    return response()->file($path);
})->where('folder', '[a-zA-Z0-9_\-]+')->where('filename', '[a-zA-Z0-9_\-\.]+');

require __DIR__.'/auth.php';
