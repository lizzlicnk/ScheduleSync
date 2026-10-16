
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titulo', 'Schedule-Sync | UPEMOR')</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Estilos del sistema -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menu-lateral.css') }}">
    <link rel="stylesheet" href="{{ asset('css/componentes/modal.css') }}">

     <!-- Permite agregar estilos específicos desde cada vista. -->
    @stack('estilos')
    
</head>
<body>
    <!-- Agrupa el menú lateral y el contenido principal de la aplicación. -->
    <div class="app-layout">
        @include('components.MenuLateral')

        <main class="contenido-principal">

            <!-- Inserta el contenido definido por la vista actual. -->
            <div class="contenido-pagina">
                @yield('contenido')
            </div>

            <!-- Muestra la información institucional al final de la página. -->
            <footer class="pie-pagina">
                <strong>Schedule-Sync</strong>
                <p>Sistema de gestión de horarios académicos · UPEMOR</p>
                <p>© {{ date('Y') }} Universidad Politécnica del Estado de Morelos.</p>
            </footer>
        </main>
    </div>
    
     <!-- script controlar la inactividad de la sesión. -->
    <script src="{{ asset('js/inicio/sesion/control-inactividad.js') }}"></script>
</body>
</html>