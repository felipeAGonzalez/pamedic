<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Patient;
use App\Models\SchedulePatients;
use App\Models\ScheduleCloneBatch;
use App\Models\ActivePatient;
use App\Models\NursePatient;
use App\Models\DialysisPrescription;
use App\Models\DialysisMonitoring;
use App\Models\Machine;
use App\Models\Supply;
use App\Models\SupplyOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
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
        $dates = [];
        foreach (array_keys($this->emptyWeek()) as $offset => $day) {
            $dates[$day] = $inicioSemana->copy()->addDays($offset)->toDateString();
        }

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
        $cloneBatch = ScheduleCloneBatch::where('source_year', $year)
            ->where('source_week', $week)
            ->where('status', 'active')
            ->latest('id')
            ->first();
        $cloneUndo = $cloneBatch ? $this->cloneUndoState($cloneBatch) : null;


        return view('schedule.index', [
            'agenda' => $agenda,
            'machines' => $machines,
            'week' => $week,
            'year' => $year,
            'range' => $range,
            'dates' => $dates,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'cloneBatch' => $cloneBatch,
            'cloneUndo' => $cloneUndo,
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

    public function searchPatients(Request $request)
    {
        $request->merge(['search' => trim((string) $request->query('search'))]);
        $data = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $patients = Patient::query()
            ->where(function ($query) use ($data) {
                $search = $data['search'];
                $query->where('expedient_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('last_name_two', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->orderBy('last_name')
            ->limit(15)
            ->get(['id', 'expedient_number', 'name', 'last_name', 'last_name_two']);

        return response()->json(['patients' => $patients]);
    }

    public function assignPatient(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:patient,id'],
            'schedules_id' => ['required', 'integer', 'exists:schedules,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
        ]);

        $schedule = Schedule::findOrFail($data['schedules_id']);
        if ($schedule->schedule_type === 'emergency') {
            return response()->json(['message' => 'El horario especial no se administra desde esta cuadrícula.'], 422);
        }

        try {
            $record = DB::transaction(function () use ($data) {
                Machine::whereKey($data['machine_id'])->lockForUpdate()->firstOrFail();

                $occupied = SchedulePatients::where('schedules_id', $data['schedules_id'])
                    ->whereDate('date', $data['date'])
                    ->where('machine_id', $data['machine_id'])
                    ->lockForUpdate()
                    ->exists();

                if ($occupied) {
                    throw new ConflictHttpException('La máquina ya está ocupada en este día y turno.');
                }

                $alreadyScheduled = SchedulePatients::where('patient_id', $data['patient_id'])
                    ->whereDate('date', $data['date'])
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyScheduled) {
                    throw new ConflictHttpException('El paciente ya está programado para este día.');
                }

                $activePatient = ActivePatient::where('patient_id', $data['patient_id'])
                    ->whereDate('date', $data['date'])
                    ->lockForUpdate()
                    ->first();

                if ($activePatient) {
                    throw new ConflictHttpException('El paciente ya tiene asistencia registrada para este día.');
                }

                $record = SchedulePatients::create([
                    'schedules_id' => $data['schedules_id'],
                    'patient_id' => $data['patient_id'],
                    'date' => $data['date'],
                    'machine_id' => $data['machine_id'],
                    'continue_schedule' => true,
                ]);

                ActivePatient::create([
                    'patient_id' => $data['patient_id'],
                    'date' => $data['date'],
                    'active' => 1,
                ]);

                return $record;
            });

            $record->load('patient');

            return response()->json([
                'message' => 'Paciente agregado al horario correctamente.',
                'html' => view('schedule.partials.patient-card', [
                    'record' => $record,
                    'canEdit' => true,
                ])->render(),
            ], 201);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json(['message' => 'El paciente o la máquina acaban de ser ocupados por otro usuario.'], 409);
            }

            throw $exception;
        }
    }

    public function movePatient(Request $request, $id)
    {
        $data = $request->validate([
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
        ]);

        try {
            $result = DB::transaction(function () use ($id, $data) {
                $sourceSnapshot = SchedulePatients::findOrFail($id);
                $sourceMachineId = (int) $sourceSnapshot->machine_id;
                $targetMachineId = (int) $data['machine_id'];

                if ($sourceMachineId === $targetMachineId) {
                    return ['swapped_record_id' => null];
                }

                Machine::whereIn('id', collect([$sourceMachineId, $targetMachineId])->sort()->values())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $source = SchedulePatients::whereKey($id)->lockForUpdate()->firstOrFail();
                if ((int) $source->machine_id !== $sourceMachineId) {
                    throw new ConflictHttpException('El paciente fue movido por otro usuario. Actualice el esquema.');
                }

                $targetRecords = SchedulePatients::where('schedules_id', $source->schedules_id)
                    ->whereDate('date', $source->date)
                    ->where('machine_id', $targetMachineId)
                    ->whereKeyNot($source->id)
                    ->lockForUpdate()
                    ->get();

                if ($targetRecords->count() > 1) {
                    throw new ConflictHttpException('La máquina destino contiene registros duplicados y no puede intercambiarse automáticamente.');
                }

                $target = $targetRecords->first();

                if ($target) {
                    DB::statement(
                        'UPDATE schedules_patient SET machine_id = CASE id WHEN ? THEN ? WHEN ? THEN ? END, updated_at = ? WHERE id IN (?, ?)',
                        [$source->id, $targetMachineId, $target->id, $sourceMachineId, now(), $source->id, $target->id]
                    );
                } else {
                    $source->machine_id = $targetMachineId;
                    $source->save();
                }

                return ['swapped_record_id' => $target?->id];
            });

            return response()->json([
                'message' => $result['swapped_record_id']
                    ? 'Pacientes intercambiados correctamente.'
                    : 'Paciente cambiado de máquina correctamente.',
                'swapped_record_id' => $result['swapped_record_id'],
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return response()->json(['message' => 'La máquina fue ocupada por otro usuario.'], 409);
            }

            throw $exception;
        }
    }

    public function permanentDestroy($id)
    {
        $schedulePatient = SchedulePatients::findOrFail($id);
        $patientId = $schedulePatient->patient_id;

        DB::transaction(function () use ($patientId) {
            SchedulePatients::withTrashed()
                ->where('patient_id', $patientId)
                ->update(['continue_schedule' => false]);

            $futureRecords = SchedulePatients::where('patient_id', $patientId)
                ->whereDate('date', '>=', today()->toDateString())
                ->lockForUpdate()
                ->get();

            foreach ($futureRecords as $record) {
                $this->cancelScheduleRecord($record, false);
            }
        });

        return back()->with('success', 'Paciente dado de baja definitivamente del horario.');
    }

    public function destroy($id)
    {
        $schedulePatient = SchedulePatients::findOrFail($id);

        DB::transaction(function () use ($schedulePatient) {
            $this->cancelScheduleRecord($schedulePatient, true);
        });

        return back()->with('success', 'Ausencia registrada correctamente. El paciente continuará la siguiente semana.');
    }

    private function cancelScheduleRecord(SchedulePatients $schedulePatient, bool $continueSchedule): void
    {
        $date = $schedulePatient->date->toDateString();
        $patientId = $schedulePatient->patient_id;

        ActivePatient::where('patient_id', $patientId)
            ->whereDate('date', $date)
            ->first()?->delete();

        $schedulePatient->continue_schedule = $continueSchedule;
        $schedulePatient->save();
        $schedulePatient->delete();

        $this->restoreCancelledSupplies($patientId, $date);
    }

    private function restoreCancelledSupplies(int $patientId, string $date): void
    {
        $order = SupplyOrder::where('period_start', '<=', $date)
            ->where('period_end', '>=', $date)
            ->latest('generated_at')
            ->first();

        if (! $order) {
            return;
        }

        $vascularAccess = DialysisMonitoring::where('patient_id', $patientId)
            ->where('history', 1)
            ->latest('id')
            ->value('vascular_access');

        $typeDialyzer = DialysisPrescription::where('patient_id', $patientId)
            ->where('history', 1)
            ->latest('id')
            ->value('type_dialyzer');

        $isElisio = in_array($typeDialyzer, ['F6ELISIO21H', 'F6ELISIO19H']);

        Supply::all()->each(function ($supply) use ($vascularAccess, $isElisio) {
            $applies = false;

            if ($supply->type === 'filter') {
                $applies = $isElisio;
            } else {
                $applies = match ($supply->for_vascular_access) {
                    'fistula' => $vascularAccess === 'fistula',
                    'catheter' => $vascularAccess === 'catheter',
                    'both' => in_array($vascularAccess, ['fistula', 'catheter']),
                    default => false,
                };
            }

            if ($applies) {
                $supply->increment('existencias');
            }
        });
    }

    public function undoCloneWeek($batchId)
    {
        $batch = ScheduleCloneBatch::findOrFail($batchId);

        if ($batch->status !== 'active') {
            return back()->with('error', 'Esta clonación ya fue deshecha.');
        }

        $blockReason = DB::transaction(function () use ($batchId) {
            $batch = ScheduleCloneBatch::whereKey($batchId)->lockForUpdate()->firstOrFail();

            if ($batch->status !== 'active') {
                return 'Esta clonación ya fue deshecha.';
            }

            [$targetStart, $targetEnd] = $this->cloneTargetBounds($batch);

            Machine::orderBy('id')->lockForUpdate()->get();
            SchedulePatients::withTrashed()
                ->whereBetween('date', [$targetStart, $targetEnd])
                ->lockForUpdate()
                ->get();
            SupplyOrder::where('period_start', '<=', $targetEnd->toDateString())
                ->where('period_end', '>=', $targetStart->toDateString())
                ->lockForUpdate()
                ->get();

            $reason = $this->cloneUndoBlockReason($batch);
            if ($reason) {
                return $reason;
            }

            $records = SchedulePatients::where('clone_batch_id', $batch->id)
                ->lockForUpdate()
                ->get();

            foreach ($records as $record) {
                ActivePatient::where('patient_id', $record->patient_id)
                    ->whereDate('date', $record->date)
                    ->delete();

                $record->forceDelete();
            }

            $batch->update([
                'status' => 'undone',
                'undone_by' => auth()->id(),
                'undone_at' => now(),
            ]);

            return null;
        });

        if ($blockReason) {
            return back()->with('error', $blockReason);
        }

        return back()->with('success', 'Clonación deshecha correctamente.');
    }

    private function cloneUndoState(ScheduleCloneBatch $batch): array
    {
        $reason = $this->cloneUndoBlockReason($batch);

        return [
            'can_undo' => $reason === null,
            'reason' => $reason,
        ];
    }

    private function cloneUndoBlockReason(ScheduleCloneBatch $batch): ?string
    {
        if ($batch->status !== 'active') {
            return 'Esta clonación ya fue deshecha.';
        }

        if (! $batch->snapshot_hash
            || ! hash_equals($batch->snapshot_hash, $this->cloneWeekSnapshotHash($batch))) {
            return 'No se puede deshacer porque la semana clonada ya tiene cambios.';
        }

        $records = SchedulePatients::where('clone_batch_id', $batch->id)->get();
        if ($records->count() !== (int) $batch->records_count) {
            return 'No se puede deshacer porque la semana clonada ya tiene cambios.';
        }

        [$targetStart, $targetEnd] = $this->cloneTargetBounds($batch);
        $activePatients = ActivePatient::whereIn('patient_id', $records->pluck('patient_id'))
            ->whereBetween('date', [$targetStart->toDateString(), $targetEnd->toDateString()])
            ->get();

        $activeByPatientAndDate = $activePatients->keyBy(function ($activePatient) {
            return $activePatient->patient_id.'|'.Carbon::parse($activePatient->date)->toDateString();
        });

        $batchActiveIds = [];
        foreach ($records as $record) {
            $key = $record->patient_id.'|'.$record->date->toDateString();
            $activePatient = $activeByPatientAndDate->get($key);

            if (! $activePatient || ! $activePatient->active) {
                return 'No se puede deshacer porque un paciente ya inició el flujo de atención.';
            }

            $batchActiveIds[] = $activePatient->id;
        }

        if (NursePatient::whereIn('active_patient_id', $batchActiveIds)->exists()) {
            return 'No se puede deshacer porque un paciente ya fue asignado o inició tratamiento.';
        }

        if (SupplyOrder::where('period_start', '<=', $targetEnd->toDateString())
            ->where('period_end', '>=', $targetStart->toDateString())
            ->exists()) {
            return 'No se puede deshacer porque ya existe un pedido de insumos para esa semana.';
        }

        return null;
    }

    private function cloneWeekSnapshotHash(ScheduleCloneBatch $batch): string
    {
        [$targetStart, $targetEnd] = $this->cloneTargetBounds($batch);

        $state = SchedulePatients::withTrashed()
            ->whereBetween('date', [$targetStart, $targetEnd])
            ->orderBy('id')
            ->get([
                'id',
                'patient_id',
                'schedules_id',
                'date',
                'machine_id',
                'deleted_at',
                'continue_schedule',
                'clone_batch_id',
            ])
            ->map(function ($record) {
                return [
                    'id' => (int) $record->id,
                    'patient_id' => (int) $record->patient_id,
                    'schedules_id' => (int) $record->schedules_id,
                    'date' => $record->date->toDateString(),
                    'machine_id' => (string) $record->machine_id,
                    'deleted_at' => $record->deleted_at?->format('Y-m-d H:i:s'),
                    'continue_schedule' => (bool) $record->continue_schedule,
                    'clone_batch_id' => $record->clone_batch_id ? (int) $record->clone_batch_id : null,
                ];
            })
            ->values()
            ->all();

        return hash('sha256', json_encode($state, JSON_THROW_ON_ERROR));
    }

    private function cloneTargetBounds(ScheduleCloneBatch $batch): array
    {
        $start = Carbon::now()
            ->setISODate($batch->target_year, $batch->target_week)
            ->startOfWeek();

        return [$start, $start->copy()->endOfWeek()];
    }

    public function cloneWeek(Request $request)
    {
        $week = (int) $request->week;
        $year = (int) $request->year;

        $startWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();
        $nextWeekStart = $startWeek->copy()->addWeek();
        $nextWeekEnd = $endWeek->copy()->addWeek();

        try {
            $cloned = DB::transaction(function () use ($year, $week, $startWeek, $endWeek, $nextWeekStart, $nextWeekEnd) {
                $alreadyCloned = SchedulePatients::whereBetween('date', [$nextWeekStart, $nextWeekEnd])
                    ->where('schedules_id', '!=', 5)
                    ->exists();

                $activeBatch = ScheduleCloneBatch::where('source_year', $year)
                    ->where('source_week', $week)
                    ->where('status', 'active')
                    ->exists();

                if ($alreadyCloned || $activeBatch) {
                    return false;
                }

                $batch = ScheduleCloneBatch::create([
                    'source_year' => $year,
                    'source_week' => $week,
                    'target_year' => (int) $nextWeekStart->isoWeekYear,
                    'target_week' => (int) $nextWeekStart->isoWeek,
                    'status' => 'active',
                    'cloned_by' => auth()->id(),
                ]);

                $records = SchedulePatients::withTrashed()
                    ->whereBetween('date', [$startWeek, $endWeek])
                    ->where('schedules_id', '!=', 5)
                    ->where('continue_schedule', true)
                    ->get();

                $createdCount = 0;

                foreach ($records as $record) {
                    $newDate = Carbon::parse($record->date)->addWeek();

                    $clonedRecord = SchedulePatients::firstOrCreate([
                        'patient_id' => $record->patient_id,
                        'date' => $newDate,
                    ], [
                        'schedules_id' => $record->schedules_id,
                        'machine_id' => $record->machine_id,
                        'continue_schedule' => true,
                        'clone_batch_id' => $batch->id,
                    ]);

                    if ($clonedRecord->wasRecentlyCreated) {
                        $createdCount++;
                    }

                    ActivePatient::firstOrCreate([
                        'patient_id' => $record->patient_id,
                        'date' => $newDate,
                    ], [
                        'active' => 1,
                    ]);
                }

                $batch->update([
                    'records_count' => $createdCount,
                    'snapshot_hash' => $this->cloneWeekSnapshotHash($batch),
                ]);

                return true;
            });
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->with('error', 'Esta semana ya fue clonada');
            }

            throw $exception;
        }

        if (! $cloned) {
            return back()->with('error', 'Esta semana ya fue clonada');
        }

        return back()->with('success', 'Semana clonada correctamente');
    }
}
