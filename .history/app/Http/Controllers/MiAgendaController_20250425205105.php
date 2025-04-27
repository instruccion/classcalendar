public function index(Request $request)
{
    $user = Auth::user();
    $instructorId = $request->get('instructor_id');

    // Si eres admin y pasas un instructor_id, lo tomamos. Si eres instructor, se usa tu ID.
    if ($user->rol === 'administrador' && $instructorId) {
        $targetInstructorId = $instructorId;
    } else {
        $targetInstructorId = $user->id;
    }

    $programaciones = Programacion::with(['curso', 'grupo.coordinacion', 'aula'])
        ->where('instructor_id', $targetInstructorId)
        ->orderBy('fecha_inicio')
        ->get();

    $eventos = $programaciones->map(function ($p) {
        return [
            'id' => $p->id,
            'titulo' => $p->curso->nombre ?? 'Curso sin título',
            'inicio' => $p->fecha_inicio . 'T' . $p->hora_inicio,
            'fin' => $p->fecha_fin . 'T' . $p->hora_fin,
            'color' => $p->grupo?->coordinacion?->color ?? '#3b82f6', // 💡 Color por coordinación
            'extendedProps' => [
                'grupo' => $p->grupo->nombre ?? '—',
                'curso' => $p->curso->nombre ?? '—',
                'aula' => $p->aula->nombre ?? '—',
                'fecha_inicio' => $p->fecha_inicio,
                'fecha_fin' => $p->fecha_fin,
                'hora_inicio' => $p->hora_inicio,
                'hora_fin' => $p->hora_fin,
                'estado' => $p->estado_confirmacion ?? 'pendiente',
            ]
        ];
    });

    return response()->json($eventos);
}
