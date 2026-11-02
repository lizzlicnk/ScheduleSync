@extends('layouts.DisenoPrincipal')

@section('title', 'Editar carga académica | Schedule-Sync')

@push('estilos')
    <!-- Estilos de la edición de carga académica -->
    <link rel="stylesheet" href="{{ asset('css/CargaAcademica/editar-cargaAcademica.css') }}">
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

<div class="pagina-editar-carga">

    <section class="panel-editar-carga">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar-carga">
            <h1>Editar carga académica: {{ $nombreCompleto }}</h1>
            <p>Modifica la carga académica del docente y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion-carga alerta-error-carga">
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
            <div class="alerta-edicion-carga alerta-error-carga">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar la carga académica -->
        <form
            action="{{ route('carga-academica.update', [
                'idDocente' => $docenteEncontrado->id_docente
            ]) }}"
            method="POST"
            class="formulario-editar-carga">

            @csrf
            @method('PUT')

            <div class="campos-edicion-carga">

                <!-- Matrícula -->
                <div class="campo-edicion-carga">
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
                <div class="campo-edicion-carga">
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
                <div class="campo-edicion-carga campo-edicion-carga-completo">
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
            <div class="acciones-edicion-carga">

                <a href="{{ route('carga-academica.index') }}"
                    class="boton-cancelar-edicion-carga">
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-carga">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar cambios
                </button>

            </div>
        </form>

    </section>
</div>

@endsection