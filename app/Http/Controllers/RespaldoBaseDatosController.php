<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RespaldoBaseDatosController extends Controller
{
    /**
     * Descripción: Muestra la interfaz de respaldo y restauración
     * y obtiene los respaldos almacenados.
     *
     * Entradas:
     * Ninguna.
     *
     * Salidas:
     * Vista de gestión de respaldos con los archivos disponibles.
     */
    public function index()
    {
        // Verificar que el usuario tenga el rol de Administrador.
        $this->verificarRol([1]);

        // Obtener los archivos de respaldo almacenados.
        $respaldos = Storage::disk('local')->files('respaldos');

        // Conservar únicamente los archivos SQL que tengan información.
        $respaldos = array_filter($respaldos, function ($archivo) {
            return str_ends_with(strtolower($archivo), '.sql') &&
                Storage::disk('local')->size($archivo) > 0;
        });

        // Ordenar los respaldos del más reciente al más antiguo.
        rsort($respaldos);

        // Mostrar la vista de gestión de respaldos.
        return view(
            'RespaldoBaseDatos.GestionRespaldoBaseDatos',
            compact('respaldos')
        );
    }


    /**
     * Descripción: Genera un respaldo de la base de datos
     * mediante el ejecutable mysqldump.
     *
     * Entradas:
     * Configuración de conexión a la base de datos.
     *
     * Salidas:
     * Archivo SQL almacenado en la carpeta privada de respaldos.
     */
    public function generarRespaldo()
    {
        // Verificar que el usuario tenga el rol de Administrador.
        $this->verificarRol([1]);

        // Inicializar la ruta del respaldo.
        $rutaAlmacenamiento = null;

        try {
            // Obtener la configuración de conexión a la base de datos.
            $configuracion = DB::connection()->getConfig();

            // Definir la ubicación del ejecutable mysqldump.
            $ejecutable = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

            // Comprobar que el ejecutable exista.
            if (!file_exists($ejecutable)) {
                throw new \RuntimeException(
                    'No se encontró el ejecutable mysqldump.'
                );
            }

            // Crear la carpeta privada de respaldos.
            Storage::disk('local')->makeDirectory('respaldos');

            // Generar el nombre del archivo.
            $nombreArchivo = 'respaldo_' .
                $configuracion['database'] . '_' .
                now()->format('Ymd_His') . '.sql';

            // Definir la ubicación del archivo.
            $rutaAlmacenamiento = 'respaldos/' . $nombreArchivo;

            $rutaArchivo = Storage::disk('local')->path( $rutaAlmacenamiento );

            // Preparar los argumentos del comando.
            $comando = [
                $ejecutable,
                '--protocol=tcp',
                '--host=' . $configuracion['host'],
                '--port=' . $configuracion['port'],
                '--user=' . $configuracion['username'],
                '--default-character-set=utf8mb4',
                '--single-transaction',
                '--routines',
                '--triggers',
                '--result-file=' . $rutaArchivo,
                $configuracion['database']
            ];

            // Obtener la contraseña de la conexión.
            $contrasena = $configuracion['password'] ?? '';

            // Configurar las variables de entorno del proceso.
            $entorno = getenv();

            if ($contrasena !== '') {
                $entorno['MYSQL_PWD'] = $contrasena;
            }

            // Crear el proceso desde la carpeta principal del proyecto.
            $proceso = new Process(
                $comando,
                base_path(),
                $entorno
            );

            // Establecer el tiempo máximo de ejecución.
            $proceso->setTimeout(600);

            // Ejecutar el respaldo.
            $proceso->run();

            // Comprobar el resultado de la ejecución.
            if (!$proceso->isSuccessful()) {
                throw new \RuntimeException(
                    'mysqldump finalizó con código: ' .
                    $proceso->getExitCode() .
                    '. ' . $proceso->getErrorOutput()
                );
            }

            // Comprobar que el archivo exista.
            if (!file_exists($rutaArchivo)) {
                throw new \RuntimeException(
                    'No se generó el archivo de respaldo.'
                );
            }

            // Comprobar que el archivo tenga información.
            if (filesize($rutaArchivo) === 0) {
                throw new \RuntimeException(
                    'El archivo de respaldo se generó vacío.'
                );
            }

            // Regresar a la vista con un mensaje de éxito.
            return redirect()
                ->route('respaldo.index')
                ->with(
                    'alerta_exito',
                    'El respaldo de la base de datos se generó correctamente.'
                );

        } catch (\Throwable $error) {

            // Eliminar el archivo si se creó un respaldo incompleto.
            if (
                $rutaAlmacenamiento !== null &&
                Storage::disk('local')->exists($rutaAlmacenamiento)
            ) {
                Storage::disk('local')->delete($rutaAlmacenamiento);
            }

            // Registrar los detalles del error.
            Log::error('Error al generar el respaldo de la base de datos.', [
                'error' => $error->getMessage()
            ]);

            // Regresar a la vista sin descargar ningún archivo.
            return redirect()
                ->route('respaldo.index')
                ->with(
                    'alerta_error',
                    'No fue posible generar el respaldo de la base de datos.'
                );
        }
    }


    /**
     * Descripción: Permite descargar un respaldo previamente generado.
     *
     * Entradas:
     * Nombre del archivo de respaldo.
     *
     * Salidas:
     * Descarga del archivo SQL solicitado.
     */
    public function descargarRespaldo($archivo)
    {
        // Verificar que el usuario tenga el rol de Administrador.
        $this->verificarRol([1]);

        // Comprobar que el nombre no contenga rutas externas.
        if (
            basename($archivo) !== $archivo ||
            !str_ends_with(strtolower($archivo), '.sql')
        ) {
            abort(404);
        }

        // Definir la ubicación privada del archivo.
        $rutaArchivo = 'respaldos/' . $archivo;

        // Comprobar que el respaldo exista.
        if (!Storage::disk('local')->exists($rutaArchivo)) {
            abort(404);
        }

        // Comprobar que el archivo tenga información.
        if (Storage::disk('local')->size($rutaArchivo) === 0) {
            abort(404);
        }

        // Descargar únicamente el respaldo válido.
        return Storage::disk('local')->download(
            $rutaArchivo,
            $archivo
        );
    }


    /**
     * Descripción: Restaura la base de datos utilizando un respaldo
     * previamente generado.
     *
     * Entradas:
     * Nombre del archivo SQL seleccionado para la restauración.
     *
     * Salidas:
     * Base de datos restaurada a partir del archivo seleccionado.
     */
    public function restaurarRespaldo(Request $solicitud)
    {
        // Verificar que el usuario tenga el rol de Administrador.
        $this->verificarRol([1]);

        // Validar el nombre del archivo seleccionado.
        $datosValidados = $solicitud->validate([
            'archivo' => ['required', 'string', 'max:255']
        ]);

        // Inicializar el proceso de restauración.
        $proceso = null;

        try {
            // Obtener el nombre del respaldo seleccionado.
            $archivo = $datosValidados['archivo'];

            // Comprobar que el nombre no contenga rutas externas.
            if (
                basename($archivo) !== $archivo ||
                !str_ends_with(strtolower($archivo), '.sql')
            ) {
                throw new \RuntimeException(
                    'El archivo seleccionado no es válido.'
                );
            }

            // Definir la ubicación privada del respaldo.
            $rutaArchivo = 'respaldos/' . $archivo;

            // Comprobar que el archivo exista.
            if (!Storage::disk('local')->exists($rutaArchivo)) {
                throw new \RuntimeException(
                    'El respaldo seleccionado no existe.'
                );
            }

            // Comprobar que el archivo tenga información.
            if (Storage::disk('local')->size($rutaArchivo) === 0) {
                throw new \RuntimeException(
                    'El archivo de respaldo está vacío.'
                );
            }

            // Obtener la ruta completa del archivo SQL.
            $rutaCompleta = Storage::disk('local')->path($rutaArchivo);

            // Definir la ubicación del ejecutable mysql.
            $ejecutable = 'C:\\xampp\\mysql\\bin\\mysql.exe';

            // Comprobar que el ejecutable exista.
            if (!file_exists($ejecutable)) {
                throw new \RuntimeException(
                    'No se encontró el ejecutable mysql.'
                );
            }

            // Obtener la configuración de conexión a la base de datos.
            $configuracion = DB::connection()->getConfig();

            // Preparar los argumentos para ejecutar mysql.
            $comando = [
                $ejecutable,
                '--protocol=tcp',
                '--host=' . $configuracion['host'],
                '--port=' . $configuracion['port'],
                '--user=' . $configuracion['username'],
                '--default-character-set=utf8mb4',
                $configuracion['database']
            ];

            // Obtener la contraseña de la conexión.
            $contrasena = $configuracion['password'] ?? '';

            // Configurar las variables de entorno del proceso.
            $entorno = getenv();

            if ($contrasena !== '') {
                $entorno['MYSQL_PWD'] = $contrasena;
            }

            // Crear el proceso de restauración.
            $proceso = new Process(
                $comando,
                base_path(),
                $entorno
            );

            // Establecer el tiempo máximo de ejecución.
            $proceso->setTimeout(600);

            // Leer el contenido del archivo SQL.
            $contenidoRespaldo = file_get_contents($rutaCompleta);

            // Comprobar que se haya podido leer el archivo.
            if ($contenidoRespaldo === false || $contenidoRespaldo === '') {
                throw new \RuntimeException(
                    'No fue posible leer el archivo de respaldo.'
                );
            }

            // Enviar el contenido SQL al proceso mysql.
            $proceso->setInput($contenidoRespaldo);

            // Ejecutar la restauración.
            $proceso->run();

            // Comprobar si la restauración terminó correctamente.
            if (!$proceso->isSuccessful()) {
                throw new \RuntimeException(
                    'mysql finalizó con código: ' .
                    $proceso->getExitCode() .
                    '. ' . $proceso->getErrorOutput()
                );
            }

            // Regresar a la vista con un mensaje de éxito.
            return redirect()
                ->route('respaldo.index')
                ->with(
                    'alerta_exito',
                    'La base de datos se restauró correctamente.'
                );

        } catch (\Throwable $error) {

            // Detener el proceso si continúa en ejecución.
            if ($proceso !== null && $proceso->isRunning()) {
                $proceso->stop();
            }

            // Registrar el error para su revisión.
            Log::error('Error al restaurar la base de datos.', [
                'error' => $error->getMessage()
            ]);

            // Regresar a la vista con un mensaje de error.
            return redirect()
                ->route('respaldo.index')
                ->with(
                    'alerta_error',
                    'No fue posible restaurar la base de datos.'
                );
        }
    }


    /**
     * Descripción: Verifica que el usuario tenga uno de los roles permitidos.
     *
     * Entradas:
     * Arreglo con los identificadores de los roles permitidos.
     *
     * Salidas:
     * No devuelve información. Restringe el acceso cuando el rol no está permitido.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtener el usuario autenticado.
        $usuarioAutenticado = Auth::user();

        // Comprobar que el usuario tenga un rol autorizado.
        abort_unless(
            $usuarioAutenticado &&
            in_array((int) $usuarioAutenticado->rol, $rolesPermitidos),
            403
        );
    }
}
