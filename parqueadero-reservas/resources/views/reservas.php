<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear reserva - Parqueadero</title>
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

  <!-- Barra de Navegación -->
  <nav class="navbar navbar-expand bg-white border-bottom px-4 py-2 no-imprimir">
    <div class="container-fluid p-0">
      <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('inicio') }}">
        <i class="fa-solid fa-car fs-4"></i>
        <span>PARQUEADERO RESERVAS</span>
      </a>
      <div class="navbar-nav gap-3">
        <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('inicio') }}">
          <i class="fa-solid fa-house"></i> Inicio
        </a>
        <a class="nav-link text-primary fw-semibold d-flex align-items-center gap-1 active" href="{{ route('reservas.vista') }}">
          <i class="fa-solid fa-calendar-days"></i> Reservar
        </a>
      </div>
    </div>
  </nav>

  <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;">

    <!-- Formulario de reserva -->
    <div class="card shadow-sm no-imprimir" id="tarjeta-formulario" style="max-width: 480px; width: 100%;">
      <div class="card-body p-4">

        <div class="mb-4">
          <h2 class="h4 mb-1">Crear nueva reserva</h2>
          <p class="text-muted mb-0">Ingresa tus datos y el horario de tu preferencia.</p>
        </div>

        <form id="form-reserva" novalidate>

          <div class="mb-3">
            <label for="no_documento_cliente" class="form-label">Tu número de documento</label>
            <input type="number" class="form-control" id="no_documento_cliente" name="no_documento_cliente" required>
            <div class="form-text">Debes estar registrado y tener un vehículo asociado a tu documento.</div>
          </div>

          <div class="mb-3">
            <label for="placa_vehiculo" class="form-label">Placa del vehículo</label>
            <input type="text" class="form-control text-uppercase" id="placa_vehiculo" name="placa_vehiculo" placeholder="ABC123" maxlength="10" required>
          </div>

          <div class="mb-3">
            <label for="id_servicio" class="form-label">Servicio requerido</label>
            <select class="form-select" id="id_servicio" name="id_servicio" required>
              <option value="" disabled selected>Cargando servicios...</option>
            </select>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label for="fecha" class="form-label">Fecha</label>
              <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="col-6">
              <label for="hora" class="form-label">Hora de inicio</label>
              <input type="time" class="form-control" id="hora" name="hora" required>
            </div>
          </div>

          <div id="mensaje" class="alert d-none" role="alert"></div>

          <button type="submit" class="btn btn-primary w-100">Confirmar reserva</button>

        </form>

      </div>
    </div>

    <!-- Voucher (oculto hasta que se crea la reserva) -->
    <div class="card shadow-sm d-none" id="tarjeta-voucher" style="max-width: 480px; width: 100%;">
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
            <dd class="col-6 text-end fw-bold" id="v-id">-</dd>

            <dt class="col-6">Cliente</dt>
            <dd class="col-6 text-end" id="v-cliente">-</dd>

            <dt class="col-6">Documento</dt>
            <dd class="col-6 text-end" id="v-documento">-</dd>

            <dt class="col-6">Placa</dt>
            <dd class="col-6 text-end" id="v-placa">-</dd>

            <dt class="col-6">Tipo de vehículo</dt>
            <dd class="col-6 text-end" id="v-tipo-vehiculo">-</dd>

            <dt class="col-6">Servicio</dt>
            <dd class="col-6 text-end" id="v-servicio">-</dd>

            <dt class="col-6">Fecha</dt>
            <dd class="col-6 text-end" id="v-fecha">-</dd>

            <dt class="col-6">Hora</dt>
            <dd class="col-6 text-end" id="v-hora">-</dd>
          </dl>
        </div>

        <div class="d-grid gap-2 no-imprimir">
          <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Imprimir / Guardar voucher
          </button>
          <button class="btn btn-secondary" id="btn-nueva-reserva">
            Hacer otra reserva
          </button>
        </div>

      </div>
    </div>

  </div>

  <script>
    const API_BASE = '/api';

    // Cargar servicios disponibles directamente desde la base de datos
    async function cargarServicios() {
      const select = document.getElementById('id_servicio');
      try {
        const respuesta = await fetch(`${API_BASE}/servicios`, { headers: { 'Accept': 'application/json' } });
        const servicios = await respuesta.json();

        select.innerHTML = '<option value="" disabled selected>-- Selecciona un servicio --</option>';
        servicios
          .filter(s => s.estado_servicio == 1 || s.estado_servicio === null)
          .forEach(s => {
            const option = document.createElement('option');
            option.value = s.id_servicio;
            option.textContent = `${s.nombre_servicio} - $${s.costo_servicio}`;
            select.appendChild(option);
          });
      } catch (error) {
        select.innerHTML = '<option value="" disabled selected>No se pudieron cargar los servicios</option>';
      }
    }

    cargarServicios();

    function mostrarVoucher(reserva) {
      document.getElementById('tarjeta-formulario').classList.add('d-none');
      document.getElementById('tarjeta-voucher').classList.remove('d-none');

      document.getElementById('v-id').textContent = reserva.id_reserva;

      const usuario = reserva.cliente && reserva.cliente.usuario ? reserva.cliente.usuario : null;
      document.getElementById('v-cliente').textContent = usuario
        ? `${usuario.nombre_usuario} ${usuario.apellido_usuario}`
        : '-';
      document.getElementById('v-documento').textContent = reserva.no_documento_cliente;

      document.getElementById('v-placa').textContent = reserva.placa_vehiculo;
      document.getElementById('v-tipo-vehiculo').textContent = reserva.tipo_vehiculo
        ? reserva.tipo_vehiculo.nombre_tipo_vehiculo
        : (reserva.tipoVehiculo ? reserva.tipoVehiculo.nombre_tipo_vehiculo : '-');

      document.getElementById('v-servicio').textContent = reserva.servicio
        ? reserva.servicio.nombre_servicio
        : '-';

      document.getElementById('v-fecha').textContent = reserva.fecha;
      document.getElementById('v-hora').textContent = reserva.hora;
    }

    document.getElementById('form-reserva').addEventListener('submit', async function (e) {
      e.preventDefault();

      const mensaje = document.getElementById('mensaje');
      mensaje.className = 'alert d-none';

      const datos = {
        no_documento_cliente: document.getElementById('no_documento_cliente').value,
        placa_vehiculo: document.getElementById('placa_vehiculo').value.trim().toUpperCase(),
        id_servicio: document.getElementById('id_servicio').value,
        fecha: document.getElementById('fecha').value,
        hora: document.getElementById('hora').value,
      };

      try {
        const respuesta = await fetch(`${API_BASE}/reservas`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
          const errores = resultado.errors
            ? Object.values(resultado.errors).flat().join(' ')
            : (resultado.message || 'Ocurrió un error al crear la reserva.');
          mensaje.textContent = errores;
          mensaje.className = 'alert alert-danger';
          return;
        }

        mostrarVoucher(resultado);

      } catch (error) {
        mensaje.textContent = 'No se pudo conectar con el servidor. Verifica que el backend esté corriendo.';
        mensaje.className = 'alert alert-danger';
      }
    });

    document.getElementById('btn-nueva-reserva').addEventListener('click', function () {
      document.getElementById('form-reserva').reset();
      document.getElementById('tarjeta-voucher').classList.add('d-none');
      document.getElementById('tarjeta-formulario').classList.remove('d-none');
    });
  </script>

</body>
</html>