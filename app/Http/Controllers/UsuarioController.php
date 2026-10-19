<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;


class UsuarioController extends Controller
{
    /**
     * Descripción: Obtiene todos los usuarios registrados en el sistema, incluyendo los que tienen baja lógica.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la vista Blade con la lista de usuarios.
     */

    public function index()
    {
        // Obtener todos los usuarios, incluyendo los que tienen baja lógica (withTrashed()).
        $usuariosRegistrados = Usuario::withTrashed()->orderBy('id_usuario', 'desc')->get();

        return view('usuarios.GestionUsuario', compact('usuariosRegistrados'));
    }

    /**
     * Descripción: Muestra el formulario para registrar un nuevo usuario.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la vista Blade del formulario de registro.
     */
    public function create()
    {
        return view('usuarios.create');
    }

    /**
     * Descripción: Registra un nuevo usuario en el sistema.
     *
     * Entradas:
     * $request: contiene los datos enviados desde el formulario.
     *
     * Salidas: Redirige al listado con un mensaje de confirmación.
     */
    public function store(Request $solicitud)
    {
        // Obtener el correo ingresado para comprobar si ya está registrado.
        $correoUsuario = trim($solicitud->input('correo', ''));

        // Comprobar si el correo ya está registrado.
        if (
            $correoUsuario !== '' &&
            Usuario::where('correo', $correoUsuario)->exists()
        ) {
            return redirect()->back()->withInput()
                ->with('tipo_modal_usuario', 'error')
                ->with('mensaje_modal_usuario', 'No se puede registrar el usuario porque ese correo electrónico ya está registrado.');
        }

        // Validar los datos antes de guardar el usuario.
        $datosValidados = $solicitud->validate([
            'nombreUsuario' => 'required|string|max:60',
            'apellidoPaterno' => 'required|string|max:60',
            'apellidoMaterno' => 'nullable|string|max:60',
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuario', 'correo'),
            ],
            'password' => 'required|string|min:8',
            'rol' => 'required|integer|in:1,2,3',
            'estado' => 'required|integer|in:0,1',
        ], [
            'nombreUsuario.required' => 'El nombre del usuario es obligatorio.',
            'nombreUsuario.string' => 'El nombre del usuario debe ser texto.',
            'nombreUsuario.max' => 'El nombre no puede superar los 60 caracteres.',

            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.string' => 'El apellido paterno debe ser texto.',
            'apellidoPaterno.max' => 'El apellido paterno no puede superar los 60 caracteres.',

            'apellidoMaterno.string' => 'El apellido materno debe ser texto.',
            'apellidoMaterno.max' => 'El apellido materno no puede superar los 60 caracteres.',

            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'correo.max' => 'El correo no puede superar los 100 caracteres.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',

            'rol.required' => 'Debes seleccionar un rol.',
            'rol.integer' => 'El rol seleccionado no es válido.',
            'rol.in' => 'El rol seleccionado no es válido.',

            'estado.required' => 'Debes seleccionar el estado del usuario.',
            'estado.integer' => 'El estado seleccionado no es válido.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
        ]);

        // Encriptar la contraseña antes de guardar el usuario.
        $datosValidados['password'] = Hash::make($datosValidados['password']);

        // Registrar la fecha de creación del usuario.
        $datosValidados['fechaRegistro'] = now();

        // Guardar el nuevo usuario en la base de datos.
        $nuevoUsuario = Usuario::create($datosValidados);

        // Registrar que el usuario debe cambiar su contraseña al ingresar.
        Cache::store('file')->forever(
            'usuario_debe_cambiar_contrasena_' . $nuevoUsuario->id_usuario,
            true
        );

        // Regresar al listado y mostrar el modal de éxito.
        return redirect()
            ->route('usuarios.index')
            ->with('tipo_modal_usuario', 'registro')
            ->with('mensaje_modal_usuario', 'El usuario se registró correctamente.');
    }

    /**
     * Descripción: Obtiene la información de un usuario específico.
     *
     * Entradas:
     * $idUsuario: identificador del usuario que se desea consultar.
     *
     * Salidas: Retorna la vista Blade con los datos del usuario.
     */
    public function show($idUsuario)
    {
        // Buscar al usuario 
        $usuarioEncontrado = Usuario::withTrashed()->findOrFail($idUsuario);

        return view('usuarios.show', compact('usuarioEncontrado'));
    }

    /**
     * Descripción: Muestra el formulario para modificar un usuario existente.
     *
     * Entradas:
     * $idUsuario: identificador del usuario que se desea modificar.
     *
     * Salidas: Retorna la vista Blade con los datos del usuario.
     */
    public function edit($idUsuario)
    {
        // Buscar al usuario, incluyendo los que tienen baja lógica.
        $usuarioEncontrado = Usuario::withTrashed()->findOrFail($idUsuario);

        return view('usuarios.EditarUsuario', compact('usuarioEncontrado'));
    }

    /**
     * Descripción: Actualiza la información de un usuario existente.
     *
     * Entradas:
     * $datosUsuario: contiene los nuevos datos enviados desde el formulario.
     * $idUsuario: identificador del usuario que se desea modificar.
     *
     * Salidas: Redirige al listado con un mensaje de confirmación.
     */
    public function update(Request $datosUsuario, $idUsuario)
    {
        // Buscar al usuario, incluyendo los que tienen baja lógica.
        $usuarioEncontrado = Usuario::withTrashed()->findOrFail($idUsuario);

        // Validar los datos recibidos.
        $datosUsuarioActualizado = $datosUsuario->validate([
            'nombreUsuario' => 'required|string|max:60',
            'apellidoPaterno' => 'required|string|max:60',
            'apellidoMaterno' => 'nullable|string|max:60',
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuario', 'correo')
                    ->ignore($idUsuario, 'id_usuario'),
            ],
            'password' => 'nullable|string|min:8',
            'rol' => 'required|integer|in:1,2,3',
            'estado' => 'required|integer|in:0,1',
        ]);
    
        // Guardar el estado antes de procesar la contraseña.
        $estadoUsuario = (int) $datosUsuarioActualizado['estado'];

        // Conservar la contraseña actual si el campo está vacío.
        if (empty($datosUsuarioActualizado['password'])) {
            unset($datosUsuarioActualizado['password']);
        } else {
            // Encriptar la nueva contraseña.
            $datosUsuarioActualizado['password'] = Hash::make(
                $datosUsuarioActualizado['password']
            );
        }

        // No modificar la fecha de registro.
        unset($datosUsuarioActualizado['fechaRegistro']);

        // Actualizar la información del usuario.
        $usuarioEncontrado->update($datosUsuarioActualizado);

        // Sincronizar el estado con la baja lógica.
        if ($estadoUsuario === 1 && $usuarioEncontrado->trashed()) {
            $usuarioEncontrado->restore();
        } elseif ($estadoUsuario === 0 && !$usuarioEncontrado->trashed()) {
            // Realizar la baja lógica
            $usuarioEncontrado->delete();
        }

        return redirect()->back()
            ->with('tipo_modal_usuario', 'modificacion')
            ->with( 'mensaje_modal_usuario','Los datos del usuario se modificaron correctamente.');
    }


    /**
     * Descripción: Desactiva un usuario mediante una baja lógica.
     *
     * Entradas:
     * $idUsuario: identificador del usuario que se desea desactivar.
     *
     * Salidas: Redirige al listado con un mensaje de confirmación.
     */
    public function destroy($idUsuario)
    {
        $usuarioEncontrado = Usuario::findOrFail($idUsuario);

        $usuarioEncontrado->update([
            'estado' => 0
        ]);

        // Realizar la baja lógica mediante SoftDeletes.
        $usuarioEncontrado->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Descripción:  Realiza la baja lógica de una cuenta docente desde la gestión de docentes.
     *
     * Entradas:
     * $idUsuario: identificador de la cuenta del docente que se dará de baja.
     *
     * Salidas:
     * Redirige al listado de docentes con un mensaje de confirmación.
     */
    public function darBajaDocente($idUsuario)
    {
        // Comprueba que el usuario autenticado tenga permiso para dar de baja docentes.
        abort_unless(
            in_array((int) auth()->user()->rol, [1, 2], true),
            403,
            'No tienes permiso para dar de baja docentes.'
        );

        // Busca una cuenta activa que pertenezca al rol docente.
        $usuarioEncontrado = Usuario::where('rol', 3)
            ->where('estado', 1)
            ->findOrFail($idUsuario);

        // Cambia el estado de la cuenta para indicar que está inactiva.
        $usuarioEncontrado->update([
            'estado' => 0,
        ]);

        // Realiza la baja lógica sin eliminar definitivamente el registro.
        $usuarioEncontrado->delete();

        // Regresa al listado de docentes con un mensaje de confirmación.
        return redirect()
            ->route('docentes.index')
            ->with('success', 'El docente se dio de baja correctamente.');
    }
}