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

          {{-- esta nos sirve para elegir el vehiculo. --}}
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
            <form method="POST" action="{{ route('reservas.guardar') }}" id="form-reserva-cliente">
              @csrf
              <input type="hidden" name="placa_vehiculo" value="{{ $vehiculoSeleccionado->placa_vehiculo }}">
              {{-- se guarda el tipo de vehículo y cupos. --}}
              <input type="hidden" id="id_tipo_vehiculo_actual" value="{{ $vehiculoSeleccionado->id_tipo_vehiculo }}">

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
                  {{-- consulta los cupos disponibles para esa fecha. --}}
                  <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha') }}" required onchange="consultarCuposReserva()">
                </div>
                <div class="col-6">
                  <label for="hora" class="form-label">Hora</label>
                  <input type="time" class="form-control" id="hora" name="hora" value="{{ old('hora') }}" required>
                </div>
              </div>

          
              <div id="cupos-info" class="small text-muted mb-3"></div>

              <div class="mb-3">
                <label for="id_metodo_pago" class="form-label">Método de pago</label>
                <select class="form-select" id="id_metodo_pago" name="id_metodo_pago" required onchange="mostrarFormularioPago()">
                  <option value="" disabled selected>-- Selecciona cómo vas a pagar --</option>
                  @forelse ($metodosPago as $m)
                    <option value="{{ $m->id_metodo_pago }}" data-nombre="{{ strtolower($m->nombre_metodo_pago) }}" {{ old('id_metodo_pago') == $m->id_metodo_pago ? 'selected' : '' }}>
                      {{ $m->nombre_metodo_pago }}
                    </option>
                  @empty
                    <option value="" disabled>No hay métodos de pago disponibles</option>
                  @endforelse
                </select>
              </div>

              {{-- NUEVO: formularios ilustrativos de pago. Ninguno de estos
                   campos se procesa ni se guarda de verdad (no están en las
                   reglas de validación del backend); es solo para mostrar
                   cómo se vería el paso de pago. Si el método es "Efectivo"
                   no se muestra nada, porque ese pago se hace en el lugar. --}}
              <div class="alert alert-secondary small py-2 mb-3 d-none" id="aviso-pago-ilustrativo">
                <i class="fa-solid fa-circle-info me-1"></i> Estos datos son solo de ejemplo, aún no se procesan pagos reales.
              </div>

              <div class="border rounded-3 p-3 mb-3 d-none" id="pago-tarjeta">
                <h4 class="h6 mb-3"><i class="fa-solid fa-credit-card me-1"></i> Datos de la tarjeta</h4>
                <div class="mb-3">
                  <label class="form-label small">Número de la tarjeta</label>
                  <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                </div>
                <div class="mb-3">
                  <label class="form-label small">Nombre del titular</label>
                  <input type="text" class="form-control" placeholder="Como aparece en la tarjeta" autocomplete="off">
                </div>
                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label small">Fecha de vencimiento</label>
                    <input type="text" class="form-control" placeholder="MM/AA" maxlength="5" autocomplete="off">
                  </div>
                  <div class="col-6">
                    <label class="form-label small">CVV</label>
                    <input type="text" class="form-control" placeholder="123" maxlength="3" inputmode="numeric" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="border rounded-3 p-3 mb-3 d-none" id="pago-nequi">
                <h4 class="h6 mb-3"><i class="fa-solid fa-mobile-screen me-1"></i> Pago con Nequi</h4>
                <div class="mb-3">
                  <label class="form-label small">Número de celular Nequi</label>
                  <input type="tel" class="form-control" placeholder="300 123 4567" maxlength="10" inputmode="numeric" autocomplete="off">
                </div>
                <div class="mb-3">
                  <label class="form-label small">Clave Nequi</label>
                  <input type="password" class="form-control" placeholder="••••" autocomplete="off">
                </div>
              </div>

              <div class="border rounded-3 p-3 mb-3 d-none" id="pago-transferencia">
                <h4 class="h6 mb-3"><i class="fa-solid fa-building-columns me-1"></i> Transferencia bancaria</h4>
                <div class="mb-3">
                  <label class="form-label small">Banco</label>
                  <input type="text" class="form-control" placeholder="Nombre del banco" autocomplete="off">
                </div>
                <div class="mb-3">
                  <label class="form-label small">Número de cuenta</label>
                  <input type="text" class="form-control" placeholder="Número de cuenta" autocomplete="off">
                </div>
                <div class="mb-3">
                  <label class="form-label small">Clave / clave dinámica</label>
                  <input type="password" class="form-control" placeholder="••••" autocomplete="off">
                </div>
              </div>

              <button type="submit" class="btn btn-primary w-100" id="btn-confirmar-reserva">Confirmar reserva</button>
            </form>
          @endif

        </div>
      </div>

    @endunless
  </div>

  {{-- consulta en vivo de cupos disponibles  --}}
  @if ($vehiculoSeleccionado)
    <script>
      const API_BASE = '/api';

      async function consultarCuposReserva() {
        const idTipoInput = document.getElementById('id_tipo_vehiculo_actual');
        const fechaInput = document.getElementById('fecha');
        const info = document.getElementById('cupos-info');
        const btn = document.getElementById('btn-confirmar-reserva');

        if (!idTipoInput || !fechaInput || !fechaInput.value) {
          if (info) info.textContent = '';
          return;
        }

        try {
          const resp = await fetch(`${API_BASE}/reservas/cupos-disponibles?id_tipo_vehiculo=${idTipoInput.value}&fecha=${fechaInput.value}`, {
            headers: { 'Accept': 'application/json' },
          });
          const d = await resp.json();

          if (d.cupos_totales === null || d.cupos_totales === undefined) {
            info.textContent = 'Este tipo de vehículo no tiene límite de cupos configurado.';
            info.className = 'small text-muted mb-3';
            if (btn) btn.disabled = false;
            return;
          }

          info.textContent = `Cupos disponibles para esta fecha: ${d.cupos_disponibles} de ${d.cupos_totales}`;
          info.className = d.cupos_disponibles > 0 ? 'small text-success mb-3' : 'small text-danger mb-3';

          if (btn) btn.disabled = d.cupos_disponibles <= 0;
        } catch (error) {
          if (info) info.textContent = '';
        }
      }

      document.addEventListener('DOMContentLoaded', consultarCuposReserva);

      // NUEVO: muestra el formulario ilustrativo según el método de pago
      // elegido. Si es "Efectivo" (o no hay selección) no muestra nada,
      // porque ese pago se hace en el lugar.
      function mostrarFormularioPago() {
        const select = document.getElementById('id_metodo_pago');
        const opcion = select.options[select.selectedIndex];
        const nombre = opcion ? (opcion.dataset.nombre || '') : '';

        const bloques = {
          tarjeta: document.getElementById('pago-tarjeta'),
          nequi: document.getElementById('pago-nequi'),
          transferencia: document.getElementById('pago-transferencia'),
        };
        const aviso = document.getElementById('aviso-pago-ilustrativo');

        Object.values(bloques).forEach(b => b.classList.add('d-none'));
        aviso.classList.add('d-none');

        if (nombre.includes('tarjeta')) {
          bloques.tarjeta.classList.remove('d-none');
          aviso.classList.remove('d-none');
        } else if (nombre.includes('nequi')) {
          bloques.nequi.classList.remove('d-none');
          aviso.classList.remove('d-none');
        } else if (nombre.includes('transferencia')) {
          bloques.transferencia.classList.remove('d-none');
          aviso.classList.remove('d-none');
        }
        // "Efectivo" (o cualquier otro método no reconocido) no muestra nada.
      }

      document.addEventListener('DOMContentLoaded', mostrarFormularioPago);
    </script>
  @endif

</body>
</html>