<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\MetodoPago::create([
            'nombre' => 'Tarjeta de Crédito',
            'descripcion' => 'Pago con tarjeta de crédito (Visa, MasterCard, AMEX)',
            'activo' => true,
            'comision' => 3.5,
        ]);

        \App\Models\MetodoPago::create([
            'nombre' => 'Tarjeta de Débito',
            'descripcion' => 'Pago con tarjeta de débito',
            'activo' => true,
            'comision' => 2.0,
        ]);

        \App\Models\MetodoPago::create([
            'nombre' => 'PayPal',
            'descripcion' => 'Pago a través de PayPal',
            'activo' => true,
            'comision' => 4.0,
        ]);

        \App\Models\MetodoPago::create([
            'nombre' => 'Transferencia Bancaria',
            'descripcion' => 'Transferencia bancaria directa',
            'activo' => true,
            'comision' => 0.0,
        ]);

        \App\Models\MetodoPago::create([
            'nombre' => 'Efectivo',
            'descripcion' => 'Pago en efectivo al momento del check-in',
            'activo' => true,
            'comision' => 0.0,
        ]);

        \App\Models\MetodoPago::create([
            'nombre' => 'Mercado Pago',
            'descripcion' => 'Pago a través de Mercado Pago',
            'activo' => true,
            'comision' => 3.8,
        ]);
    }
}
