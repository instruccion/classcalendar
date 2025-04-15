<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeriadoController extends Controller
{
    public function index()
    {
        // Lógica para mostrar los feriados
        return view('admin.feriados.index');
    }

    // Otros métodos según tus necesidades
}
