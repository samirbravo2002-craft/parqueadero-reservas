<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parqueadero Reservas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación Superior -->
    <nav class="navbar navbar-expand bg-white border-bottom px-4 py-2">
        <div class="container-fluid p-0">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('inicio') }}">
                <i class="fa-solid fa-car fs-4"></i>
                <span>PARQUEADERO RESERVAS</span>
            </a>
            <div class="navbar-nav gap-3">
                <a class="nav-link text-primary fw-semibold d-flex align-items-center gap-1 active" href="{{ route('inicio') }}">
                    <i class="fa-solid fa-house"></i> Inicio
                </a>
                <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('reservas.vista') }}">
                    <i class="fa-solid fa-calendar-days"></i> Reservar
                </a>
            </div>
        </div>
    </nav>

    <!-- Banner Hero -->
    <section class="bg-dark text-white py-4 px-4 px-md-5" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);">
        <div class="container-fluid p-0">
            <div class="row align-items-center g-3">
                <div class="col-md-7 text-center text-md-start">
                    <h1 class="fw-bold mb-1 fs-2">BIENVENIDO A<br>PARQUEADERO RESERVAS</h1>
                    <p class="text-info-subtle mb-0">Simplifica tu estacionamiento hoy</p>
                    <a href="{{ route('reservas.vista') }}" class="btn btn-info fw-bold mt-3 rounded-pill px-4">
                        Ir a reservar <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="col-md-5 text-center text-md-end">
                    <img src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&q=80&w=800"
                         alt="Ilustración Parqueadero"
                         class="img-fluid rounded-3 shadow-lg"
                         style="max-height: 180px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Registro -->
    <section class="d-flex justify-content-center align-items-center py-5" style="background-color: #e2e8f0; min-height: 480px;">

        <div class="card border-0 shadow-lg p-4 position-relative rounded-4" style="max-width: 460px; width: 90%;">
            <div class="card-body p-2">
                <h2 class="card-title fw-extrabold text-dark mb-4 fs-4 text-center">
                    ÚNETE A<br>PARQUEADERO<sup>®</sup><br>RESERVAS
                </h2>

                <form id="form-registro" novalidate>

                    <div class="row g-3 mb-3">
                        <div class="col-5">
                            <label class="form-label small">Tipo de documento</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="CC">Cédula de ciudadanía</option>
                                <option value="CE">Cédula de extranjería</option>
                                <option value="TI">Tarjeta de identidad</option>
                                <option value="PA">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label small">Número de documento</label>
                            <input type="number" class="form-control" id="no_documento_cliente" name="no_documento_cliente" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Nombre</label>
                            <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" maxlength="20" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Apellido</label>
                            <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" maxlength="20" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Número de celular</label>
                        <input type="tel" class="form-control" id="numero_celular" name="numero_celular" maxlength="20" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Contraseña</label>
                            <input type="password" class="form-control" id="contrasenia" name="contrasenia" minlength="6" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="contrasenia_confirmation" name="contrasenia_confirmation" minlength="6" required>
                        </div>
                    </div>

                    <div id="mensaje-registro" class="alert d-none" role="alert"></div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                        REGISTRARSE
                    </button>
                </form>
            </div>
        </div>

    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('form-registro').addEventListener('submit', async function (e) {
            e.preventDefault();

            const mensaje = document.getElementById('mensaje-registro');
            mensaje.className = 'alert d-none';

            const datos = {
                tipo_documento: document.getElementById('tipo_documento').value,
                no_documento_cliente: document.getElementById('no_documento_cliente').value,
                nombre_usuario: document.getElementById('nombre_usuario').value.trim(),
                apellido_usuario: document.getElementById('apellido_usuario').value.trim(),
                numero_celular: document.getElementById('numero_celular').value.trim(),
                contrasenia: document.getElementById('contrasenia').value,
                contrasenia_confirmation: document.getElementById('contrasenia_confirmation').value,
            };

            try {
                const respuesta = await fetch('/api/registro', {
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
                        : (resultado.message || 'Ocurrió un error al registrarte.');
                    mensaje.textContent = errores;
                    mensaje.className = 'alert alert-danger';
                    return;
                }

                mensaje.textContent = '¡Registro exitoso! Ya puedes ir a la sección de Reservar.';
                mensaje.className = 'alert alert-success';
                this.reset();

            } catch (error) {
                mensaje.textContent = 'No se pudo conectar con el servidor. Verifica que el backend esté corriendo.';
                mensaje.className = 'alert alert-danger';
            }
        });
    </script>

</body>
</html>