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
        Schema::create('configuracion_hotels', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_hotel');
            $table->text('direccion');
            $table->string('telefono');
            $table->string('email');
            $table->string('sitio_web')->nullable();
            $table->time('hora_checkin')->default('14:00:00');
            $table->time('hora_checkout')->default('12:00:00');
            $table->decimal('impuesto', 5, 2)->default(16.00);
            $table->integer('dias_cancelacion')->default(2);
            $table->json('politicas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_hotels');
    }
};
