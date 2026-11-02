
@extends('layouts.DisenoPrincipal')

@section('title', 'Editar carga académica | Schedule-Sync')

@push('estilos')
    <!-- Estilos de la edición de disponibilidad académica -->
    <link rel="stylesheet" href="{{ asset('css/DisponibilidadAcademica/editar-disponibilidad-academica.css') }}">
@endpush

@section('contenido')

@php
    $nombreCompleto = trim(
        $usuarioEncontrado->nombreUsuario . ' ' .
        ($usuarioEncontrado->apellidoPaterno ?? '') . ' ' .
        ($usuarioEncontrado->apellidoMaterno ?? '')
    );

    $limiteHoras = (int) $docenteEncontrado->tipoDocente === 2 ? 25 : 100;
    $tipoDocente = (int) $docenteEncontrado->tipoDocente === 1 ? 'PTC' : 'PA';
@endphp

<div class="pagina-editar-disponibilidad">

    <section class="panel-editar-disponibilidad">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar-disponibilidad">
            <h1>Editar carga académica: {{ $nombreCompleto }}</h1>
            <p>Modifica la carga académica del docente y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion-disponibilidad alerta-error-disponibilidad">
                <strong>Revisa los siguientes campos:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Mostrar errores generales -->
        @if (session('error'))
            <div class="alerta-edicion-disponibilidad alerta-error-disponibilidad">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar la carga académica -->
        <form
            action="{{ route('disponibilidad-academica.update', [
                'idDocente' => $docenteEncontrado->id_docente
            ]) }}"
            method="POST"
            class="formulario-editar-disponibilidad">

            @csrf
            @method('PUT')

            <div class="campos-edicion-disponibilidad">

                <!-- Matrícula -->
                <div class="campo-edicion-disponibilidad">
                    <label for="matricula">
                        <i class="fa-solid fa-id-card"></i>
                        Matrícula
                    </label>

                    <input
                        type="text"
                        id="matricula"
                        value="{{ $docenteEncontrado->matricula }}"
                        readonly>
                </div>

                <!-- Tipo de docente -->
                <div class="campo-edicion-disponibilidad">
                    <label for="tipoDocente">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        Tipo de docente
                    </label>

                    <input
                        type="text"
                        id="tipoDocente"
                        value="{{ $tipoDocente }}"
                        readonly>
                </div>

                <!-- Carga académica -->
                <div class="campo-edicion-disponibilidad campo-edicion-disponibilidad-completo">
                    <label for="cargaAcademica">
                        <i class="fa-solid fa-clock"></i>
                        Carga académica (horas)
                    </label>

                    <input
                        type="number"
                        id="cargaAcademica"
                        name="cargaAcademica"
                        value="{{ old('cargaAcademica', $docenteEncontrado->cargaAcademica) }}"
                        min="1"
                        max="{{ $limiteHoras }}"
                        step="1"
                        placeholder="Ingresa la cantidad de horas"
                        required>

                    <small>
                        El docente {{ $tipoDocente }} puede tener un máximo de
                        {{ $limiteHoras }} horas académicas.
                    </small>
                </div>

            </div>

            <!-- Botones para cancelar o guardar los cambios -->
            <div class="acciones-edicion-disponibilidad">

                <a href="{{ route('disponibilidad-academica.index') }}"
                    class="boton-cancelar-edicion-disponibilidad">
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-disponibilidad">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>

            </div>
        </form>

    </section>
</div>

@endsection
