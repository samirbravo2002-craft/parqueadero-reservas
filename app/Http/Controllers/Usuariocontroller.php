<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    /**
     * Reglas de validación reutilizables para no repetirlas en store() y update().
     * $idParaIgnorar se usa en 'unique' cuando estamos actualizando un registro existente.
     */
    private function reglas(?int $idParaIgnorar = null, bool $esActualizacion = false): array
    {
        $prefijo = $esActualizacion ? 'sometimes|required' : 'required';

        $reglaCorreoUnique = 'unique:usuario,correo_usuario';
        if ($idParaIgnorar) {
            $reglaCorreoUnique .= ',' . $idParaIgnorar . ',id_usuario';
        }

        return [
            'tipo_documento' => $prefijo . '|string|max:20',
            'nombre_usuario' => $prefijo . '|string|max:20',
            'apellido_usuario' => $prefijo . '|string|max:20',

            // Solo dígitos, 10 números (celular colombiano estándar).
            'numero_celular' => $prefijo . '|digits:10',

            // Debe tener @ y un dominio válido (cualquier extensión: .com, .co, .net, etc.).
            'correo_usuario' => [
                $prefijo,
                'email:rfc',
                'max:100',
                'regex:/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/',
                $reglaCorreoUnique,
            ],

            'id_rol' => $prefijo . '|exists:rol,id_rol',

            // Mínimo 8 caracteres, con al menos una letra y un número, y debe confirmarse.
            'contrasenia' => [
                $prefijo,
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/',
            ],

            'estado_usuario' => 'boolean',
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

    public function index()
    {
        return response()->json(Usuario::with('rol')->get(), 200);
    }

    public function show(int $id)
    {
        $usuario = Usuario::with('rol', 'cliente', 'administrador')->find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->reglas(), $this->mensajes());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $data['contrasenia'] = Hash::make($data['contrasenia']);

        $usuario = Usuario::create($data);

        return response()->json($usuario, 201);
    }

    public function update(Request $request, int $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            $this->reglas($id, true),
            $this->mensajes()
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();

        if (isset($data['contrasenia'])) {
            $data['contrasenia'] = Hash::make($data['contrasenia']);
        }

        $usuario->update($data);

        return response()->json($usuario, 200);
    }

    public function destroy(int $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}