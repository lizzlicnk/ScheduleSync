<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

//Importar la funcionalidad de SoftDeletes de Laravel para realizar bajas lógicas
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable
{
    // Funcionalidades para crear registros, enviar notificaciones y realizar bajas lógicas.
    use HasFactory, Notifiable, SoftDeletes;

    // Nombre de la tabla utilizada en MySQL.
    protected $table = 'usuario';

    // Nombre de la llave primaria de la tabla.
    protected $primaryKey = 'id_usuario';

    // Indicar que la tabla no utiliza created_at ni updated_at.
    public $timestamps = false;

    // Campos que Laravel permite registrar o modificar.
    protected $fillable = [
        'nombreUsuario',
        'apellidoPaterno',
        'apellidoMaterno',
        'correo',
        'password',
        'rol',
        'estado',
        'fechaRegistro',
        'ultimo_acceso',
    ];

    // Ocultar la contraseña al convertir el modelo en un arreglo o JSON.
    protected $hidden = [
        'password',
    ];

    /**
     * Descripción: Definir la conversión automática de los campos.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna un arreglo con los tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'fechaRegistro' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Descripción: Obtiene el correo utilizado para autenticar al usuario.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna el nombre del campo de autenticación.
     */
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    /**
     * Descripción: Obtiene el nombre del campo de contraseña.
     * Entradas: No recibe parámetros.
     * Salidas: Retorna el campo que almacena la contraseña.
     */
    public function getAuthPasswordName()
    {
        return 'password';
    }
}