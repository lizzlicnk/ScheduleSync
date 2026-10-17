@extends('layouts.DisenoPrincipal')

@section('titulo', 'Editar docente | Schedule-Sync')

@push('estilos')
    <!-- Estilos de la edición de docentes -->
    <link rel="stylesheet" href="{{ asset('css/docentes/editar-docentes.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-docente">

    <section class="panel-editar-docente">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar">
            <h1>
                Editar docente:
                {{ $docenteEncontrado->usuario->nombreUsuario }}
                {{ $docenteEncontrado->usuario->apellidoPaterno }}
            </h1>

            <p>Modifica los datos académicos del docente y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion alerta-error">
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
            <div class="alerta-edicion alerta-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar los datos académicos -->
        <form action="{{ route('docentes.update', ['idDocente' => $docenteEncontrado->id_docente]) }}" method="POST" class="formulario-editar">
            @csrf
            @method('PUT')

            <div class="campos-edicion">

                <!-- Cuenta de usuario asociada -->
                <div class="campo-edicion campo-edicion-completo">
                    <label for="id_usuario">
                        <i class="fa-solid fa-user"></i>
                        Cuenta del docente
                    </label>

                    <input
                        type="text"
                        id="nombre_docente"
                        value="{{ $docenteEncontrado->usuario->nombreUsuario }} {{ $docenteEncontrado->usuario->apellidoPaterno }} {{ $docenteEncontrado->usuario->apellidoMaterno }}"
                        readonly
                    >

                    <input
                        type="hidden"
                        name="id_usuario"
                        value="{{ $docenteEncontrado->id_usuario }}"
                    >

                    <small>
                        La cuenta de usuario asociada no se puede cambiar desde esta pantalla.
                    </small>
                </div>

                <!-- Matrícula -->
                <div class="campo-edicion">
                    <label for="matricula">
                        <i class="fa-solid fa-id-card"></i>
                        Matrícula
                    </label>

                    <input
                        type="text"
                        id="matricula"
                        name="matricula"
                        value="{{ old('matricula', $docenteEncontrado->matricula) }}"
                        maxlength="20"
                        placeholder="Ingresa la matrícula"
                        required
                    >
                </div>

                <!-- Especialidad -->
                <div class="campo-edicion">
                    <label for="especialidad">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Especialidad
                    </label>

                    <input
                        type="text"
                        id="especialidad"
                        name="especialidad"
                        value="{{ old('especialidad', $docenteEncontrado->especialidad) }}"
                        maxlength="100"
                        placeholder="Ingresa la especialidad"
                        required
                    >
                </div>

                <!-- Tipo de docente -->
                <div class="campo-edicion">
                    <label for="tipoDocente">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        Tipo de docente
                    </label>

                    <select id="tipoDocente" name="tipoDocente" required>
                        <option
                            value="1"
                            {{ (string) old('tipoDocente', $docenteEncontrado->tipoDocente) === '1' ? 'selected' : '' }}
                        >
                            PTC — Tiempo completo
                        </option>

                        <option
                            value="2"
                            {{ (string) old('tipoDocente', $docenteEncontrado->tipoDocente) === '2' ? 'selected' : '' }}
                        >
                            PA — Asignatura
                        </option>
                    </select>
                </div>

                <!-- Carga académica: solo lectura -->
                <div class="campo-edicion">
                    <label for="cargaAcademica">
                        <i class="fa-solid fa-clock"></i>
                        Carga académica
                    </label>

                    <input
                        type="number"
                        id="cargaAcademica"
                        value="{{ $docenteEncontrado->cargaAcademica }}"
                        readonly
                    >

                    <small>
                        La carga académica no se puede modificar desde este formulario.
                    </small>
                </div>

            </div>

            <!-- Botones para cancelar o guardar los cambios -->
            <div class="acciones-edicion">
                <a
                    href="{{ route('docentes.index') }}"
                    class="boton-cancelar-edicion"
                >
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-cambios">
                    Guardar cambios
                </button>
            </div>

        </form>
    </section>

</div>

<!-- Modal que confirma la modificación exitosa -->
@if (session('tipo_modal_docente') === 'modificacion' && session('mensaje_modal_docente'))
    <div class="modal-confirmacion" id="modal-modificar-docente">
        <div
            class="modal-contenido"
            role="dialog"
            aria-modal="true"
            aria-labelledby="titulo-modal-modificacion"
        >
            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check"></i>
            </div>

            <h2 id="titulo-modal-modificacion">¡Cambios guardados!</h2>

            <p>{{ session('mensaje_modal_docente') }}</p>

            <button
                type="button"
                class="boton-modal-confirmar boton-modal-exito"
                id="aceptar-modal-modificacion"
                onclick="window.location.href='{{ route('docentes.index') }}';"
            >
                <i class="fa-solid fa-check"></i>
                Aceptar
            </button>
        </div>
    </div>
@endif

<!-- Modal que confirma la modificación exitosa del docente -->
@if (session('tipo_modal_docente') === 'modificacion' && session('mensaje_modal_docente'))
    <div class="modal-confirmacion" id="modal-modificar-docente">
        <div class="modal-contenido">
            <div class="modal-icono modal-icono-exito">
                <i class="fa-solid fa-check"></i>
            </div>

            <h2>¡Cambios guardados!</h2>

            <p>{{ session('mensaje_modal_docente') }}</p>

            <button
                type="button"
                class="boton-modal-confirmar"
                id="aceptar-modal-modificacion-docente"
                onclick="window.location.href='{{ route('docentes.index') }}';"
            >
                <i class="fa-solid fa-check"></i>
                Aceptar
            </button>
        </div>
    </div>
@endif
@endsection
