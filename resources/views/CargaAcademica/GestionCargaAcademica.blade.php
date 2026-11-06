@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de carga académica | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/CargaAcademica/gestion-cargaAcademica.css') }}">
@endpush

@section('contenido')
<main class="pagina-carga-academica">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            @if (auth()->user()->rol == 3)
                <p class="ruta-modulo">Inicio / Carga académica</p>
                <h1>Mi carga académica</h1>
                <p class="descripcion-modulo">
                    Consulta la carga académica que tienes asignada como docente de la universidad.
                </p>
            @else
                <p class="ruta-modulo">Inicio / Administración / Carga académica</p>
                <h1>Gestión de carga académica</h1>
                <p class="descripcion-modulo">
                    Consulta y administra la carga académica registrada de los docentes de la universidad.
                </p>
            @endif
        </div>
    </header>

    <!-- FILTROS DE CARGA ACADÉMICA -->
    @if (in_array((int) auth()->user()->rol, [1, 2], true))
        <section class="panel-carga-academica panel-filtros" id="contenedor-filtros" hidden>

            <div class="encabezado-panel">
                <div>
                    <h2>Filtrar docentes</h2>
                    <p class="texto-secundario">
                        Selecciona el tipo de docente que deseas consultar.
                    </p>
                </div>

                <button type="button" class="boton-secundario" id="limpiar-filtros">
                    Limpiar filtros
                </button>
            </div>

            <div class="filtros-carga-academica">
                <div class="campo">
                    <label for="filtro-tipo">Tipo de docente</label>

                    <select id="filtro-tipo" name="filtro_tipo">
                        <option value="">Todos los tipos</option>
                        <option value="1">PTC</option>
                        <option value="2">PA</option>
                    </select>
                </div>
            </div>
        </section>
    @endif

    <!-- TABLA DE CARGA ACADÉMICA -->
    <section class="panel-carga-academica">
        <div class="encabezado-panel encabezado-tabla">
            <div class="titulo-tabla">
                <h2>Carga académica de los docentes</h2>
                <p class="texto-secundario">
                    <span id="total-resultados">{{ $docentesRegistrados->count() }}</span>
                    docente(s) encontrado(s)
                </p>
            </div>

            <div class="botones-encabezado">
                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <button type="button" class="boton-secundario" id="boton-filtros"
                        aria-expanded="false" aria-controls="contenedor-filtros">
                        <span aria-hidden="true">☷</span>
                        Filtros
                    </button>
                @endif
            </div>

            <div class="busqueda-tabla campo">
                @if (in_array((int) auth()->user()->rol, [1, 2], true))
                    <label for="buscar-carga-academica">Buscar docente</label>

                    <div class="entrada-busqueda">
                        <span aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input type="search" id="buscar-carga-academica" placeholder="Nombre o matrícula del docente..." autocomplete="off" >
                    </div>
                @endif
            </div>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla-carga-academica">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Matrícula</th>
                        <th scope="col">Nombre del docente</th>
                        <th scope="col">Tipo de docente</th>
                        <th scope="col">Horas asignadas</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>

                <!-- CUERPO DE LA TABLA -->
                <tbody id="cuerpo-tabla-carga-academica">
                    @forelse ($docentesRegistrados as $usuario)
                        @php
                            // Obtiene la información académica asociada a la cuenta.
                            $docente = $usuario->docente;

                            // Construye el nombre completo del docente.
                            $nombreCompleto = trim(
                                $usuario->nombreUsuario . ' ' .
                                ($usuario->apellidoPaterno ?? '') . ' ' .
                                ($usuario->apellidoMaterno ?? '')
                            );

                            // Obtiene el tipo de docente.
                            $tipoDocente = $docente
                                ? (int) $docente->tipoDocente
                                : null;

                            $nombreTipo = $tipoDocente === 1
                                ? 'PTC'
                                : ($tipoDocente === 2 ? 'PA' : '-');
                        @endphp

                        <tr
                            data-id-usuario="{{ $usuario->id_usuario }}"
                            data-nombre="{{ mb_strtolower($nombreCompleto) }}"
                            data-matricula="{{ mb_strtolower($docente->matricula ?? '') }}"
                            data-tipo="{{ $tipoDocente ?? '' }}"
                            data-carga="{{ $docente->cargaAcademica ?? '' }}"
                        >
                            <td>{{ $docente->id_docente ?? '-' }}</td>

                            <td>
                                @if ($docente)
                                    <span class="matricula-docente">
                                        {{ $docente->matricula }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <div class="informacion-docente">
                                    <strong>{{ $nombreCompleto }}</strong>
                                </div>
                            </td>

                            <td>
                                <span class="rol-usuario tipo-docente">
                                    {{ $nombreTipo }}
                                </span>
                            </td>

                            <td>
                                @if ($docente)
                                    <span class="carga-academica">
                                        {{ $docente->cargaAcademica }} horas
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <div class="acciones-tabla">
                                    @if ($docente)

                                        {{-- Consultar carga académica --}}
                                        <button type="button" class="boton-accion boton-consultar" data-consultar-carga="{{ $docente->id_docente }}" aria-label="Consultar carga académica de {{ $nombreCompleto }}" >
                                            <i class="fa-solid fa-eye"></i>
                                            Consultar
                                        </button>

                                        @if ((int) auth()->user()->rol === 1)
                                            <a href="{{ route('carga-academica.edit', [ 'idDocente' => $docente->id_docente ]) }}" class="boton-accion boton-modificar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Modificar
                                            </a>

                                            {{-- Eliminar carga académica --}}
                                            <form action="{{ route('carga-academica.destroy', $docente->id_docente) }}"
                                                method="POST" class="formulario-eliminar-carga" data-nombre-docente="{{ $nombreCompleto }}" >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="boton-accion boton-eliminar" >
                                                    <i class="fa-solid fa-trash"></i>
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endif

                                    @else
                                        <span class="estado-sin-informacion">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Sin información académica
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr id="fila-sin-carga-academica">
                            <td colspan="6" class="tabla-vacia">
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="fila-sin-resultados" hidden>
                        <td colspan="6" class="tabla-vacia">
                            No hay docentes que coincidan con la búsqueda o los filtros seleccionados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-carga-academica">
            <span id="informacion-pagina">Página 1 de 1</span>

            <div class="controles-paginacion">
                <button type="button" class="boton-pagina" id="pagina-anterior" aria-label="Página anterior" disabled>
                    ‹
                </button>

                <button type="button" class="boton-pagina" id="pagina-siguiente" aria-label="Página siguiente" disabled>
                    ›
                </button>
            </div>
        </div>
    </section>

    <!-- PANEL PARA CONSULTAR LA CARGA ACADÉMICA -->
    <section class="panel-carga-academica panel-consulta-carga-academica"
        id="panel-consulta">

        <div class="encabezado-panel">
            <div>
                <h2>Consultar carga académica</h2>
                <p class="texto-secundario">
                    Busca por nombre o matrícula para consultar la información académica del docente.
                </p>
            </div>
        </div>

        <!-- Buscador y tarjeta con los detalles del docente -->
        <div class="consulta-grid">

            <div class="buscador-consulta">
                <label for="buscar-consulta-carga"> Nombre del docente </label>

                <div class="entrada-busqueda">
                    <span aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input type="search" id="buscar-consulta-carga" placeholder="Escribe un nombre o matrícula..." autocomplete="off" >
                </div>

                <div class="resultados-consulta" id="resultados-consulta-carga" aria-live="polite" >
                    @forelse ($docentesRegistrados as $usuario)
                        @php
                            $docente = $usuario->docente;

                            $nombreCompleto = trim(
                                $usuario->nombreUsuario . ' ' .
                                ($usuario->apellidoPaterno ?? '') . ' ' .
                                ($usuario->apellidoMaterno ?? '')
                            );
                        @endphp

                        @if ($docente)
                            <button type="button" class="resultado-consulta-item" data-resultado-carga="{{ $docente->id_docente }}"
                                data-nombre-consulta="{{ mb_strtolower($nombreCompleto) }}"
                                data-matricula-consulta="{{ mb_strtolower($docente->matricula) }}"
                                aria-pressed="false" >
                                <span class="informacion-resultado-consulta">
                                    <strong>{{ $nombreCompleto }}</strong>
                                    <small>Matrícula: {{ $docente->matricula }}</small>
                                </span>

                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        @endif
                    @empty
                        <p class="texto-secundario">
                            No hay docentes registrados para consultar.
                        </p>
                    @endforelse

                    <p class="texto-secundario" id="sin-resultados-consulta-carga" hidden >
                        No se encontraron docentes con esa búsqueda.
                    </p>
                </div>
            </div>

            <!-- Tarjeta con los detalles del docente seleccionado -->
            <div class="tarjeta-detalle-carga" id="tarjeta-detalle-carga" >
                <div class="detalle-vacio-carga" id="detalle-vacio-carga" >
                    <div class="icono-detalle" aria-hidden="true">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <h3>Detalles del docente</h3>

                    <p class="texto-secundario">
                        Selecciona un docente de los resultados para consultar su información académica.
                    </p>
                </div>

                @foreach ($docentesRegistrados as $usuario)
                    @php
                        $docente = $usuario->docente;

                        $nombreCompleto = trim(
                            $usuario->nombreUsuario . ' ' .
                            ($usuario->apellidoPaterno ?? '') . ' ' .
                            ($usuario->apellidoMaterno ?? '')
                        );
                    @endphp

                    @if ($docente)
                        <div data-detalle-carga="{{ $docente->id_docente }}" hidden >
                            <div class="encabezado-detalle-carga">
                                <div class="icono-detalle">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>

                                <h3>{{ $nombreCompleto }}</h3>
                            </div>

                            <div class="datos-detalle-carga">
                                <div>
                                    <span>Matrícula</span>
                                    <strong>{{ $docente->matricula }}</strong>
                                </div>

                                <div>
                                    <span>Especialidad</span>
                                    <strong>{{ $docente->especialidad ?: 'No especificada' }}</strong>
                                </div>

                                <div>
                                    <span>Tipo de docente</span>
                                    <strong>
                                        {{ (int) $docente->tipoDocente === 1 ? 'PTC' : 'PA' }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Carga académica</span>
                                    <strong>{{ $docente->cargaAcademica }} horas</strong>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

            </div>
        </div>
    </section>

</main>

<!-- CONFIGURACIÓN PARA JAVASCRIPT -->
<script>
    window.rutaCargaAcademica = @json(url('/carga-academica'));
    window.tokenSeguridad = @json(csrf_token());
</script>

<script src="{{ asset('js/CargaAcademica/gestion-CargaAcademica.js') }}" defer></script>

@endsection