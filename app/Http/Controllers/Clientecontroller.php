<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TipoVehiculo;
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

    /**
     * Lista las reservas de un cliente puntual, usadas por el módulo Control
     * para mostrar el historial de reservas de cada cliente.
     */
    public function reservas(int $id)
    {
        $cliente = Cliente::with([
            'usuario',
            'reservas.vehiculo',
            'reservas.servicio',
            'reservas.tipoVehiculo',
            'reservas.administrador.usuario',
            'reservas.comprobantePago.metodoPago',
        ])->find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json([
            'cliente' => $cliente->usuario,
            'no_documento_cliente' => $cliente->no_documento_cliente,
            'reservas' => $cliente->reservas,
        ], 200);
    }

    /**
     * SITIO WEB — Página "Mi cuenta": lista los vehículos del cliente en
     * sesión y los tipos de vehículo disponibles para el formulario de
     * registro. Todo se renderiza en el servidor, sin llamadas JS al API.
     */
    public function miCuenta(Request $request)
    {
        $clienteSesion = session('cliente');

        if (!$clienteSesion) {
            return view('mi-cuenta', [
                'clienteSesion' => null,
                'vehiculos' => collect(),
                'tiposVehiculo' => collect(),
            ]);
        }

        $cliente = Cliente::with('vehiculos.tipoVehiculo')->find($clienteSesion['no_documento_cliente']);
        $vehiculos = $cliente ? $cliente->vehiculos : collect();

        $tiposVehiculo = TipoVehiculo::where(function ($q) {
            $q->where('estado_vehiculo', 1)->orWhereNull('estado_vehiculo');
        })->get();

        return view('mi-cuenta', [
            'clienteSesion' => $clienteSesion,
            'vehiculos' => $vehiculos,
            'tiposVehiculo' => $tiposVehiculo,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'required|integer|digits_between:6,10|unique:cliente,no_documento_cliente',
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