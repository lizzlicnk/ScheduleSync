
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Nueva contraseña | Schedule-Sync</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Icono -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!--Estilos-->
    <link rel="stylesheet" href="{{ asset('css/auth/panel-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/nueva-contrasena.css') }}">

</head>
<body>
    <main class="pantalla-auth container-fluid p-0">
        <div class="contenedor-auth row g-0">

            <!-- Panel institucional reutilizable -->
            <section class="panel-auth-lateral col-12 col-lg-6">
                @include('components.auth.panel-lateral')
            </section>

            <!-- Panel del formulario de nueva contraseña -->
            <section class="contenido-auth col-12 col-lg-6">
                <div class="formulario-auth">

                    <!-- Indicador de las tres etapas de recuperación -->
                    <div class="pasos-recuperacion">
                        <!-- Etapa 1: correo completado -->
                        <div class="paso-recuperacion completado">
                            <span class="numero-paso">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="nombre-paso">Correo</span>
                        </div>

                        <div class="linea-paso linea-completada"></div>

                        <!-- Etapa 2: código completado -->
                        <div class="paso-recuperacion completado">
                            <span class="numero-paso">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="nombre-paso">Código</span>
                        </div>

                        <!-- Línea que indica la etapa completada -->
                        <div class="linea-paso linea-completada"></div>

                        <!-- Etapa 3: nueva contraseña actual -->
                        <div class="paso-recuperacion paso-activo">
                            <span class="numero-paso">3</span>
                            <span class="nombre-paso">Nueva contraseña</span>
                        </div>
                    </div>

                    <!-- Identificador de la etapa actual -->
                    <span class="etiqueta-etapa">ETAPA 3 DE 3</span>

                    <div class="encabezado-verificacion">
                        <div class="icono-pagina">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <div class="textos-encabezado">
                            <p class="etiqueta-auth">SEGURIDAD DE TU CUENTA</p>
                            <h1>Nueva contraseña</h1>
                        </div>
                    </div>

                    <p class="descripcion-auth">
                        Registra una contraseña nueva para volver a ingresar a Schedule-Sync.
                    </p>

                    <!-- Mensaje de éxito -->
                    @if (session('mensaje'))
                        <div class="alerta-auth alerta-exito" role="status">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ session('mensaje') }}</span>
                        </div>
                    @endif

                    <!-- Mensajes de error de validación -->
                    @if ($errors->any())
                        <div class="alerta-auth alerta-error" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Formulario para actualizar la contraseña -->                    
                    <form id="formulario-nueva-contrasena" action="{{ route('contrasena.actualizar') }}" method="POST">
                        @csrf

                        <div class="campo-auth">

                            <!-- Nueva contraseña -->
                            <label for="password">Nueva contraseña <span class="campo-requerido">*</span> </label>

                            <div class="entrada-con-icono">
                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Escribe tu contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <!-- Botón para mostrar u ocultar la contraseña -->
                                <button
                                    type="button"
                                    class="boton-ver-contrasena"
                                    data-campo="password"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>

                            <!-- Requisitos de la contraseña -->
                            <small class="ayuda-campo">
                                Usa al menos 8 caracteres, letras y números.
                            </small>
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="campo-auth">
                            <label for="password_confirmation"> Confirmar contraseña <span class="campo-requerido">*</span> </label>
                            
                            <div class="entrada-con-icono">
                                <i class="fa-solid fa-lock"></i>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="Repite tu contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <!-- Botón para mostrar u ocultar la confirmación -->
                                <button
                                    type="button"
                                    class="boton-ver-contrasena"
                                    data-campo="password_confirmation"
                                    aria-label="Mostrar confirmación de contraseña"
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>

                            <!-- Mensaje informativo sobre la coincidencia -->
                            <small id="mensaje-coincidencia" class="ayuda-campo">
                                Ambas contraseñas deben coincidir.
                            </small>
                        </div>

                        <!--Actualizar la contraseña -->
                        <button type="submit" id="botonActualizarContrasena" class="boton-auth" >
                            Actualizar contraseña
                            <i class="fa-solid fa-check"></i>
                        </button>
                    </form>

                    <!-- Regresar al inicio -->
                    <a href="{{ route('login') }}" class="enlace-regreso">
                        <i class="fa-solid fa-arrow-left"></i>
                        Volver al inicio de sesión
                    </a>

                    <p class="nota-auth">
                        <i class="fa-solid fa-shield-halved"></i>
                        No compartas tu contraseña con otras personas.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/usuarios/auth/nueva-contrasena.js') }}?v=2"></script>

</body>
</html>
