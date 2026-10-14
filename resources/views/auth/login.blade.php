@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/usuarios/gestion-usuarios.css') }}">
@endpush

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <title>Iniciar sesión | Schedule-Sync</title>

    {{-- Bootstrap 5 --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    {{-- Estilos exclusivos del panel lateral --}}
    <link rel="stylesheet" href="{{ asset('css/auth/panel-lateral.css') }}">

    {{-- Estilos del formulario y distribución general --}}
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">

</head>
<body>

    <main class="contenedor-login container-fluid p-0">
        <div class="row g-0 min-vh-100">

            {{-- Panel institucional reutilizable --}}
            <div class="col-12 col-lg-6">
                @include('components.auth.panel-lateral')
            </div>

            {{-- Panel del formulario --}}
            <div class="col-12 col-lg-6 panel-formulario">
                <div class="contenido-formulario">

                    <div class="encabezado-formulario">
                        <h2>Bienvenido</h2>
                        <p>Ingresa tus credenciales para acceder al sistema</p>
                    </div>

                    {{-- Mensajes temporales de autenticación y validación --}}
                    @if (session('error') || $errors->any())
                        <div id="mensaje-error-login" class="alert alert-danger" role="alert">
                            {{ session('error') ?? $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ url('/login') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="correo" class="form-label"> <i class="fa-solid fa-envelope"></i>
                                Correo electrónico <span class="campo-requerido">*</span>
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="correo"
                                name="correo"
                                value="{{ old('correo') }}"
                                placeholder="usuario@upemor.edu.mx"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label"> <i class="fa-solid fa-lock"></i> 
                                Contraseña <span class="campo-requerido">*</span>
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Contraseña"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="text-end mb-4">
                            <a
                                href="{{ url('/recuperar-contrasena') }}"
                                class="enlace-recuperacion"
                            >
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>

                        <button type="submit" class="btn boton-iniciar w-100">
                            Iniciar sesión
                        </button>
                    </form>

                    <p class="texto-pie">
                        UPEMOR · Sistema de gestión de horarios académicos
                    </p>

                </div>
            </div>

        </div>
    </main>

    {{-- JavaScript de Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/inicio/sesion/sesion-caducada.js') }}"></script>

    {{-- Control de envíos repetidos y mensajes temporales --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Evitar múltiples envíos del formulario de inicio de sesión.
            const formularioLogin = document.querySelector('form');
            const botonLogin = formularioLogin?.querySelector(
                'button[type="submit"], input[type="submit"]'
            );

            if (formularioLogin && botonLogin) {
                let formularioEnviado = false;

                formularioLogin.addEventListener('submit', function (evento) {
                    if (formularioEnviado) {
                        evento.preventDefault();
                        return;
                    }

                    if (!formularioLogin.checkValidity()) {
                        return;
                    }

                    formularioEnviado = true;
                    botonLogin.disabled = true;
                    botonLogin.textContent = 'Iniciando sesión...';
                });
            }

            // Ocultar los mensajes de error después de 3 segundos.
            const mensajeErrorLogin = document.getElementById('mensaje-error-login');

            if (mensajeErrorLogin) {
                setTimeout(function () {
                    mensajeErrorLogin.style.transition = 'opacity 0.4s ease';
                    mensajeErrorLogin.style.opacity = '0';

                    setTimeout(function () {
                        mensajeErrorLogin.remove();
                    }, 400);
                }, 3000);
            }

        });
    </script>


    <!-- Modal de confirmación del cambio de contraseña -->
    <div class="modal fade"
        id="modalContrasenaActualizada"
        tabindex="-1"
        aria-labelledby="tituloModalContrasena"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalContrasena">
                        Contraseña actualizada
                    </h5>
                </div>

                <div class="modal-body text-center">

                    <div class="icono-exito-contrasena">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <p>¡Tu contraseña se cambió correctamente!</p>

                    <p>
                        Ya puedes iniciar sesión en Schedule-Sync
                        con tu nueva contraseña.
                    </p>

                    <div class="progreso-contrasena">
                        <span></span>
                    </div>

                    <p class="texto-cierre-contrasena">
                        Este aviso se cerrará automáticamente.
                    </p>

                </div>
            </div>
        </div>
    </div>

    @if (session('contrasena_actualizada'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const elementoModal = document.getElementById(
                    'modalContrasenaActualizada'
                );

                if (!elementoModal || typeof bootstrap === 'undefined') {
                    return;
                }

                const modalConfirmacion =
                    new bootstrap.Modal(elementoModal, {
                        backdrop: 'static',
                        keyboard: false
                    });

                modalConfirmacion.show();

                // Cerrar automáticamente después de 3 segundos.
                setTimeout(function () {
                    modalConfirmacion.hide();
                }, 3000);
            });
        </script>
    @endif

    
    <!-- Modal de sesión caducada -->
    <div
        id="modalSesionCaducada"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="tituloModalSesionCaducada"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="tituloModalSesionCaducada"
                    >
                        Sesión caducada
                    </h5>
                </div>

                <div class="modal-body text-center">

                    <div class="icono-exito-contrasena">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                    <p>
                        Tu sesión ha caducado por inactividad.
                    </p>

                    <p>
                        Han transcurrido 20 minutos sin actividad.
                        Inicia sesión nuevamente para continuar.
                    </p>

                    <button
                        type="button"
                        id="botonEntendidoSesionCaducada"
                        class="btn boton-iniciar w-100"
                    >
                        Entendido
                    </button>

                </div>
            </div>
        </div>
    </div>

    
</body>
</html>
