<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear reserva - Parqueadero</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

  <nav class="navbar navbar-expand bg-white border-bottom px-4 py-2">
    <div class="container-fluid p-0">
      <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('inicio') }}">
        <i class="fa-solid fa-car fs-4"></i>
        <span>PARQUEADERO RESERVAS</span>
      </a>
      <div class="navbar-nav gap-3">
        <a class="nav-link text-secondary fw-semibold" href="{{ route('inicio') }}">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a class="nav-link text-primary fw-semibold active" href="{{ route('reservas.vista') }}">
          <i class="fa-solid fa-calendar-days"></i> Reservar
        </a>
        @if(session('cliente'))
          <a class="nav-link text-secondary fw-semibold" href="{{ route('mi-cuenta') }}">
            <i class="fa-solid fa-user"></i> Mi cuenta
          </a>
        @endif
        <a class="nav-link text-secondary fw-semibold" href="{{ route('control') }}">
          <i class="fa-solid fa-gear"></i> Control
        </a>
      </div>
    </div>
  </nav>

  <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;">

    @unless(session('cliente'))
      <div class="alert alert-warning text-center" style="max-width: 480px; width: 100%;">
        Necesitas estar registrado para reservar.
        <a href="{{ route('inicio') }}" class="alert-link">Regístrate aquí</a>.
      </div>
    @else

      <div class="card shadow-sm" style="max-width: 480px; width: 100%;">
        <div class="card-body p-4">

          <div class="mb-4">
            <h2 class="h4 mb-1">Crear nueva reserva</h2>
            <p class="text-muted mb-0">
              Hola <strong>{{ session('cliente')['nombre_usuario'] }}</strong>, elige tu vehículo para ver los servicios disponibles.
            </p>
          </div>

          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Paso 1: elegir el vehículo. Recarga la página vía GET para
               que el controlador filtre los servicios por su tipo. --}}
          <form method="GET" action="{{ route('reservas.vista') }}" class="mb-4">
            <label for="placa_vehiculo" class="form-label">Vehículo</label>
            <div class="input-group">
              <select class="form-select" id="placa_vehiculo" name="placa_vehiculo" required>
                <option value="" disabled {{ !$vehiculoSeleccionado ? 'selected' : '' }}>-- Selecciona tu vehículo --</option>
                @forelse ($vehiculos as $v)
                  <option value="{{ $v->placa_vehiculo }}"
                    {{ $vehiculoSeleccionado && $vehiculoSeleccionado->placa_vehiculo === $v->placa_vehiculo ? 'selected' : '' }}>
                    {{ $v->placa_vehiculo }} - {{ $v->marca_vehiculo }} {{ $v->modelo_vehiculo }}
                    ({{ $v->tipoVehiculo->nombre_tipo_vehiculo ?? 'sin tipo' }})
                  </option>
                @empty
                  <option value="" disabled>No tienes vehículos registrados</option>
                @endforelse
              </select>
              <button class="btn btn-outline-primary" type="submit">Ver servicios</button>
            </div>
            <div class="form-text">
              ¿No ves tu vehículo? <a href="{{ route('mi-cuenta') }}">Regístralo en Mi cuenta</a>.
            </div>
          </form>

          @if ($vehiculoSeleccionado)
            {{-- Paso 2: completar la reserva con los servicios ya filtrados --}}
            <form method="POST" action="{{ route('reservas.guardar') }}">
              @csrf
              <input type="hidden" name="placa_vehiculo" value="{{ $vehiculoSeleccionado->placa_vehiculo }}">

              <div class="mb-3">
                <label for="id_servicio" class="form-label">
                  Servicio para {{ $vehiculoSeleccionado->tipoVehiculo->nombre_tipo_vehiculo ?? 'tu vehículo' }}
                </label>
                <select class="form-select" id="id_servicio" name="id_servicio" required>
                  <option value="" disabled selected>-- Selecciona un servicio --</option>
                  @forelse ($servicios as $s)
                    <option value="{{ $s->id_servicio }}" {{ old('id_servicio') == $s->id_servicio ? 'selected' : '' }}>
                      {{ $s->nombre_servicio }} - ${{ $s->costo_servicio }}
                    </option>
                  @empty
                    <option value="" disabled>No hay servicios configurados para este tipo de vehículo</option>
                  @endforelse
                </select>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label for="fecha" class="form-label">Fecha</label>
                  <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha') }}" required>
                </div>
                <div class="col-6">
                  <label for="hora" class="form-label">Hora</label>
                  <input type="time" class="form-control" id="hora" name="hora" value="{{ old('hora') }}" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="id_metodo_pago" class="form-label">Método de pago</label>
                <select class="form-select" id="id_metodo_pago" name="id_metodo_pago" required>
                  <option value="" disabled selected>-- Selecciona cómo vas a pagar --</option>
                  @forelse ($metodosPago as $m)
                    <option value="{{ $m->id_metodo_pago }}" {{ old('id_metodo_pago') == $m->id_metodo_pago ? 'selected' : '' }}>
                      {{ $m->nombre_metodo_pago }}
                    </option>
                  @empty
                    <option value="" disabled>No hay métodos de pago disponibles</option>
                  @endforelse
                </select>
              </div>

              <button type="submit" class="btn btn-primary w-100">Confirmar reserva</button>
            </form>
          @endif

        </div>
      </div>

    @endunless
  </div>

</body>
</html>