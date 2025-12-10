<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM de la columna 'estado' para incluir los nuevos valores
        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada', 'en_proceso_reembolso', 'reembolsado') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales del ENUM
        DB::statement("ALTER TABLE reservas MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'cancelada', 'completada') NOT NULL DEFAULT 'pendiente'");
    }
};
