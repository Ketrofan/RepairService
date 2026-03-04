<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DispatcherRequestsController;
use App\Http\Controllers\MasterRequestsController;
use App\Http\Controllers\PublicRepairRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('requests.create'))->name('home');

/**
 * Публичная форма создания заявки
 */
Route::get('/requests/create', [PublicRepairRequestController::class, 'create'])->name('requests.create');
Route::post('/requests', [PublicRepairRequestController::class, 'store'])->name('requests.store');

/**
 * Auth
 */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/**
 * Dispatcher panel
 */
Route::prefix('dispatcher')
    ->middleware(['auth', 'role:dispatcher'])
    ->group(function () {
        Route::get('/requests', [DispatcherRequestsController::class, 'index'])->name('dispatcher.requests.index');
        Route::post('/requests/{repairRequest}/assign', [DispatcherRequestsController::class, 'assign'])->name('dispatcher.requests.assign');
        Route::post('/requests/{repairRequest}/cancel', [DispatcherRequestsController::class, 'cancel'])->name('dispatcher.requests.cancel');
    });

/**
 * Master panel
 */
Route::prefix('master')
    ->middleware(['auth', 'role:master'])
    ->group(function () {
        Route::get('/requests', [MasterRequestsController::class, 'index'])->name('master.requests.index');
        Route::post('/requests/{repairRequest}/take', [MasterRequestsController::class, 'take'])->name('master.requests.take');
        Route::post('/requests/{repairRequest}/done', [MasterRequestsController::class, 'done'])->name('master.requests.done');
    });