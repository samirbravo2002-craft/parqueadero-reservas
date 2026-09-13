<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login para clientes (API JSON). Se conserva para Postman/pruebas,
     * pero el sitio web ahora usa loginWeb() con sesión de Laravel.
     */
    public function login(Request $request)
    {
        $usuario = $this->validarCredenciales($request);

        if ($usuario instanceof \Illuminate\Http\JsonResponse) {
            return $usuario; // el helper ya devolvió un error
        }

        if (!$usuario->cliente) {
            return response()->json([
                'message' => 'Esta cuenta no tiene un perfil de cliente asociado',
            ], 422);
        }

        return response()->json([
            'message' => 'Inicio de sesión correcto',
            'no_documento_cliente' => $usuario->cliente->no_documento_cliente,
            'nombre_usuario' => $usuario->nombre_usuario,
            'apellido_usuario' => $usuario->apellido_usuario,
        ], 200);
    }

    /**
     * Login exclusivo para el panel de Control (API JSON, solo administradores).
     */
    public function loginControl(Request $request)
    {
        $usuario = $this->validarCredenciales($request);

        if ($usuario instanceof \Illuminate\Http\JsonResponse) {
            return $usuario;
        }

        if (!$usuario->administrador) {
            return response()->json([
                'message' => 'Esta cuenta no tiene permisos de administrador',
            ], 403);
        }

        return response()->json([
            'message' => 'Inicio de sesión correcto',
            'no_documento_administrador' => $usuario->administrador->no_documento_administrador,
            'nombre_usuario' => $usuario->nombre_usuario,
            'apellido_usuario' => $usuario->apellido_usuario,
        ], 200);
    }

    /**
     * Login desde el sitio web (formulario normal, sin JS/AJAX).
     * Si las credenciales son válidas, guarda al cliente en la sesión de
     * Laravel (session('cliente')) en vez de localStorage, y redirige.
     */
    public function loginWeb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo_usuario' => 'required|email',
            'contrasenia' => 'required|string',
        ], [
            'correo_usuario.required' => 'Ingresa tu correo.',
            'contrasenia.required' => 'Ingresa tu contraseña.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('correo_usuario'));
        }

        $usuario = Usuario::with('cliente')
            ->where('correo_usuario', $request->correo_usuario)
            ->first();

        if (!$usuario || !Hash::check($request->contrasenia, $usuario->contrasenia)) {
            return back()
                ->withErrors(['login' => 'Correo o contraseña incorrectos'])
                ->withInput($request->only('correo_usuario'));
        }

        if ($usuario->estado_usuario == 0) {
            return back()
                ->withErrors(['login' => 'Esta cuenta está inactiva'])
                ->withInput($request->only('correo_usuario'));
        }

        if (!$usuario->cliente) {
            return back()
                ->withErrors(['login' => 'Esta cuenta no tiene un perfil de cliente asociado'])
                ->withInput($request->only('correo_usuario'));
        }

        $request->session()->put('cliente', [
            'no_documento_cliente' => $usuario->cliente->no_documento_cliente,
            'nombre_usuario' => $usuario->nombre_usuario,
            'apellido_usuario' => $usuario->apellido_usuario,
        ]);

        return redirect()->route('mi-cuenta')->with('status', 'Inicio de sesión correcto');
    }

    /**
     * Cierra la sesión del cliente en el sitio web.
     */
    public function logoutWeb(Request $request)
    {
        $request->session()->forget('cliente');

        return redirect()->route('inicio');
    }

    /**
     * Valida correo + contraseña contra la tabla usuario (usado por login/loginControl,
     * las versiones API en JSON). Devuelve el Usuario (con 'cliente' y 'administrador'
     * precargados) si todo está bien, o un JsonResponse de error listo para retornar tal cual.
     */
    private function validarCredenciales(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo_usuario' => 'required|email',
            'contrasenia' => 'required|string',
        ], [
            'correo_usuario.required' => 'Ingresa tu correo.',
            'contrasenia.required' => 'Ingresa tu contraseña.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $usuario = Usuario::with('cliente', 'administrador')
            ->where('correo_usuario', $request->correo_usuario)
            ->first();

        if (!$usuario || !Hash::check($request->contrasenia, $usuario->contrasenia)) {
            return response()->json(['message' => 'Correo o contraseña incorrectos'], 401);
        }

        if ($usuario->estado_usuario == 0) {
            return response()->json(['message' => 'Esta cuenta está inactiva'], 403);
        }

        return $usuario;
    }
}