<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdministradorController extends Controller
{
    public function index()
    {
        return response()->json(Administrador::with('usuario')->get(), 200);
    }

    public function show(int $id)
    {
        $administrador = Administrador::with('usuario', 'reservas')->find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        return response()->json($administrador, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_documento_administrador' => 'required|integer|unique:administrador,no_documento_administrador',
            'id_usuario' => 'required|exists:usuario,id_usuario|unique:administrador,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $administrador = Administrador::create($request->all());

        return response()->json($administrador, 201);
    }

    public function update(Request $request, int $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_usuario' => 'sometimes|required|exists:usuario,id_usuario|unique:administrador,id_usuario,' . $id . ',no_documento_administrador',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $administrador->update($request->all());

        return response()->json($administrador, 200);
    }

    public function destroy(int $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        $administrador->delete();

        return response()->json(['message' => 'Administrador eliminado correctamente'], 200);
    }
}
