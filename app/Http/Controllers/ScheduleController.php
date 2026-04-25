<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
            $registros = SchedulePatients::with('patient')->get();

            // Turnos base
            $agenda = [
                1 => $this->semanaVacia(),
                2 => $this->semanaVacia(),
                3 => $this->semanaVacia(),
                4 => $this->semanaVacia(),
            ];

            // Llenar agenda por turno y día
            foreach ($registros as $registro) {
                $dia = \Carbon\Carbon::parse($registro->date)->format('l');
                $agenda[$registro->schedules_id][$dia][] = $registro->patient;
            }

            // 🔥 dividir cada día en bloques de 15 pacientes
            foreach ($agenda as $turno => $dias) {
                foreach ($dias as $dia => $pacientes) {
                    $agenda[$turno][$dia] = collect($pacientes)->chunk(15);
                }
            }

            $semanaNumero = now()->weekOfYear;
            $rango = now()->startOfWeek()->format('d M') . ' - ' . now()->endOfWeek()->format('d M');


            return view('schedule.index', compact('agenda','semanaNumero','rango'));
    }

    private function semanaVacia()
        {
            return [
                'Monday'=>collect(),
                'Tuesday'=>collect(),
                'Wednesday'=>collect(),
                'Thursday'=>collect(),
                'Friday'=>collect(),
                'Saturday'=>collect(),
            ];
    }

}
