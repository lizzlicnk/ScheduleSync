@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de carreras | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Carreras/gestion-carreras.css') }}">
@endpush

@section('contenido')
<main class="pagina-carreras">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">Inicio / Administración / Carreras</p>
            <h1>Gestión de carreras</h1>
            <p class="descripcion-modulo">
                Administra la información de las carreras pertenecientes a las ingenierías de la universidad.
            </p>
        </div>
    </header>

    <!-- MENSAJES DEL SISTEMA -->
    @if (session('error'))
        <div class="alerta-carreras alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <section class="panel-carreras panel-filtros" id="contenedor-filtros" hidden>

        <div class="encabezado-panel">
            <div>
                <h2>Filtrar carreras</h2>
                <p class="texto-secundario">
                    Selecciona el estado de las carreras que deseas consultar.
                </p>
            </div>

            <button type="button" class="boton-secundario"  id="limpiar-filtros">
                Limpiar filtros
            </button>
        </div>

        <div class="filtros-carreras">
            <div class="campo">
                <label for="filtro-estado">Estado de la carrera</label>

                <select id="filtro-estado" name="filtro_estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
        </div>
    </section>

    <!-- TABLA DE CARRERAS -->
    <section class="panel-carreras">
        <div class="encabezado-panel encabezado-tabla">
            <div class="titulo-tabla">
                <h2>Carreras registradas</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $carrerasRegistradas->count() }}</span>
                    carrera(s) encontrada(s)
                </p>
            </div>

            <div class="botones-encabezado">
                <button type="button"class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros">
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

                <button type="button" class="boton-principal" id="boton-nueva-carrera">
                    <span aria-hidden="true">＋</span>
                    Nueva carrera
                </button>
            </div>

            <div class="busqueda-tabla campo">
                <label for="buscar-carrera">Buscar carrera</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input
                        type="search"
                        id="buscar-carrera"
                        placeholder="Clave o nombre de la carrera..."
                        autocomplete="off"
                    >
                </div>
            </div>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-carreras">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Clave</th>
                        <th scope="col">Nombre de la carrera</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-carreras">
                    @forelse ($carrerasRegistradas as $carrera)
                        <tr
                            data-id-carrera="{{ $carrera->id_carrera }}"
                            data-clave="{{ mb_strtolower($carrera->claveCarrera) }}"
                            data-nombre="{{ mb_strtolower($carrera->nombreCarrera) }}"
                            data-estado="{{ (int) $carrera->estado }}"
                        >
                            <td>{{ $carrera->id_carrera }}</td>

                            <td>
                                <span class="clave-carrera">
                                    {{ $carrera->claveCarrera }}
                                </span>
                            </td>

                            <td>
                                <div class="informacion-carrera">
                                    <strong>{{ $carrera->nombreCarrera }}</strong>
                                </div>
                            </td>

                            <td>
                                <span class="estado-usuario {{ (int) $carrera->estado === 1 ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ (int) $carrera->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td>
                                <div class="acciones-tabla">
                                    <button type="button" class="boton-accion boton-consultar" data-consultar-carrera="{{ $carrera->id_carrera }}" aria-label="Consultar {{ $carrera->nombreCarrera }}" >
                                        Consultar
                                    </button>

                                    <a href="{{ route('carreras.edit', ['idCarrera' => $carrera->id_carrera]) }}" class="boton-accion boton-modificar" aria-label="Modificar {{ $carrera->nombreCarrera }}" >
                                        Modificar
                                    </a>

                                    @if ((int) $carrera->estado === 1)
                                        @if ((int) auth()->user()->rol === 1)
                                            <button type="button" class="boton-accion boton-eliminar" data-baja-carrera="{{ $carrera->id_carrera }}" data-nombre-carrera="{{ $carrera->nombreCarrera }}" >
                                                Eliminar
                                            </button>
                                        @endif
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="fila-sin-carreras">
                            <td colspan="5" class="tabla-vacia">
                                No hay carreras registradas.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="fila-sin-resultados" hidden>
                        <td colspan="5" class="tabla-vacia">
                            No hay carreras que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-carreras">
            <span id="informacion-pagina">Página 1 de 1</span>

            <div class="controles-paginacion">
                <button type="button" class="boton-pagina" id="pagina-anterior" aria-label="Página anterior" disabled >
                    ‹
                </button>

                <button type="button" class="boton-pagina" id="pagina-siguiente" aria-label="Página siguiente" disabled >
                    ›
                </button>
            </div>
        </div>
    </section>

    <!-- FORMULARIO PARA REGISTRAR CARRERAS -->
    @if ((int) auth()->user()->rol === 1)
    <section class="panel-carreras panel-formulario-carrera" id="formulario-carrera">
            <div class="franja-institucional"></div>

        <div class="contenido-formulario">
            <div class="encabezado-panel encabezado-formulario">
                <div class="icono-formulario" aria-hidden="true">＋</div>

                <div>
                    <h2>Nueva carrera</h2>
                    <p class="texto-secundario">
                        Completa los datos para registrar una nueva carrera.
                    </p>
                </div>
            </div>

            <form action="{{ route('carreras.store') }}" method="POST" id="form-agregar-carrera">
                @csrf

            <div class="formulario-grid">

                <!-- Nombre de la carrera -->
                <div class="campo campo-nombre-carrera">
                    <label for="nombreCarrera">
                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        Nombre de la carrera
                        <span class="campo-requerido">*</span>
                    </label>

                    <input type="text" id="nombreCarrera" name="nombreCarrera" value="{{ old('nombreCarrera') }}"
                        placeholder="Ej. Ingeniería en Tecnologías de la Información" maxlength="100" required >
                </div>

                <!-- Clave de carrera -->
                <div class="campo">
                    <label for="claveCarrera">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                        Clave de carrera
                        <span class="campo-requerido">*</span>
                    </label>

                    <input type="text" id="claveCarrera" name="claveCarrera" value="{{ old('claveCarrera') }}" placeholder="Ej. ITI" maxlength="45" required >
                </div>

                <!-- Estado de la carrera -->
                <div class="campo">
                    <label for="estado">
                        <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                        Estado de la carrera
                        <span class="campo-requerido">*</span>
                    </label>

                    <select id="estado" name="estado" required>
                        <option value="1" {{ old('estado', '1') == '1' ? 'selected' : '' }}>
                            Activa
                        </option>

                        <option value="0" {{ old('estado') === '0' ? 'selected' : '' }}>
                            Inactiva
                        </option>
                    </select>
                </div>

            </div>

                <div class="acciones-formulario">
                    <button type="submit" class="boton-principal">
                        Registrar carrera
                    </button>
                </div>
            </form>
        </div>
    </section>
    @endif

    <!-- PANEL PARA CONSULTAR CARRERAS -->
    <section class="panel-carreras panel-consulta-carreras" id="panel-consulta">
        <div class="encabezado-panel">
            <div>
                <h2>Consultar carrera</h2>
                <p class="texto-secundario">
                    Busca por clave o nombre para consultar los datos registrados.
                </p>
            </div>
        </div>

        <div class="consulta-grid">
            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta-carrera">Clave o nombre de la carrera</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            type="search"
                            id="buscar-consulta-carrera"
                            placeholder="Escribe una clave o nombre..."
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta-carrera" aria-live="polite">
                        @forelse ($carrerasRegistradas as $carrera)
                            <!--Letras minusculas mb_strtolower-->
                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-carrera="{{ $carrera->id_carrera }}"
                                data-clave-consulta="{{ mb_strtolower($carrera->claveCarrera) }}"
                                data-nombre-consulta="{{ mb_strtolower($carrera->nombreCarrera) }}"
                            >
                                <span class="texto-resultado">
                                    <strong>{{ $carrera->nombreCarrera }}</strong>
                                    <small>{{ $carrera->claveCarrera }}</small>
                                </span>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay carreras para consultar.
                            </p>
                        @endforelse

                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta-carrera" hidden >
                            No se encontraron carreras con esa clave o nombre.
                        </p>
                    </div>
                </div>
            </div>

            <div class="tarjeta-detalle" id="tarjeta-detalle-carrera">
                <div class="detalle-vacio" id="detalle-vacio-carrera">
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>

                    <h3>Información de la carrera</h3>
                    <p class="texto-secundario">
                        Selecciona una carrera de los resultados para ver su información.
                    </p>
                </div>

                @foreach ($carrerasRegistradas as $carrera)
                    <div class="contenido-detalle-carrera" data-detalle-carrera="{{ $carrera->id_carrera }}" hidden >
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>

                            <div>
                                <h3>{{ $carrera->nombreCarrera }}</h3>
                                <p class="texto-secundario">
                                    Clave: {{ $carrera->claveCarrera }}
                                </p>
                            </div>
                        </div>

                        <div class="lista-detalle">
                            <div class="fila-detalle">
                                <span>ID de carrera</span>
                                <strong>{{ $carrera->id_carrera }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Clave</span>
                                <strong>{{ $carrera->claveCarrera }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Nombre de la carrera</span>
                                <strong>{{ $carrera->nombreCarrera }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Estado</span>
                                <strong class="{{ (int) $carrera->estado === 1 ? 'texto-estado-activo' : 'texto-estado-inactivo' }}">
                                    {{ (int) $carrera->estado === 1 ? 'Activo' : 'Inactivo' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</main>

<!-- CONFIGURACIÓN PARA JAVASCRIPT -->
<script>
    window.rutaCarreras = @json(url('/carreras'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<script src="{{ asset('js/carreras/gestion-carreras.js') }}" defer></script>


<!-- Modal para confirmar la baja de una carrera -->
<div class="modal-confirmacion" id="modal-baja-carrera" hidden>
    <div class="modal-contenido" role="dialog" aria-modal="true"  aria-labelledby="titulo-modal-baja-carrera">

        <button type="button" class="modal-cerrar" onclick="cerrarModalBajaCarrera()" aria-label="Cerrar modal">
            &times;
        </button>

        <div class="modal-icono">
            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
        </div>

        <h2 id="titulo-modal-baja-carrera"> ¿Dar de baja esta carrera? </h2>

        <p class="modal-mensaje"> Estás a punto de dar de baja a: </p>

        <strong id="nombre-carrera-baja" class="modal-destacado"></strong>

        <p class="modal-nota"> La carrera quedará inactiva y su información se conservará en el sistema. </p>

        <form id="formulario-baja-carrera" method="POST">
            @csrf
            @method('DELETE')

            <div class="modal-acciones">
                <button type="button" class="boton-modal-cancelar" onclick="cerrarModalBajaCarrera()" >
                    Cancelar
                </button>

                <button type="submit" class="boton-modal-confirmar">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                    Sí, dar de baja
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de registro exitoso de carrera -->
@if (session('tipo_modal_carrera') === 'registro' && session('mensaje_modal_carrera'))
    <div class="modal-confirmacion" id="modal-registro-carrera">

        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-registro-carrera">

            <button type="button" class="modal-cerrar" id="cerrar-modal-registro-carrera" aria-label="Cerrar modal">
                &times;
            </button>

            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-registro-carrera"> ¡Carrera registrada! </h2>

            <p class="modal-mensaje"> {{ session('mensaje_modal_carrera') }} </p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-exito" id="aceptar-modal-registro-carrera">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Modal de error al registrar una carrera -->
@if (session('tipo_modal_carrera') === 'error' && session('mensaje_modal_carrera'))
    <div class="modal-confirmacion" id="modal-error-carrera">

        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-error-carrera">

            <button type="button" class="modal-cerrar" id="cerrar-modal-error-carrera" aria-label="Cerrar modal">
                &times;
            </button>

            <div class="modal-icono modal-icono-error">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-error-carrera"> ¡No se pudo registrar! </h2>

            <p class="modal-mensaje"> {{ session('mensaje_modal_carrera') }} </p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-error" id="aceptar-modal-error-carrera">
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>

        </div>
    </div>
@endif

<!-- Modal de modificación exitosa de carrera -->
@if (session('tipo_modal_carrera') === 'modificacion' && session('mensaje_modal_carrera'))
    <div class="modal-confirmacion" id="modal-modificar-carrera">

        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-modificacion-carrera">
            <button type="button" class="modal-cerrar" id="cerrar-modal-modificar-carrera" aria-label="Cerrar modal">
                &times;
            </button>

            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-modificacion-carrera"> ¡Cambios guardados!</h2>
            <p class="modal-mensaje"> {{ session('mensaje_modal_carrera') }} </p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-exito" id="aceptar-modal-modificar-carrera" >
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>

        </div>
    </div>
@endif

@endsection
