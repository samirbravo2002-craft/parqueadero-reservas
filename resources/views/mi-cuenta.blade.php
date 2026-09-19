<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi cuenta - Parqueadero</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

  <!-- Barra de Navegación -->
  <nav class="navbar navbar-expand bg-white border-bottom px-4 py-2">
    <div class="container-fluid p-0">
      <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('inicio') }}">
        <i class="fa-solid fa-car fs-4"></i>
        <span>PARQUEADERO RESERVAS</span>
      </a>
      <div class="navbar-nav gap-3">
        <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('inicio') }}">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('reservas.vista') }}">
          <i class="fa-solid fa-calendar-days"></i> Reservar
        </a>
        <a class="nav-link text-primary fw-semibold d-flex align-items-center gap-1 active" href="{{ route('mi-cuenta') }}">
          <i class="fa-solid fa-user"></i> Mi cuenta
        </a>
        <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('control') }}">
          <i class="fa-solid fa-gear"></i> Control
        </a>
      </div>
    </div>
  </nav>

  @unless($clienteSesion)
    
    <div class="container py-5">
      <div class="alert alert-warning text-center">
        Aún no te has registrado.
        <a href="{{ route('inicio') }}" class="alert-link">Regístrate aquí</a> primero.
      </div>
    </div>
  @else

    <div class="container py-5">

      @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      <div class="mb-4">
        <h2 class="h4 mb-1">Hola, {{ $clienteSesion['nombre_usuario'] }} {{ $clienteSesion['apellido_usuario'] }} 👋</h2>
        <p class="text-muted mb-0">Documento: {{ $clienteSesion['no_documento_cliente'] }}</p>
      </div>

      <div class="row g-4">

        <!-- Formulario para registrar un nuevo vehículo -->
        <div class="col-lg-5">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h3 class="h5 mb-3">Registrar un vehículo</h3>

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              {{-- Formulario normal guardar vehiculo --}}
              <form method="POST" action="{{ route('vehiculos.guardar') }}">
                @csrf

                <div class="mb-3">
                  <label for="placa_vehiculo" class="form-label">Placa</label>
                  <input type="text" class="form-control text-uppercase" id="placa_vehiculo" name="placa_vehiculo"
                         value="{{ old('placa_vehiculo') }}" placeholder="ABC123" maxlength="10" required>
                </div>

                <div class="mb-3">
                  <label for="id_tipo_vehiculo" class="form-label">Tipo de vehículo</label>
                  <select class="form-select" id="id_tipo_vehiculo" name="id_tipo_vehiculo" required>
                    <option value="" disabled {{ old('id_tipo_vehiculo') ? '' : 'selected' }}>-- Selecciona un tipo --</option>
                    @forelse ($tiposVehiculo as $t)
                      <option value="{{ $t->id_tipo_vehiculo }}" {{ old('id_tipo_vehiculo') == $t->id_tipo_vehiculo ? 'selected' : '' }}>
                        {{ $t->nombre_tipo_vehiculo }}
                      </option>
                    @empty
                      <option value="" disabled>No hay tipos de vehículo configurados</option>
                    @endforelse
                  </select>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-4">
                    <label for="color_vehiculo" class="form-label">Color</label>
                    <input type="text" class="form-control" id="color_vehiculo" name="color_vehiculo" maxlength="11" value="{{ old('color_vehiculo') }}">
                  </div>
                  <div class="col-4">
                    <label for="marca_vehiculo" class="form-label">Marca</label>
                    <input type="text" class="form-control" id="marca_vehiculo" name="marca_vehiculo" maxlength="20" value="{{ old('marca_vehiculo') }}">
                  </div>
                  <div class="col-4">
                    <label for="modelo_vehiculo" class="form-label">Modelo</label>
                    <input type="text" class="form-control" id="modelo_vehiculo" name="modelo_vehiculo" maxlength="20" value="{{ old('modelo_vehiculo') }}">
                  </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Guardar vehículo</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Lista de vehículos ya registrados -->
        <div class="col-lg-7">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h3 class="h5 mb-3">Tus vehículos</h3>

              @forelse ($vehiculos as $v)
                <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ $v->placa_vehiculo }}</strong>
                    <div class="text-muted small">
                      {{ $v->marca_vehiculo }} {{ $v->modelo_vehiculo }} · {{ $v->color_vehiculo ?? 'Sin color' }}
                      · {{ $v->tipoVehiculo->nombre_tipo_vehiculo ?? 'sin tipo' }}
                    </div>
                  </div>
                </div>
              @empty
                <p class="text-muted">Todavía no tienes vehículos registrados.</p>
              @endforelse

              <a href="{{ route('reservas.vista') }}" class="btn btn-outline-primary mt-3 w-100">
                <i class="fa-solid fa-calendar-days me-1"></i> Ir a reservar con uno de estos vehículos
              </a>
            </div>
          </div>
        </div>

      </div>

      {{-- NUEVO: sección "Mis reservas". Muestra el historial completo del
           cliente (en proceso, finalizadas y canceladas) y permite
           cancelar solo las que siguen en proceso. --}}
      <div class="row g-4 mt-1">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h3 class="h5 mb-3">Mis reservas</h3>

              @forelse ($reservas as $r)
                <div class="border rounded-3 p-3 mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <div>
                    <strong>{{ $r->vehiculo->placa_vehiculo ?? '-' }}</strong>
                    · {{ $r->servicio->nombre_servicio ?? 'Servicio' }}
                    <div class="text-muted small">
                      {{ $r->fecha }} {{ $r->hora }}
                      · Pago: {{ $r->comprobantePago->metodoPago->nombre_metodo_pago ?? 'Sin definir' }}
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    @php
                      $claseEstado = match((int) $r->estado_reserva) {
                          2 => 'text-bg-success',
                          3 => 'text-bg-secondary',
                          default => 'text-bg-warning',
                      };
                    @endphp
                    <span class="badge {{ $claseEstado }}">{{ $r->estado_texto }}</span>

                    <a href="{{ route('reservas.voucher', $r->id_reserva) }}" class="btn btn-sm btn-outline-secondary">
                      Ver voucher
                    </a>

                    @if ($r->estado_reserva == 1)
                      <form method="POST" action="{{ route('reservas.cancelar', $r->id_reserva) }}"
                            onsubmit="return confirm('¿Cancelar esta reserva?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                          <i class="fa-solid fa-xmark me-1"></i> Cancelar
                        </button>
                      </form>
                    @endif
                  </div>
                </div>
              @empty
                <p class="text-muted mb-0">Todavía no has hecho ninguna reserva.</p>
              @endforelse

            </div>
          </div>
        </div>
      </div>
    </div>
  @endunless

</body>
</html>