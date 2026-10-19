<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarreraController extends Controller
{
    /**
     * Descripción: Muestra todas las carreras registradas en el sistema,
     * incluyendo las carreras que se encuentran inactivas.
     *
     * Entradas:
     * $solicitud: solicitud HTTP del usuario autenticado.
     *
     * Salidas:
     * Vista de gestión de carreras con todas las carreras registradas.
     */
    public function index(Request $solicitud)
    {
        // Comprobar que el usuario tenga un rol autorizado 
        $this->verificarRol([1, 2]);

        // Obtener todas las carreras registradas, incluyendo las inactivas.
        $carrerasRegistradas = Carrera::orderBy('id_carrera', 'desc')->get();

        return view( 'carrera.GestionCarrera', compact('carrerasRegistradas'));
    }

    /**
     * Descripción: Muestra la pantalla de gestión para registrar una nueva carrera.
     *
     * Entradas: Ninguna.
     *
     * Salidas: Vista de gestión con las opciones disponibles para registrar carreras.
     */
    public function create()
    {
        $this->verificarRol([1]);

        $carrerasRegistradas = Carrera::orderBy('nombreCarrera')->get();

        // Enviar las carreras registradas a la vista.
        return view( 'carrera.GestionCarrera', compact('carrerasRegistradas'));
    }


    /**
     * Descripción:
     * Valida y registra una nueva carrera en el sistema.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     *
     * Salidas:
     * Redirige al listado con un modal de éxito o de error.
     */
    public function store(Request $solicitud)
    {
        $this->verificarRol([1]);

        // Obtener los datos necesarios para comprobar si existen duplicados.
        $nombreCarrera = trim($solicitud->input('nombreCarrera', ''));
        $claveCarrera = trim($solicitud->input('claveCarrera', ''));

        // Comprobar si la clave ya está registrada.
        if ( $claveCarrera !== '' && Carrera::where('claveCarrera', $claveCarrera)->exists() ) {
            return redirect()->back() ->withInput()
                ->with('tipo_modal_carrera', 'error') 
                ->with('mensaje_modal_carrera','No se puede registrar la carrera porque esa clave ya está registrada.');
        }

        // Validar los datos antes de guardar la carrera.
        $datosValidados = $solicitud->validate([
            'nombreCarrera' => [
                'required',
                'string',
                'max:100',
            ],
            'claveCarrera' => [
                'required',
                'string',
                'max:45',
                Rule::unique('carrera', 'claveCarrera'),
            ],
            'estado' => [
                'required',
                'integer',
                Rule::in([0, 1]),
            ],
        ], [
            'nombreCarrera.required' => 'El nombre de la carrera es obligatorio.',
            'nombreCarrera.string' => 'El nombre de la carrera debe ser texto.',
            'nombreCarrera.max' => 'El nombre de la carrera no puede superar los 100 caracteres.',
            'claveCarrera.required' => 'La clave de la carrera es obligatoria.',
            'claveCarrera.string' => 'La clave de la carrera debe ser texto.',
            'claveCarrera.max' => 'La clave de la carrera no puede superar los 45 caracteres.',
            'claveCarrera.unique' => 'Esta clave de carrera ya está registrada.',
            'estado.required' => 'Debes seleccionar el estado de la carrera.',
            'estado.integer' => 'El estado seleccionado no es válido.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
        ]);

        // Registrar la carrera cuando todos los datos son válidos.
        Carrera::create($datosValidados);

        // Regresar al listado y mostrar el modal de éxito.
        return redirect()
            ->route('carreras.index')
            ->with('tipo_modal_carrera', 'registro')
            ->with('mensaje_modal_carrera','La carrera se registró correctamente.' );
    }


    /**
     * Descripción: Muestra la información de una carrera específica.
     *
     * Entradas:
     * $idCarrera: identificador de la carrera que se desea consultar.
     *
     * Salidas:  Vista de gestión con la carrera seleccionada.
     */
    public function show($idCarrera)
    {
        // Administrador y personal administrativo pueden consultar carreras.
        $this->verificarRol([1, 2]);

        // Buscar la carrera mediante su identificador.
        $carreraEncontrada = Carrera::findOrFail($idCarrera);

        // Obtener todas las carreras para mantener la estructura de la vista.
        $carrerasRegistradas = Carrera::orderBy('nombreCarrera')->get();

        // Enviar los datos necesarios a la vista.
        return view('carrera.GestionCarrera',
            compact( 'carrerasRegistradas', 'carreraEncontrada' )
        );
    }

    /**
     * Descripción: Muestra el formulario para modificar una carrera existente.
     *
     * Entradas:
     * $idCarrera: identificador de la carrera que se desea modificar.
     *
     * Salidas: Vista de edición con la información de la carrera seleccionada.
     */
    public function edit($idCarrera)
    {
        $this->verificarRol([1, 2]);

        // Buscar la carrera que se desea modificar.
        $carreraEncontrada = Carrera::findOrFail($idCarrera);

        // Mostar la página de edición.
        return view('carrera.EditarCarrera', compact('carreraEncontrada') );
    }

    /**
     * Descripción: Actualiza la información de una carrera existente.
     *
     * Entradas:
     * $solicitud: datos enviados desde el formulario.
     * $idCarrera: identificador de la carrera que se actualizará.
     *
     * Salidas:
     * Redirección al listado con un mensaje de confirmación.
     */
    public function update(Request $solicitud, $idCarrera)
    {
        $this->verificarRol([1, 2]);

        // Buscar la carrera que se desea actualizar.
        $carreraEncontrada = Carrera::findOrFail($idCarrera);

        // Validar los datos recibidos y excluir la clave de la carrera actual.
        $datosValidados = $solicitud->validate([
            'nombreCarrera' => [
                'required',
                'string',
                'max:100',
            ],

            'claveCarrera' => [
                'required',
                'string',
                'max:45',
                Rule::unique('carrera', 'claveCarrera')
                    ->ignore($idCarrera, 'id_carrera'),
            ],
            'estado' => ['nullable', 'integer', Rule::in([0, 1])],
        ], [
            'nombreCarrera.required' => 'El nombre de la carrera es obligatorio.',
            'nombreCarrera.string' => 'El nombre de la carrera debe ser texto.',
            'nombreCarrera.max' => 'El nombre de la carrera no puede superar los 100 caracteres.',

            'claveCarrera.required' => 'La clave de la carrera es obligatoria.',
            'claveCarrera.string' => 'La clave de la carrera debe ser texto.',
            'claveCarrera.max' => 'La clave de la carrera no puede superar los 45 caracteres.',
            'claveCarrera.unique' => 'Esta clave de carrera ya está registrada.',
        ]);

        // Si no recibe un estado, conserva el estado actual.
        if ((int) $solicitud->user()->rol === 2) {
            $datosValidados['estado'] = $carreraEncontrada->estado;
        } else {
            // Si el administrador no envía un estado, se establece como activo.
            $datosValidados['estado'] = $datosValidados['estado'] ?? 1;
        }

        // Actualizar la información de la carrera.
        $carreraEncontrada->update($datosValidados);

        // Regresar al listado con un mensaje para el modal de modificación.
        return redirect()
            ->route('carreras.index')
            ->with('tipo_modal_carrera', 'modificacion')
            ->with('mensaje_modal_carrera','Los datos de la carrera se modificaron correctamente.' );
    }

    /**
     * Descripción: Desactiva una carrera mediante borrado lógico.
     *
     * Entradas:
     * $idCarrera: identificador de la carrera que se desea desactivar.
     *
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function destroy($idCarrera)
    {
        // Solo el administrador puede desactivar carreras.
        $this->verificarRol([1]);

        // Buscar la carrera que se desea desactivar.
        $carreraEncontrada = Carrera::findOrFail($idCarrera);

        // Cambiar el estado de la carrera a inactiva.
        $carreraEncontrada->update([
            'estado' => 0,
        ]);

        return redirect()
            ->route('carreras.index')
            ->with('tipo_modal_carrera', 'baja')
            ->with('mensaje_modal_carrera','La carrera se desactivó correctamente.' );
    }

    /**
     * Descripción: Activa nuevamente una carrera que se encuentra inactiva.
     *
     * Entradas:
     * $idCarrera: identificador de la carrera que se desea activar.
     *
     * Salidas: Redirección al listado con un mensaje de confirmación.
     */
    public function activar($idCarrera)
    {
        // Solo el administrador puede activar carreras.
        $this->verificarRol([1]);

        // Buscar la carrera que se desea activar.
        $carreraEncontrada = Carrera::findOrFail($idCarrera);

        // Cambiar el estado de la carrera a activa.
        $carreraEncontrada->update([
            'estado' => 1,
        ]);

        return redirect()
            ->route('carreras.index')
            ->with('tipo_modal_carrera', 'activacion')
            ->with( 'mensaje_modal_carrera', 'La carrera se activó correctamente.' );
    }

    /**
     * Descripción: Comprueba que el usuario autenticado tenga uno de los roles permitidos.
     *
     * Entradas:
     * $rolesPermitidos: identificadores de los roles autorizados.
     *
     * Salidas:
     * No devuelve datos; genera un error 403 si el usuario no tiene permiso.
     */
    private function verificarRol(array $rolesPermitidos): void
    {
        // Obtiene al usuario autenticado.
        $usuarioAutenticado = auth()->user();

        // Comprueba que exista una sesión y tenga un rol autorizado.
        abort_unless(
            $usuarioAutenticado
            && in_array(
                (int) $usuarioAutenticado->rol,
                $rolesPermitidos,
                true
            ),
            403
        );
    }
}
