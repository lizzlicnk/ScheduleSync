<?php

namespace App\Http\Middleware;

// Importar Closure para continuar con la ejecución de la solicitud.
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrevenirCachePrivada
{
    /**
     * Descripción: Evita que el navegador almacene páginas privadas.
     *
     * Entradas:
     * $solicitud: contiene la información de la solicitud HTTP recibida.
     * $siguiente: permite continuar con la ejecución de los siguientes
     * middleware o del controlador 
     *
     * Salida: Respuesta con encabezados que impiden guardar la página en caché.
     */
    public function handle(Request $solicitud, Closure $siguiente): Response {
        // Procesar la solicitud y obtener la respuesta.
        $respuesta = $siguiente($solicitud);

        // Evitar que el navegador almacene páginas privadas.
        $respuesta->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, private'
        );

        // Indicar que no se debe utilizar una copia almacenada.
        $respuesta->headers->set('Pragma', 'no-cache');

        // Marcar la respuesta como caducada.
        $respuesta->headers->set('Expires', '0');

        return $respuesta;
    }
}
