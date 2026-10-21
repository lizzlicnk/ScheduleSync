<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsignaturaController extends Controller
{
    /**
     * Descripción: Muestra las asignaturas registradas y permite filtrarlas.
     * Entradas: Solicitud HTTP con los filtros de búsqueda.
     * Salidas: Vista de gestión de asignaturas con sus carreras.
     */
    public function index(Request $solicitud)
    {
        // Verificar que el usuario tenga permiso para consultar las asignaturas.
        $this->verificarRol([1, 2, 3]);

        // Consultar las asignaturas junto con la información de su carrera.
        $consulta = Asignatura::with('carrera');

        // Filtrar por clave o nombre de la asignatura.
        if ($solicitud->filled('buscar')) {
            $busqueda = $solicitud->buscar;

            $consulta->where(function ($subconsulta) use ($busqueda) {
                $subconsulta->where('clave', 'like', "%{$busqueda}%")
                    ->orWhere('nombreAsignatura', 'like', "%{$busqueda}%");
            });
        }

        // Filtrar por carrera.
        if ($solicitud->filled('id_carrera')) {
            $consulta->where('id_carrera', $solicitud->id_carrera);
        }

        // Filtrar por cuatrimestre.
        if ($solicitud->filled('cuatrimestre')) {
            $consulta->where('cuatrimestre', $solicitud->cuatrimestre);
        }

        // Filtrar por estado.
        if ($solicitud->filled('estado')) {
            $consulta->where('estado', $solicitud->estado);
        }

        // Obtener las asignaturas ordenadas alfabéticamente.
        $asignaturas = $consulta->orderBy('id_asignatura')->get();

        // Obtener todas las carreras para los filtros y las consultas.
        $carreras = Carrera::orderBy('nombreCarrera')->get();

        // Obtener únicamente las carreras activas para registrar asignaturas.
        $carrerasActivas = Carrera::where('estado', 1)->orderBy('nombreCarrera')->get();

        // Mostrar la vista de gestión de asignaturas.
        return view('Asignatura.GestionAsignatura', compact( 'asignaturas', 'carreras', 'carrerasActivas' ));
    }

    /**
     * Descripción: Registra una nueva asignatura.
     * Entradas: Datos de la asignatura enviados mediante el formulario.
     * Salidas: Redirección a la gestión con un mensaje de éxito o error.
     */
    public function store(Request $solicitud)
    {
        // Verificar que el usuario pueda registrar asignaturas.
        $this->verificarRol([1, 2]);

        // Validar los datos del formulario.
        $datosValidados = $solicitud->validate([
            'claveAsignatura' => [
                'required',
                'string',
                'max:20',
                'unique:asignatura,clave',
            ],
            'nombreAsignatura' => [
                'required',
                'string',
                'max:100',
            ],
            'cuatrimestre' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'horas' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],
            'id_carrera' => [
                'required',
                'exists:carrera,id_carrera',
            ],
        ], [
            'claveAsignatura.required' => 'La clave de la asignatura es obligatoria.',
            'claveAsignatura.unique' => 'La clave ingresada ya se encuentra registrada.',
            'claveAsignatura.max' => 'La clave no puede tener más de 20 caracteres.',
            'nombreAsignatura.required' => 'El nombre de la asignatura es obligatorio.',
            'nombreAsignatura.max' => 'El nombre no puede tener más de 100 caracteres.',
            'cuatrimestre.required' => 'Selecciona el cuatrimestre.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser como mínimo 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor que 12.',
            'horas.required' => 'Ingresa las horas de la asignatura.',
            'horas.integer' => 'Las horas deben ser un número entero.',
            'horas.min' => 'Las horas deben ser como mínimo 1.',
            'horas.max' => 'Las horas no pueden superar 65535.',
            'id_carrera.required' => 'Selecciona una carrera.',
            'id_carrera.exists' => 'La carrera seleccionada no existe.',
        ]);

        // Verificar que la carrera seleccionada esté activa.
        $carrera = Carrera::find($datosValidados['id_carrera']);

        if (!$carrera || (int) $carrera->estado !== 1) {
            return redirect()
                ->route('asignaturas.index')
                ->withInput()
                ->with('tipo_modal_asignatura', 'error')
                ->with(
                    'mensaje_modal_asignatura',
                    'No puedes registrar una asignatura en una carrera inactiva.'
                );
        }

        // Adaptar los nombres de los campos a las columnas de la base de datos.
        $datosValidados['clave'] = $datosValidados['claveAsignatura'];
        $datosValidados['cantidadHoras'] = $datosValidados['horas'];

        // Eliminar los nombres que no corresponden a columnas de la tabla.
        unset(
            $datosValidados['claveAsignatura'],
            $datosValidados['horas']
        );

        // Establecer la asignatura como activa.
        $datosValidados['estado'] = 1;

        // Registrar la asignatura en la base de datos.
        Asignatura::create($datosValidados);

        // Regresar a la gestión con un mensaje de éxito.
        return redirect()
            ->route('asignaturas.index')
            ->with('tipo_modal_asignatura', 'exito')
            ->with(
                'mensaje_modal_asignatura',
                'La asignatura se registró correctamente.'
            );
    }

    /**
     * Descripción: Muestra el formulario para editar una asignatura.
     * Entradas: Identificador de la asignatura.
     * Salidas: Vista de edición de la asignatura con las carreras disponibles.
     */
    public function edit($idAsignatura)
    {
        // Verificar que el usuario pueda editar asignaturas.
        $this->verificarRol([1, 2]);

        // Buscar la asignatura que se desea editar.
        $asignaturaEncontrada = Asignatura::findOrFail($idAsignatura);

        // Obtener únicamente las carreras activas.
        $carrerasActivas = Carrera::where('estado', 1)->orderBy('nombreCarrera')->get();

        // Agregar la carrera actual aunque esté inactiva.
        // Esto permite visualizar y conservar la carrera de la asignatura.
        $carreraActual = Carrera::find($asignaturaEncontrada->id_carrera);

        if ($carreraActual && (int) $carreraActual->estado !== 1 && !$carrerasActivas->contains('id_carrera', $carreraActual->id_carrera)) {
            $carrerasActivas->push($carreraActual);
        }

        // Mostrar la vista de edición.
        return view('Asignatura.EditarAsignatura', compact( 'asignaturaEncontrada','carrerasActivas' ));
    }

    /**
     * Descripción: Actualiza la información de una asignatura.
     * Entradas: Solicitud HTTP con los datos y el identificador de la asignatura.
     * Salidas: Redirección a la gestión con un mensaje de éxito o error.
     */
    public function update(Request $solicitud, $idAsignatura)
    {
        // Verificar que el usuario pueda editar asignaturas.
        $this->verificarRol([1, 2]);

        // Buscar la asignatura que se actualizará.
        $asignatura = Asignatura::findOrFail($idAsignatura);

        // Validar los datos recibidos.
        $datosValidados = $solicitud->validate([
            'claveAsignatura' => [
                'required',
                'string',
                'max:20',
                Rule::unique('asignatura', 'clave')
                    ->ignore($asignatura->id_asignatura, 'id_asignatura'),
            ],
            'nombreAsignatura' => [
                'required',
                'string',
                'max:100',
            ],
            'cuatrimestre' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'horas' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],
            'id_carrera' => [
                'required',
                'exists:carrera,id_carrera',
            ],
            'estado' => [
                'sometimes',
                'required',
                'integer',
                'in:0,1',
            ],
        ], [
            'claveAsignatura.required' => 'La clave de la asignatura es obligatoria.',
            'claveAsignatura.unique' => 'La clave ingresada ya está registrada en otra asignatura.',
            'claveAsignatura.max' => 'La clave no puede tener más de 20 caracteres.',
            'nombreAsignatura.required' => 'El nombre de la asignatura es obligatorio.',
            'nombreAsignatura.max' => 'El nombre no puede tener más de 100 caracteres.',
            'cuatrimestre.required' => 'Selecciona el cuatrimestre.',
            'cuatrimestre.min' => 'El cuatrimestre debe ser como mínimo 1.',
            'cuatrimestre.max' => 'El cuatrimestre no puede ser mayor que 12.',
            'horas.required' => 'Ingresa las horas de la asignatura.',
            'horas.integer' => 'Las horas deben ser un número entero.',
            'horas.min' => 'Las horas deben ser como mínimo 1.',
            'horas.max' => 'Las horas no pueden superar 65535.',
            'id_carrera.required' => 'Selecciona una carrera.',
            'id_carrera.exists' => 'La carrera seleccionada no existe.',
        ]);

        // Adaptar los nombres de los campos a las columnas de la base de datos.
        $datosValidados['clave'] = $datosValidados['claveAsignatura'];
        $datosValidados['cantidadHoras'] = $datosValidados['horas'];

        // Eliminar los nombres que no corresponden a columnas de la tabla.
        unset(
            $datosValidados['claveAsignatura'],
            $datosValidados['horas']
        );

        // Consultar la carrera seleccionada.
        $carreraNueva = Carrera::find($datosValidados['id_carrera']);

        // Permitir que únicamente el administrador modifique el estado.
        if ((int) $solicitud->user()->rol !== 1) {
            unset($datosValidados['estado']);
        }

        // Impedir el cambio a una carrera inactiva. Si conserva la carrera actual, puede mantenerla aunque esté inactiva.
        if (
            (!$carreraNueva || (int) $carreraNueva->estado !== 1) && (int) $datosValidados['id_carrera'] !== (int) $asignatura->id_carrera) {
            return redirect()->route('asignaturas.index')->withInput()->with('tipo_modal_asignatura', 'error')
                ->with(
                    'mensaje_modal_asignatura',
                    'No puedes cambiar la asignatura a una carrera inactiva.'
                );
        }

        // Actualizar la información de la asignatura.
        $asignatura->update($datosValidados);

        // Regresar a la gestión con un mensaje de éxito.
        return redirect()
            ->route('asignaturas.index')
            ->with('tipo_modal_asignatura', 'exito')
            ->with(
                'mensaje_modal_asignatura',
                'La asignatura se actualizó correctamente.'
            );
    }

    /**
     * Descripción: Realiza la baja lógica de una asignatura.
     *
     * Entradas: Identificador de la asignatura.
     * Salidas: Redirección a la gestión con un mensaje de éxito o error.
     */
    public function destroy($idAsignatura)
    {
        // Verificar que el usuario pueda dar de baja asignaturas.
        $this->verificarRol([1, 2]);

        // Buscar la asignatura.
        $asignatura = Asignatura::findOrFail($idAsignatura);

        // Comprobar si la asignatura ya está inactiva.
        if ((int) $asignatura->estado === 0) {
            return redirect()->route('asignaturas.index')->with('tipo_modal_asignatura', 'error')
                ->with(
                    'mensaje_modal_asignatura',
                    'La asignatura ya se encuentra inactiva.'
                );
        }

        // Cambiar su estado sin eliminar el registro de la base de datos.
        $asignatura->estado = 0;
        $asignatura->save();

        // Regresar a la gestión con un mensaje de éxito.
        return redirect()
            ->route('asignaturas.index')
            ->with('tipo_modal_asignatura', 'exito')
            ->with(
                'mensaje_modal_asignatura',
                'La asignatura se dio de baja correctamente.'
            );
    }

    /**
     * Descripción: Activa una asignatura que se encuentra inactiva.
     *
     * Entradas: Identificador de la asignatura.
     * Salidas: Redirección a la gestión con un mensaje de éxito o error.
     */
    public function activar($idAsignatura)
    {
        // Verificar que el usuario pueda activar asignaturas.
        $this->verificarRol([1, 2]);

        // Buscar la asignatura.
        $asignatura = Asignatura::findOrFail($idAsignatura);

        // Comprobar si la asignatura ya está activa.
        if ((int) $asignatura->estado === 1) {
            return redirect()
                ->route('asignaturas.index')
                ->with('tipo_modal_asignatura', 'error')
                ->with(
                    'mensaje_modal_asignatura',
                    'La asignatura ya se encuentra activa.'
                );
        }

        // Verificar que la carrera asociada siga activa.
        $carrera = Carrera::find($asignatura->id_carrera);

        if (!$carrera || (int) $carrera->estado !== 1) {
            return redirect()
                ->route('asignaturas.index')
                ->with('tipo_modal_asignatura', 'error')
                ->with(
                    'mensaje_modal_asignatura',
                    'No puedes activar la asignatura porque su carrera está inactiva.'
                );
        }

        // Cambiar el estado de la asignatura a activa.
        $asignatura->estado = 1;
        $asignatura->save();

        // Regresar a la gestión con un mensaje de éxito.
        return redirect()
            ->route('asignaturas.index')
            ->with('tipo_modal_asignatura', 'exito')
            ->with(
                'mensaje_modal_asignatura',
                'La asignatura se activó correctamente.'
            );
    }

    /**
     * Descripción: Comprueba si el usuario tiene permitido realizar una acción.
     *
     * Entradas: Arreglo con los roles autorizados.
     * Salidas: No devuelve información; genera un error 403 si no tiene permiso.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtener el usuario que inició sesión.
        $usuario = auth()->user();

        // Permitir el acceso únicamente a los roles indicados.
        abort_unless(
            $usuario && in_array((int) $usuario->rol, $rolesPermitidos, true),
            403,
            'No tienes permisos para realizar esta acción.'
        );
    }
}
