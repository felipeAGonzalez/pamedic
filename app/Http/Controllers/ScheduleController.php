<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use App\Models\ActivePatient;
use App\Models\Machine;
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

    public function index($year = null, $week = null)
    {
        $week = $week ?? now()->weekOfYear;
        $year = $year ?? now()->year;

        $inicioSemana = Carbon::now()
            ->setISODate($year, $week)
            ->startOfWeek(Carbon::MONDAY);

        $finSemana = Carbon::now()
            ->setISODate($year, $week)
            ->endOfWeek(Carbon::SATURDAY);

        $range = $inicioSemana->format('d M') . ' - ' . $finSemana->format('d M');

        $machines = Machine::orderBy('id')->get();

        $registros = SchedulePatients::with(['patient','machine'])
            ->whereBetween('date', [$inicioSemana, $finSemana])
            ->get();

        $agenda = [
            1 => $this->emptyWeek(),
            2 => $this->emptyWeek(),
            3 => $this->emptyWeek(),
            4 => $this->emptyWeek(),
        ];

        foreach ($registros as $registro) {
            $dia = Carbon::parse($registro->date)->format('l');
            $agenda[$registro->schedules_id][$dia][] = $registro;
        }
        $prevWeek = Carbon::now()->setISODate($year,$week)->subWeek();
        $nextWeek = Carbon::now()->setISODate($year,$week)->addWeek();

        return view('schedule.index', [
            'agenda'    => $agenda,
            'machines'  => $machines,
            'week'      => $week,
            'year'      => $year,
            'range'     => $range,
            'prevWeek'  => $prevWeek,
            'nextWeek'  => $nextWeek
        ]);
    }


    public function destroy($id)
    {
        $schedulePatient = SchedulePatients::findOrFail($id);

        \DB::transaction(function () use ($schedulePatient) {
            ActivePatient::where('patient_id', $schedulePatient->patient_id)
                ->where('date', $schedulePatient->date)
                ->first()?->delete();

            $schedulePatient->delete();
        });

        return back()->with('success', 'Paciente eliminado de la agenda');
    }

    public function cloneWeek(Request $request)
{
    $week = $request->week;
    $year = $request->year;

    $startWeek     = Carbon::now()->setISODate($year,$week)->startOfWeek();
    $endWeek       = Carbon::now()->setISODate($year,$week)->endOfWeek();
    $nextWeekStart = $startWeek->copy()->addWeek();
    $nextWeekEnd   = $endWeek->copy()->addWeek();

    $alreadyCloned = SchedulePatients::whereBetween('date', [$nextWeekStart, $nextWeekEnd])
        ->where('schedules_id', '!=', 5)
        ->exists();

    if ($alreadyCloned) {
        return back()->with('error', 'Esta semana ya fue clonada');
    }

    $records = SchedulePatients::whereBetween('date',[$startWeek,$endWeek])->where('schedules_id','!=',5)->get();

    foreach($records as $record){
        $newDate = Carbon::parse($record->date)->addWeek();

        SchedulePatients::firstOrCreate([
            'schedules_id' => $record->schedules_id,
            'patient_id'   => $record->patient_id,
            'date'         => $newDate,
        ], [
            'machine_id' => $record->machine_id,
        ]);

        ActivePatient::firstOrCreate([
            'patient_id' => $record->patient_id,
            'date'       => $newDate,
        ], [
            'active' => 1,
        ]);
    }

    return back()->with('success', 'Semana clonada correctamente');
}
}
