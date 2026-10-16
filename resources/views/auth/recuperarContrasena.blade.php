<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Recuperar contraseña | Schedule-Sync</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icono -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Estilos-->
    <link rel="stylesheet" href="{{ asset('css/auth/panel-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/recuperar-contrasena.css') }}">
</head>
<body>

<main class="contenedor-login container-fluid p-0">
    <div class="row g-0 min-vh-100">

    <!-- Panel institucional -->
            <div class="col-12 col-lg-6">
                @include('components.auth.panel-lateral')
            </div>


        <!-- Formulario de recuperación -->
        <div class="col-12 col-lg-6 panel-formulario">
            <div class="contenido-formulario">

                <!-- Indicador de las tres etapas -->
                <div class="pasos-recuperacion">
                    
                    <!-- Indicar que la etapa actual es la del correo electrónico. -->
                    <div class="paso-recuperacion paso-activo">
                        <span class="numero-paso">1</span>
                        <span class="nombre-paso">Correo</span>
                    </div>

                    <div class="linea-paso"></div>

                    <div class="paso-recuperacion">
                        <span class="numero-paso">2</span>
                        <span class="nombre-paso">Código</span>
                    </div>

                    <div class="linea-paso"></div>

                    <div class="paso-recuperacion">
                        <span class="numero-paso">3</span>
                        <span class="nombre-paso">Nueva contraseña</span>
                    </div>

                </div>

                <p></p>
                <!-- Indicar el número de la etapa actual. -->
                <span class="etiqueta-etapa">ETAPA 1 DE 3</span>

                <!-- Mostrar el título y las instrucciones del formulario. -->
                <div class="encabezado-formulario">
                    <h2>Recuperar contraseña</h2>
                    <p>Ingresa tu correo y te enviaremos un código de verificación.</p>
                </div>

                <!-- Errores de validación  -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-warning" role="alert">
                        <i class="fa-solid fa-circle-info me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Formulario de recuperación de contraseña -->
                <form action="{{ route('contrasena.enviar-pin') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <!-- Correo electrónico -->
                        <label for="correo" class="form-label">
                            <i class="fa-solid fa-envelope"></i>
                            Correo electrónico
                            <span class="campo-requerido">*</span>
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="correo"
                            name="correo"
                            value="{{ old('correo') }}"
                            placeholder="usuario@upemor.edu.mx"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <!-- Enviar el formulario para validar el correo y continuar. -->
                    <button type="submit" class="btn boton-iniciar w-100">
                        Continuar
                        <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <!-- Enlace para regresar al inicio de sesión. -->
                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="enlace-recuperacion">
                        <i class="fa-solid fa-arrow-left me-2"></i>
                        Regresar al inicio de sesión
                    </a>
                </div>

                <p class="texto-pie">
                    UPEMOR · Sistema de gestión de horarios académicos
                </p>
            </div>
        </div>
    </div>
</main>

<!-- Cargar bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!--                     MODALES                    -->

<!-- Modal de resultado -->
<div class="modal fade" id="modalResultadoCorreo"
     tabindex="-1" aria-labelledby="tituloModalCorreo"
     aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-correo">
        <div class="modal-content">

            <div class="modal-body text-center">

                <!-- Mostrar el contenido correspondiente cuando el correo es válido. -->
                @if (session('modal_tipo') === 'correcto')
                    <div class="icono-resultado icono-correcto">
                        <i class="fa-solid fa-check"></i>
                    </div>

                    <h4 id="tituloModalCorreo">¡Correo correcto!</h4>

                    <p class="mensaje-modal">
                        El correo se encuentra registrado en el sistema.
                        Se enviará el código de verificación para continuar
                        con la recuperación de tu contraseña.
                    </p>

                    <div class="aviso-spam">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>
                            Revisa también tu carpeta de spam si no encuentras
                            el correo.
                        </span>
                    </div>

                     <!-- Dirigir al usuario al formulario para ingresar el código. -->
                    <a href="{{ route('contrasena.formulario-pin') }}"
                       class="btn boton-modal w-100">
                        Aceptar
                    </a>

                <!-- Mostrar el contenido correspondiente cuando el correo no es válido. -->
                @elseif (session('modal_tipo') === 'error')
                    <div class="icono-resultado icono-error">
                        <i class="fa-solid fa-xmark"></i>
                    </div>

                    <h4 id="tituloModalCorreo">Correo inválido</h4>

                    <p class="mensaje-modal">
                        El correo electrónico no se encuentra registrado
                        en el sistema. Verifica que lo hayas escrito
                        correctamente e intenta de nuevo.
                    </p>

                    <button type="button" class="btn boton-modal w-100" data-bs-dismiss="modal">
                        Aceptar
                    </button>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Mostrar el modal solo cuando el controlador indique un resultado -->
@if (in_array(session('modal_tipo'), ['correcto', 'error']))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const elementoModal = document.getElementById('modalResultadoCorreo');
        
        // Verificar que el modal exista antes de intentar mostrarlo.
        if (elementoModal) {
            const modalCorreo = new bootstrap.Modal(elementoModal);
            modalCorreo.show();
        }
    });

    
</script>
@endif

<!-- Ocultar las alertas después de 5 segundos -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertasRecuperacion = document.querySelectorAll(
            '.alert.alert-danger, .alert.alert-warning'
        );

        alertasRecuperacion.forEach(function (alertaActual) {
            setTimeout(function () {
                alertaActual.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alertaActual.style.opacity = '0';
                alertaActual.style.transform = 'translateY(-10px)';

                setTimeout(function () {
                    alertaActual.remove();
                }, 500);
            }, 5000);
        });
    });
</script>

</body>
</html>
