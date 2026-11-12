<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RecuperarContrasenaController;
use App\Http\Controllers\Auth\CambiarContrasenaObligatoriaController;
use App\Http\Middleware\ForzarCambioContrasena;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CargaAcademicaController;
use App\Http\Controllers\DisponibilidadProfesorController;
use App\Http\Controllers\DatosGeneralesController;
use App\Http\Controllers\HorarioController;

                    /* Rutas de inicio de sesión */

// Mostrar el formulario de inicio de sesión.
Route::get('/login', [
    LoginController::class,
    'mostrarFormulario'
])->name('login');

// Procesar las credenciales del usuario.
Route::post('/login', [
    LoginController::class,
    'iniciarSesion'
])->name('login.procesar');

// Cerrar la sesión.
Route::post('/logout', [
    LoginController::class,
    'cerrarSesion'
])->middleware('auth')->name('logout');

                    /*Recuperación de contraseña */

// Mostrar el formulario para solicitar el PIN por correo.
Route::get('/recuperar-contrasena', [
    RecuperarContrasenaController::class,
    'mostrarFormularioCorreo'
])->name('contrasena.correo');

// Enviar el PIN de recuperación al correo del usuario.
Route::post('/recuperar-contrasena', [
    RecuperarContrasenaController::class,
    'enviarPin'
])->middleware('throttle:3,1')->name('contrasena.enviar-pin');

// Mostrar el formulario para verificar el PIN.
Route::get('/verificar-pin', [
    RecuperarContrasenaController::class,
    'mostrarFormularioPin'
])->name('contrasena.formulario-pin');

// Validar el PIN ingresado por el usuario.
Route::post('/verificar-pin', [
    RecuperarContrasenaController::class,
    'verificarPin'
])->middleware('throttle:5,1')->name('contrasena.verificar-pin');

// Mostrar el formulario para establecer una nueva contraseña.
Route::get('/nueva-contrasena', [
    RecuperarContrasenaController::class,
    'mostrarFormularioNuevaContrasena'
])->name('contrasena.formulario-nueva');

// Guardar la nueva contraseña del usuario.
Route::post('/nueva-contrasena', [
    RecuperarContrasenaController::class,
    'actualizarContrasena'
])->middleware('throttle:5,1')->name('contrasena.actualizar');

                    /* Rutas para el control de actividad de la sesión */

// Actualizar la actividad de la sesión autenticada.
Route::post('/sesion/actividad', function () {
    return response()->noContent();
})->middleware('auth')->name('sesion.actividad');

                    /* Rutas para el cambio obligatorio de contraseña */

// Formulario de cambio obligatorio de contraseña.
Route::get('/cambiar-contrasena-obligatoria', [
    CambiarContrasenaObligatoriaController::class,
    'mostrarFormulario'
])->middleware('auth')->name('contrasena.obligatoria');

// Guardar la nueva contraseña.
Route::post('/cambiar-contrasena-obligatoria', [
    CambiarContrasenaObligatoriaController::class,
    'actualizarContrasena'
])->middleware('auth')
  ->middleware('throttle:5,1')
  ->name('contrasena.obligatoria.actualizar');

                    /* Rutas disponibles para usuarios que iniciaron sesión */
                    
 // Rutas disponibles para usuarios que iniciaron sesión.
Route::middleware(['auth', \App\Http\Middleware\PrevenirCachePrivada::class, ForzarCambioContrasena::class ])->group(function () {

    // Mostrar la página principal del sistema.
    Route::get('/inicio', function () {
        return view('inicio.inicio');
    })->name('inicio');

    // Rutas exclusivas para el Administrador.
    Route::middleware([\App\Http\Middleware\VerificarAdministrador::class])->group(function () {

                    /* Rutas para la gestión de usuarios */

        // Mostrar la gestión de usuarios.
        Route::get('/usuarios', [
            UsuarioController::class,
            'index'
        ])->name('usuarios.index');

        // Mostrar el formulario para registrar un usuario.
        Route::get('/usuarios/crear', [
            UsuarioController::class,
            'create'
        ])->name('usuarios.create');

        // Guardar un usuario nuevo.
        Route::post('/usuarios', [
            UsuarioController::class,
            'store'
        ])->name('usuarios.store');

        // Consultar los datos de un usuario.
        Route::get('/usuarios/{usuario}', [
            UsuarioController::class,
            'show'
        ])->whereNumber('usuario')->name('usuarios.show');

        // Mostrar el formulario para modificar un usuario.
        Route::get('/usuarios/{usuario}/editar', [
            UsuarioController::class,
            'edit'
        ])->whereNumber('usuario')->name('usuarios.edit');

        // Actualizar los datos de un usuario.
        Route::put('/usuarios/{usuario}', [
            UsuarioController::class,
            'update'
        ])->whereNumber('usuario')->name('usuarios.update');

        // Desactivar un usuario.
        Route::delete('/usuarios/{usuario}', [
            UsuarioController::class,
            'destroy'
        ])->whereNumber('usuario')->name('usuarios.destroy');

    });
                    /* Rutas para la gestión de docentes */

    // Mostrar el listado de docentes.
    Route::get('/docentes', [
        DocenteController::class,
        'index'
    ])->name('docentes.index');

    // Mostrar la vista para registrar un docente.
    Route::get('/docentes/crear', [
        DocenteController::class,
        'create'
    ])->name('docentes.create');

    // Guardar un nuevo docente.
    Route::post('/docentes', [
        DocenteController::class,
        'store'
    ])->name('docentes.store');

    // Consultar un docente específico.
    Route::get('/docentes/{idDocente}', [
        DocenteController::class,
        'show'
    ])->whereNumber('idDocente')
      ->name('docentes.show');

    // Mostrar el formulario para editar un docente.
    Route::get('/docentes/{idDocente}/editar', [
        DocenteController::class,
        'edit'
    ])->whereNumber('idDocente')
      ->name('docentes.edit');

    // Actualizar la información del docente.
    Route::put('/docentes/{idDocente}', [
        DocenteController::class,
        'update'
    ])->whereNumber('idDocente')
      ->name('docentes.update');

      // Dar de baja a un docente desde la gestión de docentes.
    Route::delete('/docentes/{idUsuario}/baja', [
        UsuarioController::class,
        'darBajaDocente'
    ])
        ->whereNumber('idUsuario')
        ->name('docentes.baja');

    // Redirige la ruta de información personal al módulo de docentes.
    Route::redirect('/informacion-personal', '/docentes')
        ->name('informacion-personal');

                    /* Rutas para la gestión de carreras */

    // Mostrar el listado de carreras.
    Route::get('/carreras', [
        CarreraController::class,
        'index'
    ])->name('carreras.index');

    // Mostrar la vista para registrar una carrera.
    Route::get('/carreras/crear', [
        CarreraController::class,
        'create'
    ])->name('carreras.create');

    // Guardar una nueva carrera.
    Route::post('/carreras', [
        CarreraController::class,
        'store'
    ])->name('carreras.store');

    // Consultar una carrera específica.
    Route::get('/carreras/{idCarrera}', [
        CarreraController::class,
        'show'
    ])->whereNumber('idCarrera')
    ->name('carreras.show');

    // Mostrar el formulario para editar una carrera.
    Route::get('/carreras/{idCarrera}/editar', [
        CarreraController::class,
        'edit'
    ])->whereNumber('idCarrera')
    ->name('carreras.edit');

    // Actualizar la información de una carrera.
    Route::put('/carreras/{idCarrera}', [
        CarreraController::class,
        'update'
    ])->whereNumber('idCarrera')
    ->name('carreras.update');

    // Desactivar una carrera mediante borrado lógico.
    Route::delete('/carreras/{idCarrera}/baja', [
        CarreraController::class,
        'destroy'
    ])->whereNumber('idCarrera')
    ->name('carreras.baja');

    // Activar nuevamente una carrera.
    Route::put('/carreras/{idCarrera}/activar', [
        CarreraController::class,
        'activar'
    ])->whereNumber('idCarrera')
    ->name('carreras.activar');

                        /* Rutas para la tabla de Periodo 

    // Consultar los periodos registrados.
    Route::get('/periodos', [PeriodoController::class, 'index'])
        ->name('periodos.index');

    // Registrar los tres periodos de un año.
    Route::post('/periodos', [PeriodoController::class, 'store'])
        ->name('periodos.store');*/

                        /* Rutas para la gestión de grupos */

    // Mostrar el listado de grupos.
    Route::get('/grupos', [
        GrupoController::class,
        'index'
    ])->name('grupos.index');

    // Mostrar el formulario para registrar un grupo.
    Route::get('/grupos/crear', [
        GrupoController::class,
        'create'
    ])->name('grupos.create');

    // Guardar un grupo nuevo.
    Route::post('/grupos', [
        GrupoController::class,
        'store'
    ])->name('grupos.store');

    // Consultar los datos de un grupo específico.
    Route::get('/grupos/{idGrupo}', [
        GrupoController::class,
        'show'
    ])->whereNumber('idGrupo')
    ->name('grupos.show');

    // Mostrar el formulario para modificar un grupo.
    Route::get('/grupos/{idGrupo}/editar', [
        GrupoController::class,
        'edit'
    ])->whereNumber('idGrupo')
    ->name('grupos.edit');

    // Actualizar la información de un grupo.
    Route::put('/grupos/{idGrupo}', [
        GrupoController::class,
        'update'
    ])->whereNumber('idGrupo')
    ->name('grupos.update');

    // Eliminar un grupo
    Route::delete('/grupos/{idGrupo}', [
        GrupoController::class,
        'destroy'
    ])->whereNumber('idGrupo')
    ->name('grupos.destroy');

                        /* Rutas para la gestión de asignaturas */

    // Mostrar el listado de asignaturas.
    Route::get('/asignaturas', [
        AsignaturaController::class,
        'index'
    ])->name('asignaturas.index');

    // Mostrar el formulario para registrar una asignatura.
    Route::get('/asignaturas/crear', [
        AsignaturaController::class,
        'create'
    ])->name('asignaturas.create');

    // Guardar una asignatura nueva.
    Route::post('/asignaturas', [
        AsignaturaController::class,
        'store'
    ])->name('asignaturas.store');

    // Consultar los datos de una asignatura específica.
    Route::get('/asignaturas/{idAsignatura}', [
        AsignaturaController::class,
        'show'
    ])->whereNumber('idAsignatura')
    ->name('asignaturas.show');

    // Mostrar el formulario para modificar una asignatura.
    Route::get('/asignaturas/{idAsignatura}/editar', [
        AsignaturaController::class,
        'edit'
    ])->whereNumber('idAsignatura')
    ->name('asignaturas.edit');

    // Actualizar la información de una asignatura.
    Route::put('/asignaturas/{idAsignatura}', [
        AsignaturaController::class,
        'update'
    ])->whereNumber('idAsignatura')
    ->name('asignaturas.update');

    // Dar de baja una asignatura.
    Route::delete('/asignaturas/{idAsignatura}', [
        AsignaturaController::class,
        'destroy'
    ])->whereNumber('idAsignatura')
    ->name('asignaturas.destroy');

    // Reactivar una asignatura inactiva.
    Route::patch('/asignaturas/{idAsignatura}/activar', [
        AsignaturaController::class,
        'activar'
    ])->whereNumber('idAsignatura')
    ->name('asignaturas.activar');

                        /* Rutas para la gestión de la carga académica */
    // Mostrar la página de gestión de carga académica.
    Route::get('/carga-academica', [
        CargaAcademicaController::class,
        'index'
    ])->name('carga-academica.index');

    // Mostrar el formulario para modificar la carga académica de un docente.
    Route::get('/carga-academica/{idDocente}/editar', [
        CargaAcademicaController::class,
        'edit'
    ])->whereNumber('idDocente')
    ->name('carga-academica.edit');

    // Actualizar la carga académica del docente.
    Route::put('/carga-academica/{idDocente}', [
        CargaAcademicaController::class,
        'update'
    ])->whereNumber('idDocente')
    ->name('carga-academica.update');

    // Eliminar la carga académica de un docente.
    Route::delete('/carga-academica/{idDocente}', [
        CargaAcademicaController::class,
        'destroy'
    ])->whereNumber('idDocente')
    ->name('carga-academica.destroy');

                        /* Rutas para la gestión de la disponibilidad académica */

    // Mostrar la página de gestión de disponibilidad académica.
    Route::get('/disponibilidad-academica', [
        DisponibilidadProfesorController::class,
        'index'
    ])->name('disponibilidad-academica.index');

    // Mostrar el formulario para registrar la disponibilidad de un docente.
    Route::get('/disponibilidad-academica/crear/{idDocente?}', [
        DisponibilidadProfesorController::class,
        'create'
    ])->whereNumber('idDocente')
    ->name('disponibilidad-academica.create');

    // Registrar la disponibilidad horaria de un docente.
    Route::post('/disponibilidad-academica/registrar/{idDocente?}', [
        DisponibilidadProfesorController::class,
        'store'
    ])->whereNumber('idDocente')
    ->name('disponibilidad-academica.store');

    // Consultar la disponibilidad de un docente en un periodo académico.
    Route::get('/disponibilidad-academica/{idDocente}/{idPeriodo}', [
        DisponibilidadProfesorController::class,
        'show'
    ])->whereNumber('idDocente')
    ->whereNumber('idPeriodo')
    ->name('disponibilidad-academica.show');

    // Mostrar el formulario para modificar la disponibilidad de un docente.
    Route::get('/disponibilidad-academica/{idDocente}/{idPeriodo}/editar', [
        DisponibilidadProfesorController::class,
        'edit'
    ])->whereNumber('idDocente')
    ->whereNumber('idPeriodo')
    ->name('disponibilidad-academica.edit');

    // Actualizar la disponibilidad horaria de un docente.
    Route::put('/disponibilidad-academica/{idDocente}/{idPeriodo}', [
        DisponibilidadProfesorController::class,
        'update'
    ])->whereNumber('idDocente')
    ->whereNumber('idPeriodo')
    ->name('disponibilidad-academica.update');

    // Eliminar la disponibilidad de un docente en un periodo académico.
    Route::delete('/disponibilidad-academica/{idDocente}/{idPeriodo}', [
        DisponibilidadProfesorController::class,
        'destroy'
    ])->whereNumber('idDocente')
    ->whereNumber('idPeriodo')
    ->name('disponibilidad-academica.destroy'); 

                            /* Rutas de horarios académicos */

    // Mostrar la página principal del módulo de horarios.
    Route::get('/horarios', [
        HorarioController::class,
        'inicio'
    ])->name('horarios.inicio');

                            /* Rutas para la gestión de datos generales de los horarios académicos */

    // Mostrar los datos generales de los horarios registrados.
    Route::get('/horarios/datos-generales', [
        DatosGeneralesController::class,
        'index'
    ])->name('datos-generales.index');

    // Mostrar el formulario para registrar los datos generales de un horario.
    Route::get('/horarios/datos-generales/crear', [
        DatosGeneralesController::class,
        'create'
    ])->name('datos-generales.create');

    // Registrar los datos generales de un horario.
    Route::post('/horarios/datos-generales/registrar', [
        DatosGeneralesController::class,
        'store'
    ])->name('datos-generales.store');

    // Consultar la información general de un horario.
    Route::get('/horarios/datos-generales/{idHorario}', [
        DatosGeneralesController::class,
        'show'
    ])->whereNumber('idHorario')
        ->name('datos-generales.show');

    // Mostrar el formulario para modificar los datos generales de un horario.
    Route::get('/horarios/datos-generales/{idHorario}/editar', [
        DatosGeneralesController::class,
        'edit'
    ])->whereNumber('idHorario')
        ->name('datos-generales.edit');

    // Actualizar los datos generales de un horario.
    Route::put('/horarios/datos-generales/{idHorario}', [
        DatosGeneralesController::class,
        'update'
    ])->whereNumber('idHorario')
        ->name('datos-generales.update');

    // Desactivar un horario mediante borrado lógico.
    Route::delete('/horarios/datos-generales/{idHorario}', [
        DatosGeneralesController::class,
        'destroy'
    ])->whereNumber('idHorario')
        ->name('datos-generales.destroy');

    // Reactivar un horario.
    Route::patch('/horarios/datos-generales/{idHorario}/activar', [
        DatosGeneralesController::class,
        'activar'
    ])->whereNumber('idHorario')
        ->name('datos-generales.activar');

                            /* Rutas para la gestión de horarios académicos */

    // Mostrar la página de datos generales de los horarios.
    /*
    Route::get('/horarios/datos-generales', [
        HorarioController::class,
        'index'
    ])->name('horarios.index');

    // Mostrar el formulario para registrar un nuevo horario.
    Route::get('/horarios/crear', [
        HorarioController::class,
        'create'
    ])->name('horarios.create');

    // Mostrar la página de gestión de horarios académicos.
    
    Route::get('/horarios', [
        HorarioController::class,
        'index'
    ])->name('horarios.index');

    // Mostrar el formulario para registrar un nuevo horario.
    Route::get('/horarios/crear', [
        HorarioController::class,
        'create'
    ])->name('horarios.create');

    

    // Registrar un nuevo horario académico.
    Route::post('/horarios/registrar', [
        HorarioController::class,
        'store'
    ])->name('horarios.store');

    // Consultar la información de un horario académico.
    Route::get('/horarios/{idHorario}', [
        HorarioController::class,
        'show'
    ])->whereNumber('idHorario')
    ->name('horarios.show');

    // Mostrar el formulario para modificar un horario académico.
    Route::get('/horarios/{idHorario}/editar', [
        HorarioController::class,
        'edit'
    ])->whereNumber('idHorario')
    ->name('horarios.edit');

    // Actualizar la información de un horario académico.
    Route::put('/horarios/{idHorario}', [
        HorarioController::class,
        'update'
    ])->whereNumber('idHorario')
    ->name('horarios.update');

    // Desactivar un horario académico.
    Route::delete('/horarios/{idHorario}', [
        HorarioController::class,
        'destroy'
    ])->whereNumber('idHorario')
    ->name('horarios.destroy');

    // Reactivar un horario académico.
    Route::patch('/horarios/{idHorario}/activar', [
        HorarioController::class,
        'activar'
    ])->whereNumber('idHorario')
    ->name('horarios.activar');
    */
});


