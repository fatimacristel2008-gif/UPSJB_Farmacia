<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->get();

        return view('settings.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('settings.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|max:255',
            'dni' => 'required|digits:8|unique:users',
            'phone' => 'nullable|max:15',
            'tuition' => 'nullable|max:20',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create($validated);

        return to_route('users.index')->with('ok', 'Usuario registrado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        return view('settings.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|max:255',
            'dni' => ['required', 'digits:8', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|max:15',
            'tuition' => 'nullable|max:20',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return to_route('users.index')->with('ok', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->sales()->exists()) {
            return to_route('users.index')->with('error', 'No se puede eliminar el usuario porque tiene ventas registradas.');
        }

        $user->delete();

        return to_route('users.index')->with('ok', 'Usuario eliminado correctamente.');
    }
}
