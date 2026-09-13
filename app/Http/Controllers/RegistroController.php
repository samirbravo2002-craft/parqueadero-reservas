<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    /**
     * Registro público de un cliente nuevo (API JSON).
     * Se conserva para Postman/pruebas; el sitio web usa registrarClienteWeb().
     */
    public function registrarCliente(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglasCliente(), $this->mensajes());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rolCliente = Rol::where('nombre_rol', 'Cliente')->first();

        if (!$rolCliente) {
            return response()->json([
                'message' => 'No existe el rol "Cliente" en la base de datos. Créalo antes de registrar clientes.',
            ], 500);
        }

        $resultado = DB::transaction(function () use ($request, $rolCliente) {
            $usuario = Usuario::create([
                'tipo_documento' => $request->tipo_documento,
                'nombre_usuario' => $request->nombre_usuario,
                'apellido_usuario' => $request->apellido_usuario,
                'numero_celular' => $request->numero_celular,
                'correo_usuario' => $request->correo_usuario,
                'id_rol' => $rolCliente->id_rol,
                'contrasenia' => Hash::make($request->contrasenia),
                'estado_usuario' => 1,
            ]);

            $cliente = Cliente::create([
                'no_documento_cliente' => $request->no_documento_cliente,
                'id_usuario' => $usuario->id_usuario,
            ]);

            $cliente->load('usuario');

            return $cliente;
        });

        return response()->json([
            'message' => 'Cliente registrado correctamente',
            'cliente' => $resultado,
            'no_documento_cliente' => $resultado->no_documento_cliente,
        ], 201);
    }

    /**
     * Registro de cliente desde el sitio web (formulario normal, sin JS/AJAX).
     * Si todo sale bien, guarda al cliente en session('cliente') y redirige
     * a "Mi cuenta" para que registre su vehículo.
     */
    public function registrarClienteWeb(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglasCliente(), $this->mensajes());

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except(['contrasenia', 'contrasenia_confirmation']));
        }

        $rolCliente = Rol::where('nombre_rol', 'Cliente')->first();

        if (!$rolCliente) {
            return back()->withErrors([
                'registro' => 'No existe el rol "Cliente" en la base de datos. Contacta al administrador.',
            ]);
        }

        $cliente = DB::transaction(function () use ($request, $rolCliente) {
            $usuario = Usuario::create([
                'tipo_documento' => $request->tipo_documento,
                'nombre_usuario' => $request->nombre_usuario,
                'apellido_usuario' => $request->apellido_usuario,
                'numero_celular' => $request->numero_celular,
                'correo_usuario' => $request->correo_usuario,
                'id_rol' => $rolCliente->id_rol,
                'contrasenia' => Hash::make($request->contrasenia),
                'estado_usuario' => 1,
            ]);

            return Cliente::create([
                'no_documento_cliente' => $request->no_documento_cliente,
                'id_usuario' => $usuario->id_usuario,
            ]);
        });

        $request->session()->put('cliente', [
            'no_documento_cliente' => $cliente->no_documento_cliente,
            'nombre_usuario' => $request->nombre_usuario,
            'apellido_usuario' => $request->apellido_usuario,
        ]);

        return redirect()->route('mi-cuenta')->with('status', '¡Registro exitoso! Ahora registra tu vehículo.');
    }

    /**
     * Registro de un administrador nuevo (API JSON, sin formulario público).
     * Úsalo por Postman/curl para crear cuentas admin.
     */
    public function registrarAdministrador(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string|max:20',
            'no_documento_administrador' => 'required|integer|digits_between:6,10|unique:administrador,no_documento_administrador',
            'nombre_usuario' => 'required|string|max:20',
            'apellido_usuario' => 'required|string|max:20',
            'numero_celular' => 'required|digits:10',
            'correo_usuario' => [
                'required',
                'email:rfc',
                'max:100',
                'regex:/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/',
                'unique:usuario,correo_usuario',
            ],
            'contrasenia' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
        ], $this->mensajes());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rolAdministrador = Rol::where('nombre_rol', 'Administrador')->first();

        if (!$rolAdministrador) {
            return response()->json([
                'message' => 'No existe el rol "Administrador" en la base de datos. Créalo antes de registrar administradores.',
            ], 500);
        }

        $resultado = DB::transaction(function () use ($request, $rolAdministrador) {
            $usuario = Usuario::create([
                'tipo_documento' => $request->tipo_documento,
                'nombre_usuario' => $request->nombre_usuario,
                'apellido_usuario' => $request->apellido_usuario,
                'numero_celular' => $request->numero_celular,
                'correo_usuario' => $request->correo_usuario,
                'id_rol' => $rolAdministrador->id_rol,
                'contrasenia' => Hash::make($request->contrasenia),
                'estado_usuario' => 1,
            ]);

            $administrador = Administrador::create([
                'no_documento_administrador' => $request->no_documento_administrador,
                'id_usuario' => $usuario->id_usuario,
            ]);

            $administrador->load('usuario');

            return $administrador;
        });

        return response()->json([
            'message' => 'Administrador registrado correctamente',
            'administrador' => $resultado,
        ], 201);
    }

    /**
     * Reglas de validación del registro de cliente, compartidas entre
     * registrarCliente() (API) y registrarClienteWeb() (sitio).
     */
    private function reglasCliente(): array
    {
        return [
            'tipo_documento' => 'required|string|max:20',
            'no_documento_cliente' => 'required|integer|digits_between:6,10|unique:cliente,no_documento_cliente',
            'nombre_usuario' => 'required|string|max:20',
            'apellido_usuario' => 'required|string|max:20',
            'numero_celular' => 'required|digits:10',
            'correo_usuario' => [
                'required',
                'email:rfc',
                'max:100',
                'regex:/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/',
                'unique:usuario,correo_usuario',
            ],
            'contrasenia' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],
        ];
    }

    private function mensajes(): array
    {
        return [
            'numero_celular.digits' => 'El número de celular debe tener exactamente 10 dígitos numéricos.',
            'correo_usuario.email' => 'El correo no tiene un formato válido.',
            'correo_usuario.regex' => 'El correo debe incluir @ y un dominio válido (ejemplo: nombre@dominio.com).',
            'correo_usuario.unique' => 'Ese correo ya está registrado.',
            'contrasenia.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'contrasenia.regex' => 'La contraseña debe incluir al menos una letra y un número.',
            'contrasenia.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}