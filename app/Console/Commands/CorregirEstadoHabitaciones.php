<?php

namespace App\Console\Commands;

use App\Models\Habitacion;
use Illuminate\Console\Command;

class CorregirEstadoHabitaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'habitaciones:corregir-estado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige el estado de las habitaciones basándose en sus reservas activas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Verificando estado de habitaciones...');

        // Obtener todas las habitaciones
        $habitaciones = Habitacion::with(['reservas' => function ($query) {
            $query->whereIn('estado', ['pendiente', 'confirmada', 'completada']);
        }])->get();

        $corregidas = 0;

        foreach ($habitaciones as $habitacion) {
            $reservasActivas = $habitacion->reservas->count();

            // Si tiene reservas activas pero no está ocupada o reservada
            if ($reservasActivas > 0 && ! in_array($habitacion->estado, ['ocupada', 'reservada'])) {
                $habitacion->update(['estado' => 'ocupada']);
                $this->warn("Habitación #{$habitacion->numero} corregida a 'ocupada' (tenía {$reservasActivas} reserva(s) activa(s))");
                $corregidas++;
            }

            // Si NO tiene reservas activas pero está ocupada o reservada
            if ($reservasActivas == 0 && in_array($habitacion->estado, ['ocupada', 'reservada'])) {
                $habitacion->update(['estado' => 'disponible']);
                $this->warn("Habitación #{$habitacion->numero} corregida a 'disponible' (no tenía reservas activas)");
                $corregidas++;
            }
        }

        if ($corregidas > 0) {
            $this->info("✓ Se corrigieron {$corregidas} habitación(es)");
        } else {
            $this->info('✓ Todas las habitaciones están correctamente configuradas');
        }

        return Command::SUCCESS;
    }
}
