<?php

namespace App\Listeners;

use App\Events\PagoRealizado;

class EnviarReciboCliente
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
    public function handle(PagoRealizado $event): void
    {
        //
    }
}
