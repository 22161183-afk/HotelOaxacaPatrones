<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = \App\Models\TipoHabitacion::all();

        // Habitaciones Deluxe (piso 1-2)
        $deluxe = $tipos->where('nombre', 'Deluxe')->first();
        for ($i = 101; $i <= 110; $i++) {
            \App\Models\Habitacion::create([
                'tipo_habitacion_id' => $deluxe->id,
                'numero' => (string) $i,
                'piso' => 1,
                'capacidad' => 2,
                'precio_base' => 1500.00,
                'descripcion' => 'Habitación Deluxe con vista panorámica',
                'amenidades' => json_encode(['WiFi', 'TV Smart 55"', 'Minibar', 'Aire acondicionado', 'Caja fuerte']),
                'estado' => 'disponible',
            ]);
        }

        // Habitaciones Standard (piso 3-4)
        $standard = $tipos->where('nombre', 'Standard')->first();
        for ($i = 301; $i <= 320; $i++) {
            \App\Models\Habitacion::create([
                'tipo_habitacion_id' => $standard->id,
                'numero' => (string) $i,
                'piso' => 3,
                'capacidad' => 2,
                'precio_base' => 800.00,
                'descripcion' => 'Habitación Standard confortable',
                'amenidades' => json_encode(['WiFi', 'TV', 'Aire acondicionado']),
                'estado' => 'disponible',
            ]);
        }

        // Suite Presidencial (piso 5)
        $suite = $tipos->where('nombre', 'Suite Presidencial')->first();
        for ($i = 501; $i <= 503; $i++) {
            \App\Models\Habitacion::create([
                'tipo_habitacion_id' => $suite->id,
                'numero' => (string) $i,
                'piso' => 5,
                'capacidad' => 4,
                'precio_base' => 3000.00,
                'descripcion' => 'Suite Presidencial con servicio premium',
                'amenidades' => json_encode(['WiFi', 'TV Smart 75"', 'Minibar Premium', 'Jacuzzi', 'Terraza privada', 'Butler']),
                'estado' => 'disponible',
            ]);
        }

        // Habitaciones Familiares (piso 2)
        $familiar = $tipos->where('nombre', 'Familiar')->first();
        for ($i = 201; $i <= 210; $i++) {
            \App\Models\Habitacion::create([
                'tipo_habitacion_id' => $familiar->id,
                'numero' => (string) $i,
                'piso' => 2,
                'capacidad' => 5,
                'precio_base' => 1200.00,
                'descripcion' => 'Habitación Familiar espaciosa',
                'amenidades' => json_encode(['WiFi', 'TV Smart', 'Minibar', 'Balcón', 'Mesa de trabajo']),
                'estado' => 'disponible',
            ]);
        }
    }
}
