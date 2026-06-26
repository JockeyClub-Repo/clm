<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () { return view('welcome'); });


// 📌 Acceso común a todos los usuarios autenticados
Route::middleware('auth')->group(function () {
  // Dashboard
  Route::get('/dashboard', [DashboardController::class, 'dashboardRouter'])->name('dashboard');
  Route::get('/dashboard/data', [DashboardController::class, 'adminData'])->name('dashboard.data');
  // Notificaciones
  Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
  Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});    

// 📌 ADMIN: Mantenimiento y gestión
Route::middleware(['auth', 'role:admin'])->group(function () {
  // Usuarios
  Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
  Route::resource('users', UserController::class);
  // Proveedores
  Route::get('/providers/data', [ProviderController::class, 'data'])->name('providers.data');
  Route::resource('providers', ProviderController::class);
  // Contratos
  Route::get('/contracts/data', [ContractController::class, 'data'])->name('contracts.data');
  Route::delete('/contract-files/{id}', [ContractController::class, 'deleteFile'])
    ->name('contracts.file.delete');
  Route::post('/contracts/{id}/renew', [ContractController::class, 'renew'])->name('contracts.renew');
  Route::post('/contracts/{id}/not-renew', [ContractController::class, 'notRenew'])->name('contracts.not-renew');
  Route::get('/contracts/{id}/pdf', [ContractController::class, 'pdf'])
    ->name('contracts.pdf');
  Route::resource('contracts', ContractController::class);
});

require __DIR__.'/auth.php';
