<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('medicines', MedicineController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('sales', SaleController::class);
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');

    Route::get('reports/pdf', [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('reports/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');
});
