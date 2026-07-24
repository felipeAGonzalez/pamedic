<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use App\Models\ActivePatient;
use App\Models\DialysisPrescription;
use App\Models\Machine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;


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


    public function printPdf($year, $week)
    {
        $inicioSemana = Carbon::now()
            ->setISODate($year, $week)
            ->startOfWeek(Carbon::MONDAY);

        $finSemana = Carbon::now()
            ->setISODate($year, $week)
            ->endOfWeek(Carbon::SATURDAY);

        $machines = Machine::orderBy('id')->get();

        $registros = SchedulePatients::with(['patient', 'machine'])
            ->whereBetween('date', [$inicioSemana, $finSemana])
            ->get();

        $patientIds = $registros->pluck('patient_id')->unique();

        $niproPatientIds = DialysisPrescription::whereIn('patient_id', $patientIds)
            ->where('history', 1)
            ->whereIn('id', function ($q) use ($patientIds) {
                $q->selectRaw('MAX(id)')
                    ->from('dialysis_prescription')
                    ->whereIn('patient_id', $patientIds)
                    ->where('history', 1)
                    ->groupBy('patient_id');
            })
            ->whereIn('type_dialyzer', ['F6ELISIO21H', 'F6ELISIO19H'])
            ->pluck('patient_id')
            ->flip();

        $agenda = [
            1 => $this->emptyWeek(),
            2 => $this->emptyWeek(),
            3 => $this->emptyWeek(),
            4 => $this->emptyWeek(),
        ];

        foreach ($registros as $registro) {
            $dia = Carbon::parse($registro->date)->format('l');
            if (isset($agenda[$registro->schedules_id])) {
                $agenda[$registro->schedules_id][$dia][] = $registro;
            }
        }

        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $mesInicio = $meses[$inicioSemana->month];
        $mesFin    = $meses[$finSemana->month];
        $weekLabel = "SEMANA {$week} DEL {$inicioSemana->format('d')} DE {$mesInicio} AL {$finSemana->format('d')} DE {$mesFin} DEL {$year}";

        $diasNombres = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado',
        ];

        $days   = [];
        $cursor = $inicioSemana->copy();
        foreach ($diasNombres as $en => $es) {
            $days[] = ['key' => $en, 'name' => $es, 'date' => $cursor->format('d')];
            $cursor->addDay();
        }

        $turnLabels = [
            1 => 'PRIMER TURNO DE 6:00 A.M. A 9:30 A.M.',
            2 => 'SEGUNDO TURNO DE 10:00 A.M. A 13:30 P.M.',
            3 => 'TERCER TURNO DE 14:00 P.M. A 17:30 P.M.',
            4 => 'CUARTO TURNO DE 17:30 P.M. A 21:00 P.M.',
        ];

        $pdf = Pdf::loadView('schedule.pdf', compact(
            'agenda', 'machines', 'days', 'weekLabel', 'turnLabels', 'week', 'year', 'niproPatientIds'
        ))->setPaper('legal', 'landscape');

        return $pdf->stream("Programacion-Semana-{$week}-{$year}.pdf");
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
