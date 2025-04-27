<?php

namespace App\Mail;

use App\Models\Programacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmacionInstructorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Programacion $programacion;

    public function __construct(Programacion $programacion)
    {
        $this->programacion = $programacion;
    }

    public function build()
    {
        return $this->subject('Confirmación de participación en curso')
            ->view('emails.confirmar-instructor')
            ->with(['programacion' => $this->programacion]);
    }
}
