@extends('layouts.DisenoPrincipal')

@section('title', 'Gestión de horarios | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Horarios/inicio-horarios.css') }}">
@endpush

@section('contenido')

<main class="pagina-inicio-horarios">

    <!-- ENCABEZADO DEL MÓDULO -->
    <header class="encabezado-modulo">
        <div>
            <span class="ruta-navegacion">
                Inicio / Administración / Horarios
            </span>

            <h1>Gestión de horarios</h1>

            <p>
                Selecciona la opción que deseas realizar dentro del módulo de horarios académicos.
            </p>
        </div>
    </header>

    <!-- OPCIONES DEL MÓDULO -->
    <section class="contenedor-opciones-horarios">

        <!-- OPCIÓN DE DATOS GENERALES -->
        <a href="{{ route('datos-generales.index') }}" class="tarjeta-opcion-horarios">

            <div class="icono-opcion-horarios">
                <i class="fa-solid fa-calendar-days"></i>
            </div>

            <div class="contenido-opcion-horarios">
                <h2>Datos generales</h2>
                <p> Consulta y administra la información general de los horarios académicos registrados. </p>
            </div>

            <div class="flecha-opcion-horarios">
                <i class="fa-solid fa-arrow-right"></i>
            </div>

        </a>

        <!-- OPCIÓN DE CREAR HORARIO  route('horarios.create') }} -->
        <a href="" class="tarjeta-opcion-horarios">

            <div class="icono-opcion-horarios">
                <i class="fa-solid fa-calendar-plus"></i>
            </div>

            <div class="contenido-opcion-horarios">
                <h2>Crear horario</h2>
                <p> Registra un nuevo horario académico seleccionando sus datos generales. </p>
            </div>

            <div class="flecha-opcion-horarios">
                <i class="fa-solid fa-arrow-right"></i>
            </div>

        </a>

    </section>

</main>

@endsection
