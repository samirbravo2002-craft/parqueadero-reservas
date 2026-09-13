<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermisoController extends Controller
{
    public function index()
    {
        return response()->json(Permiso::all(), 200);
    }

    public function show(int $id)
    {
        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }

        return response()->json($permiso, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion_permiso' => 'required|string|max:200',
            'peticion' => 'required|string|max:20',
            'endpoint' => 'required|string|max:50',
            'estado_permiso' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $permiso = Permiso::create($request->all());

        return response()->json($permiso, 201);
    }

    public function update(Request $request, int $id)
    {
        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'descripcion_permiso' => 'sometimes|required|string|max:200',
            'peticion' => 'sometimes|required|string|max:20',
            'endpoint' => 'sometimes|required|string|max:50',
            'estado_permiso' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $permiso->update($request->all());

        return response()->json($permiso, 200);
    }

    public function destroy(int $id)
    {
        $permiso = Permiso::find($id);

        if (!$permiso) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        }

        $permiso->delete();

        return response()->json(['message' => 'Permiso eliminado correctamente'], 200);
    }
}
