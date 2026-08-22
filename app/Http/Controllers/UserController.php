<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    private $position = [
        'QUALITY' => 'Calidad',
        'DIRECTIVE' => 'Medico',
        'MANAGER' => 'Jefe de Enfermería',
        'NURSE' => 'Enfermero',
        'NEPHROLOGIST'=>'Nefrólogo',
        'RECEPCIONIST' => 'Recepcionista',
        'WHAREHOUSE' => 'Almacen'
    ];

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name_one', 'like', "%{$search}%")
                        ->orWhere('last_name_two', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('profesional_id', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $position = $this->position;
        return view('users.form',compact('position'));

    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required',
            'last_name_one' => 'required',
            'last_name_two' => 'required',
            'profesional_id' => 'required_if:position,NURSE,DIRECTIVE|nullable|string|max:15',
            'position' => 'required',
            'email' => 'required|email|unique:users,email',
        ], [
            'profesional_id.required_if' => 'La cédula profesional es obligatoria para Enfermero y Médico.',
            'profesional_id.max' => 'La cédula profesional no debe exceder los 15 caracteres.',
        ]);
        $data = $request->all();
        $data['profesional_id'] = $data['profesional_id'] ?? '';
        $user = User::create(array_merge($data,['password'=>'pamedic','need_change' => true]));
        if (! $user) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        return redirect()->route('users.show', $user->id);
    }

    public function edit($id)
    {
        $position = $this->position;
        $user = User::findOrFail($id);
        return view('users.form', compact('user','position'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable',
            'last_name_one' => 'nullable',
            'last_name_two' => 'nullable',
            'profesional_id' => 'required_if:position,NURSE,DIRECTIVE|nullable|string|max:15',
            'position' => 'nullable',
            'email' => 'nullable|email|unique:users,email,' . $id,
        ], [
            'profesional_id.required_if' => 'La cédula profesional es obligatoria para Enfermero y Médico.',
            'profesional_id.max' => 'La cédula profesional no debe exceder los 15 caracteres.',
        ]);
        $data=$request->all();
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });
        $user = User::findOrFail($id);
        $user->update($data);
        return redirect()->route('users.show', $user->id);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index');
    }

    public function updateEnabled(Request $request, $id)
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id() && ! (bool) $data['enabled']) {
            return redirect()->route('users.index')
                ->withErrors(['message' => 'No puede deshabilitar su propio usuario.']);
        }

        $user->enabled = (bool) $data['enabled'];
        $user->save();

        $message = $user->enabled ? 'Usuario habilitado correctamente.' : 'Usuario deshabilitado correctamente.';

        return redirect()->route('users.index')->with('success', $message);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        $user = User::findOrFail($request->all()['user_id']);
        $user->password = Hash::make($request->password);
        $user->need_change = false;
        $user->save();
        return redirect('/welcome');
    }

    public function resetPassword($id){

        $user = User::findOrFail($id);
        if ($user->position == 'ROOT') {
            $error = ValidationException::withMessages(['Error' => 'No tiene permisos para reiniciar la contraseña del usuario root']);
            throw $error;
        }
        $user->password = Hash::make('pamedic');
        $user->need_change = true;
        $user->save();
        return redirect()->route('users.index')->with('success', 'Contraseña reiniciada correctamente');
    }
}
