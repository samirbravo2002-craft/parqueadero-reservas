<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control - Parqueadero</title>
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
        <a class="nav-link text-primary fw-semibold d-flex align-items-center gap-1 active" href="{{ route('control') }}">
          <i class="fa-solid fa-gear"></i> Control
        </a>
        <button class="btn btn-outline-secondary btn-sm d-none" id="btn-cerrar-sesion-control">
          <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
        </button>
      </div>
    </div>
  </nav>

  <div class="container py-4">

    <!-- ============ Pantalla de login (se muestra hasta iniciar sesión) ============ -->
    <div class="d-flex justify-content-center align-items-center" id="bloque-login-control" style="min-height: 60vh;">
      <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
        <div class="card-body p-4">
          <h1 class="h4 mb-1 text-center"><i class="fa-solid fa-lock me-2"></i>Acceso administrativo</h1>
          <p class="text-muted text-center small mb-4">Solo cuentas de administrador pueden entrar a este panel.</p>

          <form id="form-login-control" novalidate>
            <div class="mb-3">
              <label class="form-label small">Correo</label>
              <input type="email" class="form-control" id="control_correo" required>
            </div>
            <div class="mb-3">
              <label class="form-label small">Contraseña</label>
              <input type="password" class="form-control" id="control_contrasenia" required>
            </div>
            <div id="mensaje-login-control" class="alert d-none" role="alert"></div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="fa-solid fa-right-to-bracket me-1"></i> Entrar
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ============ Panel de Control (oculto hasta iniciar sesión) ============ -->
    <div class="d-none" id="panel-control">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 mb-0"><i class="fa-solid fa-gear me-2"></i>Panel de Control</h1>
      <span class="text-muted small">Conectado como <strong id="nombre-admin-conectado"></strong></span>
    </div>

    <!-- Pestañas -->
    <ul class="nav nav-tabs mb-4" id="tabsControl" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-clientes-btn" data-bs-toggle="tab" data-bs-target="#tab-clientes" type="button">
          <i class="fa-solid fa-users me-1"></i> Clientes
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tipos-btn" data-bs-toggle="tab" data-bs-target="#tab-tipos" type="button">
          <i class="fa-solid fa-car-side me-1"></i> Tipos de vehículo
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-servicios-btn" data-bs-toggle="tab" data-bs-target="#tab-servicios" type="button">
          <i class="fa-solid fa-screwdriver-wrench me-1"></i> Servicios
        </button>
      </li>
      <!-- NUEVO: pestaña Reservas -->
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-reservas-btn" data-bs-toggle="tab" data-bs-target="#tab-reservas" type="button">
          <i class="fa-solid fa-calendar-check me-1"></i> Reservas
        </button>
      </li>
    </ul>

    <div class="tab-content">

      <!-- ================= CLIENTES ================= -->
      <div class="tab-pane fade show active" id="tab-clientes" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 mb-0">Clientes</h2>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="abrirModalCrearCliente()">
            <i class="fa-solid fa-plus me-1"></i> Nuevo cliente
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover bg-white align-middle">
            <thead>
              <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Celular</th>
                <th>Correo</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-clientes">
              <tr><td colspan="5" class="text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= TIPOS DE VEHÍCULO ================= -->
      <div class="tab-pane fade" id="tab-tipos" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 mb-0">Tipos de vehículo</h2>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTipo" onclick="abrirModalCrearTipo()">
            <i class="fa-solid fa-plus me-1"></i> Nuevo tipo
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover bg-white align-middle">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-tipos">
              <tr><td colspan="3" class="text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= SERVICIOS ================= -->
      <div class="tab-pane fade" id="tab-servicios" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 mb-0">Servicios</h2>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalServicio" onclick="abrirModalCrearServicio()">
            <i class="fa-solid fa-plus me-1"></i> Nuevo servicio
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-hover bg-white align-middle">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Tipo vehículo</th>
                <th>Costo</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-servicios">
              <tr><td colspan="5" class="text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= RESERVAS (NUEVO) ================= -->
      <div class="tab-pane fade" id="tab-reservas" role="tabpanel">

        <!-- Resumen del día -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <div class="text-muted small">Ganancias de hoy</div>
                <div class="fs-3 fw-bold text-success" id="resumen-ganancias">$0</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <div class="text-muted small">Reservas hechas hoy</div>
                <div class="fs-3 fw-bold" id="resumen-cantidad">0</div>
                <div class="text-muted small" id="resumen-detalle"></div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <div class="text-muted small mb-1">Cupos disponibles hoy</div>
                <div id="resumen-cupos" class="small">Cargando...</div>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h5 mb-0">Reservas en proceso</h2>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalReservaControl" onclick="abrirModalReservaControl()">
            <i class="fa-solid fa-plus me-1"></i> Nueva reserva
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-hover bg-white align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Placa</th>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-reservas-proceso">
              <tr><td colspan="7" class="text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <!-- fin #panel-control -->

    </div>
  </div>

  <!-- ============ MODAL: Crear/Editar Cliente ============ -->
  <div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModalCliente">Nuevo cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="form-cliente" novalidate>
            <input type="hidden" id="cli_id_usuario">

            <div class="row g-3 mb-3">
              <div class="col-5">
                <label class="form-label small">Tipo de documento</label>
                <select class="form-select" id="cli_tipo_documento" required>
                  <option value="CC">Cédula de ciudadanía</option>
                  <option value="CE">Cédula de extranjería</option>
                  <option value="TI">Tarjeta de identidad</option>
                  <option value="PA">Pasaporte</option>
                </select>
              </div>
              <div class="col-7">
                <label class="form-label small">Número de documento</label>
                <input type="number" class="form-control" id="cli_no_documento" required>
                <div class="form-text">No se puede modificar después de creado.</div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small">Nombre</label>
                <input type="text" class="form-control" id="cli_nombre" maxlength="20" required>
              </div>
              <div class="col-6">
                <label class="form-label small">Apellido</label>
                <input type="text" class="form-control" id="cli_apellido" maxlength="20" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Celular (10 dígitos)</label>
              <input type="tel" class="form-control" id="cli_celular" maxlength="10" pattern="[0-9]{10}"
                     inputmode="numeric" required
                     oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

            <div class="mb-3">
              <label class="form-label small">Correo</label>
              <input type="email" class="form-control" id="cli_correo" maxlength="100" required>
            </div>

            <div class="row g-3 mb-3" id="fila-contrasenia-cliente">
              <div class="col-6">
                <label class="form-label small">Contraseña</label>
                <input type="password" class="form-control" id="cli_contrasenia" minlength="8">
                <div class="form-text">Mín. 8 caracteres, con letras y números.</div>
              </div>
              <div class="col-6">
                <label class="form-label small">Confirmar contraseña</label>
                <input type="password" class="form-control" id="cli_contrasenia_confirmation" minlength="8">
              </div>
            </div>

            <div id="mensaje-cliente" class="alert d-none"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="guardarCliente()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: Reservas de un cliente ============ -->
  <div class="modal fade" id="modalReservas" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reservas de <span id="reservas-nombre-cliente"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Vehículo</th>
                  <th>Servicio</th>
                  <th>Fecha</th>
                  <th>Hora</th>
                </tr>
              </thead>
              <tbody id="tabla-reservas-cliente">
                <tr><td colspan="5" class="text-muted">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: Crear/Editar Tipo de vehículo ============ -->
  <div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModalTipo">Nuevo tipo de vehículo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="form-tipo" novalidate>
            <input type="hidden" id="tipo_id">
            <div class="mb-3">
              <label class="form-label small">Nombre</label>
              <input type="text" class="form-control" id="tipo_nombre" maxlength="20" required>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="tipo_estado" checked>
              <label class="form-check-label small" for="tipo_estado">Activo</label>
            </div>
            <div id="mensaje-tipo" class="alert d-none mt-3"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="guardarTipo()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: Crear/Editar Servicio ============ -->
  <div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tituloModalServicio">Nuevo servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="form-servicio" novalidate>
            <input type="hidden" id="serv_id">
            <div class="mb-3">
              <label class="form-label small">Nombre (máx. 10 caracteres)</label>
              <input type="text" class="form-control" id="serv_nombre" maxlength="10" required>
              <div class="form-text">La columna en la base de datos es muy corta (VARCHAR(10)); si necesitas nombres más largos, hay que ampliarla.</div>
            </div>
            <div class="mb-3">
              <label class="form-label small">Tipo de vehículo</label>
              <select class="form-select" id="serv_tipo_vehiculo" required>
                <option value="" disabled selected>Cargando...</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label small">Descripción</label>
              <textarea class="form-control" id="serv_descripcion" maxlength="200" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label small">Costo</label>
              <input type="number" class="form-control" id="serv_costo" min="0" step="0.01" required>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="serv_estado" checked>
              <label class="form-check-label small" for="serv_estado">Activo</label>
            </div>
            <div id="mensaje-servicio" class="alert d-none mt-3"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="guardarServicio()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ MODAL: Nueva reserva desde Control (NUEVO) ============ -->
  <div class="modal fade" id="modalReservaControl" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Nueva reserva (cliente en sitio)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="form-reserva-control" novalidate>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small">Documento del cliente</label>
                <input type="number" class="form-control" id="rc_documento" required>
                <div class="form-text">Si ya está registrado, se usa su cuenta; si no, se crea uno nuevo con los datos de abajo.</div>
              </div>
              <div class="col-6">
                <label class="form-label small">Celular (si es cliente nuevo)</label>
                <input type="tel" class="form-control" id="rc_celular" maxlength="10" inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small">Nombre (si es cliente nuevo)</label>
                <input type="text" class="form-control" id="rc_nombre" maxlength="20">
              </div>
              <div class="col-6">
                <label class="form-label small">Apellido (si es cliente nuevo)</label>
                <input type="text" class="form-control" id="rc_apellido" maxlength="20">
              </div>
            </div>

            <hr>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small">Placa</label>
                <input type="text" class="form-control text-uppercase" id="rc_placa" maxlength="10" required>
              </div>
              <div class="col-6">
                <label class="form-label small">Tipo de vehículo</label>
                <select class="form-select" id="rc_tipo_vehiculo" required onchange="cargarServiciosControl(); consultarCuposControl();">
                  <option value="" disabled selected>Cargando...</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-4">
                <label class="form-label small">Color (si es placa nueva)</label>
                <input type="text" class="form-control" id="rc_color" maxlength="11">
              </div>
              <div class="col-4">
                <label class="form-label small">Marca (si es placa nueva)</label>
                <input type="text" class="form-control" id="rc_marca" maxlength="20">
              </div>
              <div class="col-4">
                <label class="form-label small">Modelo (si es placa nueva)</label>
                <input type="text" class="form-control" id="rc_modelo" maxlength="20">
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small">Servicio</label>
              <select class="form-select" id="rc_servicio" required>
                <option value="" disabled selected>-- Elige primero el tipo de vehículo --</option>
              </select>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-4">
                <label class="form-label small">Fecha</label>
                <input type="date" class="form-control" id="rc_fecha" required onchange="consultarCuposControl()">
              </div>
              <div class="col-4">
                <label class="form-label small">Hora</label>
                <input type="time" class="form-control" id="rc_hora" required>
              </div>
              <div class="col-4">
                <label class="form-label small">Método de pago</label>
                <select class="form-select" id="rc_metodo_pago" required>
                  <option value="" disabled selected>Cargando...</option>
                </select>
              </div>
            </div>

            <div id="rc-cupos-info" class="small text-muted mb-3"></div>

            <div id="mensaje-reserva-control" class="alert d-none"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary" onclick="guardarReservaControl()">Crear reserva</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const API_BASE = '/api';

    function mostrarErrores(elId, resultado, mensajeGenerico) {
      const el = document.getElementById(elId);
      const errores = resultado.errors
        ? Object.values(resultado.errors).flat().join(' ')
        : (resultado.message || mensajeGenerico);
      el.textContent = errores;
      el.className = 'alert alert-danger';
    }

    /* ==================== CLIENTES ==================== */

    async function cargarClientes() {
      const tbody = document.getElementById('tabla-clientes');
      try {
        const resp = await fetch(`${API_BASE}/clientes`, { headers: { 'Accept': 'application/json' } });
        const clientes = await resp.json();

        if (clientes.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-muted">No hay clientes registrados.</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        clientes.forEach(c => {
          const u = c.usuario || {};
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${c.no_documento_cliente}</td>
            <td>${u.nombre_usuario ?? ''} ${u.apellido_usuario ?? ''}</td>
            <td>${u.numero_celular ?? '-'}</td>
            <td>${u.correo_usuario ?? '-'}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-secondary" onclick="verReservasCliente(${c.no_documento_cliente}, '${(u.nombre_usuario ?? '').replace(/'/g, "")}')" title="Ver reservas">
                <i class="fa-solid fa-calendar-days"></i>
              </button>
              <button class="btn btn-sm btn-outline-primary" onclick='abrirModalEditarCliente(${JSON.stringify(c)})' title="Editar">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(${c.no_documento_cliente})" title="Eliminar">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          `;
          tbody.appendChild(fila);
        });
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">No se pudieron cargar los clientes.</td></tr>';
      }
    }

    function abrirModalCrearCliente() {
      document.getElementById('tituloModalCliente').textContent = 'Nuevo cliente';
      document.getElementById('form-cliente').reset();
      document.getElementById('cli_id_usuario').value = '';
      document.getElementById('cli_no_documento').disabled = false;
      document.getElementById('cli_contrasenia').required = true;
      document.getElementById('cli_contrasenia_confirmation').required = true;
      document.getElementById('mensaje-cliente').className = 'alert d-none';
    }

    function abrirModalEditarCliente(cliente) {
      const u = cliente.usuario || {};
      document.getElementById('tituloModalCliente').textContent = 'Editar cliente';
      document.getElementById('cli_id_usuario').value = u.id_usuario ?? '';
      document.getElementById('cli_no_documento').value = cliente.no_documento_cliente;
      document.getElementById('cli_no_documento').disabled = true; // es la llave primaria, no se cambia
      document.getElementById('cli_tipo_documento').value = u.tipo_documento ?? 'CC';
      document.getElementById('cli_nombre').value = u.nombre_usuario ?? '';
      document.getElementById('cli_apellido').value = u.apellido_usuario ?? '';
      document.getElementById('cli_celular').value = u.numero_celular ?? '';
      document.getElementById('cli_correo').value = u.correo_usuario ?? '';
      document.getElementById('cli_contrasenia').value = '';
      document.getElementById('cli_contrasenia_confirmation').value = '';
      // Al editar, la contraseña es opcional (solo se cambia si se escribe algo nuevo).
      document.getElementById('cli_contrasenia').required = false;
      document.getElementById('cli_contrasenia_confirmation').required = false;
      document.getElementById('mensaje-cliente').className = 'alert d-none';

      new bootstrap.Modal(document.getElementById('modalCliente')).show();
    }

    async function guardarCliente() {
      const idUsuario = document.getElementById('cli_id_usuario').value;
      const esEdicion = !!idUsuario;

      const datosBase = {
        tipo_documento: document.getElementById('cli_tipo_documento').value,
        nombre_usuario: document.getElementById('cli_nombre').value.trim(),
        apellido_usuario: document.getElementById('cli_apellido').value.trim(),
        numero_celular: document.getElementById('cli_celular').value.trim(),
        correo_usuario: document.getElementById('cli_correo').value.trim(),
      };

      const contrasenia = document.getElementById('cli_contrasenia').value;
      const confirmacion = document.getElementById('cli_contrasenia_confirmation').value;

      try {
        let respuesta, resultado;

        if (esEdicion) {
          // Editar: se actualiza el usuario (nombre, celular, correo y, si se escribió, la contraseña).
          const datos = { ...datosBase };
          if (contrasenia) {
            datos.contrasenia = contrasenia;
            datos.contrasenia_confirmation = confirmacion;
          }

          respuesta = await fetch(`${API_BASE}/usuarios/${idUsuario}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos),
          });
        } else {
          // Crear: usa el mismo endpoint público de registro (crea usuario + cliente).
          const datos = {
            ...datosBase,
            no_documento_cliente: document.getElementById('cli_no_documento').value,
            contrasenia: contrasenia,
            contrasenia_confirmation: confirmacion,
          };

          respuesta = await fetch(`${API_BASE}/registro`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos),
          });
        }

        resultado = await respuesta.json();

        if (!respuesta.ok) {
          mostrarErrores('mensaje-cliente', resultado, 'Ocurrió un error al guardar el cliente.');
          return;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalCliente')).hide();
        cargarClientes();

      } catch (error) {
        mostrarErrores('mensaje-cliente', {}, 'No se pudo conectar con el servidor.');
      }
    }

    async function eliminarCliente(id) {
      if (!confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.')) return;

      try {
        const respuesta = await fetch(`${API_BASE}/clientes/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json' },
        });

        if (!respuesta.ok) {
          const resultado = await respuesta.json();
          alert(resultado.message || 'No se pudo eliminar el cliente.');
          return;
        }

        cargarClientes();
      } catch (error) {
        alert('No se pudo conectar con el servidor.');
      }
    }

    async function verReservasCliente(id, nombre) {
      document.getElementById('reservas-nombre-cliente').textContent = nombre || ('#' + id);
      const tbody = document.getElementById('tabla-reservas-cliente');
      tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Cargando...</td></tr>';

      new bootstrap.Modal(document.getElementById('modalReservas')).show();

      try {
        const resp = await fetch(`${API_BASE}/clientes/${id}/reservas`, { headers: { 'Accept': 'application/json' } });
        const data = await resp.json();
        const reservas = data.reservas || [];

        if (reservas.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Este cliente no tiene reservas.</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        reservas.forEach(r => {
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${r.id_reserva}</td>
            <td>${r.vehiculo ? r.vehiculo.placa_vehiculo : (r.placa_vehiculo ?? '-')}</td>
            <td>${r.servicio ? r.servicio.nombre_servicio : '-'}</td>
            <td>${r.fecha}</td>
            <td>${r.hora}</td>
          `;
          tbody.appendChild(fila);
        });
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">No se pudieron cargar las reservas.</td></tr>';
      }
    }

    /* ==================== TIPOS DE VEHÍCULO ==================== */

    async function cargarTipos() {
      const tbody = document.getElementById('tabla-tipos');
      try {
        const resp = await fetch(`${API_BASE}/tipos-vehiculo`, { headers: { 'Accept': 'application/json' } });
        const tipos = await resp.json();

        if (tipos.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3" class="text-muted">No hay tipos registrados.</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        tipos.forEach(t => {
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${t.nombre_tipo_vehiculo}</td>
            <td>${(t.estado_vehiculo == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" onclick='abrirModalEditarTipo(${JSON.stringify(t)})'>
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="eliminarTipo(${t.id_tipo_vehiculo})">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          `;
          tbody.appendChild(fila);
        });
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-danger">No se pudieron cargar los tipos.</td></tr>';
      }
    }

    function abrirModalCrearTipo() {
      document.getElementById('tituloModalTipo').textContent = 'Nuevo tipo de vehículo';
      document.getElementById('form-tipo').reset();
      document.getElementById('tipo_id').value = '';
      document.getElementById('tipo_estado').checked = true;
      document.getElementById('mensaje-tipo').className = 'alert d-none';
    }

    function abrirModalEditarTipo(tipo) {
      document.getElementById('tituloModalTipo').textContent = 'Editar tipo de vehículo';
      document.getElementById('tipo_id').value = tipo.id_tipo_vehiculo;
      document.getElementById('tipo_nombre').value = tipo.nombre_tipo_vehiculo;
      document.getElementById('tipo_estado').checked = tipo.estado_vehiculo == 1;
      document.getElementById('mensaje-tipo').className = 'alert d-none';

      new bootstrap.Modal(document.getElementById('modalTipo')).show();
    }

    async function guardarTipo() {
      const id = document.getElementById('tipo_id').value;
      const datos = {
        nombre_tipo_vehiculo: document.getElementById('tipo_nombre').value.trim(),
        estado_vehiculo: document.getElementById('tipo_estado').checked ? 1 : 0,
      };

      try {
        const respuesta = await fetch(`${API_BASE}/tipos-vehiculo${id ? '/' + id : ''}`, {
          method: id ? 'PUT' : 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
          mostrarErrores('mensaje-tipo', resultado, 'Ocurrió un error al guardar el tipo de vehículo.');
          return;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalTipo')).hide();
        cargarTipos();
        cargarTiposParaServicios();
      } catch (error) {
        mostrarErrores('mensaje-tipo', {}, 'No se pudo conectar con el servidor.');
      }
    }

    async function eliminarTipo(id) {
      if (!confirm('¿Eliminar este tipo de vehículo?')) return;

      try {
        const respuesta = await fetch(`${API_BASE}/tipos-vehiculo/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json' },
        });

        if (!respuesta.ok) {
          const resultado = await respuesta.json();
          alert(resultado.message || 'No se pudo eliminar (puede tener vehículos o servicios asociados).');
          return;
        }

        cargarTipos();
      } catch (error) {
        alert('No se pudo conectar con el servidor.');
      }
    }

    /* ==================== SERVICIOS ==================== */

    async function cargarTiposParaServicios() {
      const select = document.getElementById('serv_tipo_vehiculo');
      try {
        const resp = await fetch(`${API_BASE}/tipos-vehiculo`, { headers: { 'Accept': 'application/json' } });
        const tipos = await resp.json();

        select.innerHTML = '<option value="" disabled selected>-- Selecciona un tipo --</option>';
        tipos.forEach(t => {
          const option = document.createElement('option');
          option.value = t.id_tipo_vehiculo;
          option.textContent = t.nombre_tipo_vehiculo;
          select.appendChild(option);
        });
      } catch (error) {
        select.innerHTML = '<option value="" disabled selected>No se pudieron cargar los tipos</option>';
      }
    }

    async function cargarServicios() {
      const tbody = document.getElementById('tabla-servicios');
      try {
        const resp = await fetch(`${API_BASE}/servicios`, { headers: { 'Accept': 'application/json' } });
        const servicios = await resp.json();

        if (servicios.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-muted">No hay servicios registrados.</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        servicios.forEach(s => {
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${s.nombre_servicio}</td>
            <td>${s.tipo_vehiculo ? s.tipo_vehiculo.nombre_tipo_vehiculo : '-'}</td>
            <td>$${s.costo_servicio}</td>
            <td>${(s.estado_servicio == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" onclick='abrirModalEditarServicio(${JSON.stringify(s)})'>
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="eliminarServicio(${s.id_servicio})">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          `;
          tbody.appendChild(fila);
        });
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-danger">No se pudieron cargar los servicios.</td></tr>';
      }
    }

    function abrirModalCrearServicio() {
      document.getElementById('tituloModalServicio').textContent = 'Nuevo servicio';
      document.getElementById('form-servicio').reset();
      document.getElementById('serv_id').value = '';
      document.getElementById('serv_estado').checked = true;
      document.getElementById('mensaje-servicio').className = 'alert d-none';
      cargarTiposParaServicios();
    }

    function abrirModalEditarServicio(servicio) {
      document.getElementById('tituloModalServicio').textContent = 'Editar servicio';
      document.getElementById('serv_id').value = servicio.id_servicio;
      document.getElementById('serv_nombre').value = servicio.nombre_servicio;
      document.getElementById('serv_descripcion').value = servicio.descripcion_servicio;
      document.getElementById('serv_costo').value = servicio.costo_servicio;
      document.getElementById('serv_estado').checked = servicio.estado_servicio == 1;
      document.getElementById('mensaje-servicio').className = 'alert d-none';

      cargarTiposParaServicios().then(() => {
        document.getElementById('serv_tipo_vehiculo').value = servicio.id_tipo_vehiculo;
      });

      new bootstrap.Modal(document.getElementById('modalServicio')).show();
    }

    async function guardarServicio() {
      const id = document.getElementById('serv_id').value;
      const datos = {
        nombre_servicio: document.getElementById('serv_nombre').value.trim(),
        id_tipo_vehiculo: document.getElementById('serv_tipo_vehiculo').value,
        descripcion_servicio: document.getElementById('serv_descripcion').value.trim(),
        costo_servicio: document.getElementById('serv_costo').value,
        estado_servicio: document.getElementById('serv_estado').checked ? 1 : 0,
      };

      try {
        const respuesta = await fetch(`${API_BASE}/servicios${id ? '/' + id : ''}`, {
          method: id ? 'PUT' : 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
          mostrarErrores('mensaje-servicio', resultado, 'Ocurrió un error al guardar el servicio.');
          return;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalServicio')).hide();
        cargarServicios();
      } catch (error) {
        mostrarErrores('mensaje-servicio', {}, 'No se pudo conectar con el servidor.');
      }
    }

    async function eliminarServicio(id) {
      if (!confirm('¿Eliminar este servicio?')) return;

      try {
        const respuesta = await fetch(`${API_BASE}/servicios/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json' },
        });

        if (!respuesta.ok) {
          const resultado = await respuesta.json();
          alert(resultado.message || 'No se pudo eliminar el servicio.');
          return;
        }

        cargarServicios();
      } catch (error) {
        alert('No se pudo conectar con el servidor.');
      }
    }

    /* ==================== RESERVAS (panel Control) — NUEVO ==================== */

    async function cargarResumenHoy() {
      try {
        const resp = await fetch(`${API_BASE}/reservas/resumen-hoy`, { headers: { 'Accept': 'application/json' } });
        const r = await resp.json();

        document.getElementById('resumen-ganancias').textContent = `$${Number(r.ganancias).toLocaleString('es-CO')}`;
        document.getElementById('resumen-cantidad').textContent = r.cantidad_reservas;
        document.getElementById('resumen-detalle').textContent =
          `${r.reservas_en_proceso} en proceso · ${r.reservas_finalizadas} finalizadas`;
      } catch (error) {
        document.getElementById('resumen-ganancias').textContent = '-';
      }

      cargarCuposHoy();
    }

    async function cargarCuposHoy() {
      const cont = document.getElementById('resumen-cupos');
      const hoy = new Date().toISOString().slice(0, 10);

      try {
        const respTipos = await fetch(`${API_BASE}/tipos-vehiculo`, { headers: { 'Accept': 'application/json' } });
        const tipos = await respTipos.json();

        const filas = await Promise.all(tipos.map(async t => {
          const resp = await fetch(`${API_BASE}/reservas/cupos-disponibles?id_tipo_vehiculo=${t.id_tipo_vehiculo}&fecha=${hoy}`, {
            headers: { 'Accept': 'application/json' },
          });
          const d = await resp.json();
          if (d.cupos_totales === null || d.cupos_totales === undefined) return null;
          return `${t.nombre_tipo_vehiculo}: ${d.cupos_disponibles}/${d.cupos_totales}`;
        }));

        cont.innerHTML = filas.filter(Boolean).join('<br>') || 'Sin límite configurado';
      } catch (error) {
        cont.textContent = 'No se pudieron cargar los cupos.';
      }
    }

    async function cargarReservasEnProceso() {
      const tbody = document.getElementById('tabla-reservas-proceso');
      try {
        const resp = await fetch(`${API_BASE}/reservas/en-proceso`, { headers: { 'Accept': 'application/json' } });
        const reservas = await resp.json();

        if (reservas.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-muted">No hay reservas en proceso.</td></tr>';
          return;
        }

        tbody.innerHTML = '';
        reservas.forEach(r => {
          const u = (r.cliente && r.cliente.usuario) || {};
          const fila = document.createElement('tr');
          fila.innerHTML = `
            <td>${r.id_reserva}</td>
            <td>${u.nombre_usuario ?? ''} ${u.apellido_usuario ?? ''}</td>
            <td>${r.placa_vehiculo}</td>
            <td>${r.servicio ? r.servicio.nombre_servicio : '-'}</td>
            <td>${r.fecha}</td>
            <td>${r.hora}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-success" onclick="finalizarReserva(${r.id_reserva})">
                <i class="fa-solid fa-check me-1"></i> Finalizar
              </button>
            </td>
          `;
          tbody.appendChild(fila);
        });
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-danger">No se pudieron cargar las reservas.</td></tr>';
      }
    }

    async function finalizarReserva(id) {
      if (!confirm('¿Marcar esta reserva como finalizada? Esto libera el cupo del día.')) return;

      try {
        const respuesta = await fetch(`${API_BASE}/reservas/${id}/finalizar`, {
          method: 'PATCH',
          headers: { 'Accept': 'application/json' },
        });

        if (!respuesta.ok) {
          const resultado = await respuesta.json();
          alert(resultado.message || 'No se pudo finalizar la reserva.');
          return;
        }

        cargarReservasEnProceso();
        cargarResumenHoy();
      } catch (error) {
        alert('No se pudo conectar con el servidor.');
      }
    }

    function abrirModalReservaControl() {
      document.getElementById('form-reserva-control').reset();
      document.getElementById('mensaje-reserva-control').className = 'alert d-none';
      document.getElementById('rc-cupos-info').textContent = '';
      document.getElementById('rc_fecha').value = new Date().toISOString().slice(0, 10);
      cargarTiposControl();
      cargarMetodosPagoControl();
    }

    async function cargarTiposControl() {
      const select = document.getElementById('rc_tipo_vehiculo');
      const resp = await fetch(`${API_BASE}/tipos-vehiculo`, { headers: { 'Accept': 'application/json' } });
      const tipos = await resp.json();

      select.innerHTML = '<option value="" disabled selected>-- Selecciona un tipo --</option>';
      tipos.forEach(t => {
        const option = document.createElement('option');
        option.value = t.id_tipo_vehiculo;
        option.textContent = t.nombre_tipo_vehiculo;
        select.appendChild(option);
      });
    }

    async function cargarServiciosControl() {
      const idTipo = document.getElementById('rc_tipo_vehiculo').value;
      const select = document.getElementById('rc_servicio');
      select.innerHTML = '<option value="" disabled selected>Cargando...</option>';
      if (!idTipo) return;

      const resp = await fetch(`${API_BASE}/servicios`, { headers: { 'Accept': 'application/json' } });
      const servicios = await resp.json();
      const filtrados = servicios.filter(s => s.id_tipo_vehiculo == idTipo);

      select.innerHTML = '<option value="" disabled selected>-- Selecciona un servicio --</option>';
      filtrados.forEach(s => {
        const option = document.createElement('option');
        option.value = s.id_servicio;
        option.textContent = `${s.nombre_servicio} - $${s.costo_servicio}`;
        select.appendChild(option);
      });
    }

    async function cargarMetodosPagoControl() {
      const select = document.getElementById('rc_metodo_pago');
      const resp = await fetch(`${API_BASE}/metodos-pago`, { headers: { 'Accept': 'application/json' } });
      const metodos = await resp.json();

      select.innerHTML = '<option value="" disabled selected>-- Selecciona un método --</option>';
      metodos.forEach(m => {
        const option = document.createElement('option');
        option.value = m.id_metodo_pago;
        option.textContent = m.nombre_metodo_pago;
        select.appendChild(option);
      });
    }

    async function consultarCuposControl() {
      const idTipo = document.getElementById('rc_tipo_vehiculo').value;
      const fecha = document.getElementById('rc_fecha').value;
      const info = document.getElementById('rc-cupos-info');

      if (!idTipo || !fecha) return;

      try {
        const resp = await fetch(`${API_BASE}/reservas/cupos-disponibles?id_tipo_vehiculo=${idTipo}&fecha=${fecha}`, {
          headers: { 'Accept': 'application/json' },
        });
        const d = await resp.json();

        if (d.cupos_totales === null || d.cupos_totales === undefined) {
          info.textContent = 'Este tipo de vehículo no tiene límite de cupos configurado.';
          return;
        }

        info.textContent = `Cupos disponibles: ${d.cupos_disponibles} de ${d.cupos_totales}`;
        info.className = d.cupos_disponibles > 0 ? 'small text-success mb-3' : 'small text-danger mb-3';
      } catch (error) {
        info.textContent = '';
      }
    }

    async function guardarReservaControl() {
      const admin = getAdminGuardado();

      const datos = {
        no_documento_cliente: document.getElementById('rc_documento').value,
        nombre_usuario: document.getElementById('rc_nombre').value.trim(),
        apellido_usuario: document.getElementById('rc_apellido').value.trim(),
        numero_celular: document.getElementById('rc_celular').value.trim(),
        placa_vehiculo: document.getElementById('rc_placa').value.trim(),
        id_tipo_vehiculo: document.getElementById('rc_tipo_vehiculo').value,
        color_vehiculo: document.getElementById('rc_color').value.trim(),
        marca_vehiculo: document.getElementById('rc_marca').value.trim(),
        modelo_vehiculo: document.getElementById('rc_modelo').value.trim(),
        id_servicio: document.getElementById('rc_servicio').value,
        fecha: document.getElementById('rc_fecha').value,
        hora: document.getElementById('rc_hora').value,
        id_metodo_pago: document.getElementById('rc_metodo_pago').value,
        no_documento_administrador: admin ? admin.no_documento_administrador : null,
      };

      try {
        const respuesta = await fetch(`${API_BASE}/reservas/control`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(datos),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
          mostrarErrores('mensaje-reserva-control', resultado, 'Ocurrió un error al crear la reserva.');
          return;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalReservaControl')).hide();
        cargarReservasEnProceso();
        cargarResumenHoy();
      } catch (error) {
        mostrarErrores('mensaje-reserva-control', {}, 'No se pudo conectar con el servidor.');
      }
    }

    /* ==================== LOGIN DEL PANEL ==================== */

    // Sesión de administrador: se guarda en sessionStorage (se borra al
    // cerrar la pestaña/navegador), separada de la del cliente (localStorage).
    function getAdminGuardado() {
      const raw = sessionStorage.getItem('admin_control');
      return raw ? JSON.parse(raw) : null;
    }

    function mostrarPanelControl(admin) {
      document.getElementById('bloque-login-control').classList.add('d-none');
      document.getElementById('panel-control').classList.remove('d-none');
      document.getElementById('btn-cerrar-sesion-control').classList.remove('d-none');
      document.getElementById('nombre-admin-conectado').textContent =
        `${admin.nombre_usuario} ${admin.apellido_usuario}`;

      // Solo se cargan los datos reales una vez que hay sesión de administrador.
      cargarClientes();
      cargarTipos();
      cargarServicios();
      cargarResumenHoy();          // NUEVO
      cargarReservasEnProceso();   // NUEVO
    }

    function mostrarPantallaLogin() {
      document.getElementById('panel-control').classList.add('d-none');
      document.getElementById('btn-cerrar-sesion-control').classList.add('d-none');
      document.getElementById('bloque-login-control').classList.remove('d-none');
    }

    document.getElementById('form-login-control').addEventListener('submit', async function (e) {
      e.preventDefault();

      const mensaje = document.getElementById('mensaje-login-control');
      mensaje.className = 'alert d-none';

      const correo = document.getElementById('control_correo').value.trim();
      const contrasenia = document.getElementById('control_contrasenia').value;

      try {
        const respuesta = await fetch(`${API_BASE}/login-control`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ correo_usuario: correo, contrasenia: contrasenia }),
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok) {
          mensaje.textContent = resultado.message || 'No se pudo iniciar sesión.';
          mensaje.className = 'alert alert-danger';
          return;
        }

        sessionStorage.setItem('admin_control', JSON.stringify(resultado));
        document.getElementById('form-login-control').reset();
        mostrarPanelControl(resultado);

      } catch (error) {
        mensaje.textContent = 'No se pudo conectar con el servidor.';
        mensaje.className = 'alert alert-danger';
      }
    });

    document.getElementById('btn-cerrar-sesion-control').addEventListener('click', function () {
      sessionStorage.removeItem('admin_control');
      mostrarPantallaLogin();
    });

    /* ==================== INICIALIZACIÓN ==================== */

    const adminActual = getAdminGuardado();
    if (adminActual) {
      mostrarPanelControl(adminActual);
    } else {
      mostrarPantallaLogin();
    }
  </script>

</body>
</html>