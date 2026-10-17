@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de docentes | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/docentes/gestion-docentes.css') }}">
@endpush

@section('contenido')
<main class="pagina-docentes">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">Inicio / Administración / Docentes</p>
            <h1>Gestión de docentes</h1>
            <p class="descripcion-modulo">
                Administra la información académica de los docentes registrados en Schedule-Sync.
            </p>
        </div>
    </header>

    <!-- MENSAJES DEL SISTEMA -->
    @if (session('exito'))
        <div class="alerta-docentes alerta-exito" role="alert">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>{{ session('exito') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alerta-docentes alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alerta-docentes alerta-error" id="mensaje-errores-docente" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div>
                <strong>Revisa los siguientes datos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- FILTROS DE DOCENTES -->
    <section class="panel-docentes panel-filtros" id="contenedor-filtros" hidden>
        <div class="encabezado-panel">
            <div>
                <h2>Filtrar docentes</h2>
                <p class="texto-secundario">
                    Selecciona un tipo de docente y su estado.
                </p>
            </div>

            <button type="button" class="boton-secundario" id="limpiar-filtros">
                Limpiar filtros
            </button>
        </div>

        <div class="filtros-docentes">
            <div class="campo">
                <label for="filtro-tipo">Tipo de docente</label>
                <select id="filtro-tipo" name="filtro_tipo">
                    <option value="">Todos los tipos</option>
                    <option value="1">PTC</option>
                    <option value="2">PA</option>
                    <option value="sin-datos">Sin información académica</option>
                </select>
            </div>

            <div class="campo">
                <label for="filtro-estado">Estado de la cuenta</label>
                <select id="filtro-estado" name="filtro_estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
    </section>

    <!-- TABLA DE DOCENTES -->
    <section class="panel-docentes">
        <div class="encabezado-panel encabezado-tabla">
            <div class="titulo-tabla">
                <h2>Docentes registrados</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $docentesRegistrados->count() }}</span>
                    docente(s) encontrado(s)
                </p>
            </div>

            <div class="botones-encabezado">
                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <button
                        type="button"
                        class="boton-secundario"
                        id="boton-filtros"
                        aria-expanded="false"
                        aria-controls="contenedor-filtros"
                    >
                        <span aria-hidden="true">☷</span>
                        Filtros
                    </button>
                @endif

                @if ((int) auth()->user()->rol === 1)
                    <button
                        type="button"
                        class="boton-principal"
                        id="boton-nuevo-docente"
                    >
                        <span aria-hidden="true">＋</span>
                        Nuevo docente
                    </button>
                @endif
            </div>

            <div class="busqueda-tabla campo">
                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                <label for="buscar-docente">Buscar docente</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input
                        type="search"
                        id="buscar-docente"
                        placeholder="Nombre, apellido o clave..."
                        autocomplete="off"
                    >
                </div>
                @endif
            </div>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-docentes">
                <thead>
                    <tr>
                        <th scope="col">Matrícula</th>
                        <th scope="col">Nombre docente</th>
                        <th scope="col">Especialidad</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-docentes">
                    @forelse ($docentesRegistrados as $usuario)
                        @php
                            // Obtiene la información académica asociada a la cuenta.
                            $docente = $usuario->docente;

                            // Construye el nombre completo del docente.
                            $nombreCompleto = trim(
                                $usuario->nombreUsuario . ' ' .
                                $usuario->apellidoPaterno . ' ' .
                                ($usuario->apellidoMaterno ?? '')
                            );

                            // Comprueba si la cuenta está activa y no fue dada de baja lógicamente.
                            $cuentaActiva = (int) $usuario->estado === 1
                                && !$usuario->trashed();

                            // Convierte el tipo almacenado en una etiqueta legible.
                            $tipoDocente = $docente
                                ? (int) $docente->tipoDocente
                                : null;

                            $nombreTipo = $tipoDocente === 1
                                ? 'PTC'
                                : ($tipoDocente === 2 ? 'PA' : '-');
                        @endphp

                        <tr
                            data-id-usuario="{{ $usuario->id_usuario }}"
                            data-id-docente="{{ $docente?->id_docente ?? '' }}"
                            data-nombre="{{ mb_strtolower($nombreCompleto) }}"
                            data-correo="{{ mb_strtolower($usuario->correo) }}"
                            data-matricula="{{ mb_strtolower($docente?->matricula ?? '') }}"
                            data-especialidad="{{ mb_strtolower($docente?->especialidad ?? '') }}"
                            data-tipo="{{ $tipoDocente ?? 'sin-datos' }}"
                            data-estado="{{ $cuentaActiva ? '1' : '0' }}"
                            data-tiene-informacion="{{ $docente ? '1' : '0' }}"
                        >
                            <td>
                                <span class="matricula-docente">
                                    {{ $docente?->matricula ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <div class="usuario-tabla">
                                    <div class="informacion-usuario-tabla">
                                        <strong>{{ $nombreCompleto }}</strong>
                                        <span>{{ $usuario->correo }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $docente?->especialidad ?? '-' }}</td>

                            <td>
                                @if ($docente)
                                    <span class="rol-usuario tipo-docente">
                                        {{ $nombreTipo }}
                                    </span>
                                @else
                                    <span class="dato-sin-informacion">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="estado-usuario {{ $cuentaActiva ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ $cuentaActiva ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <div class="acciones-tabla">
                                    <button
                                        type="button"
                                        class="boton-accion boton-consultar"
                                        data-consultar-usuario="{{ $usuario->id_usuario }}"
                                        title="Consultar información"
                                        aria-label="Consultar a {{ $nombreCompleto }}"
                                    >
                                        Consultar
                                    </button>

                                    @if ($docente)
                                        @if (in_array((int) auth()->user()->rol, [1, 2, 3], true))
                                            <a
                                                href="{{ route('docentes.edit', ['idDocente' => $docente->id_docente]) }}"
                                                class="boton-accion boton-modificar"
                                                title="Modificar información académica"
                                                aria-label="Modificar a {{ $nombreCompleto }}"
                                            >
                                                Modificar
                                            </a>
                                        @endif
                                    @elseif ((int) auth()->user()->rol === 1 && $cuentaActiva)
                                        <button
                                            type="button"
                                            class="boton-accion boton-agregar"
                                            data-agregar-docente="{{ $usuario->id_usuario }}"
                                            title="Agregar información académica"
                                            aria-label="Agregar información académica de {{ $nombreCompleto }}"
                                        >
                                            Agregar
                                        </button>
                                    @endif

                                    <!-- Permite solicitar la baja de una cuenta docente activa. -->
                                    @if ( in_array((int) auth()->user()->rol, [1, 2], true) && (int) $usuario->rol === 3 && $cuentaActiva)
                                        <form
                                            action="{{ route('docentes.baja', ['idUsuario' => $usuario->id_usuario]) }}"
                                            method="POST"
                                            class="formulario-baja-docente"
                                            data-nombre-docente="{{ $nombreCompleto }}"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="button"
                                                class="boton-accion boton-eliminar"
                                                data-baja-docente
                                                title="Dar de baja al docente"
                                                aria-label="Dar de baja a {{ $nombreCompleto }}"
                                            >
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="fila-sin-docentes">
                            <td colspan="6" class="tabla-vacia">
                                No hay docentes registrados.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="fila-sin-filtros" hidden>
                        <td colspan="6" class="tabla-vacia">
                            No hay docentes que coincidan con la búsqueda o los filtros seleccionados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- FORMULARIO PARA AGREGAR DOCENTES -->
    @if ((int) auth()->user()->rol === 1)
        <section class="panel-docentes panel-formulario-docente" id="formulario-docente" >
            <div class="franja-institucional"></div>

            <div class="contenido-formulario">
                <div class="encabezado-panel encabezado-formulario">
                    <div class="icono-formulario" aria-hidden="true">＋</div>

                    <div>
                        <h2 id="titulo-formulario-docente">Nuevo docente</h2>
                        <p class="texto-secundario" id="descripcion-formulario-docente">
                            Completa los datos para registrar la información académica del docente.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="boton-cerrar-panel"
                        data-cerrar-formulario
                        aria-label="Cerrar formulario"
                    >
                        &times;
                    </button>
                </div>

                <form
                    action="{{ route('docentes.store') }}"
                    method="POST"
                    id="formAgregarDocente"
                >
                    @csrf

                    <div class="formulario-grid">
                        <div class="campo campo-completo">
                            <label for="id_usuario">
                                <i class="fa-solid fa-user"></i> Cuenta del docente <span class="campo-obligatorio">*</span>
                            </label>

                            <select id="id_usuario" name="id_usuario" required>
                                <option value="">Selecciona una cuenta de usuario</option>

                                @foreach ($usuariosDisponibles ?? [] as $usuarioDisponible)
                                    @php
                                        // Prepara el nombre de la cuenta disponible.
                                        $nombreDisponible = trim(
                                            $usuarioDisponible->nombreUsuario . ' ' .
                                            $usuarioDisponible->apellidoPaterno . ' ' .
                                            ($usuarioDisponible->apellidoMaterno ?? '')
                                        );
                                    @endphp

                                    <option
                                        value="{{ $usuarioDisponible->id_usuario }}"
                                        @selected((string) old('id_usuario') === (string) $usuarioDisponible->id_usuario)
                                    >
                                        {{ $nombreDisponible }} — {{ $usuarioDisponible->correo }}
                                    </option>
                                @endforeach
                            </select>

                            <small class="ayuda-campo">
                                Solo aparecen las cuentas activas que todavía no tienen información académica.
                            </small>
                        </div>

                        <div class="campo">
                            <label for="matricula">
                                <i class="fa-solid fa-id-card"></i> Matrícula <span class="campo-obligatorio">*</span>
                            </label>

                            <input
                                type="text"
                                id="matricula"
                                name="matricula"
                                value="{{ old('matricula') }}"
                                maxlength="20"
                                placeholder="Ej. DOC2026001"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="especialidad">
                                <i class="fa-solid fa-graduation-cap"></i> Especialidad <span class="campo-obligatorio">*</span>
                            </label>

                            <input
                                type="text"
                                id="especialidad"
                                name="especialidad"
                                value="{{ old('especialidad') }}"
                                maxlength="100"
                                placeholder="Ej. Programador"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="tipoDocente">
                                <i class="fa-solid fa-shield"></i> Tipo de docente <span class="campo-obligatorio">*</span>
                            </label>

                            <select id="tipoDocente" name="tipoDocente" required>
                                <option value="">Selecciona el tipo</option>
                                <option value="1" @selected(old('tipoDocente') == '1')>
                                    PTC — Tiempo completo
                                </option>
                                <option value="2" @selected(old('tipoDocente') == '2')>
                                    PA — Asignatura
                                </option>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="cargaAcademica">
                                <i class="fa-solid fa-clock"></i> Carga académica <span class="campo-obligatorio">*</span>
                            </label>

                            <input
                                type="number"
                                id="cargaAcademica"
                                name="cargaAcademica"
                                value="20"
                                min="20"
                                max="20"
                                step="1"
                                readonly
                                required
                            >

                            <small class="ayuda-campo">
                                Ingresa un valor entero entre 0 y 20 horas.
                            </small>
                        </div>
                    </div>

                    <div class="acciones-formulario">
                        <button
                            type="button"
                            class="boton-secundario"
                            data-cerrar-formulario
                        >
                            Cancelar
                        </button>

                        <button type="submit" class="boton-principal">
                            Guardar docente
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endif

    @if (session('docente_agregado'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('modal-docente-agregado');

                if (modal) {
                    modal.hidden = false;
                    modal.setAttribute('aria-hidden', 'false');
                }
            });
        </script>
    @endif

   <!-- PANEL PARA CONSULTAR DOCENTES -->
    <section class="panel-docentes panel-consulta-docentes" id="panel-consulta">
        <div class="encabezado-panel">
            <div>
                <h2>Consultar docente</h2>
                <p class="texto-secundario">
                    Busca por nombre o apellido para consultar los datos.
                </p>
            </div>
        </div>

        <div class="consulta-grid">
            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta">Nombre del docente</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input
                            type="search"
                            id="buscar-consulta"
                            placeholder="Escribe un nombre para encontrar al docente..."
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta">
                        @forelse ($docentesRegistrados as $usuario)
                            @php
                                // Construye el nombre para mostrarlo en los resultados.
                                $nombreConsulta = trim(
                                    $usuario->nombreUsuario . ' ' .
                                    $usuario->apellidoPaterno . ' ' .
                                    ($usuario->apellidoMaterno ?? '')
                                );
                            @endphp

                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-usuario="{{ $usuario->id_usuario }}"
                                data-nombre-consulta="{{ mb_strtolower($nombreConsulta) }}"
                            >
                                <span class="texto-resultado">
                                    <strong>{{ $nombreConsulta }}</strong>
                                    <small>{{ $usuario->correo }}</small>
                                </span>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay docentes para consultar.
                            </p>
                        @endforelse

                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta" hidden>
                            No se encontraron docentes con ese nombre.
                        </p>
                    </div>
                </div>
            </div>

            <div class="tarjeta-detalle" id="tarjeta-detalle">
                <div class="detalle-vacio" id="detalle-vacio">
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <h3>Información del docente</h3>
                    <p class="texto-secundario">
                        Selecciona un usuario de los resultados para ver su información.
                    </p>
                </div>

                @foreach ($docentesRegistrados as $usuario)
                    @php
                        // Obtiene la información académica, si existe.
                        $docenteConsulta = $usuario->docente;

                        // Prepara el nombre completo para la tarjeta de detalle.
                        $nombreDetalle = trim(
                            $usuario->nombreUsuario . ' ' .
                            $usuario->apellidoPaterno . ' ' .
                            ($usuario->apellidoMaterno ?? '')
                        );

                        // Determina el tipo de docente registrado.
                        $tipoDetalle = $docenteConsulta
                            ? ((int) $docenteConsulta->tipoDocente === 1 ? 'PTC — Tiempo completo' : 'PA — Asignatura')
                            : '-';

                        // Verifica el estado actual de la cuenta.
                        $estadoDetalle = (int) $usuario->estado === 1 && !$usuario->trashed();
                    @endphp

                    <div
                        class="contenido-detalle-docente"
                        data-detalle-usuario="{{ $usuario->id_usuario }}"
                        hidden
                    >
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div>
                                <h3>{{ $nombreDetalle }}</h3>
                                <p class="correo-detalle">{{ $usuario->correo }}</p>
                            </div>
                        </div>

                        <div class="lista-detalle">
                            <div class="fila-detalle">
                                <span>Matrícula</span>
                                <strong>{{ $docenteConsulta?->matricula ?? '-' }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Especialidad</span>
                                <strong>{{ $docenteConsulta?->especialidad ?? '-' }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Tipo de docente</span>
                                <strong>{{ $tipoDetalle }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Carga académica</span>
                                <strong>
                                    {{ $docenteConsulta ? $docenteConsulta->cargaAcademica . ' horas' : '-' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Estado de la cuenta</span>
                                <strong class="{{ $estadoDetalle ? 'texto-estado-activo' : 'texto-estado-inactivo' }}">
                                    {{ $estadoDetalle ? 'Activo' : 'Inactivo' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</main>

<!-- CONFIGURACIÓN PARA EL ARCHIVO JAVASCRIPT -->
<script>
    window.rutaDocentes = @json(url('/docentes'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<script src="{{ asset('js/docentes/gestion-docentes.js') }}" defer></script>

<!-- MODAL DE DOCENTE AGREGADO -->
<div
    class="modal-confirmacion"
    id="modal-docente-agregado"
    hidden
    aria-hidden="true"
>
    <div class="modal-confirmacion-contenido">

        <!-- Círculo con palomita -->
        <div class="modal-confirmacion-icono">
            <i class="fa-solid fa-check"></i>
        </div>

        <!-- Mensaje -->
        <div class="modal-confirmacion-mensaje">
            <h3>Docente agregado</h3>
            <p>El docente se agregó correctamente.</p>
        </div>

        <!-- Botón aceptar -->
        <button
            type="button"
            class="boton-confirmar"
            id="boton-aceptar-docente"
        >
            Aceptar
        </button>

    </div>
</div>

<!-- Modal para confirmar la baja de un docente -->
<div class="modal-confirmacion" id="modal-baja-docente" hidden>
    <div
        class="modal-contenido"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-baja-docente">

        <button
            type="button"
            class="modal-cerrar"
            onclick="cerrarModalBajaDocente()"
            aria-label="Cerrar modal">
            &times;
        </button>

        <div class="modal-icono">
            <i class="fa-solid fa-user-minus"></i>
        </div>

        <h2 id="titulo-modal-baja-docente">
            ¿Dar de baja a este docente?
        </h2>

        <p class="modal-mensaje">
            Estás a punto de dar de baja a:
        </p>

        <strong
            id="nombre-docente-baja"
            class="modal-destacado">
        </strong>

        <p class="modal-nota">
            El docente quedará inactivo y ya no tendrá acceso a los módulos del sistema.
        </p>

        <div class="modal-acciones">
            <button
                type="button"
                class="boton-modal-cancelar"
                onclick="cerrarModalBajaDocente()">
                Cancelar
            </button>

            <button
                type="button"
                class="boton-modal-confirmar"
                onclick="ejecutarBajaDocente()">
                <i class="fa-solid fa-trash"></i>
                Sí, dar de baja
            </button>
        </div>
    </div>
</div>

@endsection