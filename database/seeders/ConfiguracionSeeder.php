<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ConfiguracionHotel::create([
            'nombre_hotel' => 'Hotel Oaxaca Grand',
            'direccion' => 'Av. Independencia 123, Centro Histórico, Oaxaca de Juárez, Oaxaca, México',
            'telefono' => '+52 951 123 4567',
            'email' => 'reservas@hoteloaxaca.com',
            'sitio_web' => 'https://www.hoteloaxaca.com',
            'hora_checkin' => '15:00:00',
            'hora_checkout' => '12:00:00',
            'impuesto' => 16.00,
            'dias_cancelacion' => 3,
            'politicas' => json_encode([
                'cancelacion' => 'Cancelación gratuita hasta 72 horas antes del check-in',
                'mascotas' => 'Se permiten mascotas pequeñas con cargo adicional',
                'fumadores' => 'Hotel 100% libre de humo',
                'ninos' => 'Niños menores de 12 años gratis compartiendo habitación',
                'deposito' => 'Se requiere depósito del 50% para confirmar reserva',
            ]),
        ]);
    }
}
