<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperarContrasenaController;
use App\Http\Controllers\Auth\CambiarContrasenaObligatoriaController;
use App\Http\Middleware\ForzarCambioContrasena;
use App\Http\Controllers\DocenteController;

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

});


