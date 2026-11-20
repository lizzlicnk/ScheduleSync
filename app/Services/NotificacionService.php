<?php

namespace App\Services;

use App\Mail\NotificacionCorreo;
use Illuminate\Support\Facades\Mail;

class NotificacionService
{
    /**
     * Descripción: Envía un correo electrónico utilizando la plantilla general.
     * Entradas:
     * - $destinatario: Correo electrónico que recibirá la notificación.
     * - $asunto: Asunto del correo.
     * - $mensaje: Contenido del correo.
     * - $datos: Información adicional que se mostrará en el mensaje.
     * Salidas: Envía el correo al destinatario indicado.
     */
    public function enviarCorreo($destinatario, $asunto, $mensaje, $datos = [])
    {
        Mail::to($destinatario)->send(
            new NotificacionCorreo($asunto, $mensaje, $datos)
        );
    }
}
