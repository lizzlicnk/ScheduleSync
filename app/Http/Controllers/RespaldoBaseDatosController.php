<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Schema;

class RespaldoBaseDatosController extends Controller
{
    // Ubicación de los ejecutables de MySQL de XAMPP.
    private const MYSQLDUMP = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    private const MYSQL = 'C:\\xampp\\mysql\\bin\\mysql.exe';

    // Tablas técnicas de Laravel que no deben respaldarse ni restaurarse.
    private const TABLAS_IGNORADAS = [
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Descripción: Muestra la interfaz de respaldo y restauración
     * y obtiene los respaldos almacenados.
     *
     * Entradas: Ninguna.
     *
     * Salidas: Vista de gestión de respaldos con los archivos disponibles.
     */
    public function index()
    {
        $this->verificarRol([1]);

        // Obtener los archivos de respaldo almacenados.
        $respaldos = Storage::disk('local')->files('respaldos');

        // Conservar únicamente los archivos SQL que tengan información.
        $respaldos = array_filter($respaldos, function ($archivo) {
            return str_ends_with(strtolower($archivo), '.sql') &&
                Storage::disk('local')->size($archivo) > 0;
        });

        // Ordenar del más reciente al más antiguo.
        rsort($respaldos);

        return view(
            'RespaldoBaseDatos.GestionRespaldoBaseDatos',
            ['respaldos' => array_values($respaldos)]
        );
    }


    /**
     * Descripción: Genera un respaldo de la base de datos
     * mediante el ejecutable mysqldump.
     *
     * Entradas: Configuración de conexión a la base de datos.
     *
     * Salidas: Archivo SQL almacenado en la carpeta privada de respaldos.
     */
    public function generarRespaldo()
    {
        $this->verificarRol([1]);

        // Evitar que PHP detenga el script por tiempo de ejecución.
        set_time_limit(0);

        $rutaAlmacenamiento = null;

        try {
            $configuracion = DB::connection()->getConfig();

            if (!file_exists(self::MYSQLDUMP)) {
                throw new \RuntimeException('No se encontró el ejecutable mysqldump.');
            }

            // Crear la carpeta privada de respaldos.
            Storage::disk('local')->makeDirectory('respaldos');

            // Generar el nombre y la ubicación del archivo.
            $nombreArchivo = 'respaldo_' .
                $configuracion['database'] . '_' .
                now()->format('Ymd_His') . '.sql';

            $rutaAlmacenamiento = 'respaldos/' . $nombreArchivo;
            $rutaArchivo = Storage::disk('local')->path($rutaAlmacenamiento);

            // Preparar los argumentos del comando.
            $comando = [
                self::MYSQLDUMP,
                '--protocol=tcp',
                '--host=' . $configuracion['host'],
                '--port=' . $configuracion['port'],
                '--user=' . $configuracion['username'],
                '--default-character-set=utf8mb4',
                '--single-transaction',
                '--routines',
                '--triggers',
                '--events',
                '--result-file=' . $rutaArchivo,
            ];

            // Excluir las tablas técnicas de Laravel.
            foreach (self::TABLAS_IGNORADAS as $tabla) {
                $comando[] = '--ignore-table=' . $configuracion['database'] . '.' . $tabla;
            }

            // El nombre de la base de datos debe ir al final.
            $comando[] = $configuracion['database'];

            $proceso = new Process(
                $comando,
                base_path(),
                $this->entornoProceso($configuracion)
            );

            $proceso->setTimeout(600);
            $proceso->run();

            if (!$proceso->isSuccessful()) {
                throw new \RuntimeException(
                    'mysqldump finalizó con código: ' .
                    $proceso->getExitCode() . '. ' .
                    $proceso->getErrorOutput()
                );
            }

            if (!file_exists($rutaArchivo)) {
                throw new \RuntimeException('No se generó el archivo de respaldo.');
            }

            if (filesize($rutaArchivo) === 0) {
                throw new \RuntimeException('El archivo de respaldo se generó vacío.');
            }

            return redirect()
                ->route('respaldo.index')
                ->with('alerta_exito', 'El respaldo de la base de datos se generó correctamente.');

        } catch (HttpException $error) {
            throw $error;

        } catch (\Throwable $error) {

            // Eliminar el archivo si se creó un respaldo incompleto.
            if (
                $rutaAlmacenamiento !== null &&
                Storage::disk('local')->exists($rutaAlmacenamiento)
            ) {
                Storage::disk('local')->delete($rutaAlmacenamiento);
            }

            Log::error('Error al generar el respaldo de la base de datos.', [
                'error' => $error->getMessage()
            ]);

            return redirect()
                ->route('respaldo.index')
                ->with('alerta_error', 'No fue posible generar el respaldo de la base de datos.');
        }
    }


    /**
     * Descripción: Permite descargar un respaldo previamente generado.
     *
     * Entradas: Nombre del archivo de respaldo.
     *
     * Salidas: Descarga del archivo SQL solicitado.
     */
    public function descargarRespaldo($archivo)
    {
        $this->verificarRol([1]);

        // Comprobar que el nombre no contenga rutas externas.
        if (
            basename($archivo) !== $archivo ||
            !str_ends_with(strtolower($archivo), '.sql')
        ) {
            abort(404);
        }

        $rutaArchivo = 'respaldos/' . $archivo;

        if (
            !Storage::disk('local')->exists($rutaArchivo) ||
            Storage::disk('local')->size($rutaArchivo) === 0
        ) {
            abort(404);
        }

        return Storage::disk('local')->download($rutaArchivo, $archivo);
    }


    /**
     * Descripción: Restaura la base de datos utilizando un respaldo SQL
     * previamente generado por el sistema.
     *
     * Entradas: $solicitud: contiene el nombre del archivo seleccionado.
     *
     * Salidas: Redirige a la vista de respaldos con el resultado.
     */
    public function restaurarRespaldo(Request $solicitud)
    {
        $this->verificarRol([1]);

        // Evitar que PHP detenga el script por tiempo de ejecución
        // o porque el navegador cierre la conexión.
        set_time_limit(0);
        ignore_user_abort(true);

        $flujo = null;
        $proceso = null;
        $nombreArchivo = null;

        try {
            // Validar el archivo seleccionado.
            $datosValidados = $solicitud->validate([
                'archivo' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^respaldo_[a-zA-Z0-9_-]+\.sql$/i'
                ]
            ]);

            $nombreArchivo = basename($datosValidados['archivo']);
            $rutaRelativa = 'respaldos/' . $nombreArchivo;

            if (!Storage::disk('local')->exists($rutaRelativa)) {
                return $this->respuestaError('El respaldo seleccionado no existe.');
            }

            $rutaCompleta = Storage::disk('local')->path($rutaRelativa);

            if (filesize($rutaCompleta) === 0) {
                return $this->respuestaError('El archivo de respaldo está vacío.');
            }

            if (!is_readable($rutaCompleta)) {
                return $this->respuestaError('No se puede leer el archivo de respaldo.');
            }

            if (!file_exists(self::MYSQL)) {
                throw new \RuntimeException('No se encontró el ejecutable de MySQL.');
            }

            $configuracion = DB::connection()->getConfig();

            $comando = [
                self::MYSQL,
                '--protocol=tcp',
                '--host=' . $configuracion['host'],
                '--port=' . $configuracion['port'],
                '--user=' . $configuracion['username'],
                '--default-character-set=utf8mb4',
                '--max-allowed-packet=256M',
                $configuracion['database']
            ];

            $proceso = new Process(
                $comando,
                base_path(),
                $this->entornoProceso($configuracion)
            );

            $proceso->setTimeout(600);

            // Enviar el archivo como flujo, sin cargarlo completo en memoria.
            $flujo = fopen($rutaCompleta, 'rb');

            if ($flujo === false) {
                throw new \RuntimeException('No fue posible abrir el archivo SQL.');
            }

            $proceso->setInput($flujo);
            $proceso->run();

            if (!$proceso->isSuccessful()) {
                Log::error('Error al restaurar la base de datos.', [
                    'archivo' => $nombreArchivo,
                    'codigo'  => $proceso->getExitCode(),
                    'error'   => $proceso->getErrorOutput(),
                    'salida'  => $proceso->getOutput()
                ]);

                return $this->respuestaError(
                    'No fue posible completar la restauración de la base de datos.'
                );
            }

            Log::info('Base de datos restaurada correctamente.', [
                'archivo' => $nombreArchivo,
                'usuario' => Auth::id()
            ]);

            return redirect()
                ->route('respaldo.index')
                ->with('alerta_exito', 'La base de datos se restauró correctamente.');

        } catch (ValidationException | HttpException $error) {
            // Validación y 403/404 se manejan con su comportamiento normal.
            throw $error;

        } catch (\Throwable $error) {

            if ($proceso !== null && $proceso->isRunning()) {
                $proceso->stop();
            }

            Log::error('Error durante la restauración de la base de datos.', [
                'archivo' => $nombreArchivo,
                'error'   => $error->getMessage(),
                'usuario' => Auth::id()
            ]);

            return $this->respuestaError('Ocurrió un problema al restaurar la base de datos.');

        } finally {
            if (is_resource($flujo)) {
                fclose($flujo);
            }
        }
    }


    /**
     * Descripción: Construye las variables de entorno del proceso,
     * enviando la contraseña sin incluirla en el comando.
     *
     * Entradas: Configuración de la conexión.
     *
     * Salidas: Arreglo con las variables de entorno adicionales.
     */
    private function entornoProceso(array $configuracion): array
    {
        // Conservar las variables del sistema (necesarias en Windows).
        $entorno = getenv();

        $contrasena = $configuracion['password'] ?? '';

        if ($contrasena !== '') {
            $entorno['MYSQL_PWD'] = $contrasena;
        }

        return $entorno;
    }


    /**
     * Descripción: Regresa a la vista de respaldos con un mensaje de error.
     *
     * Entradas: Mensaje que se mostrará.
     *
     * Salidas: Redirección con la alerta de error.
     */
    private function respuestaError(string $mensaje)
    {
        return redirect()
            ->route('respaldo.index')
            ->with('alerta_error', $mensaje);
    }


    /**
     * Descripción: Verifica que el usuario tenga uno de los roles permitidos.
     *
     * Entradas: Arreglo con los identificadores de los roles permitidos.
     *
     * Salidas: No devuelve información. Restringe el acceso si el rol no está permitido.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        $usuarioAutenticado = Auth::user();

        abort_unless(
            $usuarioAutenticado &&
            in_array((int) $usuarioAutenticado->rol, $rolesPermitidos),
            403
        );
    }
}