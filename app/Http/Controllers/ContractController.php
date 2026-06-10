<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
  /* Pagina de contratos*/
  public function index()
  {
    try {
      return view('contracts.index');
    } catch (Exception $e) {
      Log::error('Error en ContractController@index: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Error al cargar los contratos.');
    }
  }

   /* Lista de proveedores*/
    public function data()
{
    try {

        $contracts = Contract::with('provider')->get();

        $contracts->each(function ($contract) {

            $contract->status_badge = match ($contract->status_label) {

                'Vigente' =>
                    '<span class="badge bg-success">Vigente</span>',

                'Próximo a Renovar' =>
                    '<span class="badge bg-info">Próximo a Renovar</span>',

                'Renovación Pendiente' =>
                    '<span class="badge bg-warning text-dark">Renovación Pendiente</span>',

                'Próximo a Vencer' =>
                    '<span class="badge bg-warning">Próximo a Vencer</span>',

                'Vence Hoy' =>
                    '<span class="badge bg-danger">Vence Hoy</span>',

                default =>
                    '<span class="badge bg-dark">Vencido</span>',
            };

        });

        return response()->json([
            'data' => $contracts
        ]);

    } catch (\Exception $e) {

        Log::error($e->getMessage());

        return response()->json([
            'data' => []
        ]);
    }
}

  /* Muestra el formulario de creación. */
  public function create()
  {
    try {
      return view('contracts.create');
    } catch (Exception $e) {
      Log::error('Error en ContractController@create: ' . $e->getMessage());
      return redirect()->route('contracts.index')->with('error', 'Error al cargar el formulario.');
    }
  }

  /* Almacena una nueva area. */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'provider_id' => 'required|exists:providers,id',
      'amount' => 'required|numeric|min:0',
      'currency' => 'required|string|max:10',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'renewal_notice_days' => 'required|integer|min:0',
      'auto_renewal' => 'boolean',
      'messaging_enabled' => 'boolean',
      'description' => 'nullable|string',
    ]);

    try {
      $contract = Contract::create([
        'name' => $request->name,
        'provider_id' => $request->provider_id,
        'amount' => $request->amount,
        'currency' => $request->currency,
        'start_date' => Carbon::parse($request->start_date),
        'end_date' => Carbon::parse($request->end_date),
        'renewal_notice_days' => $request->renewal_notice_days,
        'auto_renewal' => $request->auto_renewal ?? false,
        'messaging_enabled' => $request->messaging_enabled ?? false,
        'description' => $request->description,
        'created_by' => Auth::id(),
      ]);

      return redirect()->route('contracts.index')->with('success', 'Contrato creado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al crear contrato: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al crear el contrato.');
    }
  }

  /* Muestra el formulario de edición. */
  public function edit($id)
  {
    try {
      $contract = Contract::findOrFail($id);
      return view('contracts.edit', compact('contract'));
    } catch (\Exception $e) {
      Log::error('Error al cargar formulario de edición: ' . $e->getMessage());
      return redirect()->route('contracts.index')->with('error', 'Contrato no encontrado.');
    }
  }

  /* Actualiza un contrato existente. */
  public function update(Request $request, $id)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'provider_id' => 'required|exists:providers,id',
      'amount' => 'required|numeric|min:0',
      'currency' => 'required|string|max:10',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'renewal_notice_days' => 'required|integer|min:0',
      'auto_renewal' => 'boolean',
      'messaging_enabled' => 'boolean',
      'description' => 'nullable|string',
    ]);

    try {
      $contract = Contract::findOrFail($id);
      $contract->update([
        'name' => $request->name,
        'provider_id' => $request->provider_id,
        'amount' => $request->amount,
        'currency' => $request->currency,
        'start_date' => Carbon::parse($request->start_date),
        'end_date' => Carbon::parse($request->end_date),
        'renewal_notice_days' => $request->renewal_notice_days,
        'auto_renewal' => $request->auto_renewal ?? false,
        'messaging_enabled' => $request->messaging_enabled ?? false,
        'description' => $request->description,
      ]);

      return redirect()->route('contracts.index')->with('success', 'Contrato actualizado correctamente.');
    } catch (\Exception $e) {
      Log::error('Error al actualizar contrato: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Ocurrió un error al actualizar el contrato.');
    }
  }

  /* Elimina un contrato. */
  public function destroy($id)
  {
    try {
      $contract = Contract::findOrFail($id);
      $contract->delete();

      return response()->json(['success' => true, 'message' => 'Contrato eliminado correctamente.']);
    } catch (\Exception $e) {
      Log::error('Error al eliminar contrato: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Error al eliminar el contrato.']);
    }
  }

  public function renew(Request $request, $id)
  {
    $request->validate([
      'start_date' => 'required|date',
      'end_date'   => 'required|date|after:start_date',
      'amount'     => 'required|numeric|min:0',
      'currency'   => 'required|string|max:10',
      'description' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $id) {
      $contract = Contract::findOrFail($id);
      $contract->update([
        'status' => 'renewed',
      ]);

      Contract::create([
        'name' => $contract->name,
        'provider_id' => $contract->provider_id,
        'amount' => $request->amount,
        'currency' => $contract->currency,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'renewal_notice_days' => $contract->renewal_notice_days,
        'auto_renewal' => $contract->auto_renewal,
        'messaging_enabled' => $contract->messaging_enabled,
        'description' => $contract->description,
        'created_by' => Auth::id(),
        'previous_contract_id' => $contract->id,
        'status' => 'active'
      ]);
      // aquí luego pondremos la lógica de eventos/notificaciones
    });

    return response()->json([
      'success' => true,
      'message' => 'Contrato renovado correctamente.'
    ]);
  }

  public function notRenew($id)
  {
    $contract = Contract::findOrFail($id);

    $contract->update([
      'status' => 'not_renewed',
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Contrato marcado como no renovado.'
    ]);
  }

}
