@extends('layouts.DisenoPrincipal')

@section('title', 'Editar grupo | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/Grupos/editar-grupos.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-grupo">

    <section class="panel-editar-grupo">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar-grupo">
            <h1>Editar grupo: {{ $grupoEncontrado->nombreGrupo }}</h1>
            <p>Modifica los datos del grupo académico y guarda los cambios.</p>
        </div>

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="alerta-edicion-grupo alerta-error-grupo">
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
            <div class="alerta-edicion-grupo alerta-error-grupo">
                {{ session('error') }}
            </div>
        @endif

        <!-- Formulario para actualizar el grupo -->
        <form action="{{ route('grupos.update', ['idGrupo' => $grupoEncontrado->id_grupo]) }}" method="POST" class="formulario-editar-grupo" >
            @csrf
            @method('PUT')

            <div class="campos-edicion-grupo">
                <!-- Carrera del grupo -->
                <div class="campo-edicion-grupo campo-edicion-grupo-completo">
                    <label for="id_carrera">
                        <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                        Carrera
                    </label>

                    <select id="id_carrera" name="id_carrera" required>
                        <option value="">Selecciona una carrera</option>

                        @foreach ($carrerasRegistradas as $carrera)
                            @if ((int) $carrera->estado === 1)
                                <option value="{{ $carrera->id_carrera }}"  data-clave="{{ $carrera->claveCarrera }}" @selected(old('id_carrera', $grupoEncontrado->id_carrera) == $carrera->id_carrera)>
                                    {{ $carrera->claveCarrera }} - {{ $carrera->nombreCarrera }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Cuatrimestre del grupo -->
                <div class="campo-edicion-grupo">
                    <label for="cuatrimestre">
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        Cuatrimestre
                    </label>

                    <select id="cuatrimestre" name="cuatrimestre" required>
                        <option value="">Selecciona un cuatrimestre</option>

                        @for ($numero = 1; $numero <= 10; $numero++)
                            <option value="{{ $numero }}" @selected(old('cuatrimestre', $grupoEncontrado->cuatrimestre) == $numero) >
                                {{ $numero }}.°
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Letra del grupo -->
                <div class="campo-edicion-grupo">
                    <label for="letraGrupo">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                        Letra del grupo
                    </label>

                    <select id="letraGrupo" name="letraGrupo" required>
                        <option value="">Selecciona una letra</option>

                        @foreach (range('A', 'L') as $letra)
                            <option value="{{ $letra }}"  @selected( strtoupper((string) old('letraGrupo', substr($grupoEncontrado->nombreGrupo, -1) )) === $letra ) >
                                {{ $letra }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periodo académico -->
                <div class="campo-edicion-grupo campo-edicion-grupo-completo">
                    <label for="id_periodo">
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        Periodo académico
                    </label>

                    <select id="id_periodo" name="id_periodo" required>
                        <option value="">Selecciona un periodo</option>

                        @foreach ($periodosRegistrados as $periodo)
                            <option value="{{ $periodo->id_periodo }}" @selected(old('id_periodo', $grupoEncontrado->id_periodo) == $periodo->id_periodo)>
                                {{ $periodo->estacion }} {{ $periodo->anio }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="acciones-edicion-grupo">

                <a href="{{ route('grupos.index') }}" class="boton-cancelar-edicion-grupo">
                    Cancelar
                </a>

                <button type="submit" class="boton-guardar-cambios-grupo">
                    Guardar cambios
                </button>

            </div>
        </form>
    </section>
</div>

<!-- Datos para actualizar la vista previa del nombre -->
<script>
    window.datosEdicionGrupo = {
        carreras: @json($carrerasRegistradas->map(fn ($carrera) => [
            'id' => (string) $carrera->id_carrera,
            'clave' => strtoupper(trim($carrera->claveCarrera)),
        ])->values()),
        nombreActual: @json($grupoEncontrado->nombreGrupo)
    };
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campoCarrera = document.getElementById('id_carrera');
    const campoCuatrimestre = document.getElementById('cuatrimestre');
    const campoLetra = document.getElementById('letraGrupo');
    const vistaNombre = document.getElementById('nombreGrupoVista');

    // Actualiza el nombre según los campos seleccionados.
    function actualizarNombreGrupo() {
        const carrera = window.datosEdicionGrupo.carreras.find(
            elemento => elemento.id === campoCarrera.value
        );

        if (!carrera || !campoCuatrimestre.value || !campoLetra.value) {
            vistaNombre.value = '';
            return;
        }

        vistaNombre.value =
            carrera.clave + '-' +
            campoCuatrimestre.value +
            campoLetra.value.toUpperCase();
    }

    campoCarrera.addEventListener('change', actualizarNombreGrupo);
    campoCuatrimestre.addEventListener('change', actualizarNombreGrupo);
    campoLetra.addEventListener('change', actualizarNombreGrupo);

    actualizarNombreGrupo();
});
</script>
@endsection
