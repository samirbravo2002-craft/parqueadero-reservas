<?php

// Agregar en routes/api.php

use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\AuthController;
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

// Reservas de un cliente puntual (usado por el módulo Control).
// Debe ir ANTES del apiResource de clientes para que no choque con clientes/{cliente}.
Route::get('clientes/{id}/reservas', [ClienteController::class, 'reservas']);
Route::apiResource('clientes', ClienteController::class);

Route::apiResource('administradores', AdministradorController::class);
Route::apiResource('metodos-pago', MetodoPagoController::class);
Route::apiResource('comprobantes-pago', ComprobantePagoController::class);

// Registro público de clientes (usado por el formulario de la página de inicio
// y también por el módulo Control para crear clientes nuevos).
Route::post('registro', [RegistroController::class, 'registrarCliente']);

// Registro de administradores. Sin formulario público a propósito:
// úsalo una vez por Postman/curl para crear la primera cuenta admin.
Route::post('registro-administrador', [RegistroController::class, 'registrarAdministrador']);

// Login de clientes ya registrados (correo + contraseña).
Route::post('login', [AuthController::class, 'login']);

// Login exclusivo del panel de Control (solo cuentas con perfil de Administrador).
Route::post('login-control', [AuthController::class, 'loginControl']);

// Vehiculo usa placa_vehiculo (string) como llave primaria
Route::apiResource('vehiculos', VehiculoController::class)->parameters([
    'vehiculos' => 'placa',
]);

// Reserva: endpoint extra para consultar cupos disponibles antes de reservar
Route::get('reservas/cupos-disponibles', [ReservaController::class, 'cuposDisponibles']);
Route::apiResource('reservas', ReservaController::class);