<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para agregar el estado 'reservada'
        DB::statement("ALTER TABLE habitacions MODIFY COLUMN estado ENUM('disponible', 'reservada', 'ocupada', 'mantenimiento') DEFAULT 'disponible'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Volver al enum original
        DB::statement("ALTER TABLE habitacions MODIFY COLUMN estado ENUM('disponible', 'ocupada', 'mantenimiento') DEFAULT 'disponible'");
    }
};
