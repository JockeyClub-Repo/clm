<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Area;
use App\Models\Department;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Exception;

class UserController extends Controller
{
  /*Pagina de usuarios. */
  public function index()
  {
    try {
      return view('users.index');
    } catch (Exception $e) {
      Log::error('Error en UserController@index: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Error al cargar los usuarios.');
    }
  }

  /* Lista de usuarios*/
  public function data()
  {
    try {
      return response()->json(['data' => User::latest()->get()]);
    } catch (Exception $e) {
      Log::error('UserController@data -> '. $e->getMessage());
      return response()->json(['data' => []]);
    }
  }

  /* Muestra el formulario de creación de usuario.*/
  public function create()
  {
    try {
      return view('users.create');
    } catch (Exception $e) {
      Log::error('Error en UserController@create: ' . $e->getMessage());
      return redirect()->route('users.index')->with('error', 'Error al cargar el formulario.');
    }
  }

  /* Almacena un nuevo usuario.*/
  public function store(Request $request)
  {
    $request->validate([
      'name'          => 'required|string|max:255',
      'email'         => 'required|email|unique:users,email',
      'phone'         => 'nullable|string|max:20|unique:users,phone',
      'receive_notifications' => 'boolean',
      'password'      => 'required|string|min:6',
      'role'          => ['required', Rule::in(['admin', 'agent'])],
    ]);
    try {
      $user = User::create([
        'name'          => $request->name,
        'email'         => $request->email,
        'password'      => Hash::make($request->password),
        'phone'         => $request->phone,
        'receive_notifications' => $request->receive_notifications,
        'role'          => $request->role,
      ]);
      return redirect()->route('users.index')->with('success', 'Usuario registrado correctamente.');
    } catch (Exception $e) {
      Log::error('Error en UserController@store: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al guardar el usuario.');
    }
  }

  /* Muestra el formulario de edición de un usuario.*/
  public function edit(User $user)
  {
    try {
      return view('users.edit', compact('user'));
    } catch (Exception $e) {
      Log::error('Error en UserController@edit: ' . $e->getMessage());
      return redirect()->route('users.index')->with('error', 'Error al cargar el usuario.');
    }
  }

  /* Actualiza un usuario existente.*/
  public function update(Request $request, User $user)
  {
    $request->validate([
      'name'          => 'required|string|max:255',
      'email'         => ['required', 'email', Rule::unique('users')->ignore($user->id)],
      'password'      => 'nullable|string|min:6',
      'phone'         => 'nullable|string|max:20|unique:users,phone,' . $user->id,
      'receive_notifications' => 'boolean',
      'role'          => ['required', Rule::in(['admin', 'client', 'agent'])],
    ]);

    try {
      $user->name          = $request->name;
      $user->email         = $request->email;
      $user->phone         = $request->phone;
      $user->receive_notifications = $request->receive_notifications;
      $user->role          = $request->role;

      if ($request->filled('password')) { $user->password = Hash::make($request->password); }

      $user->save();
      return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    } catch (Exception $e) {
      Log::error('Error en UserController@update: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al actualizar el usuario.');
    }
  }

  /* Elimina un usuario. */
  public function destroy(User $user)
  {
    try {
      $user->delete();
      return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
    } catch (Exception $e) {
      Log::error('Error en UserController@destroy: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'No se pudo eliminar el usuario.']);
    }
  }
}
