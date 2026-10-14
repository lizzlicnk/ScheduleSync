
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titulo', 'ScheduleSync | UPEMOR')</title>

    {{-- Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Iconos de Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    {{-- Estilos del sistema --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menu-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/modal.css') }}">

    @stack('estilos')
    
</head>
<body>
    <div class="app-layout">
        @include('components.MenuLateral')

        <main class="contenido-principal">
            <div class="contenido-pagina">
                @yield('contenido')
            </div>

            <footer class="pie-pagina">
                <strong>ScheduleSync</strong>
                <p>Sistema de gestión de horarios · UPEMOR</p>
                <p>© {{ date('Y') }} Universidad Politécnica del Estado de Morelos.</p>
            </footer>
        </main>
    </div>
    
    <script src="{{ asset('js/inicio/sesion/control-inactividad.js') }}"></script>
</body>
</html>