<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ContractFile;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
  /* Pagina de contratos*/
public function index()
{
  try {
    return view('contracts.index');
  } catch (\Exception $e) {
    Log::error('Error en ContractController@index: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Error al cargar los contratos.');
  }
}

   /* Lista de proveedores*/
    public function data()
{
    try {

$contracts = Contract::with(['provider', 'files'])->get();

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
  } catch (\Exception $e) {
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
$contract = Contract::with('files')->findOrFail($id);
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
      'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
      if ($request->hasFile('archivo')) {
  $file = $request->file('archivo');

  $path = $file->store('contracts', 'public');

  $contract->files()->create([
    'file_name'   => $file->getClientOriginalName(),
    'file_path'   => $path,
    'file_size'   => $file->getSize(),
    'mime_type'   => $file->getMimeType(),
    'uploaded_by' => Auth::id(),
  ]);
}

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
  public function deleteFile($id)
{
    try {

        $file = ContractFile::findOrFail($id);

        if ($file->file_path &&
            Storage::disk('public')->exists($file->file_path)) {

            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return response()->json([
            'success' => true
        ]);

    } catch (\Exception $e) {

    Log::error($e->getMessage());

    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
}

public function pdf($id)
{
    try {
        $contract = Contract::with(['provider', 'files'])->findOrFail($id);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
                h1 { text-align: center; color: #0d6efd; margin-bottom: 5px; }
                h2 { text-align: center; color: #555; font-size: 15px; margin-top: 0; }
                h3 { background: #0d6efd; color: white; padding: 8px; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; }
                th, td { border: 1px solid #ccc; padding: 8px; }
                th { background: #f2f2f2; text-align: left; width: 30%; }
                .resumen { background: #eef5ff; border: 1px solid #0d6efd; padding: 10px; margin-top: 10px; }
                .footer { position: fixed; bottom: 10px; text-align: center; font-size: 10px; color: #777; width: 100%; }
                .firma { width: 30%; display: inline-block; text-align: center; margin-top: 70px; }
                .linea { border-top: 1px solid #000; margin-bottom: 5px; }
            </style>
        </head>
        <body>

            <h3>1. Información General</h3>
            <table>
                <tr><th>Contrato</th><td>' . e($contract->name) . '</td></tr>
                <tr><th>Proveedor</th><td>' . e($contract->provider->name ?? '-') . '</td></tr>
                <tr><th>Estado</th><td>' . e($contract->status_label ?? '-') . '</td></tr>
                <tr><th>Moneda</th><td>' . e($contract->currency) . '</td></tr>
                <tr><th>Monto</th><td>' . number_format($contract->amount, 2) . '</td></tr>
                <tr><th>Fecha Inicio</th><td>' . e($contract->start_date) . '</td></tr>
                <tr><th>Fecha Fin</th><td>' . e($contract->end_date) . '</td></tr>
                <tr><th>Renovación Automática</th><td>' . ($contract->auto_renewal ? 'SI' : 'NO') . '</td></tr>
                <tr><th>Aviso Renovación</th><td>' . e($contract->renewal_notice_days) . ' días antes</td></tr>
            </table>

            <h3>2. Descripción</h3>
            <table>
                <tr><td>' . e($contract->description ?? 'Sin descripción') . '</td></tr>
            </table>

            <h3>3. Documentos Adjuntos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Tamaño</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>';

        if ($contract->files->count() > 0) {
            foreach ($contract->files as $file) {
                $html .= '
                    <tr>
                        <td>' . e($file->file_name) . '</td>
                        <td>' . number_format($file->file_size / 1024, 2) . ' KB</td>
                        <td>' . e($file->mime_type) . '</td>
                    </tr>';
            }
        } else {
            $html .= '
                    <tr>
                        <td colspan="3">No existen archivos adjuntos.</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <h3>4. Resumen Ejecutivo</h3>
            <div class="resumen">
                <strong>Estado:</strong> ' . e($contract->status_label ?? '-') . '<br>
                <strong>Proveedor:</strong> ' . e($contract->provider->name ?? '-') . '<br>
                <strong>Monto:</strong> ' . e($contract->currency) . ' ' . number_format($contract->amount, 2) . '<br>
                <strong>Fecha de vencimiento:</strong> ' . e($contract->end_date) . '
            </div>

            <h3>5. Firmas</h3>

            <div class="firma">
                <div class="linea"></div>
                Administrador
            </div>

            <div class="firma">
                <div class="linea"></div>
                Proveedor
            </div>

            <div class="firma">
                <div class="linea"></div>
                Gerencia
            </div>

            <div class="footer">
                ITELCA PERÚ | Documento generado automáticamente el ' . date('d/m/Y H:i') . '
            </div>

        </body>
        </html>';

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->stream('contrato-' . $contract->id . '.pdf');

    } catch (\Exception $e) {
        Log::error('Error al generar PDF: ' . $e->getMessage());

        return redirect()
            ->route('contracts.index')
            ->with('error', 'Error al generar el PDF.');
    }
}

}
