<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->decimal('monto_diferencia', 10, 2)->nullable()->after('monto_reembolso');
            $table->string('tipo_diferencia')->nullable()->after('monto_diferencia'); // 'pagar' o 'reembolsar'
            $table->timestamp('fecha_diferencia_pagada')->nullable()->after('tipo_diferencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['monto_diferencia', 'tipo_diferencia', 'fecha_diferencia_pagada']);
        });
    }
};
