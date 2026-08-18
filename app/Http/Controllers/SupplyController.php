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
use Carbon\Carbon;

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
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $week      = $request->query('week', now()->weekOfYear);
        $period    = $request->query('period', '');
        $today     = Carbon::today();

        if ($startDate && $endDate) {
            Supply::query()->update(['existencias' => 0]);

            SupplyOrder::create([
                'period_start' => $startDate,
                'period_end'   => $endDate,
                'generated_at' => now(),
            ]);
        }

        [$vascularCounts, $dialyzerCounts, $vascularCountsBajas, $dialyzerCountsBajas] =
            $this->getCalculationData($startDate, $endDate);

        $supplies = $this->applyRequestedQuantities(
            Supply::orderBy('material')->get(),
            $vascularCounts,
            $dialyzerCounts,
            $vascularCountsBajas,
            $dialyzerCountsBajas
        );

        $pdf = Pdf::loadView('supplies', compact('supplies', 'today', 'week', 'period'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('insumos-' . $week . '.pdf');
    }

    public function create()
    {
        return view('supplies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'material'            => 'required|unique:supplies,material',
            'type'                => 'required|in:' . implode(',', array_keys(\App\Models\Supply::TYPES)),
            'for_vascular_access' => 'required|in:catheter,fistula,both,no_apply',
            'existencias'         => 'required|integer|min:0',
        ]);

        Supply::create($request->all());

        return redirect()->route('supplies.index')->with('success', 'Insumo creado correctamente');
    }

    public function edit(Supply $supply)
    {
        return view('supplies.edit', compact('supply'));
    }

    public function update(Request $request, Supply $supply)
    {
        $request->validate([
            'material'            => 'required|unique:supplies,material,' . $supply->id,
            'type'                => 'required|in:' . implode(',', array_keys(\App\Models\Supply::TYPES)),
            'for_vascular_access' => 'required|in:catheter,fistula,both,no_apply',
            'existencias'         => 'required|integer|min:0',
        ]);

        $supply->update($request->all());

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
                ->selectRaw('schedules_id, COUNT(*) as total')
                ->groupBy('schedules_id')
                ->pluck('total', 'schedules_id');

            [$vascularCounts, $dialyzerCounts, $vascularCountsBajas, $dialyzerCountsBajas] =
                $this->getCalculationData($startDate, $endDate);

            $supplies = $this->applyRequestedQuantities(
                Supply::orderBy('material')->get(),
                $vascularCounts,
                $dialyzerCounts,
                $vascularCountsBajas,
                $dialyzerCountsBajas
            );
        }

        return view('supplies.calculate', compact(
            'activePatients', 'vascularCounts', 'dialyzerCounts',
            'vascularCountsBajas', 'dialyzerCountsBajas',
            'shiftCounts', 'supplies', 'period', 'week', 'startDate', 'endDate'
        ));
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
            ->pluck('patient_id');

        $bajaPatientIds = SchedulePatients::onlyTrashed()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
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
            if ($supply->type === 'filter') {
                $supply->requested_quantity = $elisio;
                $supply->baja_quantity      = $bajaElisio;
            } else {
                [$qty, $baja] = match ($supply->for_vascular_access) {
                    'fistula'  => [$fistula,            $bajaFistula],
                    'catheter' => [$catheter,            $bajaCatheter],
                    'both'     => [$fistula + $catheter, $bajaFistula + $bajaCatheter],
                    default    => [0, 0],
                };
                $supply->requested_quantity = $qty;
                $supply->baja_quantity      = $baja;
            }
            return $supply;
        });
    }
}
