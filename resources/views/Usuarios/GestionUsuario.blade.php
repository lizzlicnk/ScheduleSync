@extends('layouts.DisenoPrincipal')

@section('titulo', 'Gestión de usuarios | Schedule-Sync')

@push('estilos')
    <!-- Estilos-->
    <link rel="stylesheet" href="{{ asset('css/usuarios/gestion-usuarios.css') }}">
@endpush

@section('contenido')

<div class="pagina-usuarios">
    <!-- Mensajes de error -->
    @if (session('error'))
        <div class="mensaje-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Errores de validación del formulario -->
    @if ($errors->any())
        <div class="mensaje-error">
            <strong>Revisa los siguientes campos:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FILTROS DESPLEGABLES -->
    <section
        class="panel-usuarios panel-filtros"
        id="contenedor-filtros"
        hidden>

        <div class="encabezado-panel">
            <div>
                <h2>Filtrar usuarios</h2>
                <p class="texto-secundario">
                    Selecciona un rol o estado para reducir los resultados.
                </p>
            </div>

            <button type="button" class="boton-secundario" id="limpiar-filtros">
                Limpiar filtros
            </button>
        </div>

        <!-- Campos para filtrar por rol y estado -->
        <div class="filtros-usuarios">
            <!-- Filtro por rol -->
            <div class="campo">
                <label for="filtro-rol">Rol</label>
                <select id="filtro-rol">
                    <option value="">Todos los roles</option>
                    <option value="1">Administrador</option>
                    <option value="2">Personal administrativo</option>
                    <option value="3">Docente</option>
                </select>
            </div>

            <!-- Filtro por estado -->
            <div class="campo">
                <label for="filtro-estado">Estado</label>
                <select id="filtro-estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
    </section>

    <!-- TABLA DE USUARIOS -->
    <section class="panel-usuarios">
        <div class="encabezado-panel encabezado-tabla">
            <div>
                <h2>Usuarios registrados</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $usuariosRegistrados->count() }}</span>
                    usuario(s) encontrado(s)
                </p>
            </div>

                <!-- Botón para mostrar u ocultar los filtros -->
                <div class="botones-encabezado">
                    <button
                        type="button"
                        class="boton-secundario"
                        id="boton-filtros"
                        aria-expanded="false"
                        aria-controls="contenedor-filtros">
                        <span aria-hidden="true">☷</span>
                        Filtros
                    </button>

                    <!-- Botón para abrir el formulario de registro -->
                    <button
                        type="button"
                        class="boton-principal"
                        onclick="abrirFormularioUsuario()">
                        <span aria-hidden="true">＋</span>
                        Nuevo usuario
                    </button>
                </div>
                
            <!-- Campo para buscar usuarios por nombre, apellido o correo -->
            <div class="busqueda-tabla campo">
                <label for="buscar-usuario">Buscar usuario</label>
                <div class="entrada-busqueda">
                    <span aria-hidden="true"> <i class="fa-solid fa-magnifying-glass"></i> </span>
                    <input
                        type="search"
                        id="buscar-usuario"
                        placeholder="Nombre, apellido o correo..."
                        autocomplete="off">
                </div>
            </div>
        </div>

        <!-- Tabla usuarios -->
        <div class="tabla-contenedor">
            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <!-- Mostrar los usuarios registrados -->
                <tbody id="tabla-usuarios">
                    @foreach ($usuariosRegistrados as $usuario)
                        @php
                            $nombreCompleto = trim(
                                $usuario->nombreUsuario . ' ' .
                                $usuario->apellidoPaterno . ' ' .
                                ($usuario->apellidoMaterno ?? '')
                            );

                            $nombreRol = match ((int) $usuario->rol) {
                                1 => 'Administrador',
                                2 => 'Personal administrativo',
                                3 => 'Docente',
                                default => 'Sin rol asignado',
                            };
                        @endphp

                        <tr
                            data-rol="{{ $usuario->rol }}"
                            data-estado="{{ $usuario->estado }}"
                            data-nombre="{{ $nombreCompleto }}"
                            data-correo="{{ $usuario->correo }}">

                            <td>{{ $usuario->id_usuario }}</td>

                            <td>
                                <div class="usuario-tabla">
                                    <strong>{{ $nombreCompleto }}</strong>
                                    <span>{{ $usuario->correo }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="rol-usuario"> {{ $nombreRol }} </span>
                            </td>

                            <td>
                                <span class="estado-usuario {{ (int) $usuario->estado === 1 ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ (int) $usuario->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <div class="acciones-tabla">
                                    <button
                                        type="button"
                                        class="boton-accion"
                                        onclick="consultarUsuario(this)"
                                        data-id="{{ $usuario->id_usuario }}"
                                        data-nombre="{{ $usuario->nombreUsuario }}"
                                        data-paterno="{{ $usuario->apellidoPaterno }}"
                                        data-materno="{{ $usuario->apellidoMaterno }}"
                                        data-correo="{{ $usuario->correo }}"
                                        data-rol="{{ $usuario->rol }}"
                                        data-estado="{{ $usuario->estado }}">
                                        Consultar
                                    </button>

                                    <a href="{{ route('usuarios.edit', ['usuario' => $usuario->id_usuario]) }}" class="boton-accion boton-editar" >
                                        Modificar
                                    </a>

                                    <!-- Mostrar Eliminar únicamente para usuarios activos -->
                                    @if ( (int) $usuario->estado === 1 && (int) $usuario->id_usuario !== (int) auth()->id() )
                                        <button
                                            type="button"
                                            class="boton-accion boton-eliminar"
                                            onclick="abrirModalBajaUsuario(
                                                {{ $usuario->id_usuario }},
                                                @js($nombreCompleto)
                                            )">
                                            Eliminar
                                        </button>
                                    @endif

                                
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <!-- Mensaje cuando no existen usuarios registrados -->
                    <tr id="fila-sin-usuarios" hidden>
                        <td colspan="5" class="tabla-vacia">
                            No se encontraron usuarios.
                        </td>
                    </tr>

                    <!-- Mensaje cuando ningún usuario coincide con los filtros -->
                    <tr id="fila-sin-filtros" hidden>
                        <td colspan="5" class="tabla-vacia">
                            No hay usuarios que coincidan con los filtros seleccionados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Controles para cambiar de página -->
        <div class="paginacion-usuarios">
            <span id="informacion-pagina">Página 1 de 1</span>

            <div class="controles-paginacion">
                <button
                    type="button"
                    class="boton-pagina"
                    id="pagina-anterior"
                    aria-label="Página anterior"
                    disabled>
                    ‹
                </button>

                <button
                    type="button"
                    class="boton-pagina"
                    id="pagina-siguiente"
                    aria-label="Página siguiente"
                    disabled>
                    ›
                </button>
            </div>
        </div>
    </section>


    <!-- Modal para confirmar la baja de un usuario -->
    <div class="modal-confirmacion" id="modal-baja-usuario" hidden>
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-baja">

            <button
                type="button"
                class="modal-cerrar"
                onclick="cerrarModalBajaUsuario()"
                aria-label="Cerrar modal">
                &times;
            </button>

            <div class="modal-icono">
                <i class="fa-solid fa-user-minus"></i>
            </div>

            <h2 id="titulo-modal-baja">
                ¿Dar de baja a este usuario?
            </h2>

            <p class="modal-mensaje">
                Estás a punto de dar de baja a:
            </p>

            <strong id="nombre-usuario-baja" class="modal-destacado"> </strong>

            <p class="modal-nota"> El usuario quedará inactivo y ya no tendrá acceso a los modulos del sistema. </p>

            <div class="modal-acciones">
                <button
                    type="button"
                    class="boton-modal-cancelar"
                    onclick="cerrarModalBajaUsuario()">
                    Cancelar
                </button>

                <button
                    type="button"
                    class="boton-modal-confirmar"
                    onclick="ejecutarBajaUsuario()">
                    <i class="fa-solid fa-trash"></i>
                    Sí, dar de baja
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de registro exitoso de usuario -->
    @if (session('tipo_modal_usuario') === 'registro' && session('mensaje_modal_usuario'))
        <div class="modal-confirmacion" id="modal-registro-usuario">

            <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-registro">

                <button
                    type="button"
                    class="modal-cerrar"
                    id="cerrar-modal-registro"
                    aria-label="Cerrar modal">
                    &times;
                </button>

                <div class="modal-icono modal-icono-exito">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h2 id="titulo-modal-registro"> ¡Usuario registrado! </h2>

                <p class="modal-mensaje">
                    {{ session('mensaje_modal_usuario') }}
                </p>

                <div class="modal-acciones">
                    <button
                        type="button"
                        class="boton-modal-confirmar boton-modal-exito"
                        id="aceptar-modal-registro">
                        <i class="fa-solid fa-check"></i>
                        Aceptar
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- Formulario para registrar o modificar usuarios -->
    <section class="panel-usuarios panel-formulario" id="formulario-usuario">

        <div class="franja-institucional"></div>

        <div class="contenido-formulario">
            <div class="encabezado-panel encabezado-formulario">
                <div class="icono-formulario" aria-hidden="true">＋</div>

                <div>
                    <h2 id="titulo-formulario">Nuevo usuario</h2>
                    <p class="texto-secundario" id="descripcion-formulario">
                        Completa los datos para registrar un nuevo usuario.
                    </p>
                </div>
            </div>

            <form id="formulario-datos-usuario" action="{{ url('/usuarios') }}" method="POST">
                @csrf

                <div id="metodo-modificacion"></div>
                <div class="formulario-grid">

                <!-- Nombre -->
                <div class="campo">
                    <label for="nombreUsuario"><i class="fa-solid fa-user"></i>  Nombre <span class="campo-requerido">*</span> </label>
                        <input
                            type="text"
                            id="nombreUsuario"
                            name="nombreUsuario"
                            placeholder="Laura"
                            required>
                </div>

                    <!-- Apellido paterno -->
                    <div class="campo">
                        <label for="apellidoPaterno"><i class="fa-solid fa-user"></i> Apellido paterno <span class="campo-requerido">*</span></label>
                        
                        <input
                            type="text"
                            id="apellidoPaterno"
                            name="apellidoPaterno"
                            placeholder="Gómez"
                            maxlength="100"
                            autocomplete="family-name"
                            required>
                    </div>

                    <!-- Apellido materno -->
                    <div class="campo">
                        <label for="apellidoMaterno"><i class="fa-solid fa-user"></i> Apellido materno </label>
                        <input
                            type="text"
                            id="apellidoMaterno"
                            name="apellidoMaterno"
                            placeholder="López"
                            maxlength="100">
                    </div>

                    <!-- Correo -->
                    <div class="campo">
                        <label for="correo"><i class="fa-solid fa-envelope"></i> Correo electrónico <span class="campo-requerido">*</span></label>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            placeholder="ejemplo@upemor.edu.mx"
                            maxlength="150"
                            autocomplete="email"
                            required>
                    </div>

                    <!-- Contraseña -->
                    <div class="campo campo-contrasena">
                        <label for="password"><i class="fa-solid fa-lock"></i> Contraseña <span id="asterisco-password" class="campo-requerido">*</span></label>

                        <div class="entrada-contrasena">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Mínimo 8 caracteres"
                                minlength="8"
                                autocomplete="new-password"
                                required>

                            <button
                                type="button"
                                id="mostrar-password"
                                class="boton-mostrar-password"
                                aria-label="Mostrar contraseña"
                                aria-pressed="false">
                                Mostrar
                            </button>
                        </div>

                        <!-- Indicador de seguridad de la contraseña -->
                        <div class="medidor-contrasena">
                            <div class="medidor-textos">
                                <span><i class="fa-solid fa-shield-alt"></i> Seguridad de la contraseña</span>
                                <strong id="texto-seguridad">Sin contraseña</strong>
                            </div>

                            <!-- Barra que representa la fortaleza de la contraseña -->
                            <div
                                class="barra-seguridad"
                                role="progressbar"
                                aria-label="Fortaleza de la contraseña"
                                aria-valuemin="0"
                                aria-valuemax="4"
                                aria-valuenow="0">
                                <div id="progreso-seguridad"></div>
                            </div>

                            <small id="ayuda-password" class="texto-secundario">
                                Usa al menos 8 caracteres, letras y números.
                            </small>
                        </div>
                    </div>

                    <!-- Rol -->
                    <div class="campo">
                        <label for="rol"> <i class="fa-solid fa-shield"></i> Rol <span class="campo-requerido">*</span></label>
                        <select id="rol" name="rol" required>
                            <option value=""> Selecciona un rol</option>
                            <option value="1" {{ old('rol') == '1' ? 'selected' : '' }}>Administrador</option>
                            <option value="2" {{ old('rol') == '2' ? 'selected' : '' }}>Personal administrativo</option>
                            <option value="3" {{ old('rol') == '3' ? 'selected' : '' }}>Docente</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="campo">
                        <label for="estado"> <i class="fa-solid fa-toggle-on"></i> Estado</label>
                        <select id="estado" name="estado">
                            <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botones para cancelar o guardar el formulario -->
                <div class="acciones-formulario">
                    <!-- Botón para cancelar la modificación -->
                    <button
                        type="button"
                        class="boton-secundario"
                        id="cancelar-edicion"
                        style="display: none;"
                        onclick="cancelarEdicionUsuario()">
                        Cancelar
                    </button>

                    <!-- Botón para registrar o guardar los cambios -->
                    <button
                        type="submit"
                        class="boton-principal"
                        id="boton-guardar">
                        Registrar usuario
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Panel para consultar los datos de un usuario -->
    <section class="panel-usuarios panel-consulta" id="panel-consulta">
        <div class="encabezado-panel">
            <div>
                <h2>Consultar usuario</h2>
                <p class="texto-secundario">
                    Busca por nombre o apellido para consultar los datos.
                </p>
            </div>
        </div>

        <!-- Buscador y tarjeta con los detalles del usuario -->
        <div class="consulta-grid">
            <div class="buscador-consulta">
                <label for="buscar-consulta">Nombre del usuario</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true"> <i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="search"
                        id="buscar-consulta"
                        placeholder="Escribe un nombre..."
                        autocomplete="off">
                </div>

                <div
                    class="resultados-consulta"
                    id="resultados-consulta"
                    aria-live="polite">
                    <p class="texto-secundario">
                        Escribe un nombre para encontrar usuarios.
                    </p>
                </div>
            </div>

            <!-- Tarjeta donde se muestran los detalles del usuario seleccionado -->
            <div class="tarjeta-detalle" id="tarjeta-detalle">
                <div class="detalle-vacio">
                    <div class="icono-detalle" aria-hidden="true">  <i class="fa-solid fa-user"> </i></div>
                    <h3>Detalles del usuario</h3>
                    <p class="texto-secundario">
                        Selecciona un usuario de los resultados para ver su información.
                    </p>
                </div>
            </div>
        </div>
    </section>


</div>

<script>
    // Rutas y datos que el JavaScript necesita de Laravel.
    window.rutaUsuarios = @json(url('/usuarios'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<!--  JavaScript para las funciones de gestión de usuarios -->
<script src="{{ asset('js/usuarios/gestion-usuarios.js') }}" defer></script>
@endsection
