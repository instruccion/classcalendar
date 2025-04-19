<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // Asegúrate de que esto apunta a tu archivo de layout correcto
        return view('layouts.app');
    }
}
