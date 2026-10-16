<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
// Importar la clase para recibir y validar las solicitudes HTTP.
use Illuminate\Http\Request;
// Importar la herramienta para almacenar y consultar datos temporales en caché.
use Illuminate\Support\Facades\Cache;
// Importar la herramienta para generar y verificar hashes de contraseñas y PIN.
use Illuminate\Support\Facades\Hash;
// Importar la herramienta para enviar correos electrónicos.
use Illuminate\Support\Facades\Mail;
// Importar la herramienta para generar cadenas aleatorias utilizadas como tokens.
use Illuminate\Support\Str;
// Importar las reglas de validación para establecer requisitos de seguridad en contraseñas.
use Illuminate\Validation\Rules\Password;
// Importar la clase para capturar errores y excepciones.
use Throwable;

class RecuperarContrasenaController extends Controller
{
    /**
     * Descripción: Muestra el formulario para recuperar la contraseña mediante el correo electrónico registrado.
     *
     * Entradas: No recibe parámetros.
     *
     * Salidas: Retorna la vista del formulario de recuperación.
     */
    public function mostrarFormularioCorreo()
    {
        return view('auth.recuperarContrasena');
    }

    /**
     * Descripción: Valida el correo electrónico, genera un PIN de recuperación
     * y lo envía al usuario mediante correo electrónico.
     *
     * Entradas:
     * $solicitud: contiene el correo electrónico ingresado por el usuario
     *
     * Salidas: Redirige al formulario con un modal de error o éxito, según el resultado del envío del PIN.
     */
    public function enviarPin(Request $solicitud)
    {
        // Validar el formato y la longitud del correo electrónico.
        $datosValidados = $solicitud->validate([
            'correo' => ['required', 'email', 'max:255'],
        ]);

        // Normalizar el correo para realizar la búsqueda.
        $correoUsuario = strtolower(trim($datosValidados['correo']));

        // Limpia una recuperación anterior de la sesión.
        $solicitud->session()->forget([
            'correo_recuperacion',
            'token_recuperacion_verificada',
        ]);

        // Busca el correo en la tabla usuario.
        $usuario = Usuario::where('correo', $correoUsuario)
            ->where('estado', 1)
            ->first();

        // Identifica los datos temporales sin guardar el correo en la clave.
        $identificadorCorreo = hash('sha256', $correoUsuario);

        $clavePin = 'recuperacion_pin:' . $identificadorCorreo;
        $claveVerificacion = 'recuperacion_verificada:' . $identificadorCorreo;

        // Elimina los códigos anteriores.
        Cache::forget($clavePin);
        Cache::forget($claveVerificacion);

        // Si no existe una cuenta activa, mostrar el modal de error.
        if (!$usuario) {
            return redirect()->back()->withInput()->with('modal_tipo', 'error');
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
            // Elimina el PIN si falla el envío.
            Cache::forget($clavePin);

            // Registrar el error para facilitar su revisión.
            report($errorCorreo);

            return redirect()->back()->withInput()->with('error', 'No fue posible enviar el código. Inténtalo más tarde.');
        }

        // Guardar el correo para la siguiente etapa.
        $solicitud->session()->put('correo_recuperacion',$correoUsuario);

        return redirect()->back()->with('modal_tipo', 'correcto');
    }

    /**
     * Descripción: Muestra el formulario para ingresar el PIN de recuperación.
     *
     * Entradas:
     * $solicitud: permite consultar los datos guardados en la sesión.
     *
     * Salidas: Retorna la vista  para verificar el PIN o redirige
     * al formulario de recuperación si no existe un correo en la sesión.
     */
    public function mostrarFormularioPin(Request $solicitud)
    {
        // Verificar que el usuario haya solicitado un PIN previamente.
        if (!$solicitud->session()->has('correo_recuperacion')) {
            return redirect()->route('contrasena.correo');
        }

        return view('auth.verificarPin');
    }

    /**
     * Descripción: Verifica el PIN ingresado, controla su vigencia y limita
     * los intentos permitidos para validar la identidad del usuario.
     *
     * Entradas:
     * $solicitud: contiene el PIN ingresado y los datos de la sesión.
     *
     * Salidas: Redirige al formulario para establecer una nueva contraseña
     * cuando el PIN es válido, o devuelve un mensaje de error si no lo es.
     */
    public function verificarPin(Request $solicitud)
    {
        // Validar que el PIN contenga exactamente seis dígitos.
        $datosValidados = $solicitud->validate([
            'pin' => ['required', 'digits:6'],
        ]);

        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        // Regresar al inicio si no existe una recuperación.
        if (!$correoUsuario) {
            return redirect()->route('contrasena.correo');
        }

        // Obtener las claves utilizadas para consultar los datos temporales.
        $identificadorCorreo = hash('sha256', $correoUsuario);

        $clavePin = 'recuperacion_pin:' . $identificadorCorreo;
        $claveVerificacion = 'recuperacion_verificada:' . $identificadorCorreo;

        // Consultar los datos del PIN almacenados en caché.
        $datosPin = Cache::get($clavePin);

        // Comprobar que el PIN exista y no haya vencido.
        if (!$datosPin || now()->timestamp > $datosPin['expira_en']) {
            Cache::forget($clavePin);

            return redirect()->route('contrasena.correo')
                ->withErrors(['pin' => 'El PIN venció o ya no es válido. Solicita uno nuevo.',]);
        }

        // Bloquear el PIN cuando se alcanza el límite de cinco intentos.
        if ($datosPin['intentos'] >= 5) {
            Cache::forget($clavePin);

            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'pin' => 'Se alcanzó el límite de intentos. Solicita un PIN nuevo.',
                ]);
        }

        // Comparar el PIN ingresado con el hash almacenado.
        if (!Hash::check($datosValidados['pin'], $datosPin['codigo_hash'])) {
            $datosPin['intentos']++;

            // Calcular el tiempo que le queda al PIN antes de vencer.
            $segundosRestantes = max(
                1,
                $datosPin['expira_en'] - now()->timestamp
            );

             // Actualizar los datos del PIN sin extender su vigencia original.
            Cache::put(
                $clavePin,
                $datosPin,
                now()->addSeconds($segundosRestantes)
            );

            return back()->withErrors(['pin' => 'El PIN ingresado es incorrecto.',]);
        }

        // Invalidar el PIN para impedir su reutilización.
        Cache::forget($clavePin);

        // Generar un token aleatorio para autorizar el cambio de contraseña.
        $tokenVerificacion = Str::random(64);

        // Guardar el token de autorización durante cinco minutos.
        Cache::put(
            $claveVerificacion,
            $tokenVerificacion,
            now()->addMinutes(5)
        );

        // Guardar el token en la sesión para validar las siguientes solicitudes.
        $solicitud->session()->put(
            'token_recuperacion_verificada',
            $tokenVerificacion
        );

        return redirect()
            ->route('contrasena.formulario-nueva')
            ->with('mensaje', 'Identidad verificada. Ya puedes registrar tu nueva contraseña.');
    }

    /**
     * Descripción: Comprueba que la identidad del usuario haya sido verificada
     * y muestra el formulario para establecer una nueva contraseña.
     *
     * Entradas:
     * $solicitud: contiene el correo y el token de verificación de la sesión.
     *
     * Salidas: Retorna la vista de la nueva contraseña o redirige
     * al formulario de recuperación si la autorización no es válida.
     */
    public function mostrarFormularioNuevaContrasena(Request $solicitud)
    {
        // Verificar que exista una autorización vigente para cambiar la contraseña.
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
     * Descripción: Valida y actualiza la contraseña del usuario después
     * de verificar su identidad, y elimina los datos temporales de recuperación.
     *
     * Entradas:
     * $solicitud: contiene la nueva contraseña, su confirmación y los datos
     * de la sesión necesarios para identificar al usuario.
     *
     * Salidas: Actualiza la contraseña y redirige al inicio de sesión
     * con una señal para mostrar el modal de confirmación, o devuelve
     * un mensaje de error si la autorización o la cuenta no son válidas.
     */
    public function actualizarContrasena(Request $solicitud)
    {
        // Comprobar que la autorización para cambiar la contraseña siga vigente.
        if (!$this->recuperacionVerificada($solicitud)) {
            return redirect()
                ->route('contrasena.correo')
                ->withErrors([
                    'password' => 'La autorización venció. Solicita la recuperación nuevamente.',
                ]);
        }

        // Validar la contraseña y comprobar que cumpla los requisitos de seguridad.
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

        // Recuperar el correo de la cuenta que solicitó la recuperación.
        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        // Buscar la cuenta  asociada con el correo electrónico.
        $usuario = Usuario::where('correo', $correoUsuario)->where('estado', 1)->first();

         // Limpiar los datos temporales si la cuenta ya no está disponible.
        if (!$usuario) {
            $this->limpiarRecuperacion($solicitud);

            return redirect()
                ->route('contrasena.correo')
                ->withErrors(['correo' => 'No fue posible completar la recuperación.',]);
        }

        // Guardar la contraseña con el hash seguro de Laravel.
        $usuario->password = Hash::make($datosValidados['password']);
        $usuario->save();

        $this->limpiarRecuperacion($solicitud);
        
        // Redirigir al login y activar el modal de confirmación.
        return redirect()->route('login')->with('contrasena_actualizada', true);
    }

    /**
     * Descripción: Comprueba que el token de verificación de la sesión
     * coincida con el token almacenado en caché y que siga vigente.
     *
     * Entradas:
     * $solicitud: contiene el correo y el token de verificación de la sesión.
     *
     * Salidas: Retorna true si la autorización es válida o false
     * si faltan datos o los tokens no coinciden.
     */
    private function recuperacionVerificada(Request $solicitud): bool
    {
        // Recuperar el correo y el token de autorización de la sesión.
        $correoUsuario = $solicitud->session()->get('correo_recuperacion');
        $tokenSesion = $solicitud->session()->get('token_recuperacion_verificada');

         // Comprobar que ambos datos existan.
        if (!$correoUsuario || !$tokenSesion) {
            return false;
        }

        // Generar la clave para consultar el token almacenado en caché.
        $claveVerificacion = 'recuperacion_verificada:'
            . hash('sha256', $correoUsuario);

        $tokenAlmacenado = Cache::get($claveVerificacion);

        // Comparar los tokens para comprobar la autorización.
        return is_string($tokenAlmacenado) && hash_equals($tokenAlmacenado, $tokenSesion);
    }

    /**
     * Descripción: Elimina los datos temporales asociados con la recuperación
     * de contraseña y limpia la información correspondiente de la sesión.
     *
     * Entradas:
     * $solicitud: permite consultar y modificar los datos de la sesión.
     *
     * Salidas: No retorna ningún valor. 
     */
    private function limpiarRecuperacion(Request $solicitud): void
    {
        // Recuperar el correo asociado con la recuperación en curso.
        $correoUsuario = $solicitud->session()->get('correo_recuperacion');

        // Eliminar los datos temporales.
        if ($correoUsuario) {
            $identificadorCorreo = hash('sha256', $correoUsuario);

            Cache::forget('recuperacion_pin:' . $identificadorCorreo);
            Cache::forget('recuperacion_verificada:' . $identificadorCorreo);
        }

        // Limpiar los datos de recuperación guardados en la sesión.
        $solicitud->session()->forget([
            'correo_recuperacion',
            'token_recuperacion_verificada',
        ]);
    }
}
