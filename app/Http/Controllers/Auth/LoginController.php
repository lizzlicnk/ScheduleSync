<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /**
     * Descripción: Muestra el formulario de inicio de sesión.
     */
    public function mostrarFormulario()
    {
        return view('auth.login');
    }

    /**
 * Descripción: Valida las credenciales e inicia la sesión del usuario.
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
        $usuarioEncontrado = Usuario::where(
            'correo',
            $datosValidados['correo']
        )->first();

        // Verificar que exista y que su cuenta esté activa.
        if (
            !$usuarioEncontrado ||
            (int) $usuarioEncontrado->estado !== 1
        ) {
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
     * Descripción: Cierra la sesión del usuario actual.
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
