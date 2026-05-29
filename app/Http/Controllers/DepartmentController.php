<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Department;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
  /* Pagina de departamentos*/
  public function index()
  {
    try {
      return view('departments.index');
    } catch (Exception $e) {
      Log::error('Error en DepartmentController@index: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Error al cargar los departamentos.');
    }
  }

  /* Lista de departamentos*/
  public function data()
  {
    try {
      return response()->json([
        'data' => Department::latest()->get()
      ]);
    } catch (Exception $e) {
      Log::error('DepartmentController@data -> ' . $e->getMessage());
      return response()->json(['data' => []]);
    }
  }

  /* Lista de áreas por departamento */
  public function areas(Department $department)
  {
    try {
      return response()->json(['data' => $department->areas]);
    } catch (Exception $e) {
      Log::error('DepartmentController@areas -> '. $e->getMessage());
      return response()->json(['data' => []]);
    }
  }

  /* Muestra el formulario de creación de FAQ. */
  public function create()
  {
    return view('departments.create');
  }

  /* Almacena un nuevo departamento */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:departments,name',
    ]);

    try {
      $department = Department::create([
          'name' => $request->name,
      ]);

      // Registrar en system_logs
      SystemLog::register('department', 'create', 'Se creó el departamento: ' . $department->name);
      return redirect()->route('departments.index')->with('success', 'Departamento creado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al crear departamento: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al crear el departamento.');
    }
  }

  public function edit($id)
  {
    try {
      $department = Department::findOrFail($id);
      return view('departments.edit', compact('department'));
    } catch (\Exception $e) {
      Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
      return redirect()->route('departments.index')->with('error', 'Departamento no encontrado.');
    }
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:departments,name,' . $id,
    ]);

    try {
      $department = Department::findOrFail($id);
      $department->update([
        'name' => $request->name,
      ]);

      // Registrar en system_logs
      SystemLog::register('department', 'update', 'Se actualizó el departamento ID ' . $department->id);
      return redirect()->route('departments.index')->with('success', 'Departamento actualizado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al actualizar departamento: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al actualizar el departamento.');
    }
  }

  public function destroy($id)
  {
    try {
      $department = Department::findOrFail($id);
      $department->delete();
      // Registrar en system_logs
      SystemLog::register('department', 'delete', 'Se eliminó la departamento ID ' . $department->id);
      return response()->json(['success' => true, 'message' => 'Departamento eliminado correctamente.']);
    } catch (\Exception $e) {
      Log::error('Error al eliminar departamento: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error al eliminar el departamento.']);
    }
  }
}
