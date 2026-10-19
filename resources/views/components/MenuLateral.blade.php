<aside class="sidebar">

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
    <nav class="sidebar-menu" aria-label="Navegación principal">

        <!-- Inicio -->
        <a href="{{ route('inicio') }}"
            class="menu-item {{ request()->routeIs('inicio') ? 'activo' : '' }}">

            <i class="fa-solid fa-house"></i>
            <span>Inicio</span>
        </a>

        <!-- Gestión de usuarios -->
        @if ((int) auth()->user()->rol === 1)
            <a href="{{ route('usuarios.index') }}"
                class="menu-item {{ request()->routeIs('usuarios.*') ? 'activo' : '' }}">

                <i class="fa-solid fa-users"></i>
                <span>Usuarios</span>
            </a>
        @endif

        <!-- Mostrar una opción diferente según el rol del usuario -->
        @if ((int) auth()->user()->rol === 3)

            <!-- Información personal para docentes -->
            <a href="{{ url('/informacion-personal') }}"
                class="menu-item {{ request()->is('informacion-personal*') ? 'activo' : '' }}">

                <i class="fa-solid fa-user"></i>
                <span>Información personal</span>
            </a>

        @else

            <!-- Gestión de docentes para los demás roles -->
            <a href="{{ url('/docentes') }}"
                class="menu-item {{ request()->is('docentes*') ? 'activo' : '' }}">

                <i class="fa-solid fa-user"></i>
                <span>Docentes</span>
            </a>

        @endif

        <!-- Gestión de asignaturas -->
        <a href="{{ url('/asignaturas') }}"
            class="menu-item {{ request()->is('asignaturas*') ? 'activo' : '' }}">
            <i class="fa-solid fa-book-open"></i>
            <span>Asignaturas</span>
        </a>

        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/grupos') }}"
                class="menu-item {{ request()->is('grupos*') ? 'activo' : '' }}">
                <i class="fa-solid fa-people-group"></i>
                <span>Grupos</span>
            </a>
        @endif
        <!-- Gestión de carreras -->
        @if (in_array((int) auth()->user()->rol, [1, 2]))
            <a href="{{ url('/carreras') }}"
                class="menu-item {{ request()->is('carreras*') ? 'activo' : '' }}">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Carreras</span>
            </a>
        @endif

        <!-- Disponibilidad docente -->
        <a href="{{ url('/disponibilidad') }}"
            class="menu-item {{ request()->is('disponibilidad*') ? 'activo' : '' }}">
            <i class="fa-solid fa-clock"></i>
            <span>Disponibilidad docente</span>
        </a>

        <!-- Carga académica -->
        <a href="{{ url('/carga-academica') }}"
            class="menu-item {{ request()->is('carga-academica*') ? 'activo' : '' }}">
            <i class="fa-solid fa-clipboard"></i>
            <span>Carga académica</span>
        </a>

        <!-- Gestión de horarios -->
        <a href="{{ url('/horarios') }}"
            class="menu-item {{ request()->is('horarios*') ? 'activo' : '' }}">
            <i class="fa-solid fa-calendar"></i>
            <span>Horarios</span>
        </a>

        <!-- Generación de reportes -->
        <a href="{{ url('/reportes') }}"
            class="menu-item {{ request()->is('reportes*') ? 'activo' : '' }}">
            <i class="fa-solid fa-file"></i>
            <span>Reportes</span>
        </a>

        <!-- Notificaciones -->
        <a href="{{ url('/notificaciones') }}"
            class="menu-item {{ request()->is('notificaciones*') ? 'activo' : '' }}">
            <i class="fa-solid fa-bell"></i>
            <span>Notificaciones</span>
        </a>

    </nav>

    <!-- Información del usuario que inició sesión -->
    <div class="sidebar-usuario">
        <div class="usuario-icono">
            <i class="fa-solid fa-user"></i>
        </div>

        <div class="usuario-info">
            <p class="usuario-nombre">
                {{ auth()->user()->nombreUsuario ?? 'Usuario' }}  
                {{ auth()->user()->apellidoPaterno ?? '' }}
            </p>

            <!-- Mostrar el nombre del rol del usuario que inició sesión -->
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
        </div>
    </div>

</aside>
