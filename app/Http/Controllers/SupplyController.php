<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivePatient;
use App\Models\SchedulePatients;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use App\Models\SupplyOrder;
use App\Models\NursePatient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplyController extends Controller
{

    public function index()
    {
        $supplies = Supply::orderBy('material')->paginate(15);
        $user     = auth()->user();
        return view('supplies.index', compact('supplies', 'user'));
    }

    public function printSupplies(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        $result = DB::transaction(function () use ($startDate, $endDate) {
            $previousOrder = SupplyOrder::whereNotNull('emergency_to')
                ->latest('emergency_to')
                ->lockForUpdate()
                ->first();
            $generatedAt = now();
            $emergencyFrom = $previousOrder?->emergency_to
                ?? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->subSecond();

            $calculation = $this->calculationWithEmergencies(
                $startDate,
                $endDate,
                $emergencyFrom,
                $generatedAt,
                true
            );

            $order = SupplyOrder::create([
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'generated_at' => $generatedAt,
                'emergency_from' => $emergencyFrom,
                'emergency_to' => $generatedAt,
                'emergency_count' => $calculation['emergency_count'],
            ]);

            foreach ($calculation['supplies'] as $supply) {
                $order->items()->create([
                    'supply_id' => $supply->id,
                    'material' => $supply->material,
                    'requested_quantity' => $supply->requested_quantity,
                    'cancellation_quantity' => $supply->baja_quantity,
                ]);

                if ($supply->reserve_used > 0) {
                    $remainingReserve = round(
                        max(0, (float) $supply->existencias - $supply->reserve_used),
                        2
                    );

                    Supply::whereKey($supply->id)->update([
                        'existencias' => $remainingReserve,
                        'reserve_origin' => $remainingReserve === 0.0
                            ? null
                            : $supply->reserve_origin,
                    ]);
                }
            }

            return [
                'order' => $order,
                'supplies' => $calculation['supplies'],
            ];
        }, 3);

        return $this->supplyOrderPdf($result['order'], $result['supplies']);
    }

    public function reprintSupplies(SupplyOrder $order)
    {
        $order->load('items');
        abort_if($order->items->isEmpty(), 404, 'Esta requisición no cuenta con un snapshot para reimpresión.');

        return $this->supplyOrderPdf($order, $order->items);
    }

    private function supplyOrderPdf(SupplyOrder $order, $supplies)
    {
        $today = $order->generated_at;
        $week = $order->period_start->weekOfYear;
        $period = $order->period_start->format('d-m-y').' al '.$order->period_end->format('d-m-y');

        $pdf = Pdf::loadView('supplies', compact('supplies', 'today', 'week', 'period', 'order'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('insumos-requisicion-'.$order->id.'.pdf');
    }

    public function create()
    {
        return view('supplies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material'            => 'required|unique:supplies,material',
            'type'                => 'required|in:' . implode(',', array_keys(\App\Models\Supply::TYPES)),
            'for_vascular_access' => 'required|in:catheter,fistula,both,no_apply',
            'units_per_patient'   => 'required|numeric|min:0.01',
            'existencias'         => 'required|numeric|min:0',
            'reserve_origin'      => [
                Rule::requiredIf((float) $request->input('existencias', 0) > 0),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'reserve_origin.required' => 'Indique de dónde proviene la reserva.',
        ]);

        if ((float) $data['existencias'] === 0.0) {
            $data['reserve_origin'] = null;
        }

        Supply::create($data);

        return redirect()->route('supplies.index')->with('success', 'Insumo creado correctamente');
    }

    public function edit(Supply $supply)
    {
        return view('supplies.edit', compact('supply'));
    }

    public function update(Request $request, Supply $supply)
    {
        $data = $request->validate([
            'material'            => 'required|unique:supplies,material,' . $supply->id,
            'type'                => 'required|in:' . implode(',', array_keys(\App\Models\Supply::TYPES)),
            'for_vascular_access' => 'required|in:catheter,fistula,both,no_apply',
            'units_per_patient'   => 'required|numeric|min:0.01',
            'existencias'         => 'required|numeric|min:0',
            'reserve_origin'      => [
                Rule::requiredIf((float) $request->input('existencias', 0) > 0),
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'reserve_origin.required' => 'Indique de dónde proviene la reserva.',
        ]);

        if ((float) $data['existencias'] === 0.0) {
            $data['reserve_origin'] = null;
        }

        $supply->update($data);

        return redirect()->route('supplies.index')->with('success', 'Insumo actualizado correctamente');
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();

        return back()->with('success', 'Insumo eliminado correctamente');
    }

    public function suppliesCalculate(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $vascularCounts      = collect();
        $dialyzerCounts      = collect();
        $vascularCountsBajas = collect();
        $dialyzerCountsBajas = collect();
        $activePatients      = collect();
        $shiftCounts         = collect();
        $supplies            = collect();
        $period              = '';
        $week                = now()->weekOfYear;
        $lastOrder           = SupplyOrder::with('items')
            ->whereHas('items')
            ->latest('generated_at')
            ->first();
        $lastEmergencyCutoff = SupplyOrder::whereNotNull('emergency_to')
            ->latest('emergency_to')
            ->first();
        $emergencyCount      = 0;

        if ($startDate && $endDate) {
            $start = Carbon::createFromFormat('Y-m-d', $startDate);
            $end   = Carbon::createFromFormat('Y-m-d', $endDate);

            $period = $start->format('d-m-y') . ' al ' . $end->format('d-m-y');
            $week   = $start->weekOfYear;

            $activePatientsQuery = ActivePatient::where('active', 1)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

            $activePatients   = $activePatientsQuery->get();
            $activePatientIds = $activePatients->pluck('patient_id');

            $shiftCounts = SchedulePatients::whereIn('patient_id', $activePatientIds)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('schedules_id', '!=', 5)
                ->selectRaw('schedules_id, COUNT(*) as total')
                ->groupBy('schedules_id')
                ->pluck('total', 'schedules_id');

            $emergencyFrom = $lastEmergencyCutoff?->emergency_to ?? $start->copy()->startOfDay()->subSecond();
            $calculation = $this->calculationWithEmergencies(
                $startDate,
                $endDate,
                $emergencyFrom,
                now()
            );
            $vascularCounts = $calculation['vascular_counts'];
            $dialyzerCounts = $calculation['dialyzer_counts'];
            $vascularCountsBajas = $calculation['vascular_bajas'];
            $dialyzerCountsBajas = $calculation['dialyzer_bajas'];
            $emergencyCount = $calculation['emergency_count'];
            $shiftCounts->put(5, $emergencyCount);
            $supplies = $calculation['supplies'];
        }

        return view('supplies.calculate', compact(
            'activePatients', 'vascularCounts', 'dialyzerCounts',
            'vascularCountsBajas', 'dialyzerCountsBajas',
            'shiftCounts', 'supplies', 'period', 'week', 'startDate', 'endDate',
            'lastOrder', 'lastEmergencyCutoff', 'emergencyCount'
        ));
    }

    private function calculationWithEmergencies(
        string $startDate,
        string $endDate,
        Carbon $emergencyFrom,
        Carbon $emergencyTo,
        bool $lockSupplies = false
    ): array {
        [$vascularCounts, $dialyzerCounts, $vascularCountsBajas, $dialyzerCountsBajas] =
            $this->getCalculationData($startDate, $endDate);
        [$emergencyVascular, $emergencyDialyzer, $emergencyCount] =
            $this->getEmergencyCalculationData($emergencyFrom, $emergencyTo);

        $vascularCounts = $this->mergeCounts($vascularCounts, $emergencyVascular);
        $dialyzerCounts = $this->mergeCounts($dialyzerCounts, $emergencyDialyzer);
        $suppliesQuery = Supply::orderBy('material');
        if ($lockSupplies) {
            $suppliesQuery->lockForUpdate();
        }

        $supplies = $this->applyRequestedQuantities(
            $suppliesQuery->get(),
            $vascularCounts,
            $dialyzerCounts,
            $vascularCountsBajas,
            $dialyzerCountsBajas
        );

        return [
            'vascular_counts' => $vascularCounts,
            'dialyzer_counts' => $dialyzerCounts,
            'vascular_bajas' => $vascularCountsBajas,
            'dialyzer_bajas' => $dialyzerCountsBajas,
            'emergency_count' => $emergencyCount,
            'supplies' => $supplies,
        ];
    }

    private function getEmergencyCalculationData(Carbon $from, Carbon $to): array
    {
        $patientIds = NursePatient::query()
            ->join('active_patient', 'active_patient.id', '=', 'nurse_patient.active_patient_id')
            ->join('schedules_patient', function ($join) {
                $join->on('schedules_patient.patient_id', '=', 'active_patient.patient_id')
                    ->on('schedules_patient.date', '=', 'active_patient.date');
            })
            ->where('schedules_patient.schedules_id', 5)
            ->whereNull('schedules_patient.deleted_at')
            ->where('nurse_patient.created_at', '>', $from)
            ->where('nurse_patient.created_at', '<=', $to)
            ->orderBy('nurse_patient.id')
            ->pluck('active_patient.patient_id');

        if ($patientIds->isEmpty()) {
            return [collect(), collect(), 0];
        }

        $uniquePatientIds = $patientIds->unique()->values();
        $vascularByPatient = DialysisMonitoring::whereIn('patient_id', $uniquePatientIds)
            ->whereIn('id', function ($query) use ($uniquePatientIds) {
                $query->selectRaw('MAX(id)')
                    ->from('dialysis_monitoring')
                    ->whereIn('patient_id', $uniquePatientIds)
                    ->groupBy('patient_id');
            })
            ->pluck('vascular_access', 'patient_id');
        $dialyzerByPatient = DialysisPrescription::whereIn('patient_id', $uniquePatientIds)
            ->whereIn('id', function ($query) use ($uniquePatientIds) {
                $query->selectRaw('MAX(id)')
                    ->from('dialysis_prescription')
                    ->whereIn('patient_id', $uniquePatientIds)
                    ->groupBy('patient_id');
            })
            ->pluck('type_dialyzer', 'patient_id');
        $vascularCounts = collect();
        $dialyzerCounts = collect();

        foreach ($patientIds as $patientId) {
            if ($vascularByPatient->has($patientId)) {
                $type = $vascularByPatient->get($patientId);
                $vascularCounts->put($type, ($vascularCounts->get($type, 0) + 1));
            }
            if ($dialyzerByPatient->has($patientId)) {
                $type = $dialyzerByPatient->get($patientId);
                $dialyzerCounts->put($type, ($dialyzerCounts->get($type, 0) + 1));
            }
        }

        return [$vascularCounts, $dialyzerCounts, $patientIds->count()];
    }

    private function mergeCounts($base, $additional)
    {
        $merged = collect($base);
        foreach ($additional as $key => $value) {
            $merged->put($key, $merged->get($key, 0) + $value);
        }

        return $merged;
    }

    private function getCalculationData(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [collect(), collect(), collect(), collect()];
        }

        $start = Carbon::createFromFormat('Y-m-d', $startDate);
        $end   = Carbon::createFromFormat('Y-m-d', $endDate);

        $activePatientIds = ActivePatient::where('active', 1)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('schedules_patient')
                    ->whereColumn('schedules_patient.patient_id', 'active_patient.patient_id')
                    ->whereColumn('schedules_patient.date', 'active_patient.date')
                    ->where('schedules_patient.schedules_id', '!=', 5)
                    ->whereNull('schedules_patient.deleted_at');
            })
            ->pluck('patient_id');

        $bajaPatientIds = SchedulePatients::onlyTrashed()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('schedules_id', '!=', 5)
            ->pluck('patient_id');

        $vascularCounts = DialysisMonitoring::whereIn('patient_id', $activePatientIds)
            ->where('history', 1)
            ->whereIn('id', function ($q) use ($activePatientIds) {
                $q->selectRaw('MAX(id)')
                    ->from('dialysis_monitoring')
                    ->whereIn('patient_id', $activePatientIds)
                    ->where('history', 1)
                    ->groupBy('patient_id');
            })
            ->selectRaw('vascular_access, COUNT(*) as total')
            ->groupBy('vascular_access')
            ->pluck('total', 'vascular_access');

        $dialyzerCounts = DialysisPrescription::whereIn('patient_id', $activePatientIds)
            ->where('history', 1)
            ->whereIn('id', function ($q) use ($activePatientIds) {
                $q->selectRaw('MAX(id)')
                    ->from('dialysis_prescription')
                    ->whereIn('patient_id', $activePatientIds)
                    ->where('history', 1)
                    ->groupBy('patient_id');
            })
            ->selectRaw('type_dialyzer, COUNT(*) as total')
            ->groupBy('type_dialyzer')
            ->pluck('total', 'type_dialyzer');

        $vascularCountsBajas = DialysisMonitoring::whereIn('patient_id', $bajaPatientIds)
            ->where('history', 1)
            ->whereIn('id', function ($q) use ($bajaPatientIds) {
                $q->selectRaw('MAX(id)')
                    ->from('dialysis_monitoring')
                    ->whereIn('patient_id', $bajaPatientIds)
                    ->where('history', 1)
                    ->groupBy('patient_id');
            })
            ->selectRaw('vascular_access, COUNT(*) as total')
            ->groupBy('vascular_access')
            ->pluck('total', 'vascular_access');

        $dialyzerCountsBajas = DialysisPrescription::whereIn('patient_id', $bajaPatientIds)
            ->where('history', 1)
            ->whereIn('id', function ($q) use ($bajaPatientIds) {
                $q->selectRaw('MAX(id)')
                    ->from('dialysis_prescription')
                    ->whereIn('patient_id', $bajaPatientIds)
                    ->where('history', 1)
                    ->groupBy('patient_id');
            })
            ->selectRaw('type_dialyzer, COUNT(*) as total')
            ->groupBy('type_dialyzer')
            ->pluck('total', 'type_dialyzer');

        return [$vascularCounts, $dialyzerCounts, $vascularCountsBajas, $dialyzerCountsBajas];
    }

    private function applyRequestedQuantities(
        $supplies,
        $vascularCounts,
        $dialyzerCounts,
        $vascularCountsBajas,
        $dialyzerCountsBajas
    ) {
        $fistula  = $vascularCounts['fistula']  ?? 0;
        $catheter = $vascularCounts['catheter'] ?? 0;
        $elisio   = ($dialyzerCounts['F6ELISIO21H'] ?? 0) + ($dialyzerCounts['F6ELISIO19H'] ?? 0);

        $bajaFistula  = $vascularCountsBajas['fistula']  ?? 0;
        $bajaCatheter = $vascularCountsBajas['catheter'] ?? 0;
        $bajaElisio   = ($dialyzerCountsBajas['F6ELISIO21H'] ?? 0) + ($dialyzerCountsBajas['F6ELISIO19H'] ?? 0);

        return $supplies->map(function ($supply) use (
            $fistula, $catheter, $elisio,
            $bajaFistula, $bajaCatheter, $bajaElisio
        ) {
            $factor = max(0.01, (float) $supply->units_per_patient);

            if ($supply->type === 'filter') {
                $supply->requested_quantity = $elisio * $factor;
                $supply->baja_quantity      = $bajaElisio * $factor;
            } else {
                [$qty, $baja] = match ($supply->for_vascular_access) {
                    'fistula'  => [$fistula,            $bajaFistula],
                    'catheter' => [$catheter,            $bajaCatheter],
                    'both'     => [$fistula + $catheter, $bajaFistula + $bajaCatheter],
                    default    => [0, 0],
                };
                $supply->requested_quantity = $qty * $factor;
                $supply->baja_quantity      = $baja * $factor;
            }

            $supply->quantity_before_reserve = $supply->requested_quantity;
            $supply->reserve_used = min(
                max(0, (float) $supply->existencias),
                $supply->quantity_before_reserve
            );
            $supply->requested_quantity = round(
                $supply->quantity_before_reserve - $supply->reserve_used,
                2
            );

            return $supply;
        });
    }
}
