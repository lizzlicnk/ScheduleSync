<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CambiarContrasenaObligatoriaController extends Controller
{
    /**
     * Descripción: Muestra el formulario de cambio obligatorio.
     */
    public function mostrarFormulario()
    {
        $usuario = Auth::user();

        $claveCambioObligatorio =
            'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

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
     * Descripción: Actualiza la contraseña del usuario.
     */
    public function actualizarContrasena(Request $solicitud)
    {

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

        if (!Hash::check(
            $datosValidados['password_actual'],
            $usuario->password
        )) {
            return back()->withErrors([
                'password_actual' => 'La contraseña actual es incorrecta.',
            ]);
        }

        if (Hash::check(
            $datosValidados['password'],
            $usuario->password
        )) {
            return back()->withErrors([
                'password' =>
                    'La nueva contraseña debe ser diferente de la actual.',
            ]);
        }

        $usuario->password = Hash::make($datosValidados['password']);
        $usuario->save();

        $claveCambioObligatorio =
            'usuario_debe_cambiar_contrasena_' . $usuario->id_usuario;

        Cache::store('file')->forget($claveCambioObligatorio);

        $solicitud->session()->regenerate();

        return redirect()->route('contrasena.obligatoria')
            ->with(
                'mensaje_contrasena',
                'Tu contraseña se cambió correctamente.'
            );
    }
}

