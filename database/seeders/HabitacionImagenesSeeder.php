<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use Illuminate\Database\Seeder;

class HabitacionImagenesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Imágenes variadas para cada tipo de habitación
        $imagenesStandard = [
            'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1595576508898-0ad5c879a061?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1594560913095-8cf34bbd3784?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=800&h=600&fit=crop',
        ];

        $imagenesDeluxe = [
            'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1591088398332-8a7791972843?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1631049552240-59c37f38802b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1568495248636-6432b97bd949?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1615460549969-36fa19521a4f?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=800&h=600&fit=crop',
        ];

        $imagenesFamiliar = [
            'https://images.unsplash.com/photo-1602002418082-a4443e081dd1?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1566195992011-5f6b21e539aa?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1587985064135-0366536eab42?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1615874959474-d609969a20ed?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1609766857041-ed402ea8069a?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1571508601936-6d0de3cd9bb1?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1598928636135-d146006ff4be?w=800&h=600&fit=crop',
        ];

        $imagenesSuite = [
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1455587734955-081b22074882?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1560185893-a55cbc8c57e8?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1591825729269-caeb344f6df2?w=800&h=600&fit=crop',
        ];

        // Actualizar habitaciones Standard
        $habitacionesStandard = Habitacion::whereHas('tipoHabitacion', function ($query) {
            $query->where('nombre', 'Standard');
        })->get();

        foreach ($habitacionesStandard as $index => $habitacion) {
            $habitacion->update([
                'imagen_url' => $imagenesStandard[$index % count($imagenesStandard)],
            ]);
        }

        // Actualizar habitaciones Deluxe
        $habitacionesDeluxe = Habitacion::whereHas('tipoHabitacion', function ($query) {
            $query->where('nombre', 'Deluxe');
        })->get();

        foreach ($habitacionesDeluxe as $index => $habitacion) {
            $habitacion->update([
                'imagen_url' => $imagenesDeluxe[$index % count($imagenesDeluxe)],
            ]);
        }

        // Actualizar habitaciones Familiar
        $habitacionesFamiliar = Habitacion::whereHas('tipoHabitacion', function ($query) {
            $query->where('nombre', 'Familiar');
        })->get();

        foreach ($habitacionesFamiliar as $index => $habitacion) {
            $habitacion->update([
                'imagen_url' => $imagenesFamiliar[$index % count($imagenesFamiliar)],
            ]);
        }

        // Actualizar habitaciones Suite Presidencial
        $habitacionesSuite = Habitacion::whereHas('tipoHabitacion', function ($query) {
            $query->where('nombre', 'Suite Presidencial');
        })->get();

        foreach ($habitacionesSuite as $index => $habitacion) {
            $habitacion->update([
                'imagen_url' => $imagenesSuite[$index % count($imagenesSuite)],
            ]);
        }

        $this->command->info('✓ Imágenes asignadas a todas las habitaciones');
    }
}
