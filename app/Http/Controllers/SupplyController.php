<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use App\Models\ActivePatient;
use App\Models\DialysisMonitoring;
use App\Models\DialysisPrescription;
use Carbon\Carbon;

class SupplyController extends Controller
{

    public function index()
    {
        $supplies = Supply::orderBy('material')->get();
        return view('supplies.index', compact('supplies'));
    }


    public function create()
    {
        return view('supplies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'material'    => 'required|unique:supplies,material',
            'existencias' => 'required|integer|min:0',
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
            'material'    => 'required|unique:supplies,material,' . $supply->id,
            'existencias' => 'required|integer|min:0',
        ]);

        $supply->update($request->all());

        return redirect()->route('supplies.index')->with('success', 'Insumo actualizado correctamente');
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();

        return back()->with('success', 'Insumo eliminado correctamente');
    }

    public function suppliesCalculate()
    {
        $today = Carbon::today();
        $week = $week ?? now()->weekOfYear;
        $activePatients = ActivePatient::where('active', 1)->where('date', $today);
        $activePatientIds = $activePatients->pluck('patient_id');

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

        $activePatients = $activePatients->get();
        return view('supplies.calculate', compact('activePatients', 'vascularCounts', 'dialyzerCounts'));
    }
}
