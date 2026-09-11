<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehiculoController extends Controller
{
    public function index()
    {
        return response()->json(Vehiculo::with('cliente', 'tipoVehiculo')->get(), 200);
    }

    public function show(string $placa)
    {
        $vehiculo = Vehiculo::with('cliente', 'tipoVehiculo')->find($placa);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        return response()->json($vehiculo, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placa_vehiculo' => 'required|string|max:10|unique:vehiculo,placa_vehiculo',
            'no_documento_cliente' => 'required|exists:cliente,no_documento_cliente',
            'id_tipo_vehiculo' => 'required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'color_vehiculo' => 'nullable|string|max:11',
            'marca_vehiculo' => 'nullable|string|max:20',
            'modelo_vehiculo' => 'nullable|string|max:20',
            'estado_vehiculo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehiculo = Vehiculo::create($request->all());

        return response()->json($vehiculo, 201);
    }

    public function update(Request $request, string $placa)
    {
        $vehiculo = Vehiculo::find($placa);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'sometimes|required|exists:cliente,no_documento_cliente',
            'id_tipo_vehiculo' => 'sometimes|required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'color_vehiculo' => 'nullable|string|max:11',
            'marca_vehiculo' => 'nullable|string|max:20',
            'modelo_vehiculo' => 'nullable|string|max:20',
            'estado_vehiculo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehiculo->update($request->all());

        return response()->json($vehiculo, 200);
    }

    public function destroy(string $placa)
    {
        $vehiculo = Vehiculo::find($placa);

        if (!$vehiculo) {
            return response()->json(['message' => 'Vehículo no encontrado'], 404);
        }

        $vehiculo->delete();

        return response()->json(['message' => 'Vehículo eliminado correctamente'], 200);
    }
}
