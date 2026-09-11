<?php

// Agregar en routes/api.php

use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprobantePagoController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('roles', RolController::class);
Route::apiResource('permisos', PermisoController::class);
Route::apiResource('tipos-vehiculo', TipoVehiculoController::class);
Route::apiResource('servicios', ServicioController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('clientes', ClienteController::class);
Route::apiResource('administradores', AdministradorController::class);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('comprobantes-pago', ComprobantePagoController::class);

// Registro público de clientes (usado por el formulario de la página de inicio)
Route::post('registro', [RegistroController::class, 'registrarCliente']);

// Vehiculo usa placa_vehiculo (string) como llave primaria
Route::apiResource('vehiculos', VehiculoController::class)->parameters([
    'vehiculos' => 'placa',
]);

// Reserva: endpoint extra para consultar cupos disponibles antes de reservar
Route::get('reservas/cupos-disponibles', [ReservaController::class, 'cuposDisponibles']);
Route::apiResource('reservas', ReservaController::class);