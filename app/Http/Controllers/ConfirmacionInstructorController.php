<?php

namespace App\Http\Controllers;

use App\Models\Programacion;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConfirmacionInstructorController extends Controller
{
    public function mostrar($token)
    {
        $programacion = Programacion::where('token_confirmacion', $token)->firstOrFail();

        return view('instructor.confirmar', compact('programacion'));
    }

    public function procesar(Request $request, $token)
    {
        $programacion = Programacion::where('token_confirmacion', $token)->firstOrFail();

        $request->validate([
            'accion' => 'required|in:confirmar,rechazar',
            'motivo_rechazo' => 'nullable|string|max:1000',
        ]);

        if ($request->accion === 'confirmar') {
            $programacion->estado_confirmacion = 'confirmado';
            $programacion->fecha_confirmacion = Carbon::now();
        } else {
            $programacion->estado_confirmacion = 'rechazado';
            $programacion->fecha_confirmacion = Carbon::now();
            $programacion->motivo_rechazo = $request->motivo_rechazo;
        }

        $programacion->save();

        return redirect()->route('instructor.agenda')->with('success', 'Tu respuesta ha sido registrada. Gracias.');
    }
}
