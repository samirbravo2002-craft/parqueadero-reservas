<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index()
    {
        return response()->json(Cliente::with('usuario')->get(), 200);
    }

    public function show(int $id)
    {
        $cliente = Cliente::with('usuario', 'vehiculos')->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json($cliente, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'required|integer|unique:cliente,no_documento_cliente',
            'id_usuario' => 'required|exists:usuario,id_usuario|unique:cliente,id_usuario',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    public function update(Request $request, int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_usuario' => 'sometimes|required|exists:usuario,id_usuario|unique:cliente,id_usuario,' . $id . ',no_documento_cliente',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cliente->update($request->all());

        return response()->json($cliente, 200);
    }

    public function destroy(int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente'], 200);
    }
}
