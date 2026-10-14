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
    public function store(Request $request)
    {
        // Validar los datos ingresados desde el formulario.
        $datosNuevoUsuario = $request->validate([
            'nombreUsuario' => 'required|string|max:60',
            'apellidoPaterno' => 'required|string|max:60',
            'apellidoMaterno' => 'nullable|string|max:60',
            'correo' => 'required|email|max:100|unique:usuario,correo',
            'password' => 'required|string|min:8',
            'rol' => 'required|integer|in:1,2,3',
            'estado' => 'required|integer|in:0,1',
        ]);

        // Encriptar contraseña
        $datosNuevoUsuario['password'] = Hash::make($datosNuevoUsuario['password']);


        $datosNuevoUsuario['fechaRegistro'] = now();

        // Guardar el nuevo usuario en la base de datos.
        $nuevoUsuario = Usuario::create($datosNuevoUsuario);

        // Registrar que el usuario debe cambiar su contraseña al ingresar.
        Cache::store('file')->forever(
            'usuario_debe_cambiar_contrasena_' . $nuevoUsuario->id_usuario,
            true
        );

        //return redirect()->route('usuarios.index') ->with('success', 'Usuario registrado correctamente.');
        return redirect()->route('usuarios.index')
            ->with('mensaje_modal_usuario', 'El usuario se registró correctamente.')
            ->with('tipo_modal_usuario', 'registro');
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
}