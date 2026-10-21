@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de grupos | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Grupos/gestion-grupos.css') }}">
@endpush

@section('contenido')

<main class="pagina-grupos">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo">Inicio / Administración / Grupos</p>
            <h1>Gestión de grupos</h1>
            <p class="descripcion-modulo">
                Administra la información de los grupos académicos de la universidad.
            </p>
        </div>
    </header>

    <!-- MENSAJES DEL SISTEMA -->
    @if (session('error'))
        <div class="alerta-grupos alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- PANEL DE FILTROS -->
    <section class="panel-grupos panel-filtros" id="contenedor-filtros" hidden>
        <div class="encabezado-panel">
            <div>
                <h2>Filtrar grupos</h2>
                <p class="texto-secundario">
                    Selecciona la carrera y el periodo académico que deseas consultar.
                </p>
            </div>

            <button type="button" class="boton-secundario" id="limpiar-filtros">
                Limpiar filtros
            </button>
        </div>

        <div class="filtros-grupos">
            <!-- Filtro por carrera -->
            <div class="campo">
                <label for="filtro-carrera">Carrera</label>

                <select id="filtro-carrera" name="filtro_carrera">
                    <option value="">Todas las carreras</option>

                    @foreach ($carrerasRegistradas as $carrera)
                        <option value="{{ $carrera->id_carrera }}">
                            {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por periodo académico -->
            <div class="campo">
                <label for="filtro-periodo">Periodo académico</label>

                <select id="filtro-periodo" name="filtro_periodo">
                    <option value="">Todos los periodos</option>

                    @foreach ($periodosRegistrados as $periodo)
                        <option value="{{ $periodo->id_periodo }}">
                            {{ $periodo->estacion }} {{ $periodo->anio }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <!-- TABLA DE GRUPOS -->
    <section class="panel-grupos">
        <div class="encabezado-panel encabezado-tabla">

            <div class="titulo-tabla">
                <h2>Grupos registrados</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $gruposRegistrados->count() }}</span>
                    grupo(s) encontrado(s)
                </p>
            </div>

            <div class="botones-encabezado">
                <button type="button" class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros" >
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <button type="button" class="boton-principal" id="boton-nuevo-grupo">
                        <span aria-hidden="true">＋</span>
                        Nuevo grupo
                    </button>
                @endif
            </div>

            <div class="busqueda-tabla campo">
                <label for="buscar-grupo">Buscar grupo</label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input type="search" id="buscar-grupo" placeholder="Nombre, carrera o cuatrimestre..." autocomplete="off">
                </div>
            </div>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-grupos">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Grupo</th>
                        <th scope="col">Carrera</th>
                        <th scope="col">Cuatrimestre</th>
                        <th scope="col">Periodo</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <tbody id="cuerpo-tabla-grupos">
                    @forelse ($gruposRegistrados as $grupo)
                        <tr
                            data-id-grupo="{{ $grupo->id_grupo }}"
                            data-nombre="{{ mb_strtolower($grupo->nombreGrupo) }}"
                            data-id-carrera="{{ $grupo->id_carrera }}"
                            data-clave-carrera="{{ mb_strtolower($grupo->carrera->claveCarrera ?? '') }}"
                            data-nombre-carrera="{{ mb_strtolower($grupo->carrera->nombreCarrera ?? '') }}"
                            data-cuatrimestre="{{ $grupo->cuatrimestre }}"
                            data-id-periodo="{{ $grupo->id_periodo }}"
                            data-estacion="{{ mb_strtolower($grupo->periodo->estacion ?? '') }}"
                            data-anio="{{ $grupo->periodo->anio ?? '' }}"
                            data-estado="{{ $grupo->estado }}"
                        >
                            <td>{{ $grupo->id_grupo }}</td>

                            <td>
                                <div class="grupo-tabla">
                                    <strong>{{ $grupo->nombreGrupo }}</strong>
                                    <span>Grupo académico</span>
                                </div>
                            </td>

                            <td>
                                <div class="grupo-tabla">
                                    <strong> {{ $grupo->carrera->claveCarrera ?? 'Sin carrera' }} </strong>
                                    <span> {{ $grupo->carrera->nombreCarrera ?? 'No disponible' }} </span>
                                </div>
                            </td>

                            <td>{{ $grupo->cuatrimestre }}.°</td>

                            <td>
                                <span class="periodo-grupo">
                                    {{ $grupo->periodo->estacion ?? 'Sin periodo' }}
                                    {{ $grupo->periodo->anio ?? '' }}
                                </span>
                            </td>

                            <!-- Estado del grupo -->
                            <td>
                                @if ((int) $grupo->estado === 1)
                                    <span class="estado-grupo estado-activo">Activo</span>
                                @else
                                    <span class="estado-grupo estado-inactivo">Inactivo</span>
                                @endif
                            </td>

                            <td>
                                <div class="acciones-tabla">
                                    <button type="button" class="boton-accion boton-consultar" data-consultar-grupo="{{ $grupo->id_grupo }}"
                                        aria-label="Consultar {{ $grupo->nombreGrupo }}" >
                                        Consultar
                                    </button>

                                    @if (in_array((int) auth()->user()->rol, [1, 2], true))
                                        <a href="{{ route('grupos.edit', ['idGrupo' => $grupo->id_grupo]) }}" class="boton-accion boton-modificar"
                                            aria-label="Modificar {{ $grupo->nombreGrupo }}" >
                                            Modificar
                                        </a>
                                    @endif

                                    @if ((int) $grupo->estado === 1)
                                        @if ((int) auth()->user()->rol === 1)
                                            <button type="button" class="boton-accion boton-eliminar" data-baja-grupo="{{ $grupo->id_grupo }}"
                                                data-nombre-grupo="{{ $grupo->nombreGrupo }}" >
                                                Eliminar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="fila-sin-grupos">
                            <td colspan="7" class="tabla-vacia">
                                No hay grupos registrados.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="fila-sin-resultados" hidden>
                        <td colspan="7" class="tabla-vacia">
                            No hay grupos que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-grupos">
            <span id="informacion-pagina">Página 1 de 1</span>

            <div class="controles-paginacion">
                <button type="button" class="boton-pagina" id="pagina-anterior" aria-label="Página anterior" disabled >
                    ‹
                </button>

                <button type="button" class="boton-pagina" id="pagina-siguiente" aria-label="Página siguiente" disabled>
                    ›
                </button>
            </div>
        </div>
    </section>

    <!-- FORMULARIO PARA REGISTRAR GRUPOS -->
    @if (in_array((int) auth()->user()->rol, [1, 2], true))
        <section class="panel-grupos panel-formulario-grupo" id="formulario-grupo">
            <div class="franja-institucional"></div>

            <div class="contenido-formulario">
                <div class="encabezado-panel encabezado-formulario">
                    <div class="icono-formulario" aria-hidden="true">＋</div>

                    <div>
                        <h2>Nuevo grupo</h2>
                        <p class="texto-secundario">
                            Completa los datos académicos para registrar un grupo.
                        </p>
                    </div>
                </div>

                <form action="{{ route('grupos.store') }}" method="POST" id="form-agregar-grupo">
                    @csrf

                    <div class="formulario-grid">

                        <!-- Carrera del grupo -->
                        <div class="campo">
                            <label for="id_carrera">
                                <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                                Carrera
                                <span class="campo-requerido">*</span>
                            </label>

                            <select id="id_carrera" name="id_carrera" required>
                                <option value="">Selecciona una carrera</option>

                                @foreach ($carrerasRegistradas as $carrera)
                                    @if ((int) $carrera->estado === 1)
                                        <option value="{{ $carrera->id_carrera }}" data-clave="{{ $carrera->claveCarrera }}"
                                            @selected(old('id_carrera') == $carrera->id_carrera) >
                                            {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Cuatrimestre del grupo -->
                        <div class="campo">
                            <label for="cuatrimestre">
                                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                Cuatrimestre
                                <span class="campo-requerido">*</span>
                            </label>

                            <select id="cuatrimestre" name="cuatrimestre" required>
                                <option value="">Selecciona un cuatrimestre</option>

                                @for ($numero = 1; $numero <= 10; $numero++)
                                    <option value="{{ $numero }}" @selected(old('cuatrimestre') == $numero) >
                                        {{ $numero }}.°
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- Letra del grupo -->
                        <div class="campo">
                            <label for="letraGrupo">
                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                Letra del grupo
                                <span class="campo-requerido">*</span>
                            </label>

                            <select id="letraGrupo" name="letraGrupo" required>
                                <option value="">Selecciona una letra</option>

                                @foreach (range('A', 'L') as $letra)
                                    <option value="{{ $letra }}" @selected(old('letraGrupo') === $letra) >
                                        {{ $letra }}
                                    </option>
                                @endforeach
                            </select>

                            <small class="texto-secundario">
                                El nombre se generará automáticamente, por ejemplo: ITI-8A.
                            </small>
                        </div>

                        <!-- Periodo académico -->
                        <div class="campo">
                            <label for="id_periodo">
                                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                                Periodo académico
                                <span class="campo-requerido">*</span>
                            </label>

                            <select id="id_periodo" name="id_periodo" required>
                                <option value="">Selecciona un periodo</option>

                                @foreach ($periodosRegistrados as $periodo)
                                    <option value="{{ $periodo->id_periodo }}" @selected(old('id_periodo') == $periodo->id_periodo) >
                                        {{ $periodo->estacion }} {{ $periodo->anio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="acciones-formulario">
                        <button type="submit" class="boton-principal">
                            Registrar grupo
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endif

    <!-- PANEL PARA CONSULTAR GRUPOS -->
    <section class="panel-grupos panel-consulta-grupos" id="panel-consulta" >
        <div class="encabezado-panel">
            <div>
                <h2>Consultar grupo</h2>
                <p class="texto-secundario">
                    Busca por nombre o carrera para consultar los datos registrados.
                </p>
            </div>
        </div>

        <div class="consulta-grid">
            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta-grupo">Nombre del grupo o carrera</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input type="search" id="buscar-consulta-grupo" placeholder="Escribe el nombre del grupo o carrera..." autocomplete="off" >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3>Resultados</h3>

                    <div id="resultados-consulta-grupo" aria-live="polite">
                        @forelse ($gruposRegistrados as $grupo)
                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-grupo="{{ $grupo->id_grupo }}"
                                data-nombre-consulta="{{ mb_strtolower($grupo->nombreGrupo) }}"
                                data-clave-consulta="{{ mb_strtolower($grupo->carrera->claveCarrera ?? '') }}"
                                hidden
                            >
                                <span class="texto-resultado">
                                    <strong>{{ $grupo->nombreGrupo }}</strong>
                                    <small>
                                        {{ $grupo->carrera->claveCarrera ?? 'Sin carrera' }}
                                        · {{ $grupo->periodo->estacion ?? '' }}
                                        {{ $grupo->periodo->anio ?? '' }}
                                    </small>
                                </span>
                            </button>
                        @empty
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay grupos para consultar.
                            </p>
                        @endforelse

                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta-grupo" hidden >
                            No se encontraron grupos con ese nombre o carrera.
                        </p>
                    </div>
                </div>
            </div>

            <div class="tarjeta-detalle" id="tarjeta-detalle-grupo">
                <div class="detalle-vacio" id="detalle-vacio-grupo">
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <h3>Información del grupo</h3>
                    <p class="texto-secundario">
                        Selecciona un grupo de los resultados para ver su información.
                    </p>
                </div>

                @foreach ($gruposRegistrados as $grupo)
                    <div class="contenido-detalle-grupo" data-detalle-grupo="{{ $grupo->id_grupo }}" hidden >
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true">
                                <i class="fa-solid fa-users"></i>
                            </div>

                            <div>
                                <h3>{{ $grupo->nombreGrupo }}</h3>
                                <p class="texto-secundario">
                                    {{ $grupo->carrera->nombreCarrera ?? 'Sin carrera asociada' }}
                                </p>
                            </div>
                        </div>

                        <div class="lista-detalle">
                            <div class="fila-detalle">
                                <span>ID de grupo</span>
                                <strong>{{ $grupo->id_grupo }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Nombre del grupo</span>
                                <strong>{{ $grupo->nombreGrupo }}</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Clave de carrera</span>
                                <strong>
                                    {{ $grupo->carrera->claveCarrera ?? 'No disponible' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Nombre de la carrera</span>
                                <strong>
                                    {{ $grupo->carrera->nombreCarrera ?? 'No disponible' }}
                                </strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Cuatrimestre</span>
                                <strong>{{ $grupo->cuatrimestre }}.°</strong>
                            </div>

                            <div class="fila-detalle">
                                <span>Periodo académico</span>
                                <strong>
                                    {{ $grupo->periodo->estacion ?? 'No disponible' }}
                                    {{ $grupo->periodo->anio ?? '' }}
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
    window.rutaGrupos = @json(url('/grupos'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<script src="{{ asset('js/Grupos/gestion-grupos.js') }}" defer></script>

<!-- MODAL PARA CONFIRMAR LA BAJA DE UN GRUPO -->
@if ((int) auth()->user()->rol === 1)
    <div class="modal-confirmacion" id="modal-baja-grupo" hidden>
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-baja-grupo" >
            <button type="button" class="modal-cerrar" data-cerrar-baja-grupo aria-label="Cerrar modal" >
                &times;
            </button>

            <div class="modal-icono modal-icono-error">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-baja-grupo">¿Dar de baja este grupo?</h2>

            <p class="modal-mensaje">
                Estás a punto de dar de baja a:
            </p>

            <strong id="nombre-grupo-baja" class="modal-destacado"></strong>

            <p class="modal-nota">
                El grupo dejará de estar disponible en el sistema.
            </p>

            <form id="formulario-baja-grupo" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-acciones">
                    <button type="button" class="boton-modal-cancelar" data-cerrar-baja-grupo >
                        Cancelar
                    </button>

                    <button type="submit" class="boton-modal-confirmar boton-modal-error">
                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                        Sí, dar de baja
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- MODAL DE REGISTRO EXITOSO -->
@if (session('tipo_modal_grupo') === 'registro' && session('mensaje_modal_grupo'))
    <div class="modal-confirmacion" id="modal-registro-grupo">
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-registro-grupo" >
            <button type="button" class="modal-cerrar" id="cerrar-modal-registro-grupo" aria-label="Cerrar modal" >
                &times;
            </button>

            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-registro-grupo">¡Grupo registrado!</h2>

            <p class="modal-mensaje">{{ session('mensaje_modal_grupo') }}</p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-exito" id="aceptar-modal-registro-grupo" >
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

<!-- MODAL DE ERROR -->
@if (session('tipo_modal_grupo') === 'error' && session('mensaje_modal_grupo'))
    <div class="modal-confirmacion" id="modal-error-grupo">
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-error-grupo" >
            <button type="button" class="modal-cerrar" id="cerrar-modal-error-grupo" aria-label="Cerrar modal" >
                &times;
            </button>

            <div class="modal-icono modal-icono-error">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-error-grupo">¡No se pudo registrar!</h2>

            <p class="modal-mensaje">{{ session('mensaje_modal_grupo') }}</p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-error" id="aceptar-modal-error-grupo" >
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

<!-- MODAL DE MODIFICACIÓN EXITOSA -->
@if (session('tipo_modal_grupo') === 'modificacion' && session('mensaje_modal_grupo'))
    <div class="modal-confirmacion" id="modal-modificar-grupo">
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-modificacion-grupo" >
            <button type="button" class="modal-cerrar" id="cerrar-modal-modificar-grupo" aria-label="Cerrar modal" >
                &times;
            </button>

            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </div>

            <h2 id="titulo-modal-modificacion-grupo">¡Cambios guardados!</h2>

            <p class="modal-mensaje">{{ session('mensaje_modal_grupo') }}</p>

            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar boton-modal-exito" id="aceptar-modal-modificar-grupo" >
                    <i class="fa-solid fa-check" aria-hidden="true"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

@endsection
