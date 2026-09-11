<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetodoPagoController extends Controller
{
    public function index()
    {
        return response()->json(MetodoPago::all(), 200);
    }

    public function show(int $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        return response()->json($metodoPago, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_metodo_pago' => 'required|string|max:20',
            'estado_metodo_pago' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $metodoPago = MetodoPago::create($request->all());

        return response()->json($metodoPago, 201);
    }

    public function update(Request $request, int $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_metodo_pago' => 'sometimes|required|string|max:20',
            'estado_metodo_pago' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $metodoPago->update($request->all());

        return response()->json($metodoPago, 200);
    }

    public function destroy(int $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $metodoPago->delete();

        return response()->json(['message' => 'Método de pago eliminado correctamente'], 200);
    }
}
