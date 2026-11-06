
{{-- 
    COMPONENTE: SweetAlert2
    Descripción: Carga SweetAlert2 y muestra las alertas
    recibidas desde las variables de sesión de Laravel.
--}}

{{-- Librería SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Funciones genéricas de alertas --}}
<script src="{{ asset('js/componentes/sweet-alert.js') }}"></script>

{{-- Mensajes enviados desde Laravel --}}


@if (session('alerta_exito'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mostrarExito(
                @json(session('alerta_exito')),
                '¡Cambios guardados!',
                'Aceptar',
                '#E0A800'
            );
        });
    </script>
@endif

@if (session('alerta_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mostrarError(@json(session('alerta_error')));
        });
    </script>
@endif

@if (session('alerta_advertencia'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mostrarAdvertencia(@json(session('alerta_advertencia')));
        });
    </script>
@endif

@if (session('alerta_informacion'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mostrarInformacion(@json(session('alerta_informacion')));
        });
    </script>
@endif
