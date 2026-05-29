<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () { return view('welcome'); });

Route::get('/dashboard', [DashboardController::class, 'dashboardRouter'])->middleware(['auth'])->name('dashboard');

// 📌 Acceso común a todos los usuarios autenticados
Route::middleware('auth')->group(function () {
  Route::get('/dashboard', function () { return view('dashboard');})->name('dashboard');
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
  Route::resource('contracts', ContractController::class);
});

require __DIR__.'/auth.php';
