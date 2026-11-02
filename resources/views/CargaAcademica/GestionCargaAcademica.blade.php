@extends('layouts.DisenoPrincipal')

@section('contenido')

<div class="container">
    <h1>Gestión de carga académica</h1>

    <p>
        Consulta de las horas académicas registradas para cada docente.
    </p>

    @if ((int) auth()->user()->rol !== 3)
        <form method="GET" action="{{ route('cargaAcademica.index') }}">
            <label for="id_usuario">Seleccionar docente:</label>

            <select name="id_usuario" id="id_usuario">
                <option value="">Todos los docentes</option>

                @foreach ($usuariosDocentes as $usuario)
                    <option value="{{ $usuario->id_usuario }}" {{ request('id_usuario') == $usuario->id_usuario ? 'selected' : '' }} >
                        {{ $usuario->nombreUsuario }}
                        {{ $usuario->apellidoPaterno }}
                        {{ $usuario->apellidoMaterno }}
                    </option>
                @endforeach
            </select>

            <button type="submit">Consultar</button>
        </form>
    @endif

    <table>
        <thead>
            <tr>
                <th>Docente</th>
                <th>Matrícula</th>
                <th>Carga académica</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($docentesRegistrados as $usuario)
                <tr>
                    <td>
                        {{ $usuario->nombreUsuario }}
                        {{ $usuario->apellidoPaterno }}
                        {{ $usuario->apellidoMaterno }}
                    </td>

                    <td>
                        {{ $usuario->docente->matricula ?? '-' }}
                    </td>

                    <td>
                        @if ($usuario->docente)
                            {{ $usuario->docente->cargaAcademica }} horas
                        @else
                            Sin información registrada
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        No se encontraron docentes registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
