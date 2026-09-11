<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string|max:20',
            'nombre_usuario' => 'required|string|max:20',
            'apellido_usuario' => 'required|string|max:20',
            'numero_celular' => 'required|string|max:20',
            'id_rol' => 'required|exists:rol,id_rol',
            'contrasenia' => 'required|string|min:6',
            'estado_usuario' => 'boolean',
        ]);

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

        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'sometimes|required|string|max:20',
            'nombre_usuario' => 'sometimes|required|string|max:20',
            'apellido_usuario' => 'sometimes|required|string|max:20',
            'numero_celular' => 'sometimes|required|string|max:20',
            'id_rol' => 'sometimes|required|exists:rol,id_rol',
            'contrasenia' => 'sometimes|required|string|min:6',
            'estado_usuario' => 'boolean',
        ]);

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
