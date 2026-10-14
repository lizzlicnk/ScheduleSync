
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cambiar contraseña | ScheduleSync</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" >

    <link rel="stylesheet" href="{{ asset('css/auth/panel-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/cambiar-contrasena-obligatoria.css') }}" >
</head>

<body>
    <main class="contenedor-login container-fluid p-0">
        <div class="row g-0 min-vh-100">

            {{-- Panel institucional: ocupa la mitad en pantallas grandes. --}}
            <div class="col-12 col-lg-6">
                @include('components.auth.panel-lateral')
            </div>

            {{-- Formulario: ocupa toda la pantalla en móviles. --}}
            <div class="col-12 col-lg-6 panel-formulario">
                <div class="contenido-formulario">
                    <div class="encabezado-formulario">

                        <div class="d-flex align-items-center gap-3 mb-4">

                            <div class="icono-cambio flex-shrink-0">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>

                            <div class="flex-grow-1">
                                <h2 class="mb-2">Cambia tu contraseña</h2>

                                <p class="mb-0">
                                    Para continuar, establece una nueva contraseña
                                    para tu cuenta de ScheduleSync.
                                </p>
                            </div>

                        </div>

                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="formulario-cambio-contrasena" action="{{ route('contrasena.obligatoria.actualizar') }}" method="POST" >
                        @csrf

                        <div class="mb-3">
                            <label for="password_actual" class="form-label">
                                Contraseña actual
                                <span class="campo-requerido">*</span>
                            </label>

                            <div class="entrada-con-icono">
                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_actual"
                                    name="password_actual"
                                    autocomplete="current-password"
                                    placeholder="Ingresa tu contraseña actual"
                                    required
                                >

                                <button
                                    type="button"
                                    class="boton-ver-contrasena"
                                    data-campo="password_actual"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Nueva contraseña
                                <span class="campo-requerido">*</span>
                            </label>

                            <div class="entrada-con-icono">
                                <i class="fa-solid fa-key"></i>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="Crea una contraseña nueva"
                                    required
                                >

                                <button
                                    type="button"
                                    class="boton-ver-contrasena"
                                    data-campo="password"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>

                            <small class="texto-ayuda">
                                Mínimo 8 caracteres, una letra y un número.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirmar nueva contraseña
                                <span class="campo-requerido">*</span>
                            </label>

                            <div class="entrada-con-icono">
                                <i class="fa-solid fa-shield-halved"></i>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="Repite la contraseña nueva"
                                    required
                                >

                                <button
                                    type="button"
                                    class="boton-ver-contrasena"
                                    data-campo="password_confirmation"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>

                            <small
                                id="mensaje-coincidencia"
                                class="ayuda-campo"
                                aria-live="polite"
                            >
                                Ambas contraseñas deben coincidir.
                            </small>
                        </div>

                        <button
                            type="submit"
                            id="botonActualizarContrasena"
                            class="btn boton-iniciar w-100"
                        >
                            <i class="fa-solid fa-lock me-2"></i>
                            Guardar nueva contraseña
                        </button>
                    </form>

                    <p class="texto-pie">
                        UPEMOR · Sistema de gestión de horarios académicos
                    </p>

                </div>
            </div>
        </div>
    </main>

    @if (session('mensaje_contrasena'))
        <div class="modal fade" id="modalContrasenaActualizada"
            tabindex="-1" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">

            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-contrasena-exito">
                    <div class="modal-body text-center">

                        <div class="icono-modal-exito">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <h2>¡Contraseña actualizada!</h2>

                        <p>{{ session('mensaje_contrasena') }}</p>

                        <div class="barra-tiempo-contenedor">
                            <div id="barraTiempoRedireccion"
                                class="barra-tiempo-progreso"></div>
                        </div>

                        <small class="texto-redireccion">
                            Redirigiendo a Inicio...
                        </small>

                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/usuarios/auth/cambiar-contrasena-obligatoria.js') }}"></script>
</body>
</html>
