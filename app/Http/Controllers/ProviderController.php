<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
  /* Pagina de proveedores*/
  public function index()
  {
    try {
      return view('providers.index');
    } catch (Exception $e) {
      Log::error('Error en ProviderController@index: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Error al cargar los proveedores.');
    }
  }

   /* Lista de proveedores*/
  public function data()
  {
    try {
      return response()->json([
        'data' => Provider::with('contracts')->latest()->get()
      ]);
    } catch (Exception $e) {
      Log::error('ProviderController@data -> ' . $e->getMessage());
      return response()->json(['data' => []]);
    }
  }

  /* Muestra el formulario de creación. */
  public function create()
  {
    return view('providers.create');
  }

  /* Almacena una nueva area. */
  public function store(Request $request)
  {
    $request->validate([
      'ruc' => 'required|digits:11|unique:providers,ruc',
      'name' => 'required|string|max:255|unique:providers,name',
    ]);

    try {
      $area = Provider::create([
        'ruc' => $request->ruc,
        'name' => $request->name,
        'description' => $request->description,
      ]);

      return redirect()->route('providers.index')->with('success', 'Proveedor creado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al crear proveedor: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al crear el proveedor.');
    }
  }

  /* Muestra el formulario de edición. */
  public function edit($id)
  {
    try {
      $area = Area::findOrFail($id);
      return view('areas.edit', compact('area'));
    } catch (\Exception $e) {
      Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
      return redirect()->route('areas.index')->with('error', 'Área no encontrada.');
    }
  }

  /* Actualiza un area existente. */
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:areas,name,' . $id,
      'department_id' => 'required|exists:departments,id',
    ]);

    try {
      $area = Area::findOrFail($id);
      $area->update([
        'name' => $request->name,
        'department_id' => $request->department_id,
      ]);

      // Registrar en system_logs
      SystemLog::register('area', 'update', 'Se actualizó el área ID ' . $area->id);
      return redirect()->route('areas.index')->with('success', 'Área actualizada correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al actualizar área: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al actualizar el área.');
    }
  }

  /* Elimina un area. */
  public function destroy($id)
  {
    try {
      $area = Area::findOrFail($id);
      $area->delete();

      // Registrar en system_logs
      SystemLog::register('area', 'delete', 'Se eliminó la área ID ' . $area->id);
      return response()->json(['success' => true, 'message' => 'Área eliminada correctamente.']);
    } catch (\Exception $e) {
      Log::error('Error al eliminar área: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error al eliminar el área.']);
    }
  }
}
