<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
// Importar la clase para recibir y validar solicitudes HTTP.
use Illuminate\Http\Request;
// Importar las herramientas para iniciar y cerrar sesiones de autenticación.
use Illuminate\Support\Facades\Auth;
// Importar la herramienta para verificar contraseñas mediante hashes.
use Illuminate\Support\Facades\Hash;
// Importar la herramienta para consultar los datos temporales almacenados en caché.
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /**
     * Descripción: Muestra el formulario de inicio de sesión.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la vista Blade del formulario de inicio de sesión.
     */
    public function mostrarFormulario()
    {
        return view('auth.login');
    }

    /**
     * Descripción: Valida las credenciales del usuario, verifica que su cuenta
     * esté activa e inicia la sesión correspondiente.
     *
     * Entradas:
     * $solicitud: contiene el correo electrónico y la contraseña ingresados
     * en el formulario de inicio de sesión.
     *
     * Salidas: Redirige al formulario con un mensaje de error si las
     * credenciales no son válidas, al cambio obligatorio de contraseña
     * si corresponde o a la página de inicio.
     */
    public function iniciarSesion(Request $solicitud)
    {
        // Validar los datos ingresados en el formulario.
        $datosValidados = $solicitud->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'correo.required' => 'Ingresa tu correo electrónico.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        // Buscar al usuario mediante su correo electrónico.
        $usuarioEncontrado = Usuario::where('correo', $datosValidados['correo'])->first();

        // Verificar que exista y que su cuenta esté activa.
        if (
            !$usuarioEncontrado ||
            (int) $usuarioEncontrado->estado !== 1
        ) {
            // Mostrar un mensaje de error y conservar únicamente el correo ingresado.
            return back()
                ->withErrors([
                    'correo' => 'Las credenciales son incorrectas o la cuenta está inactiva.',
                ])
                ->onlyInput('correo');
        }

        // Comprobar la contraseña con el hash almacenado.
        if (!Hash::check(
            $datosValidados['password'],
            $usuarioEncontrado->password
        )) {
            return back()
                ->withErrors([
                    'correo' => 'Las credenciales son incorrectas o la cuenta está inactiva.',
                ])
                ->onlyInput('correo');
        }

        // Validar que el usuario tenga un rol reconocido.
        if (!in_array((int) $usuarioEncontrado->rol, [1, 2, 3], true)) {
            return back()
                ->withErrors([
                    'correo' => 'El rol de la cuenta no está configurado correctamente.',
                ])
                ->onlyInput('correo');
        }

        // Iniciar la sesión del usuario.
        Auth::login($usuarioEncontrado);

        // Regenerar el identificador de sesión por seguridad.
        $solicitud->session()->regenerate();

        // Guardar la fecha y hora del último acceso.
        $usuarioEncontrado->ultimo_acceso = now();
        $usuarioEncontrado->save();

        // Verificar si el usuario debe cambiar su contraseña.
        $claveCambioObligatorio = 'usuario_debe_cambiar_contrasena_' . $usuarioEncontrado->id_usuario;

        // Redirigir al formulario de cambio obligatorio si existe la indicación.
        if (Cache::store('file')->has($claveCambioObligatorio)) {
            return redirect()->route('contrasena.obligatoria');
        }   

        // Redirige según el rol del usuario.
        switch ((int) $usuarioEncontrado->rol) {
            case 1:
                // Administrador: destino temporal para las pruebas.
                return redirect()->route('inicio');

            case 2:
                // Personal administrativo: destino temporal para las pruebas.
                return redirect()->route('inicio');

            case 3:
                // Docente: destino temporal para las pruebas.
                return redirect()->route('inicio');

            default:
                // Cierra la sesión si el rol no es válido.
                Auth::logout();

                $solicitud->session()->invalidate();
                $solicitud->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'correo' => 'El rol de la cuenta no está configurado correctamente.',
                ]);
        }

    }

    /**
     * Descripción: Cierra la sesión del usuario autenticado y elimina
     * los datos asociados con la sesión actual.
     *
     * Entradas:
     * $solicitud: permite acceder a la sesión que se desea invalidar.
     *
     * Salidas: Redirige al formulario de inicio de sesión después
     * de cerrar la sesión y generar un nuevo token de seguridad.
     */
    public function cerrarSesion(Request $solicitud)
    {
        Auth::logout();

        // Invalidar la sesión y generar un nuevo token de seguridad.
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return redirect()->route('login');
    }
}
