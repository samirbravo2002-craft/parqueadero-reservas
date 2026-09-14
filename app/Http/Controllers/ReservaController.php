<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ComprobantePago;
use App\Models\MetodoPago;
use App\Models\Reserva;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\TipoVehiculo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    // Estados posibles de una reserva (columna estado_reserva).
    private const ESTADO_EN_PROCESO = 1;
    private const ESTADO_FINALIZADA = 2;

    /**
     * Cupos máximos disponibles por día, según el tipo de vehículo.
     * La comparación se hace por el nombre del tipo de vehículo en minúsculas.
     */
    private const CUPOS_POR_TIPO = [
        'carro' => 30,
        'moto' => 30,
        'bicicleta' => 20,
    ];

    /**
     * Documento del "cliente ocasional" genérico, reutilizado por
     * crearControl() cuando no se indica un cliente real: solo sirve
     * para poder marcar un cupo como ocupado. NUEVO.
     */
    private const DOCUMENTO_CLIENTE_GENERICO = 900000000;

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
            'placa_vehiculo' => 'required|exists:vehiculo,placa_vehiculo',
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
            'estado_reserva' => self::ESTADO_EN_PROCESO, // NUEVO
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
            'estado_reserva' => 'sometimes|required|in:1,2',
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
     * Consulta cuántos cupos hay disponibles para un tipo de vehículo en una fecha dada (API).
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
     * CONTROL — Lista solo las reservas que siguen en proceso (pendientes de atender).
     * NUEVO.
     */
    public function enProceso()
    {
        $reservas = Reserva::with(
            'cliente.usuario',
            'vehiculo',
            'servicio',
            'tipoVehiculo',
            'comprobantePago.metodoPago',
            'administrador.usuario'
        )
            ->where('estado_reserva', self::ESTADO_EN_PROCESO)
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return response()->json($reservas, 200);
    }

    /**
     * CONTROL — Marca una reserva como finalizada (libera el cupo de ese día).
     * NUEVO.
     */
    public function finalizar(int $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        if ($reserva->estado_reserva == self::ESTADO_FINALIZADA) {
            return response()->json(['message' => 'Esta reserva ya estaba finalizada'], 422);
        }

        $reserva->estado_reserva = self::ESTADO_FINALIZADA;
        $reserva->save();

        return response()->json([
            'message' => 'Reserva finalizada correctamente',
            'reserva' => $reserva,
        ], 200);
    }

    /**
     * CONTROL — Ganancias y cantidad de reservas del día de hoy.
     * NUEVO.
     */
    public function resumenHoy()
    {
        $hoy = now()->toDateString();

        $reservasHoy = Reserva::with('servicio')->where('fecha', $hoy)->get();

        return response()->json([
            'fecha' => $hoy,
            'cantidad_reservas' => $reservasHoy->count(),
            'reservas_en_proceso' => $reservasHoy->where('estado_reserva', self::ESTADO_EN_PROCESO)->count(),
            'reservas_finalizadas' => $reservasHoy->where('estado_reserva', self::ESTADO_FINALIZADA)->count(),
            'ganancias' => $reservasHoy->sum(fn ($r) => $r->servicio->costo_servicio ?? 0),
        ], 200);
    }

    /**
     * CONTROL — Crea una reserva desde el panel, ya sea para un cliente real
     * que llega al parqueadero (llenando documento/placa, con o sin cuenta
     * previa) o, en el caso más simple, solo para marcar un cupo como
     * ocupado sin capturar ningún dato del cliente ni del vehículo.
     *
     * ÚNICO campo obligatorio: id_tipo_vehiculo (para saber a qué cupo
     * descuenta). Todo lo demás es opcional:
     *   - Si no se manda no_documento_cliente, se usa un "cliente ocasional"
     *     genérico reutilizable (ver clienteOcasionalGenerico()).
     *   - Si no se manda placa_vehiculo, se usa un vehículo genérico por
     *     tipo (ver vehiculoOcasionalGenerico()).
     *   - Si no se manda id_servicio, se usa el primer servicio activo
     *     configurado para ese tipo de vehículo.
     *   - Si no se manda fecha/hora, se usa la fecha/hora actual.
     *   - Si no se manda id_metodo_pago, simplemente no se crea comprobante
     *     de pago (el pago se puede registrar después si hace falta).
     * MODIFICADO: simplificado para no exigir tantos campos.
     */
    public function crearControl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_documento_cliente' => 'nullable|integer|digits_between:6,10',
            'nombre_usuario' => 'nullable|string|max:20',
            'apellido_usuario' => 'nullable|string|max:20',
            'numero_celular' => 'nullable|digits:10',

            'placa_vehiculo' => 'nullable|string|max:10',
            'id_tipo_vehiculo' => 'required|exists:tipo_vehiculo,id_tipo_vehiculo',
            'color_vehiculo' => 'nullable|string|max:11',
            'marca_vehiculo' => 'nullable|string|max:20',
            'modelo_vehiculo' => 'nullable|string|max:20',

            'id_servicio' => 'nullable|exists:servicio,id_servicio',
            'fecha' => 'nullable|date',
            'hora' => 'nullable|string|max:10',
            'id_metodo_pago' => 'nullable|exists:metodo_pago,id_metodo_pago',
            'no_documento_administrador' => 'nullable|exists:administrador,no_documento_administrador',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $fecha = $request->fecha ?: now()->toDateString();
        $hora = $request->hora ?: now()->format('H:i');
        $placaEnviada = $request->filled('placa_vehiculo') ? strtoupper(trim($request->placa_vehiculo)) : null;

        try {
            $reserva = DB::transaction(function () use ($request, $placaEnviada, $fecha, $hora) {

                // 1) Cliente: si se indicó documento, se busca o se crea (como
                //    antes). Si no se indicó nada, se usa el cliente genérico.
                if ($request->filled('no_documento_cliente')) {
                    $cliente = Cliente::find($request->no_documento_cliente);

                    if (!$cliente) {
                        if (!$request->nombre_usuario || !$request->apellido_usuario || !$request->numero_celular) {
                            throw new \Exception('FALTAN_DATOS_CLIENTE');
                        }

                        $rolCliente = Rol::where('nombre_rol', 'Cliente')->first();

                        $usuario = Usuario::create([
                            'tipo_documento' => 'CC',
                            'nombre_usuario' => $request->nombre_usuario,
                            'apellido_usuario' => $request->apellido_usuario,
                            'numero_celular' => $request->numero_celular,
                            'correo_usuario' => 'walkin_' . $request->no_documento_cliente . '@parqueadero.local',
                            'id_rol' => $rolCliente->id_rol,
                            'contrasenia' => Hash::make(Str::random(16)),
                            'estado_usuario' => 1,
                        ]);

                        $cliente = Cliente::create([
                            'no_documento_cliente' => $request->no_documento_cliente,
                            'id_usuario' => $usuario->id_usuario,
                        ]);
                    }
                } else {
                    $cliente = $this->clienteOcasionalGenerico();
                }

                // 2) Vehículo: si se indicó placa, se busca o se crea a nombre
                //    de ese cliente (como antes). Si no, se usa un vehículo
                //    genérico por tipo, solo para ocupar el cupo.
                if ($placaEnviada) {
                    $vehiculo = Vehiculo::find($placaEnviada);

                    if (!$vehiculo) {
                        $vehiculo = Vehiculo::create([
                            'placa_vehiculo' => $placaEnviada,
                            'no_documento_cliente' => $cliente->no_documento_cliente,
                            'id_tipo_vehiculo' => $request->id_tipo_vehiculo,
                            'color_vehiculo' => $request->color_vehiculo,
                            'marca_vehiculo' => $request->marca_vehiculo,
                            'modelo_vehiculo' => $request->modelo_vehiculo,
                            'estado_vehiculo' => 1,
                        ]);
                    } elseif ($vehiculo->no_documento_cliente != $cliente->no_documento_cliente) {
                        throw new \Exception('PLACA_DE_OTRO_CLIENTE');
                    }
                } else {
                    $vehiculo = $this->vehiculoOcasionalGenerico($request->id_tipo_vehiculo, $cliente->no_documento_cliente);
                }

                // 3) Servicio: si no se indicó uno, se usa el primero activo
                //    configurado para ese tipo de vehículo (solo para dejar
                //    constancia de qué tipo ocupó el cupo).
                $idServicio = $request->id_servicio;

                if (!$idServicio) {
                    $servicio = Servicio::where('id_tipo_vehiculo', $request->id_tipo_vehiculo)
                        ->where(function ($q) {
                            $q->where('estado_servicio', 1)->orWhereNull('estado_servicio');
                        })
                        ->first();

                    if (!$servicio) {
                        throw new \Exception('SIN_SERVICIO_CONFIGURADO');
                    }

                    $idServicio = $servicio->id_servicio;
                }

                // 4) Cupo disponible para ese tipo de vehículo/fecha.
                $disponibilidad = $this->verificarCupoDisponible($request->id_tipo_vehiculo, $fecha);

                if (!$disponibilidad['disponible']) {
                    throw new \Exception('SIN_CUPO');
                }

                // 5) Reserva.
                $reserva = Reserva::create([
                    'no_documento_cliente' => $cliente->no_documento_cliente,
                    'placa_vehiculo' => $vehiculo->placa_vehiculo,
                    'no_documento_administrador' => $request->no_documento_administrador,
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'id_servicio' => $idServicio,
                    'id_tipo_vehiculo' => $request->id_tipo_vehiculo,
                    'estado_reserva' => self::ESTADO_EN_PROCESO,
                ]);

                // 6) Comprobante de pago: solo si se indicó un método de pago.
                if ($request->filled('id_metodo_pago')) {
                    ComprobantePago::create([
                        'id_reserva' => $reserva->id_reserva,
                        'id_metodo_pago' => $request->id_metodo_pago,
                    ]);
                }

                return $reserva;
            });
        } catch (\Exception $e) {
            return match ($e->getMessage()) {
                'SIN_CUPO' => response()->json(['message' => 'No hay cupos disponibles para ese tipo de vehículo en esa fecha'], 409),
                'PLACA_DE_OTRO_CLIENTE' => response()->json(['message' => 'Esa placa ya está registrada a nombre de otro cliente'], 422),
                'FALTAN_DATOS_CLIENTE' => response()->json(['message' => 'Ese documento no está registrado; completa nombre, apellido y celular para crearlo'], 422),
                'SIN_SERVICIO_CONFIGURADO' => response()->json(['message' => 'No hay ningún servicio configurado para ese tipo de vehículo'], 422),
                default => response()->json(['message' => 'Ocurrió un error al crear la reserva'], 500),
            };
        }

        $reserva->load('cliente.usuario', 'vehiculo', 'servicio', 'tipoVehiculo', 'comprobantePago.metodoPago');

        return response()->json($reserva, 201);
    }

    /**
     * Devuelve (creándolo si hace falta) el cliente genérico "ocasional"
     * que se reutiliza cuando crearControl() se usa solo para ocupar un
     * cupo, sin capturar datos de un cliente real. NUEVO.
     */
    private function clienteOcasionalGenerico(): Cliente
    {
        $cliente = Cliente::find(self::DOCUMENTO_CLIENTE_GENERICO);

        if ($cliente) {
            return $cliente;
        }

        $rolCliente = Rol::where('nombre_rol', 'Cliente')->first();

        $usuario = Usuario::create([
            'tipo_documento' => 'CC',
            'nombre_usuario' => 'Ocupación',
            'apellido_usuario' => 'Control',
            'numero_celular' => '0000000000',
            'correo_usuario' => 'ocupacion.control@parqueadero.local',
            'id_rol' => $rolCliente->id_rol,
            'contrasenia' => Hash::make(Str::random(16)),
            'estado_usuario' => 1,
        ]);

        return Cliente::create([
            'no_documento_cliente' => self::DOCUMENTO_CLIENTE_GENERICO,
            'id_usuario' => $usuario->id_usuario,
        ]);
    }

    /**
     * Devuelve (creándolo si hace falta) un vehículo genérico por tipo de
     * vehículo, usado solo para ocupar un cupo sin pedir placa real. Varias
     * reservas pueden compartir el mismo vehículo genérico sin problema,
     * ya que aquí solo importa contar cupos por tipo/fecha. NUEVO.
     */
    private function vehiculoOcasionalGenerico(int $idTipoVehiculo, int $noDocumentoCliente): Vehiculo
    {
        $placaGenerica = 'OCUPA' . $idTipoVehiculo;

        $vehiculo = Vehiculo::find($placaGenerica);

        if ($vehiculo) {
            return $vehiculo;
        }

        return Vehiculo::create([
            'placa_vehiculo' => $placaGenerica,
            'no_documento_cliente' => $noDocumentoCliente,
            'id_tipo_vehiculo' => $idTipoVehiculo,
            'color_vehiculo' => null,
            'marca_vehiculo' => null,
            'modelo_vehiculo' => null,
            'estado_vehiculo' => 1,
        ]);
    }

    /**
     * SITIO WEB — Paso 1 y 2 del formulario de reserva (sin JS).
     * Muestra los vehículos del cliente en sesión. Si ya viene "placa_vehiculo"
     * en la URL (el cliente ya eligió su vehículo), filtra los servicios por
     * el tipo de ese vehículo y muestra el resto del formulario + métodos de pago.
     */
    public function crear(Request $request)
    {
        $clienteSesion = session('cliente');

        if (!$clienteSesion) {
            return redirect()->route('inicio')->with('status', 'Necesitas iniciar sesión para reservar.');
        }

        $cliente = Cliente::with('vehiculos.tipoVehiculo')->find($clienteSesion['no_documento_cliente']);
        $vehiculos = $cliente ? $cliente->vehiculos : collect();

        $vehiculoSeleccionado = null;
        $servicios = collect();

        if ($request->filled('placa_vehiculo')) {
            $vehiculoSeleccionado = $vehiculos->firstWhere('placa_vehiculo', $request->placa_vehiculo);

            if ($vehiculoSeleccionado) {
                $servicios = Servicio::where('id_tipo_vehiculo', $vehiculoSeleccionado->id_tipo_vehiculo)
                    ->where(function ($q) {
                        $q->where('estado_servicio', 1)->orWhereNull('estado_servicio');
                    })
                    ->get();
            }
        }

        $metodosPago = MetodoPago::where(function ($q) {
            $q->where('estado_metodo_pago', 1)->orWhereNull('estado_metodo_pago');
        })->get();

        return view('reservas', [
            'cliente' => $clienteSesion,
            'vehiculos' => $vehiculos,
            'vehiculoSeleccionado' => $vehiculoSeleccionado,
            'servicios' => $servicios,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * SITIO WEB — Procesa el formulario de reserva: crea la reserva y,
     * en la misma transacción, el comprobante de pago con el método elegido.
     */
    public function guardar(Request $request)
    {
        $clienteSesion = session('cliente');

        if (!$clienteSesion) {
            return redirect()->route('inicio')->with('status', 'Necesitas iniciar sesión para reservar.');
        }

        $validator = Validator::make($request->all(), [
            'placa_vehiculo' => 'required|exists:vehiculo,placa_vehiculo',
            'id_servicio' => 'required|exists:servicio,id_servicio',
            'fecha' => 'required|date',
            'hora' => 'required|string|max:10',
            'id_metodo_pago' => 'required|exists:metodo_pago,id_metodo_pago',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $vehiculo = Vehiculo::find($request->placa_vehiculo);

        if (!$vehiculo || $vehiculo->no_documento_cliente != $clienteSesion['no_documento_cliente']) {
            return back()
                ->withErrors(['placa_vehiculo' => 'Ese vehículo no está registrado a tu nombre'])
                ->withInput();
        }

        $disponibilidad = $this->verificarCupoDisponible($vehiculo->id_tipo_vehiculo, $request->fecha);

        if (!$disponibilidad['disponible']) {
            return back()
                ->withErrors(['fecha' => 'No hay cupos disponibles para ese tipo de vehículo en esa fecha'])
                ->withInput();
        }

        $reserva = DB::transaction(function () use ($request, $clienteSesion, $vehiculo) {
            $reserva = Reserva::create([
                'no_documento_cliente' => $clienteSesion['no_documento_cliente'],
                'placa_vehiculo' => $request->placa_vehiculo,
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'id_servicio' => $request->id_servicio,
                'id_tipo_vehiculo' => $vehiculo->id_tipo_vehiculo,
                'estado_reserva' => self::ESTADO_EN_PROCESO, // NUEVO
            ]);

            ComprobantePago::create([
                'id_reserva' => $reserva->id_reserva,
                'id_metodo_pago' => $request->id_metodo_pago,
            ]);

            return $reserva;
        });

        return redirect()->route('reservas.voucher', $reserva->id_reserva);
    }

    /**
     * SITIO WEB — Muestra el voucher de una reserva ya creada.
     */
    public function voucher(int $id)
    {
        $reserva = Reserva::with('cliente.usuario', 'vehiculo', 'servicio', 'tipoVehiculo', 'comprobantePago.metodoPago')
            ->find($id);

        if (!$reserva) {
            abort(404);
        }

        return view('reserva-voucher', ['reserva' => $reserva]);
    }

    /**
     * Verifica si hay cupo disponible para un tipo de vehículo en una fecha.
     * NOTA: solo cuenta reservas EN PROCESO — una reserva finalizada ya
     * liberó su cupo para ese día.
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
            ->where('fecha', $fecha)
            ->where('estado_reserva', self::ESTADO_EN_PROCESO); // NUEVO filtro

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