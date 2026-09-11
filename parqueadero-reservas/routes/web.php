<?php

// Reemplazar el contenido de routes/web.php por esto

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('inicio');
})->name('inicio');

Route::get('/reservas', function () {
    return view('reservas');
})->name('reservas.vista');