<?php

namespace App\Console\Commands;

use App\Patterns\Creational\HabitacionFactoryCreator;
use Illuminate\Console\Command;

class CrearHabitacionesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'habitaciones:crear
                            {tipo : Tipo de habitación (deluxe, standard, suite, familiar)}
                            {numero : Número de la habitación}
                            {piso : Piso de la habitación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear habitación usando el patrón Factory con configuración predefinida según tipo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tipo = $this->argument('tipo');
        $numero = $this->argument('numero');
        $piso = $this->argument('piso');

        // Validar tipo
        $tiposValidos = ['deluxe', 'standard', 'suite', 'familiar'];
        if (! in_array($tipo, $tiposValidos)) {
            $this->error('Tipo de habitación inválido. Tipos válidos: '.implode(', ', $tiposValidos));

            return self::FAILURE;
        }

        // Verificar que el número no exista
        if (\App\Models\Habitacion::where('numero', $numero)->exists()) {
            $this->error("Ya existe una habitación con el número {$numero}");

            return self::FAILURE;
        }

        $this->info("Creando habitación {$tipo} #{$numero} en el piso {$piso}...");

        try {
            // Usar el patrón Factory para crear la habitación
            $factory = HabitacionFactoryCreator::getFactory($tipo);
            $habitacion = $factory->procesarCreacion([
                'numero' => $numero,
                'piso' => $piso,
            ]);

            $this->newLine();
            $this->info('✓ Habitación creada exitosamente!');
            $this->newLine();

            // Mostrar detalles
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $habitacion->id],
                    ['Número', $habitacion->numero],
                    ['Tipo', $habitacion->tipoHabitacion->nombre],
                    ['Piso', $habitacion->piso],
                    ['Capacidad', $habitacion->capacidad.' personas'],
                    ['Precio Base', '$'.number_format($habitacion->precio_base, 2)],
                    ['Estado', $habitacion->estado],
                    ['Amenidades', count(json_decode($habitacion->amenidades, true)).' incluidas'],
                ]
            );

            $this->newLine();
            $this->comment('Amenidades incluidas:');
            foreach (json_decode($habitacion->amenidades, true) as $amenidad) {
                $this->line("  • {$amenidad}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al crear la habitación: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
