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
            <div class="navbar-nav gap-3 align-items-center">
                <a class="nav-link text-primary fw-semibold d-flex align-items-center gap-1 active" href="{{ route('inicio') }}">
                    <i class="fa-solid fa-house"></i> Inicio
                </a>
                <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('reservas.vista') }}">
                    <i class="fa-solid fa-calendar-days"></i> Reservar
                </a>
                @if(session('cliente'))
                    <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('mi-cuenta') }}">
                        <i class="fa-solid fa-user"></i> Mi cuenta
                    </a>
                @endif
                <a class="nav-link text-secondary fw-semibold d-flex align-items-center gap-1" href="{{ route('control') }}">
                    <i class="fa-solid fa-gear"></i> Control
                </a>

                @if(session('cliente'))
                    <form method="POST" action="{{ route('logout.web') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar sesión
                        </button>
                    </form>
                @else
                    <button class="btn btn-outline-primary btn-sm rounded-pill" id="btn-abrir-login" data-bs-toggle="modal" data-bs-target="#modalLogin">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar sesión
                    </button>
                @endif
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

    @if(session('status'))
        <div class="container mt-4">
            <div class="alert alert-success text-center mb-0">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if($errors->any() && !$errors->has('login'))
        <div class="container mt-4">
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if(session('cliente'))
        <!-- Ya hay una sesión de cliente activa en el servidor -->
        <section class="d-flex justify-content-center align-items-center py-5" style="background-color: #e2e8f0; min-height: 480px;">
            <div class="card border-0 shadow-lg p-4 rounded-4 text-center" style="max-width: 460px; width: 90%;">
                <div class="card-body p-2">
                    <i class="fa-solid fa-circle-check text-success fs-1 mb-3"></i>
                    <h2 class="fs-4 fw-bold mb-2">¡Hola de nuevo, {{ session('cliente')['nombre_usuario'] }}!</h2>
                    <p class="text-muted mb-4">Ya iniciaste sesión.</p>
                    <a href="{{ route('mi-cuenta') }}" class="btn btn-primary rounded-pill mb-2">
                        <i class="fa-solid fa-car-side me-1"></i> Ir a Mi cuenta / Mis vehículos
                    </a>
                    <a href="{{ route('reservas.vista') }}" class="btn btn-outline-primary rounded-pill mb-2">
                        <i class="fa-solid fa-calendar-days me-1"></i> Hacer una reserva
                    </a>
                </div>
            </div>
        </section>
    @else
        <!-- Sección de Registro -->
        <section class="d-flex justify-content-center align-items-center py-5" style="background-color: #e2e8f0; min-height: 480px;">

            <div class="card border-0 shadow-lg p-4 position-relative rounded-4" style="max-width: 460px; width: 90%;">
                <div class="card-body p-2">
                    <h2 class="card-title fw-extrabold text-dark mb-4 fs-4 text-center">
                        ÚNETE A<br>PARQUEADERO<sup>®</sup><br>RESERVAS
                    </h2>

                    {{-- Formulario normal (sin JS/AJAX): hace POST directo a
                         registro.web, que crea el usuario+cliente y deja
                         guardado session('cliente') en el servidor. --}}
                    <form method="POST" action="{{ route('registro.web') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-5">
                                <label class="form-label small">Tipo de documento</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="CC" {{ old('tipo_documento') == 'CC' ? 'selected' : '' }}>Cédula de ciudadanía</option>
                                    <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Cédula de extranjería</option>
                                    <option value="TI" {{ old('tipo_documento') == 'TI' ? 'selected' : '' }}>Tarjeta de identidad</option>
                                    <option value="PA" {{ old('tipo_documento') == 'PA' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-7">
                                <label class="form-label small">Número de documento</label>
                                <input type="number" class="form-control" id="no_documento_cliente" name="no_documento_cliente" value="{{ old('no_documento_cliente') }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small">Nombre</label>
                                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" maxlength="20" value="{{ old('nombre_usuario') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Apellido</label>
                                <input type="text" class="form-control" id="apellido_usuario" name="apellido_usuario" maxlength="20" value="{{ old('apellido_usuario') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Número de celular</label>
                            <input type="tel" class="form-control" id="numero_celular" name="numero_celular"
                                   maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                   title="10 dígitos numéricos, sin espacios ni letras"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   value="{{ old('numero_celular') }}"
                                   required>
                            <div class="form-text">Solo números, 10 dígitos.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Correo</label>
                            <input type="email" class="form-control" id="correo_usuario" name="correo_usuario"
                                   maxlength="100"
                                   pattern="[^\s@]+@[^\s@]+\.[A-Za-z]{2,}"
                                   title="Debe incluir @ y un dominio válido, ejemplo: nombre@correo.com"
                                   value="{{ old('correo_usuario') }}"
                                   required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small">Contraseña</label>
                                <input type="password" class="form-control" id="contrasenia" name="contrasenia"
                                       minlength="8"
                                       pattern="(?=.*[A-Za-z])(?=.*\d).+"
                                       title="Mínimo 8 caracteres, con al menos una letra y un número"
                                       required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Confirmar contraseña</label>
                                <input type="password" class="form-control" id="contrasenia_confirmation" name="contrasenia_confirmation"
                                       minlength="8" required>
                            </div>
                        </div>
                        <div class="form-text mb-3 mt-n2">Mín. 8 caracteres, con letras y números. Debe coincidir en ambos campos.</div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                            REGISTRARSE
                        </button>
                    </form>
                </div>
            </div>

        </section>
    @endif

    <!-- ============ MODAL: Iniciar sesión ============ -->
    <div class="modal fade" id="modalLogin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                {{-- Formulario normal (sin JS/AJAX): hace POST directo a
                     login.web, que verifica la contraseña y crea
                     session('cliente') en el servidor si es correcta. --}}
                <form method="POST" action="{{ route('login.web') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa-solid fa-right-to-bracket me-1"></i> Iniciar sesión</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small">Correo</label>
                            <input type="email" class="form-control" name="correo_usuario" value="{{ old('correo_usuario') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Contraseña</label>
                            <input type="password" class="form-control" name="contrasenia" required>
                        </div>
                        @if($errors->has('login'))
                            <div class="alert alert-danger">
                                {{ $errors->first('login') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @if($errors->has('login'))
        {{-- Si el login falló, la página recarga completa (POST normal),
             así que reabrimos el modal para que el cliente vea el error
             sin tener que volver a darle clic a "Iniciar sesión". --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                new bootstrap.Modal(document.getElementById('modalLogin')).show();
            });
        </script>
    @endif

</body>
</html>