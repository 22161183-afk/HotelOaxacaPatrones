<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;

class HabitacionController extends Controller
{
    public function show($id)
    {
        $habitacion = Habitacion::with('tipoHabitacion', 'reservas')->findOrFail($id);

        return view('habitaciones.show', [
            'habitacion' => $habitacion,
        ]);
    }
}
