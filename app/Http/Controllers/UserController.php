<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    private $position = [
        "QUALITY" => "Calidad",
        "DIRECTIVE" => "Medico",
        "MANAGER" => "Jefe de Enfermería",
        "NURSE" => "Enfermero",
        'NEPHROLOGIST'=>'Nefrólogo',
        "RECEPCIONIST" => 'Recepcionista',
    ];

    public function index()
    {
        $users = User::paginate(10);
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
            'profesional_id' => 'required',
            'position' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);
        $user = User::create(array_merge($request->all(),['password'=>'pamedic','need_change' => true]));
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
            'profesional_id' => 'nullable',
            'position' => 'nullable',
            'email' => 'nullable|email|unique:users,email,' . $id,
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
