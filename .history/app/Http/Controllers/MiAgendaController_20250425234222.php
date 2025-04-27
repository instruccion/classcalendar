<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Programacion;
use App\Models\Instructor;
use App\Models\Coordinacion; // Asegúrate de importar Coordinacion si no lo estaba

class MiAgendaController extends Controller
{
    public function index(Request $request)
    {
        // Para la API, requerimos explícitamente un instructor_id
        // No usaremos Auth::id() como fallback aquí para evitar mezclar contextos
        $instructorId = $request->get('instructor_id');

        // Si no se proporciona instructor_id en la solicitud API, devolver vacío
        if (!$instructorId) {
            return response()->json([]);
        }

        $programaciones = Programacion::with([
                'curso',
                // Asegúrate de que la relación 'grupo.coordinacion' esté bien definida
                // y que Coordinacion tenga un atributo 'color'
                'grupo.coordinacion' => function ($query) {
                    // Seleccionar solo el id y el color para optimizar
                    $query->select('id', 'color');
                },
                'aula'
            ])
            ->where('instructor_id', $instructorId)
            ->orderBy('fecha_inicio')
            ->get();

        $eventos = $programaciones->map(function ($p) {
            // Validar que las relaciones y datos existen antes de usarlos
            $cursoNombre = $p->curso->nombre ?? 'Curso no especificado';
            $grupoNombre = $p->grupo->nombre ?? 'Grupo no especificado';
            $coordinacionColor = $p->grupo?->coordinacion?->color ?? '#3788D8'; // Color por defecto FullCalendar
            $aulaNombre = $p->aula->nombre ?? 'Aula no especificada';
            $estado = $p->estado_confirmacion ?? 'pendiente';

            // Formatear fechas y horas asegurando que no sean null
             // Formato ISO8601 es más robusto para FullCalendar
            $start = $p->fecha_inicio?->format('Y-m-d') . 'T' . ($p->hora_inicio ? substr($p->hora_inicio, 0, 5) : '00:00');
            $end = $p->fecha_fin?->format('Y-m-d') . 'T' . ($p->hora_fin ? substr($p->hora_fin, 0, 5) : '23:59'); // Hora fin si no existe

            // Capitalizar estado para mostrar
             $estadoDisplay = ucfirst($estado);

            return [
                'id'            => $p->id, // Útil tener el ID de la programación
                'title'         => $cursoNombre,
                'start'         => $start,
                'end'           => $end,
                'color'         => $coordinacionColor, // Color del evento basado en la coordinación
                'borderColor'   => $coordinacionColor, // Opcional: borde del mismo color
                'extendedProps' => [
                    'grupo'       => $grupoNombre,
                    'aula'        => $aulaNombre,
                    'hora_inicio' => substr($p->hora_inicio, 0, 5) ?? 'N/A',
                    'hora_fin'    => substr($p->hora_fin, 0, 5) ?? 'N/A',
                    'estado'      => $estado,
                    'estadoDisplay' => $estadoDisplay, // Usar este en el modal
                    'fecha_inicio_fmt' => $p->fecha_inicio?->format('d/m/Y') ?? 'N/A',
                    'fecha_fin_fmt' => $p->fecha_fin?->format('d/m/Y') ?? 'N/A',
                ]
            ];
        });

        return response()->json($eventos);
    }

    // La función agendaAdministrador parece correcta para pasar los datos iniciales a la vista
    public function agendaAdministrador(Request $request)
    {
        $instructor_id = $request->get('instructor_id');
        $instructores = Instructor::orderBy('nombre')->get();
        $programaciones = collect(); // Inicialmente vacío

        $selectedInstructor = null; // Para saber qué instructor está seleccionado

        if ($instructor_id) {
             $selectedInstructor = Instructor::find($instructor_id); // Opcional: pasar el objeto instructor
            $programaciones = Programacion::where('instructor_id', $instructor_id)
                ->with(['curso', 'grupo', 'aula'])
                ->orderBy('fecha_inicio')
                ->get();
        }

        // Pasar el instructor_id actual a la vista para usarlo en JS
        return view('admin.instructores.agenda', compact('instructores', 'programaciones', 'instructor_id', 'selectedInstructor'));
    }

    // agendaInstructor no se usa según tus rutas para la vista admin, la dejo por si acaso
    // public function agendaInstructor(Instructor $instructor)
    // {
    //     return view('admin.instructores.agenda', compact('instructor'));
    // }
}
