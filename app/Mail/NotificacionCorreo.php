<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionCorreo extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Descripción: Almacena el asunto del correo.
     */
    public $asunto;

    /**
     * Descripción: Almacena el mensaje del correo.
     */
    public $mensaje;

    /**
     * Descripción: Almacena los datos adicionales del correo.
     */
    public $datos;

    /**
     * Descripción: Inicializa la información que tendrá el correo.
     * Entradas: Recibe el asunto, mensaje y datos adicionales.
     * Salidas: Inicializa los atributos del Mailable.
     */
    public function __construct($asunto, $mensaje, $datos = [])
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->datos = $datos;
    }

    /**
     * Descripción: Define el asunto del correo electrónico.
     * Entradas: No recibe parámetros.
     * Salidas: Devuelve la configuración del asunto.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    /**
     * Descripción: Define la vista que se utilizará para el correo.
     * Entradas: No recibe parámetros.
     * Salidas: Devuelve la plantilla del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Correos.Notificacion',
        );
    }
}
