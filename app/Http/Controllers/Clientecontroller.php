<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Reserva;
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
        $cliente = Cliente::with('usuario', 'vehiculos', 'reservas')->find($id);

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

    /**
     * NOTA: si el cliente tiene vehículos o reservas asociadas, el borrado
     * falla por las llaves foráneas de la base de datos. Se captura esa
     * situación para devolver un mensaje entendible en vez de un error 500.
     */
    public function destroy(int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        try {
            $cliente->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'No se puede eliminar: este cliente tiene vehículos o reservas registradas.',
            ], 422);
        }

        return response()->json(['message' => 'Cliente eliminado correctamente'], 200);
    }

    /**
     * CONTROL — Reservas de un cliente puntual, usado por el modal "Ver
     * reservas" del panel de Control (verReservasCliente() en control.blade.php).
     */
    public function reservas(int $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        $reservas = Reserva::with('vehiculo', 'servicio')
            ->where('no_documento_cliente', $id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return response()->json(['reservas' => $reservas], 200);
    }

    /**
     * SITIO WEB — "Mi cuenta". Arma los datos del cliente en sesión, sus
     * vehículos, los tipos de vehículo disponibles (para el formulario de
     * registrar vehículo) y sus reservas (NUEVO: para la sección "Mis
     * reservas", con opción de cancelar).
     */
    public function miCuenta()
    {
        $clienteSesion = session('cliente');

        if (!$clienteSesion) {
            return view('mi-cuenta', [
                'clienteSesion' => null,
                'vehiculos' => collect(),
                'tiposVehiculo' => collect(),
                'reservas' => collect(),
            ]);
        }

        $cliente = Cliente::with('vehiculos.tipoVehiculo')->find($clienteSesion['no_documento_cliente']);
        $vehiculos = $cliente ? $cliente->vehiculos : collect();

        $tiposVehiculo = TipoVehiculo::where(function ($q) {
            $q->where('estado_vehiculo', 1)->orWhereNull('estado_vehiculo');
        })->get();

        // NUEVO: reservas del cliente (todas, sin importar el estado), para
        // que pueda ver su historial y cancelar las que sigan en proceso.
        $reservas = Reserva::with('vehiculo', 'servicio', 'tipoVehiculo', 'comprobantePago.metodoPago')
            ->where('no_documento_cliente', $clienteSesion['no_documento_cliente'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get();

        return view('mi-cuenta', [
            'clienteSesion' => $clienteSesion,
            'vehiculos' => $vehiculos,
            'tiposVehiculo' => $tiposVehiculo,
            'reservas' => $reservas,
        ]);
    }
}