<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;

class AuditoriaController extends Controller
{
    /**
     * Mostrar todas las auditorías.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Aquí puedes filtrar y mostrar las auditorías
        $auditorias = Auditoria::with('user')->latest()->get();  // Obtén todas las auditorías

        return view('admin.auditorias.index', compact('auditorias'));
    }
}
