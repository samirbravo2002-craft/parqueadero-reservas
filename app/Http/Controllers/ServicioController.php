<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicioController extends Controller
{
    /**
     * Acepta un filtro opcional ?id_tipo_vehiculo=X para traer solo los
     * servicios configurados para ese tipo de vehículo (usado en la vista
     * de reservas, para que un cliente con un carro no vea servicios de moto).
     */
    public function index(Request $request)
    {
        $query = Servicio::with('tipoVehiculo');

        if ($request->filled('id_tipo_vehiculo')) {
            $query->where('id_tipo_vehiculo', $request->id_tipo_vehiculo);
        }

        return response()->json($query->get(), 200);
    }

    public function show(int $id)
    {
        $servicio = Servicio::with('tipoVehiculo')->find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_servicio' => 'required|string|max:10',
            'id_tipo_vehiculo' => 'required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'descripcion_servicio' => 'required|string|max:200',
            'costo_servicio' => 'required|numeric|min:0',
            'estado_servicio' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $servicio = Servicio::create($request->all());

        return response()->json($servicio, 201);
    }

    public function update(Request $request, int $id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_servicio' => 'sometimes|required|string|max:10',
            'id_tipo_vehiculo' => 'sometimes|required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'descripcion_servicio' => 'sometimes|required|string|max:200',
            'costo_servicio' => 'sometimes|required|numeric|min:0',
            'estado_servicio' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $servicio->update($request->all());

        return response()->json($servicio, 200);
    }

    public function destroy(int $id)
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente'], 200);
    }
}