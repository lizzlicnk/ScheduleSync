<?php

namespace App\Providers;

use App\Models\Notificacion;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir las notificaciones con las vistas del sistema.
        View::composer('*', function ($vista) {

            // Obtener el usuario que inició sesión.
            $usuario = auth()->user();

            // Inicializar las variables.
            $notificaciones = collect();
            $cantidadNoLeidas = 0;

            // Consultar notificaciones únicamente para administradores
            // y personal administrativo.
            if ($usuario && in_array((int) $usuario->rol, [1, 2])) {

                $notificaciones = Notificacion::where(
                    'id_usuario',
                    $usuario->id_usuario
                )
                    ->where('estado', false)
                    ->orderByDesc('fechaEnvio')
                    ->get();

                // Obtener la cantidad de notificaciones pendientes.
                $cantidadNoLeidas = $notificaciones->count();
            }

            // Enviar las variables a las vistas.
            $vista->with([
                'notificaciones' => $notificaciones,
                'cantidadNoLeidas' => $cantidadNoLeidas
            ]);
        });
    }
}
