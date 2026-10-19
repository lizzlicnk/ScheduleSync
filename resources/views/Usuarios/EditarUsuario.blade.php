
@extends('layouts.DisenoPrincipal')

@section('titulo', 'Editar usuario | Schedule-Sync')

@push('estilos')
    <!-- Estilos -->
    <link rel="stylesheet" href="{{ asset('css/Usuarios/editar-usuario.css') }}">
@endpush

@section('contenido')
<div class="pagina-editar-usuario">

    <section class="panel-editar-usuario">

        <!-- Encabezado del formulario de edición -->
        <div class="encabezado-editar">
            <h1>Editar usuario: {{ $usuarioEncontrado->nombreUsuario }} {{ $usuarioEncontrado->apellidoPaterno }}</h1>
            <p>Modifica los datos del usuario y guarda los cambios.</p>
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

        <!-- Formulario para actualizar los datos del usuario -->
        <form action="{{ route('usuarios.update', ['usuario' => $usuarioEncontrado->id_usuario]) }}" method="POST" class="formulario-editar" >
            <!-- Token de seguridad para validar el formulario -->    
            @csrf
            @method('PUT')

            <div class="campos-edicion">

                <div class="campo-edicion">
                    <!-- Nombre -->
                    <label for="nombreUsuario"> <i class="fa-solid fa-user"></i> Nombre</label>
                    <input
                        type="text"
                        id="nombreUsuario"
                        name="nombreUsuario"
                        value="{{ old('nombreUsuario', $usuarioEncontrado->nombreUsuario) }}"
                        placeholder="Ej. Laura"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="campo-edicion">
                    <!-- Apellido paterno -->
                    <label for="apellidoPaterno"><i class="fa-solid fa-lock"></i> Apellido paterno</label>
                    <input
                        type="text"
                        id="apellidoPaterno"
                        name="apellidoPaterno"
                        value="{{ old('apellidoPaterno', $usuarioEncontrado->apellidoPaterno) }}"
                        placeholder="Ej. Gómez"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="campo-edicion">
                    <!-- Apellido materno -->
                    <label for="apellidoMaterno"> <i class="fa-solid fa-lock"></i> Apellido materno</label>
                    <input
                        type="text"
                        id="apellidoMaterno"
                        name="apellidoMaterno"
                        value="{{ old('apellidoMaterno', $usuarioEncontrado->apellidoMaterno) }}"
                        placeholder="Ej. López"
                        maxlength="100"
                    >
                </div>

                <div class="campo-edicion">
                    <!-- Correo electrónico -->
                    <label for="correo"> <i class="fa-solid fa-envelope"></i> Correo electrónico</label>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        value="{{ old('correo', $usuarioEncontrado->correo) }}"
                        placeholder="ejemplo@upemor.edu.mx"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="campo-edicion campo-edicion-completo">
                    <!-- Cambiar la contraseña -->
                    <label for="password"> <i class="fa-solid fa-shield-alt"></i> Nueva contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Déjala vacía para conservar la actual"
                        minlength="8"
                        autocomplete="new-password"
                    >
                    <small>
                        Solo escribe una contraseña si deseas cambiarla.
                        Debe tener al menos 8 caracteres.
                    </small>
                </div>

                <!-- rol del usuario -->
                <div class="campo-edicion">
                    <label for="rol"> <i class="fa-solid fa-shield"></i> Rol</label>
                    <select id="rol" name="rol" required>
                        <option value="1" {{ (string) old('rol', $usuarioEncontrado->rol) === '1' ? 'selected' : '' }}>
                            Administrador
                        </option>
                        <option value="2" {{ (string) old('rol', $usuarioEncontrado->rol) === '2' ? 'selected' : '' }}>
                            Personal administrativo
                        </option>
                        <option value="3" {{ (string) old('rol', $usuarioEncontrado->rol) === '3' ? 'selected' : '' }}>
                            Docente
                        </option>
                    </select>
                </div>

                <!-- Estado del usuario -->
                <div class="campo-edicion">
                    <label for="estado">
                        <i class="fa-solid fa-toggle-on"></i> Estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                        required
                        {{ (int) $usuarioEncontrado->id_usuario === (int) auth()->id() ? 'disabled' : '' }}>

                        <option
                            value="1"
                            {{ (string) old('estado', $usuarioEncontrado->estado) === '1' ? 'selected' : '' }}>
                            Activo
                        </option>

                        @if ((int) $usuarioEncontrado->id_usuario !== (int) auth()->id())
                            <option
                                value="0"
                                {{ (string) old('estado', $usuarioEncontrado->estado) === '0' ? 'selected' : '' }}>
                                Inactivo
                            </option>
                        @endif
                    </select>

                    @if ((int) $usuarioEncontrado->id_usuario === (int) auth()->id())
                        <small>No puedes cambiar el estado de la cuenta con la que has iniciado sesión.</small>
                        <!-- Enviar el estado actual, ya que los campos deshabilitados no se envían -->
                        <input type="hidden" name="estado" value="{{ $usuarioEncontrado->estado }}">
                    @endif
                </div>
            </div>

            <!-- Botones para cancelar o guardar los cambios -->
            <div class="acciones-edicion">
                <a href="{{ url('/usuarios') }}" class="boton-cancelar-edicion">
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
    @if (session('tipo_modal_usuario') === 'modificacion' && session('mensaje_modal_usuario'))
        <div class="modal-confirmacion" id="modal-modificar-usuario">
            <div class="modal-contenido">
                <div class="modal-icono modal-icono-exito">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h2>¡Cambios guardados!</h2>

                <p>{{ session('mensaje_modal_usuario') }}</p>

                <button
                    type="button"
                    class="boton-modal-confirmar"
                    id="aceptar-modal-modificacion"
                    onclick="window.location.href='{{ route('usuarios.index') }}';"
                >
                    <i class="fa-solid fa-check"></i>
                    Aceptar
                </button>
            </div>
        </div>
    @endif

@push('scripts')
    <!-- JavaScript para las funciones de gestión de usuarios -->
    <script src="{{ asset('js/usuarios/gestion-usuarios.js') }}"></script>
@endpush

@endsection