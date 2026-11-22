
<aside class="sidebar">

    <!-- Botón para abrir o cerrar el menú lateral -->
    <div class="sidebar-control">
        <button type="button" id="boton-menu-lateral" class="boton-menu-lateral" aria-label="Abrir menú lateral" 
            aria-expanded="false" aria-controls="menu-lateral">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <!-- Logo y nombre de la institución -->
    <div class="sidebar-logo">

        <div class="logo-icon">
            <i class="fa-regular fa-calendar-days"></i>
        </div>

        <div class="logo-text">
            <h2>UPEMOR</h2>
            <p>Universidad Politécnica del Estado de Morelos</p>
        </div>

    </div>

    <!-- Opciones de navegación vertical -->
    <nav class="sidebar-menu" id="menu-lateral" aria-label="Navegación principal">

        <!-- Inicio -->
        <a href="{{ route('inicio') }}" class="menu-item {{ request()->routeIs('inicio') ? 'activo' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>

        <!-- Gestión de usuarios -->
        @if ((int) auth()->user()->rol === 1)
            <a href="{{ route('usuarios.index') }}" class="menu-item {{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Usuarios</span>
            </a>
        @endif

        <!-- Opciones según el rol -->
        @if ((int) auth()->user()->rol === 3)
            <a href="{{ url('/informacion-personal') }}" class="menu-item {{ request()->is('informacion-personal*') ? 'activo' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>Información personal</span>
            </a>
        @else
            <a href="{{ url('/docentes') }}" class="menu-item {{ request()->is('docentes*') ? 'activo' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>Docentes</span>
            </a>
        @endif

        <!-- Gestión de asignaturas -->
        <a href="{{ url('/asignaturas') }}" class="menu-item {{ request()->is('asignaturas*') ? 'activo' : '' }}">
            <i class="fa-solid fa-book-open"></i>
            <span>Asignaturas</span>
        </a>

        <!-- Grupos -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/grupos') }}" class="menu-item {{ request()->is('grupos*') ? 'activo' : '' }}">
                <i class="fa-solid fa-people-group"></i>
                <span>Grupos</span>
            </a>
        @endif

        <!-- Carreras -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/carreras') }}" class="menu-item {{ request()->is('carreras*') ? 'activo' : '' }}">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Carreras</span>
            </a>
        @endif

        <!-- Disponibilidad docente -->
        <a href="{{ url('/disponibilidad-academica') }}" class="menu-item {{ request()->is('disponibilidad-academica*') ? 'activo' : '' }}">
            <i class="fa-solid fa-clock"></i>
            <span>Disponibilidad académica</span>
        </a>

        <!-- Carga académica -->
        <a href="{{ url('/carga-academica') }}" class="menu-item {{ request()->is('carga-academica*') ? 'activo' : '' }}">
            <i class="fa-solid fa-clipboard"></i>
            <span>Carga académica</span>
        </a>

        <!-- Gestión de horarios -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))

            <a href="{{ route('horarios.inicio') }}" class="menu-item {{ request()->routeIs('horarios.inicio') ? 'activo' : '' }}">
                <i class="fa-solid fa-calendar"></i>
                <span>Horarios</span>
            </a>

            <!-- Submenú de horarios -->
            @if (request()->routeIs('datos-generales.*', 'creacion-horario.*'))

                <div class="submenu-horarios">

                    <a href="{{ route('datos-generales.index') }}" class="submenu-item {{ request()->routeIs('datos-generales.*') ? 'activo' : '' }}">
                        <i class="fa-solid fa-calendar-plus"></i>
                        <span>Datos generales</span>
                    </a>

                    <a href="{{ route('creacion-horario.index') }}" class="submenu-item {{ request()->routeIs('creacion-horario.*') ? 'activo' : '' }}">
                        <i class="fa-solid fa-calendar-week"></i>
                        <span>Gestionar horario</span>
                    </a>

                </div>
            @endif
        @endif

        <!-- Reportes -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/reportes') }}" class="menu-item {{ request()->is('reportes*') ? 'activo' : '' }}">
                <i class="fa-solid fa-file"></i>
                <span>Reportes</span>
            </a>
        @endif

        <!-- Notificaciones -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/notificaciones') }}" class="menu-item {{ request()->is('notificaciones*') ? 'activo' : '' }}">
                <i class="fa-solid fa-bell"></i>
                <span>Notificaciones</span>
            </a>
        @endif

        <!-- Respaldo y restauración de la base de datos -->
        @if ((int) auth()->user()->rol === 1)
            <a href="{{ route('respaldo.index') }}"
                class="menu-item {{ request()->routeIs('respaldo.*') ? 'activo' : '' }}">

                <i class="fa-solid fa-database"></i>
                <span>Respaldo y restauración</span>

            </a>
        @endif
        
    </nav>

    <!-- Información del usuario -->
    <div class="sidebar-usuario">

        <div class="usuario-icono">
            <i class="fa-solid fa-user"></i>
        </div>

        <div class="usuario-info">

            <p class="usuario-nombre">
                {{ auth()->user()->nombreUsuario ?? 'Usuario' }}
                {{ auth()->user()->apellidoPaterno ?? '' }}
            </p>

            <p class="usuario-rol">
                @switch(auth()->user()->rol ?? null)

                    @case(1)
                        Administrador
                        @break

                    @case(2)
                        Personal administrativo
                        @break

                    @case(3)
                        Docente
                        @break

                    @default
                        Sin rol asignado

                @endswitch
            </p>

            <!-- Cerrar sesión -->
            <form action="{{ route('logout') }}" method="POST" class="form-cerrar-sesion-sidebar">
                @csrf

                <button type="submit"
                    class="boton-cerrar-sesion-sidebar">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Cerrar sesión</span>

                </button>
            </form>
        </div>
    </div>
</aside>
