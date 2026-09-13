<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Voucher de reserva - Parqueadero</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @media print {
      .no-imprimir { display: none !important; }
      body { background: #fff !important; }
    }
  </style>
</head>
<body class="bg-light">

  <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 100vh;">
    <div class="card shadow-sm" style="max-width: 480px; width: 100%;">
      <div class="card-body p-4">

        <div class="text-center mb-4 no-imprimir">
          <i class="fa-solid fa-circle-check text-success fs-1"></i>
          <h2 class="h4 mt-2 mb-0">¡Reserva creada!</h2>
          <p class="text-muted">Muestra este voucher al administrador cuando llegues al parqueadero.</p>
        </div>

        <div class="border rounded-3 p-3 mb-3">
          <h3 class="h5 text-center fw-bold mb-3">
            <i class="fa-solid fa-car me-1"></i> VOUCHER DE RESERVA
          </h3>
          <hr>
          <dl class="row mb-0 small">
            <dt class="col-6">N° de reserva</dt>
            <dd class="col-6 text-end fw-bold">{{ $reserva->id_reserva }}</dd>

            <dt class="col-6">Cliente</dt>
            <dd class="col-6 text-end">
              {{ $reserva->cliente->usuario->nombre_usuario ?? '' }}
              {{ $reserva->cliente->usuario->apellido_usuario ?? '' }}
            </dd>

            <dt class="col-6">Documento</dt>
            <dd class="col-6 text-end">{{ $reserva->no_documento_cliente }}</dd>

            <dt class="col-6">Placa</dt>
            <dd class="col-6 text-end">{{ $reserva->placa_vehiculo }}</dd>

            <dt class="col-6">Tipo de vehículo</dt>
            <dd class="col-6 text-end">{{ $reserva->tipoVehiculo->nombre_tipo_vehiculo ?? '-' }}</dd>

            <dt class="col-6">Servicio</dt>
            <dd class="col-6 text-end">{{ $reserva->servicio->nombre_servicio ?? '-' }}</dd>

            <dt class="col-6">Fecha</dt>
            <dd class="col-6 text-end">{{ $reserva->fecha }}</dd>

            <dt class="col-6">Hora</dt>
            <dd class="col-6 text-end">{{ $reserva->hora }}</dd>

            <dt class="col-6">Método de pago</dt>
            <dd class="col-6 text-end">{{ $reserva->comprobantePago->metodoPago->nombre_metodo_pago ?? '-' }}</dd>
          </dl>
        </div>

        <div class="d-grid gap-2 no-imprimir">
          <a href="{{ route('reservas.vista') }}" class="btn btn-secondary">Hacer otra reserva</a>
        </div>

      </div>
    </div>
  </div>

</body>
</html>