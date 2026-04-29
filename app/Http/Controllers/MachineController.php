<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::orderBy('machine_number')->get();
        return view('machines.index', compact('machines'));
    }

    public function create()
    {
        return view('machines.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|unique:machines,serial_number',
            'machine_number' => 'required|numeric|unique:machines,machine_number',
        ]);

        Machine::create($request->all());

        return redirect()->route('machines.index')
            ->with('success','Machine created successfully');
    }

    public function edit(Machine $machine)
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'serial_number' => 'required|unique:machines,serial_number,'.$machine->id,
            'machine_number' => 'required|numeric|unique:machines,machine_number,'.$machine->id,
        ]);

        $machine->update($request->all());

        return redirect()->route('machines.index')
            ->with('success','Machine updated successfully');
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();

        return back()->with('success','Machine deleted successfully');
    }
}
