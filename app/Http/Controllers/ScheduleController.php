<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use App\Models\ActivePatient;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


class ScheduleController extends Controller
{
    private function emptyWeek()
    {
        return [
            'Monday'    => collect(),
            'Tuesday'   => collect(),
            'Wednesday' => collect(),
            'Thursday'  => collect(),
            'Friday'    => collect(),
            'Saturday'  => collect(),
        ];
    }

    public function index()
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = Carbon::now()->endOfWeek(Carbon::SATURDAY);

        $weekNumber = $weekStart->weekOfYear;
        $range = $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');

        $records = SchedulePatients::with('patient')
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $agenda = [
            1 => $this->emptyWeek(),
            2 => $this->emptyWeek(),
            3 => $this->emptyWeek(),
            4 => $this->emptyWeek(),
        ];

        foreach ($records as $record) {
            $day = Carbon::parse($record->date)->format('l');
            $agenda[$record->schedules_id][$day][] = $record;
        }

        foreach ($agenda as $shift => $days) {
            foreach ($days as $day => $patients) {
                $agenda[$shift][$day] = collect($patients)->chunk(15);
            }
        }

        return view('schedule.index', compact('agenda','weekNumber','range'));
    }


    public function destroy($id)
    {
       $schedulePatient = SchedulePatients::findOrFail($id);

        $schedulePatient->delete();
        $activePatient = ActivePatient::where('patient_id', $schedulePatient->patient_id)
            ->where('date', $schedulePatient->date)
            ->first()->delete();
        return back()->with('success','Paciente eliminado de la agenda');
    }
}
