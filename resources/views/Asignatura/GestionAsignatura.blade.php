@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de asignaturas | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Asignaturas/gestion-asignaturas.css') }}">
@endpush

@section('contenido')
<main class="pagina-asignaturas">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <p class="ruta-modulo"> Inicio / Administración / Asignaturas</p>
            <h1>Gestión de asignaturas</h1>
            <p class="descripcion-modulo"> Administra las asignaturas correspondientes a las carreras de la universidad. </p>
        </div>
    </header>

    <!-- MENSAJES DEL SISTEMA -->
    @if (session('error'))
        <div class="alerta-asignaturas alerta-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span> {{ session('error') }} </span>
        </div>
    @endif
    
    <!-- PANEL DE FILTROS -->
    <section class="panel-filtros" id="contenedor-filtros" hidden>

        <!-- ENCABEZADO Y ACCIONES DE LOS FILTROS -->
        <div class="encabezado-panel encabezado-filtros">
            <div>
                <h2>Filtrar asignaturas</h2>
                <p class="texto-secundario">
                    Selecciona el estado, la carrera o el cuatrimestre
                    de las asignaturas que deseas consultar.
                </p>
            </div>

            <!-- BOTÓN PARA LIMPIAR LOS FILTROS -->
            <button type="button" class="boton-secundario" id="limpiar-filtros" >
                Limpiar filtros
            </button>
        </div>

        <div class="campos-filtros">
            <!-- FILTRO POR CARRERA -->
            <div class="campo">
                <label for="filtro-carrera">Carrera</label>
                <select id="filtro-carrera" name="filtro_carrera">
                    <option value="">Todas las carreras</option>

                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}">
                            {{ $carrera->nombreCarrera }} - {{ $carrera->claveCarrera }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- FILTRO POR CUATRIMESTRE -->
            <div class="campo">
                <label for="filtro-cuatrimestre">Cuatrimestre</label>

                <select id="filtro-cuatrimestre" name="filtro_cuatrimestre">
                    <option value="">Todos los cuatrimestres</option>

                    @for ($cuatrimestre = 1; $cuatrimestre <= 12; $cuatrimestre++)
                        <option value="{{ $cuatrimestre }}">
                            {{ $cuatrimestre }}.°
                        </option>
                    @endfor
                </select>
            </div>

            <!-- FILTRO POR ESTADO -->
            <div class="campo">
                <label for="filtro-estado">Estado de la asignatura</label>

                <select id="filtro-estado" name="filtro_estado">
                    <option value="">Todos los estados</option>
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </div>

        </div>
    </section>
 
    <!-- TABLA DE ASIGNATURAS -->
    <section class="panel-asignaturas">
        <div class="encabezado-panel encabezado-tabla">
            <div class="titulo-tabla">
                <h2>Asignaturas registradas</h2>

                <p class="texto-secundario">
                    <span id="total-resultados">
                        {{ $asignaturas->count() }}
                    </span>
                    asignatura(s) encontrada(s)
                </p>
            </div>

            <!-- BOTONES DE ACCIONES -->
            <div class="botones-encabezado">

                <!-- BOTÓN PARA MOSTRAR LOS FILTROS -->
                <button type="button" class="boton-secundario" id="boton-filtros" aria-expanded="false" aria-controls="contenedor-filtros">
                    <span aria-hidden="true">☷</span>
                    Filtros
                </button>

                <!-- BOTÓN PARA MOSTRAR EL FORMULARIO DE REGISTRO -->
                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <button type="button" class="boton-principal" id="boton-nueva-asignatura" >
                        <span aria-hidden="true">＋</span>
                        Nueva asignatura
                    </button>
                @endif
            </div>

            <!-- BÚSQUEDA DE ASIGNATURAS -->
            <div class="busqueda-tabla campo">
                <label for="buscar-asignatura"> Buscar asignatura </label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true"> <i class="fa-solid fa-magnifying-glass"></i> </span>
                    <input type="search" id="buscar-asignatura" placeholder="Clave o nombre de la asignatura..." autocomplete="off" >
                </div>
            </div>
        </div>

        <!-- CONTENEDOR DE LA TABLA -->
        <div class="tabla-contenedor">
            <table class="tabla-asignaturas">

                <thead>
                    <tr>
                        <th scope="col">Clave de la asignatura</th>
                        <th scope="col">Nombre de la asignatura</th>
                        <th scope="col">Carrera</th>
                        <th scope="col">Cuatrimestre</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <!-- CUERPO DE LA TABLA -->
                <tbody id="cuerpo-tabla-asignaturas">
                    @forelse ($asignaturas as $asignatura)
                        <tr
                            data-id-asignatura="{{ $asignatura->id_asignatura }}"
                            data-clave="{{ mb_strtolower($asignatura->clave) }}"
                            data-nombre="{{ mb_strtolower($asignatura->nombreAsignatura) }}"
                            data-carrera="{{ $asignatura->id_carrera }}"
                            data-cuatrimestre="{{ $asignatura->cuatrimestre }}"
                            data-estado="{{ (int) $asignatura->estado }}"
                        >

                            <!-- CLAVE DE LA ASIGNATURA -->
                            <td>
                                <span class="clave-asignatura"> {{ $asignatura->clave }} </span>
                            </td>

                            <!-- NOMBRE DE LA ASIGNATURA -->
                            <td>
                                <div class="asignatura-tabla">
                                    <strong> {{ $asignatura->nombreAsignatura }} </strong>
                                </div>
                            </td>

                            <!-- CLAVE DE LA CARRERA -->
                            <td>
                                {{ $asignatura->carrera?->claveCarrera ?? 'Sin carrera asignada' }}
                            </td>

                            <!-- CUATRIMESTRE -->
                            <td>
                                {{ $asignatura->cuatrimestre }}.°
                            </td>

                            <!-- ESTADO DE LA ASIGNATURA -->
                            <td>
                                <span class="estado-usuario {{ (int) $asignatura->estado === 1 ? 'estado-activo' : 'estado-inactivo' }}">
                                    {{ (int) $asignatura->estado === 1 ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>

                            <!-- ACCIONES DISPONIBLES -->
                            <td>
                                <div class="acciones-tabla">

                                    <!-- CONSULTAR ASIGNATURA -->
                                    <button type="button" class="boton-accion boton-consultar" data-consultar-asignatura="{{ $asignatura->id_asignatura }}"
                                        aria-label="Consultar {{ $asignatura->nombreAsignatura }}" >
                                        Consultar
                                    </button>

                                    @if (in_array((int) auth()->user()->rol, [1, 2], true))
                                        <!-- MODIFICAR ASIGNATURA -->
                                        <a href="{{ route('asignaturas.edit', ['idAsignatura' => $asignatura->id_asignatura]) }}"
                                            class="boton-accion boton-modificar" aria-label="Modificar {{ $asignatura->nombreAsignatura }}" >
                                            Modificar
                                        </a>

                                        <!-- DAR DE BAJA -->
                                        @if ((int) auth()->user()->rol === 1 && (int) $asignatura->estado === 1)
                                            <button type="button" class="boton-accion boton-eliminar" data-baja-asignatura="{{ $asignatura->id_asignatura }}"
                                                data-nombre-asignatura="{{ $asignatura->nombreAsignatura }}" >
                                                Eliminar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- MENSAJE CUANDO NO EXISTEN ASIGNATURAS -->
                        <tr id="fila-sin-asignaturas">
                            <td colspan="6" class="tabla-vacia">
                                <i class="fa-solid fa-book" aria-hidden="true"></i>
                                <strong> No hay asignaturas registradas. </strong>
                                <span> Cuando registres una asignatura aparecerá en esta tabla. </span>
                            </td>
                        </tr>

                    @endforelse

                    <!-- MENSAJE CUANDO NO HAY COINCIDENCIAS -->
                    <tr id="fila-sin-resultados" hidden>
                        <td colspan="6" class="tabla-vacia">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <strong> No se encontraron asignaturas. </strong>
                            <span> No hay asignaturas que coincidan con la búsqueda o los filtros seleccionados. </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-asignaturas">
            <span id="informacion-pagina"> Página 1 de 1 </span>
            <div class="controles-paginacion">
                <!-- BOTÓN PARA IR A LA PÁGINA ANTERIOR -->
                <button type="button" class="boton-pagina" id="pagina-anterior" aria-label="Página anterior" disabled >
                    ‹
                </button>

                <!-- BOTÓN PARA IR A LA PÁGINA SIGUIENTE -->
                <button type="button" class="boton-pagina" id="pagina-siguiente" aria-label="Página siguiente" disabled >
                    ›
                </button>
            </div>
        </div>
    </section>

    <!-- FORMULARIO PARA REGISTRAR ASIGNATURAS -->
    @if (in_array((int) auth()->user()->rol, [1, 2], true))
        <section class="panel-asignaturas"  id="formulario-asignatura" >
            <div class="franja-institucional"></div>
            <div class="contenido-formulario">
                <!-- ENCABEZADO DEL FORMULARIO -->
                <div class="encabezado-panel encabezado-formulario">
                    <div class="icono-formulario" aria-hidden="true" >
                        <i class="fa-solid fa-plus"></i>
                    </div>

                    <div>
                        <h2>Nueva asignatura</h2>

                        <p class="texto-secundario">
                            Completa los datos para registrar una nueva asignatura.
                        </p>
                    </div>
                </div>

                <!-- FORMULARIO DE REGISTRO -->
                <form action="{{ route('asignaturas.store') }}" method="POST" id="form-agregar-asignatura" >
                    @csrf

                    <div class="formulario-grid">
                        <!-- NOMBRE DE LA ASIGNATURA -->
                        <div class="campo">
                            <label for="nombreAsignatura">
                                <i class="fa-solid fa-book" aria-hidden="true" ></i> Nombre de la asignatura <span class="campo-requerido"> * </span>
                            </label>

                            <input type="text" id="nombreAsignatura" name="nombreAsignatura" value="{{ old('nombreAsignatura') }}" placeholder="Ej. Programación web" maxlength="150" required >
                        </div>

                        <!-- CLAVE DE LA ASIGNATURA -->
                        <div class="campo">
                            <label for="claveAsignatura">
                                <i class="fa-solid fa-key" aria-hidden="true" ></i> Clave de la asignatura <span class="campo-requerido"> * </span>
                            </label>

                            <input type="text" id="claveAsignatura" name="claveAsignatura" value="{{ old('claveAsignatura') }}"
                                placeholder="Ej. PROGWEB" maxlength="20" required >
                        </div>

                        <!-- CARRERA A LA QUE PERTENECE -->
                        <div class="campo">
                            <label for="id_carrera">
                                <i class="fa-solid fa-graduation-cap" aria-hidden="true" ></i> Carrera <span class="campo-requerido"> * </span>
                            </label>

                            <select id="id_carrera" name="id_carrera" required >
                                <option value="">
                                    Selecciona una carrera
                                </option>

                                @foreach ($carrerasActivas as $carrera)
                                    <option
                                        value="{{ $carrera->id_carrera }}"
                                        {{ old('id_carrera') == $carrera->id_carrera ? 'selected' : '' }}
                                    >
                                        {{ $carrera->nombreCarrera }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- CUATRIMESTRE DE LA ASIGNATURA -->
                        <div class="campo">
                            <label for="cuatrimestre">
                                <i class="fa-solid fa-calendar-days" aria-hidden="true" ></i> Cuatrimestre <span class="campo-requerido"> * </span>
                            </label>

                            <select id="cuatrimestre" name="cuatrimestre" required >
                                <option value="">
                                    Selecciona un cuatrimestre
                                </option>

                                @for ($cuatrimestre = 1; $cuatrimestre <= 12; $cuatrimestre++)
                                    <option
                                        value="{{ $cuatrimestre }}"
                                        {{ old('cuatrimestre') == $cuatrimestre ? 'selected' : '' }}
                                    >
                                        {{ $cuatrimestre }}.° 
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- CANTIDAD DE HORAS SEMANALES -->
                        <div class="campo">
                            <label for="horas">
                                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                                Horas semanales
                                <span class="campo-requerido">*</span>
                            </label>

                            <input type="number" id="horas" name="horas" value="{{ old('horas') }}" placeholder="Ej. 5" min="1" max="15" required >
                        </div>
                    </div>

                    <!-- BOTÓN PARA GUARDAR -->
                    <div class="acciones-formulario">
                        <button type="submit" class="boton-principal" >
                            Registrar asignatura
                        </button>
                    </div>
                </form>
            </div>
        </section>
    @endif

    <!-- PANEL PARA CONSULTAR ASIGNATURAS -->
    <section class="panel-asignaturas" id="panel-consulta" >
        <div class="encabezado-panel">
            <div>
                <h2> Consultar asignatura </h2>
                <p class="texto-secundario"> Busca por clave o nombre para consultar los datos registrados. </p>
            </div>
        </div>

        <div class="consulta-grid">
            <div class="buscador-consulta">
                <div class="campo">
                    <label for="buscar-consulta-asignatura"> Clave o nombre de la asignatura </label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input type="search" id="buscar-consulta-asignatura" placeholder="Escribe una clave o nombre..." autocomplete="off" >
                    </div>
                </div>

                <div class="resultados-consulta">
                    <h3> Resultados </h3>

                    <div id="resultados-consulta-asignatura" aria-live="polite" >
                        @forelse ($asignaturas as $asignatura)
                            <!-- RESULTADO INDIVIDUAL -->
                            <button
                                type="button"
                                class="resultado-consulta-item"
                                data-resultado-asignatura="{{ $asignatura->id_asignatura }}"
                                data-clave-consulta="{{ mb_strtolower($asignatura->clave ?? '') }}"
                                data-nombre-consulta="{{ mb_strtolower($asignatura->nombreAsignatura) }}"
                            >
                                <span class="texto-resultado">
                                    <strong> {{ $asignatura->nombreAsignatura }} </strong>
                                    <small>{{ $asignatura->clave }}</small>
                                </span>
                            </button>
                        @empty
                            <!-- MENSAJE CUANDO NO EXISTEN ASIGNATURAS -->
                            <p class="texto-secundario mensaje-sin-resultados">
                                No hay asignaturas para consultar.
                            </p>
                        @endforelse

                        <!-- MENSAJE CUANDO LA BÚSQUEDA NO ENCUENTRA RESULTADOS -->
                        <p class="texto-secundario mensaje-sin-resultados" id="sin-resultados-consulta-asignatura" hidden >
                            No se encontraron asignaturas con esa clave o nombre.
                        </p>
                    </div>
                </div>
            </div>

            <!-- TARJETA CON LOS DETALLES DE LA ASIGNATURA -->
            <div class="tarjeta-detalle" id="tarjeta-detalle-asignatura" >
                <div class="detalle-vacio" id="detalle-vacio-asignatura" >
                    <div class="icono-detalle" aria-hidden="true" >
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <h3> Información de la asignatura </h3>

                    <p class="texto-secundario"> Selecciona una asignatura de los resultados para ver su información. </p>
                </div>

                <!-- DETALLE INDIVIDUAL DE CADA ASIGNATURA -->
                @foreach ($asignaturas as $asignatura)
                    <div class="contenido-detalle-asignatura" data-detalle-asignatura="{{ $asignatura->id_asignatura }}" hidden >
                        <!-- ENCABEZADO DEL DETALLE -->
                        <div class="encabezado-detalle">
                            <div class="icono-detalle" aria-hidden="true" >
                                <i class="fa-solid fa-book"></i>
                            </div>

                            <div>
                                <h3> {{ $asignatura->nombreAsignatura }} </h3>
                                <p class="texto-secundario"> Clave: {{ $asignatura->clave }} </p>
                            </div>
                        </div>

                        <div class="lista-detalle">
                            <div class="fila-detalle">
                                <span> ID de asignatura </span>
                                <strong> {{ $asignatura->id_asignatura }} </strong>
                            </div>

                            <!-- CLAVE DE LA ASIGNATURA -->
                            <div class="fila-detalle">
                                <span> Clave </span>
                                <strong> {{ $asignatura->clave }} </strong>
                            </div>

                            <!-- NOMBRE DE LA ASIGNATURA -->
                            <div class="fila-detalle">
                                <span> Nombre de la asignatura </span>
                                <strong> {{ $asignatura->nombreAsignatura }} </strong>
                            </div>

                            <!-- CARRERA DE LA ASIGNATURA -->
                            <div class="fila-detalle">
                                <span> Carrera </span>
                                <strong> {{ $asignatura->carrera?->nombreCarrera ?? 'Sin carrera asignada' }} </strong>
                            </div>

                            <!-- CUATRIMESTRE DE LA ASIGNATURA -->
                            <div class="fila-detalle">
                                <span> Cuatrimestre </span>
                                <strong> {{ $asignatura->cuatrimestre }}.° </strong>
                            </div>

                            <!-- HORAS SEMANALES -->
                            <div class="fila-detalle">
                                <span> Horas semanales </span>
                                <strong>{{ $asignatura->cantidadHoras }} h</strong>
                            </div>

                            <!-- ESTADO DE LA ASIGNATURA -->
                            <div class="fila-detalle">
                                <span> Estado </span>
                                <strong class="{{ (int) $asignatura->estado === 1 ? 'texto-estado-activo' : 'texto-estado-inactivo' }}" >
                                    {{ (int) $asignatura->estado === 1 ? 'Activa' : 'Inactiva' }}
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
    window.rutaAsignaturas = @json(url('/asignaturas'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<script src="{{ asset('js/Asignaturas/gestion-asignaturas.js') }}" defer></script>

<!--  MODAL PARA CONFIRMAR LA BAJA DE UNA ASIGNATURA -->
<div class="modal-asignatura" id="modal-baja-asignatura" hidden >
    <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-baja-asignatura" >
        <div class="modal-icono">
            <i class="fa-solid fa-book" aria-hidden="true" ></i>
        </div>

        <h2 id="titulo-modal-baja-asignatura"> ¿Dar de baja esta asignatura? </h2>
        <p class="modal-mensaje"> Estás a punto de dar de baja a: </p>
        <strong id="nombre-asignatura-baja" class="modal-destacado" ></strong>

        <p class="modal-nota"> La asignatura quedará inactiva y su información se conservará en el sistema. </p>

       <!-- FORMULARIO DE CONFIRMACIÓN -->
        <form id="formulario-baja-asignatura" method="POST" >
            @csrf
            @method('DELETE')

            <div class="modal-acciones">
                <!-- CANCELAR LA OPERACIÓN -->
                <button type="button" class="boton-modal-cancelar" onclick="cerrarModalBajaAsignatura()" >
                    Cancelar
                </button>

                <!-- CONFIRMAR LA BAJA -->
                <button type="submit" class="boton-modal-confirmar" >
                    <i class="fa-solid fa-trash" aria-hidden="true" ></i>
                    Sí, dar de baja
                </button>
            </div>
        </form>
    </div>
</div> 

<!--  MODAL DE REGISTRO O MODIFICACIÓN EXITOSA -->
@if ( session('tipo_modal_asignatura') === 'exito' && session('mensaje_modal_asignatura'))
    <div class="modal-asignatura" id="modal-registro-asignatura" >
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-registro-asignatura" >

            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check" aria-hidden="true" ></i>
            </div>

            <h2 id="titulo-modal-registro-asignatura"> ¡Operación exitosa! </h2>
            <p class="modal-mensaje"> {{ session('mensaje_modal_asignatura') }} </p>

            <!-- BOTÓN PARA ACEPTAR -->
            <div class="modal-acciones">
                <button type="button" class="boton-modal-confirmar" id="aceptar-modal-registro-asignatura" >
                    <i class="fa-solid fa-check" aria-hidden="true" ></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif

<!--  MODAL DE ERROR -->
@if ( session('tipo_modal_asignatura') === 'error' && session('mensaje_modal_asignatura') )
    <div class="modal-asignatura" id="modal-error-asignatura" >
        <div class="modal-contenido" role="dialog" aria-modal="true" aria-labelledby="titulo-modal-error-asignatura" >
            <button type="button" class="boton-cerrar-panel" id="cerrar-modal-error-asignatura" aria-label="Cerrar modal" >
                &times;
            </button>

            <div class="modal-icono modal-icono-error">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true" ></i>
            </div>

            <h2 id="titulo-modal-error-asignatura"> ¡No se pudo completar la operación! </h2>
            <p class="modal-mensaje"> {{ session('mensaje_modal_asignatura') }} </p>

            <!-- BOTÓN PARA ACEPTAR -->
            <div class="modal-acciones">
                <button type="button" class="boton-modal-error" id="aceptar-modal-error-asignatura" >
                    <i class="fa-solid fa-check" aria-hidden="true" ></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
@endif
@endsection
