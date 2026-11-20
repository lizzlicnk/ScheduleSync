<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $asunto }}</title>
</head>

<body style="margin: 0; padding: 25px; background-color: #f4f1f7; font-family: Arial, sans-serif;">

    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 10px; overflow: hidden;">

        <div style="background-color: #5A3E7D; padding: 25px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0;">
                Schedule-Sync
            </h1>
        </div>

        <div style="padding: 30px; color: #333333;">

            <h2 style="color: #5A3E7D;">
                {{ $asunto }}
            </h2>

            <p style="font-size: 15px; line-height: 1.6;">
                {{ $mensaje }}
            </p>

            @if (!empty($datos))
                <div style="margin-top: 20px; padding: 20px; background-color: #f4f1f7; border-radius: 8px;">

                    @foreach ($datos as $nombre => $valor)

                        @if ($nombre === 'PIN de recuperación')

                            <div style="text-align: center; padding: 15px 0;">
                                <p style="font-size: 15px; color: #333333; margin: 0 0 10px;">
                                    PIN de recuperación
                                </p>

                                <p style="
                                    font-size: 40px;
                                    font-weight: bold;
                                    letter-spacing: 8px;
                                    color: #5A3E7D;
                                    margin: 0;
                                ">
                                    {{ $valor }}
                                </p>

                                <p style="font-size: 12px; color: #777777; margin: 10px 0 0;">
                                    Vigencia: 10 minutos
                                </p>
                            </div>

                        @elseif ($nombre !== 'Vigencia')

                            <p style="margin: 8px 0;">
                                <strong>{{ $nombre }}:</strong>
                                {{ $valor }}
                            </p>

                        @endif

                    @endforeach

                </div>
            @endif

            <p style="margin-top: 30px; font-size: 13px; color: #777777;">
                Este correo fue generado automáticamente por el sistema ScheduleSync.
                Por favor, no respondas a este mensaje.
            </p>

        </div>

        <div style="background-color: #f4f1f7; padding: 15px; text-align: center;">

            <p style="margin: 0; color: #777777; font-size: 12px;">
                Sistema de Gestión de Horarios Académicos
            </p>

        </div>

    </div>

</body>
</html>
