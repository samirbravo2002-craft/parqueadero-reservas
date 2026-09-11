<?php

namespace App\Http\Controllers;

use App\Models\ComprobantePago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComprobantePagoController extends Controller
{
    public function index()
    {
        return response()->json(ComprobantePago::with('reserva', 'metodoPago')->get(), 200);
    }

    public function show(int $id)
    {
        $comprobante = ComprobantePago::with('reserva', 'metodoPago')->find($id);

        if (!$comprobante) {
            return response()->json(['message' => 'Comprobante de pago no encontrado'], 404);
        }

        return response()->json($comprobante, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_reserva' => 'required|exists:reserva,id_reserva|unique:comprobante_pago,id_reserva',
            'id_metodo_pago' => 'required|exists:metodo_pago,id_metodo_pago',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comprobante = ComprobantePago::create($request->all());

        return response()->json($comprobante, 201);
    }

    public function update(Request $request, int $id)
    {
        $comprobante = ComprobantePago::find($id);

        if (!$comprobante) {
            return response()->json(['message' => 'Comprobante de pago no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_reserva' => 'sometimes|required|exists:reserva,id_reserva|unique:comprobante_pago,id_reserva,' . $id . ',id_comprobante_pago',
            'id_metodo_pago' => 'sometimes|required|exists:metodo_pago,id_metodo_pago',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comprobante->update($request->all());

        return response()->json($comprobante, 200);
    }

    public function destroy(int $id)
    {
        $comprobante = ComprobantePago::find($id);

        if (!$comprobante) {
            return response()->json(['message' => 'Comprobante de pago no encontrado'], 404);
        }

        $comprobante->delete();

        return response()->json(['message' => 'Comprobante de pago eliminado correctamente'], 200);
    }
}
