<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

use App\Http\Middleware\VerificarAdministrador;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withMiddleware(function (Middleware $middleware): void {

        // Descripción: Registra el middleware para proteger el módulo de usuarios.
        // Entrada: Solicitud HTTP que necesita verificar el rol.
        // Salida: Permite utilizar el alias administrador en las rutas.
        $middleware->alias([
            'administrador' => VerificarAdministrador::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Descripción: Muestra un mensaje amigable cuando se solicitan demasiados códigos.
        // Entrada: La excepción generada por el límite de solicitudes y la petición HTTP.
        // Salida: Redirige al formulario de recuperación con un mensaje informativo.
        $exceptions->render(function (
            ThrottleRequestsException $exception,
            Request $request
        ) {
            if ($request->routeIs('contrasena.enviar-pin')) {
                return redirect()
                    ->route('contrasena.correo')
                    ->with(
                        'error',
                        'Has solicitado varios códigos. Espera un minuto antes de intentarlo nuevamente.'
                    );
            }

                if ($request->routeIs('contrasena.verificar-pin')) {
                    return redirect()
                        ->route('contrasena.formulario-pin')
                        ->withErrors([
                            'pin' => 'Has intentado verificar el PIN demasiadas veces. Espera un minuto antes de intentarlo nuevamente.',
                        ]);
                }

                return null;
        });

    })->create();
