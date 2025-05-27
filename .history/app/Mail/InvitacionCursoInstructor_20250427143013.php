<?php

namespace App\Mail;

use App\Models\Programacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitacionCursoInstructor extends Mailable
{
    use Queueable, SerializesModels;

    public Programacion $programacion;

    public function __construct(Programacion $programacion)
    {
        $this->programacion = $programacion;
    }

    public function build()
    {
        return $this->subject('Invitación para Confirmar Asignación de Curso')
            ->markdown('emails.instructores.invitacion')
            ->with([
                'programacion' => $this->programacion,
            ]);
    }

}
