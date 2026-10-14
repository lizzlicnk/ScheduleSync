<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Throwable;

class RecuperarContrasenaController extends Controller
{
    /**
     * Descripción: Muestra el formulario para recuperar la contraseña.
     */
    public function mostrarFormularioCorreo()
    {
        return view('auth.recuperarContrasena');
    }

    /**
     * Descripción: Valida el correo, genera el PIN y lo envía.
     * Entrada: Correo electrónico registrado.
     * Salida: Modal de error o modal de éxito al enviar el PIN.
     */
    public function enviarPin(Request $solicitud)
    {
        $datosValidados = $solicitud->validate([
            'correo' => ['required', 'email', 'max:255'],
        ]);

        $correoUsuario = strtolower(trim($datosValidados['correo']));

        // Limpiar una recuperación anterior de la sesión.
        $solicitud->session()->forget([
            'correo_recuperacion',
            'token_recuperacion_verificada',
        ]);

        // Buscar el correo en la tabla usuario.
        $usuario = Usuario::where('correo', $correoUsuario)
            ->where('estado', 1)
            ->first();

        // Identificar los datos temporales sin guardar el correo en la clave.
        $identificadorCorreo = hash('sha256', $correoUsuario);

        $clavePin = 'recuperacion_pin:' . $identificadorCorreo;
        $claveVerificacion = 'recuperacion_verificada:' . $identificadorCorreo;

        // Eliminar códigos anteriores.
        Cache::forget($clavePin);
        Cache::forget($claveVerificacion);

        // Si no existe una cuenta activa, mostrar el modal de error.
        if (!$usuario) {
            return redirect()
                ->back()
                ->withInput()
                ->with('modal_tipo', 'error');
        }

        // Generar un PIN numérico de seis dígitos.
        $codigoPin = (string) random_int(100000, 999999);

        // Guardar el hash del PIN durante diez minutos.
        Cache::put($clavePin, [
            'codigo_hash' => Hash::make($codigoPin),
            'expira_en' => now()->addMinutes(10)->timestamp,
            'intentos' => 0,
        ], now()->addMinutes(10));

//                                                                                              QUITAR
//                                                                                              QUITAR
//                                                                                              QUITAR
//                                                                                              QUITAR
//                                                                                              QUITAR
//                                                                                              QUITAR
//                                                                                              QUITAR
        if (app()->environment('local')) {
            // Guardar el correo para continuar con la recuperación.
            $solicitud->session()->put(
                'correo_recuperacion',
                $correoUsuario
            );

            // Mostrar el PIN temporalmente para las pruebas.
            return redirect()
                ->route('contrasena.verificar-pin')
                ->with('pin_prueba', $codigoPin);
        }
//                                                                                              HAST AQUI
//                                                                                              HAST AQUI
//                                                                                              HAST AQUI
//                                                                                              HAST AQUI
//                                                                                              HAST AQUI
        try {
            // Enviar el PIN al correo del usuario.
            Mail::raw(
                "Tu PIN para recuperar la contraseña de Schedule-Sync es: {$codigoPin}\n\n"
                . "Este código vence en 10 minutos. Si no solicitaste esta recuperación, "
                . "ignora este correo.",
                function ($mensajeCorreo) use ($correoUsuario) {
                    $mensajeCorreo
                        ->to($correoUsuario)
                        ->subject('PIN de recuperación | Schedule-Sync');
                }
            );
        } catch (Throwable $errorCorreo) {
            // Eliminar el PIN si falla el envío.
            Cache::forget($clavePin);

            report($errorCorreo);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No fue posible enviar el código. Inténtalo más tarde.');
        }

        // Guardar el correo para la siguiente etapa.
        $solicitud->session()->put(
            'correo_recuperacion',
            $correoUsuario
        );

        // Mostrar el modal de éxito después de enviar el correo.
        return redirect()
            ->back()
            ->with('modal_tipo', 'correcto');
    }

    /**
     * Descripción: Muestra el formulario para introducir el PIN.
     */
    public function mostrarFormularioPin(Request $solicitud)
    {
        if (!$solicitud->session()->has('correo_recuperacion')) {
            return redirect()->route('contrasena.correo');
        }

        return view('auth.verificarPin');
    }

    /**
     * Descripción: Verifica el PIN recibido.
     */
    public function verificarPin(Request $solicitud)
    {
        $datosValidados = $solicitud->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        if (!$correoUsuario) {
            return redirect()->route('contrasena.correo');
        }

        $identificadorCorreo = hash('sha256', $correoUsuario);

        $clavePin = 'recuperacion_pin:' . $identificadorCorreo;
        $claveVerificacion = 'recuperacion_verificada:' . $identificadorCorreo;

        $datosPin = Cache::get($clavePin);

        if (!$datosPin || now()->timestamp > $datosPin['expira_en']) {
            Cache::forget($clavePin);

            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'pin' => 'El PIN venció o ya no es válido. Solicita uno nuevo.',
                ]);
        }

        if ($datosPin['intentos'] >= 5) {
            Cache::forget($clavePin);

            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'pin' => 'Se alcanzó el límite de intentos. Solicita un PIN nuevo.',
                ]);
        }

        if (!Hash::check($datosValidados['pin'], $datosPin['codigo_hash'])) {
            $datosPin['intentos']++;

            $segundosRestantes = max(
                1,
                $datosPin['expira_en'] - now()->timestamp
            );

            Cache::put(
                $clavePin,
                $datosPin,
                now()->addSeconds($segundosRestantes)
            );

            return back()->withErrors([
                'pin' => 'El PIN ingresado es incorrecto.',
            ]);
        }

        // Invalidar el PIN para impedir su reutilización.
        Cache::forget($clavePin);

        // Crear una autorización temporal para cambiar la contraseña.
        $tokenVerificacion = Str::random(64);

        Cache::put(
            $claveVerificacion,
            $tokenVerificacion,
            now()->addMinutes(5)
        );

        $solicitud->session()->put(
            'token_recuperacion_verificada',
            $tokenVerificacion
        );

        return redirect()
            ->route('contrasena.formulario-nueva')
            ->with('mensaje', 'Identidad verificada. Ya puedes registrar tu nueva contraseña.');
    }

    /**
     * Descripción: Muestra el formulario para establecer una contraseña nueva.
     */
    public function mostrarFormularioNuevaContrasena(Request $solicitud)
    {
        if (!$this->recuperacionVerificada($solicitud)) {
            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'pin' => 'Primero debes verificar tu PIN.',
                ]);
        }

        return view('auth.nuevaContrasena');
    }

    /**
     * Descripción: Actualiza la contraseña y elimina los datos temporales.
     */
    public function actualizarContrasena(Request $solicitud)
    {
        if (!$this->recuperacionVerificada($solicitud)) {
            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'password' => 'La autorización venció. Solicita la recuperación nuevamente.',
                ]);
        }

        $datosValidados = $solicitud->validate(
            [
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->numbers(),
                ],
            ],
            [
                'password.required' => 'Debes escribir una contraseña nueva.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.letters' => 'La contraseña debe contener al menos una letra.',
                'password.numbers' => 'La contraseña debe contener al menos un número.',
            ]
        );

        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        $usuario = Usuario::where('correo', $correoUsuario)
            ->where('estado', 1)
            ->first();

        if (!$usuario) {
            $this->limpiarRecuperacion($solicitud);

            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'correo' => 'No fue posible completar la recuperación.',
                ]);
        }

        // Guardar la contraseña con el hash seguro de Laravel.
        $usuario->password = Hash::make($datosValidados['password']);
        $usuario->save();

        $this->limpiarRecuperacion($solicitud);
        
        // Redirigir al login y activar el modal de confirmación.
        return redirect()
            ->route('login')
            ->with('contrasena_actualizada', true);

        /* return redirect('/login')->with(
            'mensaje',
            'Tu contraseña se actualizó correctamente. Inicia sesión con tu nueva contraseña.'
        ); */
    }

    /**
     * Descripción: Comprueba que exista una autorización vigente.
     */
    private function recuperacionVerificada(Request $solicitud): bool
    {
        $correoUsuario = $solicitud->session()->get('correo_recuperacion');
        $tokenSesion = $solicitud->session()->get('token_recuperacion_verificada');

        if (!$correoUsuario || !$tokenSesion) {
            return false;
        }

        $claveVerificacion = 'recuperacion_verificada:'
            . hash('sha256', $correoUsuario);

        $tokenAlmacenado = Cache::get($claveVerificacion);

        return is_string($tokenAlmacenado)
            && hash_equals($tokenAlmacenado, $tokenSesion);
    }

    /**
     * Descripción: Elimina los datos temporales de recuperación.
     */
    private function limpiarRecuperacion(Request $solicitud): void
    {
        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        if ($correoUsuario) {
            $identificadorCorreo = hash('sha256', $correoUsuario);

            Cache::forget('recuperacion_pin:' . $identificadorCorreo);
            Cache::forget('recuperacion_verificada:' . $identificadorCorreo);
        }

        $solicitud->session()->forget([
            'correo_recuperacion',
            'token_recuperacion_verificada',
        ]);
    }
}
