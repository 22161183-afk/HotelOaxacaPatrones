<?php

namespace App\Listeners;

use App\Events\ReservaCreada;

class EnviarNotificacionReserva
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReservaCreada $event): void
    {
        //
    }
}
