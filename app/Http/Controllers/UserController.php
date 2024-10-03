<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehiculo\Vehiculo;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::when(auth()->user()->hasRole('Administrador de Agencia'), function ($query) {
            return $query->where('agencia', auth()->user()->agencia);
        })->with('roles')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $agencias = \App\Models\Vehiculo\Vehiculo::distinct()->pluck('agencia');
        return view('users.create', compact('roles', 'agencias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id',
            'agencia' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'agencia' => $request->agencia,
        ]);

        //$user->assignRole($request->role);
        $role = Role::findById($request->role);
        $user->assignRole($role->name);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $agencias = \App\Models\Vehiculo\Vehiculo::distinct()->pluck('agencia');
        return view('users.edit', compact('user', 'roles', 'agencias'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,id',
            'agencia' => 'required|string',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'agencia' => $request->agencia,
        ]);

        //$user->syncRoles([$request->role]);
        $role = Role::findById($request->role);
        $user->syncRoles([$role->name]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
