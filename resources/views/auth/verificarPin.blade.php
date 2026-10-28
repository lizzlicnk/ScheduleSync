<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verificar PIN | Schedule-Sync</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Estilos del panel lateral y del formulario -->
    <link rel="stylesheet" href="{{ asset('css/auth/panel-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth/verificar-pin.css') }}">
</head>

<body>
    <main class="pantalla-auth container-fluid p-0">
        <div class="contenedor-auth row g-0">

            <!-- Panel institucional reutilizable -->
            <section class="panel-auth-lateral col-12 col-lg-6">
                @include('components.auth.panel-lateral')
            </section>

            <!-- Panel del formulario de verificación -->
            <section class="contenido-auth col-12 col-lg-6">
                <div class="formulario-auth">

                    <!-- Indicador de las etapas de recuperación -->
                    <div class="pasos-recuperacion">

                        <!-- Etapa 1: correo completado -->
                        <div class="paso-recuperacion completado">
                            <span class="numero-paso">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <span class="nombre-paso">Correo</span>
                        </div>

                        <div class="linea-paso linea-completada"></div>

                        <!-- Etapa 2: verificación del PIN actual -->
                        <div class="paso-recuperacion paso-activo">
                            <span class="numero-paso">2</span>
                            <span class="nombre-paso">Código</span>
                        </div>

                        <div class="linea-paso"></div>

                        <!-- Etapa 3: nueva contraseña -->
                        <div class="paso-recuperacion">
                            <span class="numero-paso">3</span>
                            <span class="nombre-paso">Nueva contraseña</span>
                        </div>

                    </div>

                    <!-- Identificador de la etapa actual -->
                    <span class="etiqueta-etapa">ETAPA 2 DE 3</span>

                    <!-- Encabezado de verificación -->
                    <div class="encabezado-verificacion">
                        <div class="icono-pagina">
                            <i class="fa-solid fa-envelope-circle-check"></i>
                        </div>

                        <div class="textos-encabezado">
                            <p class="etiqueta-auth">VERIFICACIÓN DE IDENTIDAD</p>
                            <h1>Verifica tu identidad</h1>
                        </div>
                    </div>

                    <!-- Mostrar el PIN únicamente en el entorno local -->
                    @if (app()->environment('local') && session('pin_prueba'))
                        <div class="alerta-auth alerta-exito" role="status">
                            <i class="fa-solid fa-vial"></i>
                            <span>
                                PIN de prueba:
                                <strong>{{ session('pin_prueba') }}</strong>
                            </span>
                        </div>
                    @endif

                    <!-- Instrucciones de verificación -->
                    <p class="descripcion-auth">
                        Ingresa el PIN de seis dígitos que enviamos a tu correo.
                        Revisa también la carpeta de correo no deseado.
                    </p>

                    <!-- Mostrar el mensaje de confirmación -->
                    @if (session('mensaje'))
                        <div class="alerta-auth alerta-exito" role="status">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>{{ session('mensaje') }}</span>
                        </div>
                    @endif

                    <!-- Mostrar los errores de validación -->
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

                    <!-- Formulario para verificar el PIN -->
                    <form action="{{ route('contrasena.verificar-pin') }}" method="POST" id="formulario-pin" >
                        @csrf
                        <div class="mb-4">
                            <!-- Etiqueta del PIN -->
                            <label class="form-label fw-semibold d-flex align-items-center gap-2" for="pin-1" >
                                <i class="fa-solid fa-key" style="color: #654282;"></i>
                                PIN de verificación
                            </label>

                            <!-- Seis cuadros individuales para el PIN -->
                            <div id="contenedor-pin" class="d-flex justify-content-between gap-2" role="group" aria-label="PIN de verificación de seis dígitos" >
                                @for ($numeroCuadro = 1; $numeroCuadro <= 6; $numeroCuadro++)
                                    <input type="text" id="pin-{{ $numeroCuadro }}" class="form-control pin-cuadro text-center fw-bold" inputmode="numeric" pattern="[0-9]*"
                                        maxlength="1" autocomplete="{{ $numeroCuadro === 1 ? 'one-time-code' : 'off' }}" aria-label="Dígito {{ $numeroCuadro }}" required >
                                @endfor
                            </div>

                            <!-- Campo oculto que enviará los seis dígitos al controlador -->
                            <input type="hidden" id="pin" name="pin" value="{{ old('pin') }}" >

                            <!-- Instrucciones para ingresar el PIN -->
                            <div id="ayuda-pin" class="form-text mt-2">
                                Escribe los seis números que recibiste por correo.
                            </div>

                        </div>

                        <!-- Enviar el PIN -->
                        <button type="submit" class="boton-auth">
                            Verificar PIN
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </form>

                    <!-- Regresar para solicitar otro PIN -->
                    <a href="{{ route('contrasena.correo') }}" class="enlace-regreso">
                        <i class="fa-solid fa-arrow-left"></i>
                        Solicitar otro PIN
                    </a>

                    <!-- Tiempo de expiración -->
                    <p class="nota-auth">
                        <i class="fa-regular fa-clock"></i>
                        El PIN vence después de 10 minutos.
                    </p>

                </div>
            </section>

        </div>
    </main>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Validación del PIN -->
    <script src="{{ asset('js/usuarios/auth/verificar-pin.js') }}?v=4"></script>
</body>
</html>
