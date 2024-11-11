<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;

class MedicineController extends Controller
{

    public function index()
    {
        $medicines = Medicine::paginate(10);
        return view('medicines.index', compact('medicines'));
    }
    public function create()
    {
        return view('medicines.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'route_administration' => 'required|string|max:255',
        ]);

        $medicine = Medicine::create([
            'name' => $request->name,
            'route_administration' => $request->route_administration,
        ]);

        return redirect()->route('medicines.index')->with('success', 'Medicina creada exitosamente.');
    }

    public function show($id)
    {
        $medicine = Medicine::findOrFail($id);
        return view('medicines.show', compact('medicine'));
    }
    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);
        return view('medicines.form', compact('medicine'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'route_administration' => 'sometimes|required|string|max:255',
        ]);

        $medicine = Medicine::findOrFail($id);
        $medicine->update($request->all());

        return redirect()->route('medicines.show', $medicine->id)->with('success', 'Medicina actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();

        return redirect()->route('medicines.index')->with('success', 'Medicina eliminada exitosamente.');
    }
}
