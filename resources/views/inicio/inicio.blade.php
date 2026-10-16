
@extends('layouts.DisenoPrincipal')

@section('titulo', 'Inicio | Schedule-Sync')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('css/inicio/inicio.css') }}">
@endpush

@section('contenido')

<div class="container-fluid px-3 px-md-4 py-3 py-md-4 pagina-inicio">

    <!-- Encabezado responsivo -->
    <div class="row align-items-center g-3 mb-4 encabezado-inicio">

        <div class="col-12 col-md">
            <p class="etiqueta-inicio">SCHEDULE-SYNC · UPEMOR</p>

            <h1 class="mb-2">¡Bienvenido!</h1>

            <p class="descripcion-inicio mb-0">
                Bienvenido al sistema de gestión de horarios académicos de UPEMOR.
            </p>
        </div>

        <!-- Botón de cerrar sesión -->
        <div class="col-12 col-md-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf

                <button type="submit"
                    class="btn boton-cerrar-sesion-inicio w-100 w-md-auto">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Panel de bienvenida responsivo -->
    <section class="card border-0 shadow-sm panel-bienvenida">
        <div class="card-body p-3 p-md-4">

            <div class="row align-items-center g-3">

                <div class="col-12 col-sm-auto text-center">
                    <div class="icono-bienvenida mx-auto">
                        <i class="fa-solid fa-house"></i>
                    </div>
                </div>

                <div class="col-12 col-sm">
                    <div class="texto-bienvenida">
                        <h2 class="h4">Inicio</h2>

                        <p class="mb-0">
                            Desde aquí podrás acceder a los módulos del sistema
                            para gestionar la información académica de UPEMOR.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

</div>

<!-- Indicador para mostrar el modal de permisos -->
@if (session('modal_sin_permisos'))
    <div id="aviso-permisos" data-mostrar="true"></div>
@endif

@endsection

