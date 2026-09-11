<?php

namespace App\Http\Controllers;

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
     * Registro público de un cliente nuevo.
     * Crea el usuario y su registro de cliente asociado en una sola transacción.
     */
    public function registrarCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string|max:20',
            'no_documento_cliente' => 'required|integer|unique:cliente,no_documento_cliente',
            'nombre_usuario' => 'required|string|max:20',
            'apellido_usuario' => 'required|string|max:20',
            'numero_celular' => 'required|string|max:20',
            'contrasenia' => 'required|string|min:6|confirmed',
        ]);

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
        ], 201);
    }
}