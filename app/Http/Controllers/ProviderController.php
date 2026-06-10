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
  public function data(Request $request)
  {
    $term = $request->term;
    $query = Provider::query();
    if (!empty($term)) {
      $query->where('name', 'like', "%{$term}%")->orWhere('ruc', 'like', "%{$term}%");
    }
    return response()->json([
        'data' => $query->orderBy('name')->get()
    ]);
  }

  /* Muestra el formulario de creación. */
  public function create()
  {
    try {
      return view('providers.create');
    } catch (Exception $e) {
      Log::error('Error en ProviderController@create: ' . $e->getMessage());
      return redirect()->route('providers.index')->with('error', 'Error al cargar el formulario.');
    }
  }

  /* Almacena una nueva area. */
  public function store(Request $request)
  {
    $request->validate([
      'ruc' => 'required|digits:11|unique:providers,ruc',
      'name' => 'required|string|max:255|unique:providers,name',
    ]);

    try {
      $provider = Provider::create([
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
      $provider = Provider::findOrFail($id);
      return view('providers.edit', compact('provider'));
    } catch (\Exception $e) {
      Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
      return redirect()->route('providers.index')->with('error', 'Proveedor no encontrado.');
    }
  }

  /* Actualiza un proveedor existente. */
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255|unique:providers,name,' . $id,
      'ruc' => 'required|digits:11|unique:providers,ruc,' . $id,
    ]);

    try {
      $provider = Provider::findOrFail($id);
      $provider->update([
        'name' => $request->name,
        'ruc' => $request->ruc,
        'description' => $request->description,
      ]);

      return redirect()->route('providers.index')->with('success', 'Proveedor actualizado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al actualizar proveedor: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al actualizar el proveedor.');
    }
  }

  /* Elimina un proveedor. */
  public function destroy($id)
  {
    try {
      $provider = Provider::findOrFail($id);
      $provider->delete();

      return response()->json(['success' => true, 'message' => 'Proveedor eliminado correctamente.']);
    } catch (\Exception $e) {
      Log::error('Error al eliminar proveedor: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error al eliminar el proveedor.']);
    }
  }
}
