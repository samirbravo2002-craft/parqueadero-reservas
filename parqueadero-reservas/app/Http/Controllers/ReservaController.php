<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    /**
     * Cupos máximos disponibles por día, según el tipo de vehículo.
     * La comparación se hace por el nombre del tipo de vehículo en minúsculas.
     */
    private const CUPOS_POR_TIPO = [
        'carro' => 30,
        'moto' => 30,
        'bicicleta' => 20,
    ];

    public function index()
    {
        return response()->json(
            Reserva::with('cliente.usuario', 'vehiculo', 'administrador.usuario', 'servicio', 'tipoVehiculo')->get(),
            200
        );
    }

    public function show(int $id)
    {
        $reserva = Reserva::with('cliente.usuario', 'vehiculo', 'administrador.usuario', 'servicio', 'tipoVehiculo', 'comprobantePago')
            ->find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        return response()->json($reserva, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'required|exists:cliente,no_documento_cliente',
            '' => 'required|exists:vehiculo,placa_vehiculo',
            'fecha' => 'required|date',
            'hora' => 'required|string|max:10',
            'id_servicio' => 'required|exists:servicio,id_servicio',
            // El administrador es opcional: se asigna cuando atiende la reserva.
            'no_documento_administrador' => 'nullable|exists:administrador,no_documento_administrador',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehiculo = Vehiculo::find($request->placa_vehiculo);

        // El vehículo debe pertenecer al cliente que está reservando.
        if ($vehiculo->no_documento_cliente != $request->no_documento_cliente) {
            return response()->json([
                'message' => 'Ese vehículo no está registrado a nombre del cliente indicado',
            ], 422);
        }

        $idTipoVehiculo = $vehiculo->id_tipo_vehiculo;

        $disponibilidad = $this->verificarCupoDisponible($idTipoVehiculo, $request->fecha);

        if (!$disponibilidad['disponible']) {
            return response()->json([
                'message' => 'No hay cupos disponibles para ese tipo de vehículo en la fecha seleccionada',
                'cupos_totales' => $disponibilidad['cupos_totales'],
                'cupos_ocupados' => $disponibilidad['cupos_ocupados'],
            ], 409);
        }

        $reserva = Reserva::create([
            'no_documento_cliente' => $request->no_documento_cliente,
            'placa_vehiculo' => $request->placa_vehiculo,
            'no_documento_administrador' => $request->no_documento_administrador,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'id_servicio' => $request->id_servicio,
            'id_tipo_vehiculo' => $idTipoVehiculo,
        ]);

        $reserva->load('cliente.usuario', 'vehiculo', 'servicio', 'tipoVehiculo');

        return response()->json($reserva, 201);
    }

    public function update(Request $request, int $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'sometimes|required|exists:cliente,no_documento_cliente',
            'placa_vehiculo' => 'sometimes|required|exists:vehiculo,placa_vehiculo',
            'no_documento_administrador' => 'nullable|exists:administrador,no_documento_administrador',
            'fecha' => 'sometimes|required|date',
            'hora' => 'sometimes|required|string|max:10',
            'id_servicio' => 'sometimes|required|exists:servicio,id_servicio',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $datos = $request->all();

        // Si cambia la placa, se vuelve a derivar el tipo de vehículo.
        if ($request->has('placa_vehiculo')) {
            $vehiculo = Vehiculo::find($request->placa_vehiculo);
            $datos['id_tipo_vehiculo'] = $vehiculo->id_tipo_vehiculo;
        }

        // Si cambia la placa o la fecha, se vuelve a verificar el cupo
        // (excluyendo la propia reserva que se está actualizando).
        if ($request->has('placa_vehiculo') || $request->has('fecha')) {
            $idTipoVehiculo = $datos['id_tipo_vehiculo'] ?? $reserva->id_tipo_vehiculo;
            $fecha = $request->fecha ?? $reserva->fecha;

            $disponibilidad = $this->verificarCupoDisponible($idTipoVehiculo, $fecha, $reserva->id_reserva);

            if (!$disponibilidad['disponible']) {
                return response()->json([
                    'message' => 'No hay cupos disponibles para ese tipo de vehículo en la fecha seleccionada',
                    'cupos_totales' => $disponibilidad['cupos_totales'],
                    'cupos_ocupados' => $disponibilidad['cupos_ocupados'],
                ], 409);
            }
        }

        $reserva->update($datos);

        return response()->json($reserva, 200);
    }

    public function destroy(int $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $reserva->delete();

        return response()->json(['message' => 'Reserva eliminada correctamente'], 200);
    }

    /**
     * Consulta cuántos cupos hay disponibles para un tipo de vehículo en una fecha dada.
     */
    public function cuposDisponibles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_tipo_vehiculo' => 'required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $disponibilidad = $this->verificarCupoDisponible($request->id_tipo_vehiculo, $request->fecha);

        return response()->json([
            'cupos_totales' => $disponibilidad['cupos_totales'],
            'cupos_ocupados' => $disponibilidad['cupos_ocupados'],
            'cupos_disponibles' => $disponibilidad['cupos_totales'] - $disponibilidad['cupos_ocupados'],
        ], 200);
    }

    /**
     * Verifica si hay cupo disponible para un tipo de vehículo en una fecha.
     */
    private function verificarCupoDisponible(int $idTipoVehiculo, string $fecha, ?int $excluirReservaId = null)
    {
        $tipoVehiculo = TipoVehiculo::find($idTipoVehiculo);
        $nombreTipo = $tipoVehiculo ? strtolower(trim($tipoVehiculo->nombre_tipo_vehiculo)) : null;

        $cuposTotales = self::CUPOS_POR_TIPO[$nombreTipo] ?? null;

        if ($cuposTotales === null) {
            return [
                'disponible' => true,
                'cupos_totales' => null,
                'cupos_ocupados' => 0,
            ];
        }

        $query = Reserva::where('id_tipo_vehiculo', $idTipoVehiculo)
            ->where('fecha', $fecha);

        if ($excluirReservaId) {
            $query->where('id_reserva', '!=', $excluirReservaId);
        }

        $cuposOcupados = $query->count();

        return [
            'disponible' => $cuposOcupados < $cuposTotales,
            'cupos_totales' => $cuposTotales,
            'cupos_ocupados' => $cuposOcupados,
        ];
    }
}