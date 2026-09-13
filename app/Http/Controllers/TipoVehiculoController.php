<?php

namespace App\Http\Controllers;

use App\Models\TipoVehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoVehiculoController extends Controller
{
    public function index()
    {
        return response()->json(TipoVehiculo::all(), 200);
    }

    public function show(int $id)
    {
        $tipoVehiculo = TipoVehiculo::find($id);

        if (!$tipoVehiculo) {
            return response()->json(['message' => 'Tipo de vehículo no encontrado'], 404);
        }

        return response()->json($tipoVehiculo, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_tipo_vehiculo' => 'required|string|max:20',
            'estado_vehiculo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tipoVehiculo = TipoVehiculo::create($request->all());

        return response()->json($tipoVehiculo, 201);
    }

    public function update(Request $request, int $id)
    {
        $tipoVehiculo = TipoVehiculo::find($id);

        if (!$tipoVehiculo) {
            return response()->json(['message' => 'Tipo de vehículo no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_tipo_vehiculo' => 'sometimes|required|string|max:20',
            'estado_vehiculo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tipoVehiculo->update($request->all());

        return response()->json($tipoVehiculo, 200);
    }

    public function destroy(int $id)
    {
        $tipoVehiculo = TipoVehiculo::find($id);

        if (!$tipoVehiculo) {
            return response()->json(['message' => 'Tipo de vehículo no encontrado'], 404);
        }

        $tipoVehiculo->delete();

        return response()->json(['message' => 'Tipo de vehículo eliminado correctamente'], 200);
    }
}
