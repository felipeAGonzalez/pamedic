<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Utils;
use Throwable;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::paginate(10);
        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.form');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'last_name_two' => 'nullable|string|max:255',
                'gender' => 'nullable|string|in:M,F|max:255',
                'contact_phone_number' => 'nullable|numeric|digits:10',
                'birth_date' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15360',
                'date_entry' => 'required|date',
            ]);
            $patient=$request->all();
            $patient['photo'] = Utils::saveImage($request->file('photo'));
            $lastPatient = Patient::latest()->first();
            $lastExpedientNumber = $lastPatient ? $lastPatient->expedient_number : 'PM-UH/EC737';
            $expedientNumber = 'PM-UH/EC' . (intval(substr($lastExpedientNumber, -3)) + 1);
            $patient['expedient_number'] = $expedientNumber;
            $patient=Patient::create($patient);
            return redirect()->route('patients.index')->with('success', 'Paciente dado de alta exitosamente');
        } catch (Throwable $th) {
            switch ($th->getCode()) {
                case 23000:
                        return redirect()->back()->withInput()->withErrors(['message' => 'Usuario Duplicado']);
                    break;
                default:
                        return redirect()->back()->withInput()->withErrors(__($th->getMessage()));
                    break;
            }
        }
    }
    public function search(Request $request){
        $search = $request->query('search');
        $patients = Patient::query();

        if ($search ?? false) {
            $patients = Patient::where('expedient_number','LIKE','%'.$search.'%')->orWhere('name','LIKE','%'.$search.'%')->orWhere('last_name','LIKE','%'.$search.'%')->orWhere('last_name_two','LIKE','%'.$search.'%');
        }
        $patients = $patients->paginate(10);
        if (! $patients) {
            $error = ValidationException::withMessages(['Error' => 'Paciente no encontrado']);
            throw $error;
        }
        return view('patients.index', compact('patients'));
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        return view('patients.show', compact('patient'));
    }

    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        return view('patients.form', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'last_name_two' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:M,F|max:255',
            'birth_date' => 'nullable|date',
            'contact_phone_number' => 'nullable|numeric|digits:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15360',
            'date_entry' => 'nullable|date',
        ]);
        $query=$request->all();
        $patient = Patient::findOrFail($id);

        if ($request->hasFile('photo')) {
            Utils::deleteImage($patient->photo);
            $query['photo'] = Utils::saveImage($request->file('photo'));
        }
        $patient->update($query);
        return redirect()->route('patients.show', $patient->id)->with('success', 'Paciente actualizado exitosamente');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();
        Utils::deleteImage($patient->photo);
        return redirect()->route('patients.index')->with('success', 'Paciente eliminado exitosamente');
    }
    public function showPhoto($id)
    {
        $patient = Patient::findOrFail($id);

        return view('patients.photo', compact('patient'));
    }
    public function photo(Request $request,$id)
    {
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15360',
        ]);
        $query=$request->all();
        $patient = Patient::findOrFail($id);
        if ($request->hasFile('photo')) {
            Utils::deleteImage($patient->photo);
            $query['photo'] = Utils::saveImage($request->file('photo'));
        }
        $patient->update($query);

        return redirect()->route('patients.show',$patient->id)->with('success', 'Paciente actualizado exitosamente');
    }
}
