public function apiAgendaInstructor(Request $request)
{
    $instructorId = $request->query('instructor_id');

    if (!$instructorId) {
        return response()->json([]);
    }

    $programaciones = Programacion::with(['curso', 'grupo.coordinacion', 'aula'])
        ->where('instructor_id', $instructorId)
        ->orderBy('fecha_inicio')
        ->get();

    $eventos = $programaciones->map(function ($p) {
        // Si existe hora de inicio, la formateamos, sino ponemos 08:30 como defecto
        $horaInicio = $p->hora_inicio ? substr($p->hora_inicio, 0, 5) : '08:30';
        $horaFin = $p->hora_fin ? substr($p->hora_fin, 0, 5) : '17:00';

        $start = ($p->fecha_inicio ? $p->fecha_inicio->format('Y-m-d') : '2025-01-01') . 'T' . $horaInicio;
        $end = ($p->fecha_fin ? $p->fecha_fin->format('Y-m-d') : '2025-01-01') . 'T' . $horaFin;

        return [
            'title' => $p->curso->nombre ?? 'Curso sin título',
            'start' => $start,
            'end' => $end,
            'color' => $p->grupo->coordinacion->color ?? '#3788D8',
            'allDay' => false, // 🔥 Aquí la clave: NO ES ALLDAY
            'extendedProps' => [
                'grupo' => $p->grupo->nombre ?? '—',
                'aula' => $p->aula->nombre ?? '—',
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'estado' => ucfirst($p->estado_confirmacion ?? 'pendiente'),
                'estadoDisplay' => ucfirst($p->estado_confirmacion ?? 'pendiente'),
                'fecha_inicio_fmt' => $p->fecha_inicio ? $p->fecha_inicio->format('d/m/Y') : '—',
                'fecha_fin_fmt' => $p->fecha_fin ? $p->fecha_fin->format('d/m/Y') : '—',
            ],
        ];
    });

    return response()->json($eventos);
}
