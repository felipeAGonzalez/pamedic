<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivePatient;
use App\Models\SchedulePatients;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use Carbon\Carbon;

class SupplyController extends Controller
{

    public function index()
    {
        $supplies = Supply::orderBy('material')->paginate(10);
        return view('supplies.index', compact('supplies'));
    }

    public function printSupplies(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $week      = $request->query('week', now()->weekOfYear);
        $period    = $request->query('period', '');
        $today     = Carbon::today();

        [$vascularCounts, $dialyzerCounts] = $this->getCalculationData($startDate, $endDate);

        $supplies = $this->applyRequestedQuantities(
            Supply::orderBy('material')->get(),
            $vascularCounts,
            $dialyzerCounts
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

        $vascularCounts = collect();
        $dialyzerCounts = collect();
        $activePatients = collect();
        $shiftCounts    = collect();
        $period         = '';
        $week           = now()->weekOfYear;

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

            [$vascularCounts, $dialyzerCounts] = $this->getCalculationData($startDate, $endDate);
        }

        return view('supplies.calculate', compact(
            'activePatients', 'vascularCounts', 'dialyzerCounts',
            'shiftCounts', 'period', 'week', 'startDate', 'endDate'
        ));
    }

    private function getCalculationData(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [collect(), collect()];
        }

        $start = Carbon::createFromFormat('Y-m-d', $startDate);
        $end   = Carbon::createFromFormat('Y-m-d', $endDate);

        $activePatientIds = ActivePatient::where('active', 1)
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

        return [$vascularCounts, $dialyzerCounts];
    }

    private function applyRequestedQuantities($supplies, $vascularCounts, $dialyzerCounts)
    {
        $fistula  = $vascularCounts['fistula']  ?? 0;
        $catheter = $vascularCounts['catheter'] ?? 0;
        $elisio   = ($dialyzerCounts['F6ELISIO21H'] ?? 0) + ($dialyzerCounts['F6ELISIO19H'] ?? 0);

        return $supplies->map(function ($supply) use ($fistula, $catheter, $elisio) {
            if ($supply->type === 'filter') {
                $supply->requested_quantity = $elisio;
            } else {
                $supply->requested_quantity = match ($supply->for_vascular_access) {
                    'fistula'  => $fistula,
                    'catheter' => $catheter,
                    'both'     => $fistula + $catheter,
                    default    => 0,
                };
            }
            return $supply;
        });
    }
}
