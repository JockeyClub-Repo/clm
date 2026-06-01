<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () { return view('welcome'); });


// 📌 Acceso común a todos los usuarios autenticados
Route::middleware('auth')->group(function () {
  // Dashboard
  Route::get('/dashboard', [DashboardController::class, 'dashboardRouter'])->name('dashboard');
  Route::get('/dashboard/stats', [DashboardDataController::class, 'stats']);
  Route::get('/dashboard/calendar', [DashboardDataController::class, 'calendar']);
  Route::get('/dashboard/timeline', [DashboardDataController::class, 'timeline']);
  Route::get('/dashboard/expiring-contracts', [DashboardDataController::class, 'expiringContracts']);
  Route::get('/dashboard/charts', [DashboardDataController::class, 'charts']);
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
