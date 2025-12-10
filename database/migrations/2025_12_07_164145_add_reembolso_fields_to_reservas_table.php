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
            $table->decimal('monto_reembolso', 10, 2)->nullable()->after('precio_total');
            $table->timestamp('fecha_solicitud_reembolso')->nullable()->after('fecha_cancelacion');
            $table->timestamp('fecha_reembolso')->nullable()->after('fecha_solicitud_reembolso');
            $table->text('motivo_reembolso')->nullable()->after('fecha_reembolso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['monto_reembolso', 'fecha_solicitud_reembolso', 'fecha_reembolso', 'motivo_reembolso']);
        });
    }
};
