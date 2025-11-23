<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Desayuno Buffet', 'descripcion' => 'Desayuno buffet internacional', 'precio' => 250.00, 'tipo' => 'comida'],
            ['nombre' => 'Room Service 24h', 'descripcion' => 'Servicio a la habitación 24 horas', 'precio' => 150.00, 'tipo' => 'comida'],
            ['nombre' => 'Cena Romántica', 'descripcion' => 'Cena romántica para dos personas', 'precio' => 800.00, 'tipo' => 'comida'],
            ['nombre' => 'Masaje Relajante', 'descripcion' => 'Masaje relajante de 60 minutos', 'precio' => 600.00, 'tipo' => 'spa'],
            ['nombre' => 'Spa Completo', 'descripcion' => 'Paquete spa completo (masaje, facial, sauna)', 'precio' => 1200.00, 'tipo' => 'spa'],
            ['nombre' => 'Traslado Aeropuerto', 'descripcion' => 'Traslado privado aeropuerto-hotel', 'precio' => 400.00, 'tipo' => 'transporte'],
            ['nombre' => 'Tour Ciudad', 'descripcion' => 'Tour guiado por la ciudad de Oaxaca', 'precio' => 500.00, 'tipo' => 'transporte'],
            ['nombre' => 'Limpieza Extra', 'descripcion' => 'Servicio de limpieza adicional', 'precio' => 200.00, 'tipo' => 'limpieza'],
            ['nombre' => 'Lavandería Express', 'descripcion' => 'Servicio de lavandería express', 'precio' => 300.00, 'tipo' => 'limpieza'],
            ['nombre' => 'Botella de Vino', 'descripcion' => 'Botella de vino premium', 'precio' => 450.00, 'tipo' => 'bebida'],
            ['nombre' => 'Champagne', 'descripcion' => 'Botella de champagne', 'precio' => 800.00, 'tipo' => 'bebida'],
            ['nombre' => 'Decoración Romántica', 'descripcion' => 'Decoración especial con pétalos y velas', 'precio' => 350.00, 'tipo' => 'otro'],
        ];

        foreach ($servicios as $servicio) {
            \App\Models\Servicio::create($servicio);
        }
    }
}
