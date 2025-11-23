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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('apellido')->nullable()->change();
            $table->string('documento_identidad')->nullable()->change();
            $table->string('tipo_documento')->nullable()->change();
            $table->string('direccion')->nullable()->change();
            $table->string('ciudad')->nullable()->change();
            $table->string('pais')->nullable()->change();
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('tipo_cliente')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('apellido')->nullable(false)->change();
            $table->string('documento_identidad')->nullable(false)->change();
            $table->string('tipo_documento')->nullable(false)->change();
            $table->string('direccion')->nullable(false)->change();
            $table->string('ciudad')->nullable(false)->change();
            $table->string('pais')->nullable(false)->change();
            $table->date('fecha_nacimiento')->nullable(false)->change();
            $table->string('tipo_cliente')->nullable(false)->change();
        });
    }
};
