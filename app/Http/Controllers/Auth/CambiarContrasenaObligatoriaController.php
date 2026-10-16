<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
// Importar la clase para recibir y validar solicitudes HTTP.
use Illuminate\Http\Request;
// Importar las herramientas para consultar al usuario autenticado y administrar su sesión.
use Illuminate\Support\Facades\Auth;
// Importar la herramienta para consultar y eliminar datos temporales en caché.
use Illuminate\Support\Facades\Cache;
// Importar la herramienta para verificar y generar hashes de contraseñas.
use Illuminate\Support\Facades\Hash;
// Importar las reglas de validación para establecer requisitos de seguridad en contraseñas.
use Illuminate\Validation\Rules\Password;

class CambiarContrasenaObligatoriaController extends Controller
{
    /**
     * Descripción: Muestra el formulario para realizar el cambio obligatorio 
     * de contraseña del usuario autenticado.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la vista Blade del formulario de cambio de contraseña
     * o redirige a la página de inicio si no existe un cambio pendiente
     */
    public function mostrarFormulario()
    {
        // Obtener los datos del usuario que inició sesión.
        $usuario = Auth::user();

        // Generar la clave de caché que identifica el cambio de contraseña.
        $claveCambioObligatorio = 'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

        // Permitir mostrar el formulario si existe el mensaje de éxito.
        if (
            !Cache::store('file')->has($claveCambioObligatorio)
            && !session()->has('mensaje_contrasena')
        ) {
            return redirect()->route('inicio');
        }

        return view('auth.cambiar-contrasena-obligatoria');
    }


    /**
     * Descripción: Valida la contraseña actual y los datos de la nueva contraseña,
     * actualiza la contraseña del usuario y elimina la indicación de cambio obligatorio.
     *
     * Entradas:
     * $solicitud: contiene la contraseña actual, la nueva contraseña
     * y su confirmación
     *
     * Salidas: Redirige al formulario con un mensaje de error si la validación
     * falla o si la contraseña actual es incorrecta. Si el cambio se completa,
     * redirige al formulario con un mensaje de confirmación.
     */
    public function actualizarContrasena(Request $solicitud)
    {
        // Validar la contraseña actual y los requisitos de la nueva contraseña.
        $datosValidados = $solicitud->validate(
            [
                'password_actual' => [
                    'required',
                    'string',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->numbers(),
                ],
            ],
            [
                'password_actual.required' => 'Ingresa tu contraseña actual.',
                'password.required' => 'Debes escribir una contraseña nueva.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.letters' => 'La contraseña debe contener al menos una letra.',
                'password.numbers' => 'La contraseña debe contener al menos un número.',
            ]
        );

        $usuario = Auth::user();

        // Comprobar que la contraseña actual ingresada coincida con la almacenada.
        if (!Hash::check(
            $datosValidados['password_actual'],
            $usuario->password
        )) {
            return back()->withErrors([
                'password_actual' => 'La contraseña actual es incorrecta.',
            ]);
        }

         // Verificar que la nueva contraseña sea diferente de la actual.
        if (Hash::check(
            $datosValidados['password'],
            $usuario->password
        )) {
            return back()->withErrors([
                'password' =>
                    'La nueva contraseña debe ser diferente de la actual.',
            ]);
        }
        // Encriptar y guardar la nueva contraseña.
        $usuario->password = Hash::make($datosValidados['password']);
        $usuario->save();

        $claveCambioObligatorio =
            'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

        // Eliminar la indicación de cambio obligatorio.
        Cache::store('file')->forget($claveCambioObligatorio);

        // Regenerar el identificador de sesión.
        $solicitud->session()->regenerate();

        return redirect()->route('contrasena.obligatoria')
            ->with(
                'mensaje_contrasena',
                'Tu contraseña se cambió correctamente.'
            );
    }
}

