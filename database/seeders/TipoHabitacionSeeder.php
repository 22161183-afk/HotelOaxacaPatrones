<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TipoHabitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\TipoHabitacion::create([
            'nombre' => 'Deluxe',
            'descripcion' => 'Habitación de lujo con todas las comodidades',
            'capacidad_maxima' => 2,
            'precio_base' => 1500.00,
            'caracteristicas' => json_encode(['Vista al mar', 'Jacuzzi', 'King Size Bed', 'Smart TV 55"']),
        ]);

        \App\Models\TipoHabitacion::create([
            'nombre' => 'Suite Presidencial',
            'descripcion' => 'Suite presidencial con sala de estar y comedor',
            'capacidad_maxima' => 4,
            'precio_base' => 3000.00,
            'caracteristicas' => json_encode(['2 Habitaciones', 'Sala', 'Comedor', 'Terraza privada', 'Butler service']),
        ]);

        \App\Models\TipoHabitacion::create([
            'nombre' => 'Standard',
            'descripcion' => 'Habitación estándar con servicios básicos',
            'capacidad_maxima' => 2,
            'precio_base' => 800.00,
            'caracteristicas' => json_encode(['WiFi', 'TV por cable', 'Aire acondicionado', 'Baño privado']),
        ]);

        \App\Models\TipoHabitacion::create([
            'nombre' => 'Familiar',
            'descripcion' => 'Habitación amplia ideal para familias',
            'capacidad_maxima' => 5,
            'precio_base' => 1200.00,
            'caracteristicas' => json_encode(['2 Camas matrimoniales', '1 Cama individual', 'Minibar', 'Balcón']),
        ]);
    }
}
