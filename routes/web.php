<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.web');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout.web');
Route::post('/registro', [RegistroController::class, 'registrarClienteWeb'])->name('registro.web');

Route::get('/reservas', [ReservaController::class, 'crear'])->name('reservas.vista');
Route::post('/reservas', [ReservaController::class, 'guardar'])->name('reservas.guardar');
Route::get('/reservas/{id}/voucher', [ReservaController::class, 'voucher'])->name('reservas.voucher');

// ANTES: esta ruta devolvía view('mi-cuenta') directo, sin pasar por el
// controlador, así que la vista no recibía $clienteSesion/$vehiculos/$tiposVehiculo
// y tenía que reconstruir todo por JS + localStorage (lo que rompía el
// flujo, porque el login web guarda la sesión en el servidor, no en el navegador).
// AHORA: se usa ClienteController@miCuenta, que ya arma esos datos con
// session('cliente'), tal como reservas.vista usa ReservaController@crear.
Route::get('/mi-cuenta', [ClienteController::class, 'miCuenta'])->name('mi-cuenta');

// Ruta que faltaba: registrar un vehículo desde el formulario normal de
// "Mi cuenta" (sin JS/AJAX). El método guardarWeb() ya existía en
// VehiculoController pero no tenía ninguna ruta apuntando a él.
Route::post('/vehiculos', [VehiculoController::class, 'guardarWeb'])->name('vehiculos.guardar');

Route::get('/control', function () {
    return view('control');
})->name('control');