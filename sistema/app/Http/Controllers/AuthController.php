<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // Mostrar inicio de sesión
    public function showLogin()
    {
        return view('auth.login');
    }

    // Validar e iniciar sesión
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard.index'));
        }

        return back()
            ->withErrors([
                'email' => 'El correo o la contraseña son incorrectos.',
            ])
            ->onlyInput('email');
    }

    // Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('ok', 'Has cerrado sesión correctamente.');
    }

    // Mostrar perfil
    public function profile()
    {
        return view('settings.profile.index', [
            'user' => Auth::user()
        ]);
    }

    // Actualizar perfil
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        $user->update($validated);

        return back()->with('ok', 'Tu perfil se actualizó correctamente.');
    }

    // Mostrar formulario para cambiar contraseña
    public function password()
    {
        return view('settings.profile.password');
    }

    // Actualizar contraseña
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' =>
                'La contraseña actual no es correcta.',
            'password.confirmed' =>
                'La confirmación de la contraseña no coincide.',
            'password.min' =>
                'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = Auth::user();

        // El cast hashed del modelo User encripta automáticamente
        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('ok', 'Tu contraseña se cambió correctamente.');
    }

    // Mostrar preferencias
    public function preferences()
    {
        return view('settings.profile.preferences', [
            'preferences' => session('preferences', [
                'theme' => 'light',
                'notifications' => true,
            ]),
        ]);
    }

    // Guardar preferencias en la sesión
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'notifications' => ['nullable', 'boolean'],
        ]);

        session([
            'preferences' => [
                'theme' => $validated['theme'],
                'notifications' => $request->boolean('notifications'),
            ],
        ]);

        return back()->with('ok', 'Tus preferencias se guardaron correctamente.');
    }
}

