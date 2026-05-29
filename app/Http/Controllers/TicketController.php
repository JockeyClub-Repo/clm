<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log as LogFacade;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Category;
use App\Models\User;
use App\Models\Priority;
use App\Models\Status;
use App\Models\AttachmentTicket;
use App\Models\AttachmentMessage;
use App\Models\Log;
use App\Models\Department;
use App\Models\Area;

use App\Mail\TicketCreatedMail;
use App\Mail\TicketAssignedToCreator;
use App\Mail\TicketAssignedToAgent;
use App\Mail\TicketReplyToClient;
use App\Mail\TicketReplyToAgent;
use App\Mail\TicketClosedMail;
use App\Mail\TicketReopenedMail;
use App\Mail\TicketCanceledMail;

use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
  public function index()
  {
    try {
      $user = Auth::user();
      $query = Ticket::with(['status', 'priority', 'category', 'agent']);
      $tickets = $query->where('user_id', $user->id)->orderByDesc('created_at')->get();
      return view('tickets.index', compact('tickets'));
    } catch (\Exception $e) {
      return back()->with('error', 'Ocurrió un error al cargar los tickets.');
    }
  }

  /* Muestra el formulario de creación. */
  public function create()
  {
    return view('tickets.create');
  }

  /* Almacena un nuevo ticket. */
  public function store(Request $request)
  {
    // Validación de campos requeridos en el formulario de ticket
    try {
      $validated = $request->validate([
        'subject' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'files.*' => 'nullable|file|max:2048', // 2MB por archivo
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Retorna errores de validación si falla alguna regla
      return response()->json(['message' => 'Validación fallida', 'errors' => $e->errors()], 422);
    }

    try {
      DB::beginTransaction();

      $user = Auth::user();
      // Detectar si es agente o admin
      $isInternalUser = in_array($user->role, ['admin', 'agent']);
      // En caso sea interno debe tener estado asignado por el creador
      $statusId = $isInternalUser ? 2 : 1;
      // Se crea el ticket en la base de datos con estado y prioridad por defecto
      $ticket = Ticket::create([
        'user_id' => Auth::id(),
        'subject' => $request->subject,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'status_id' => $statusId,
        'priority_id' => 1,
        // Autoasignación si es agente/admin
        'assigned_to' => $isInternalUser ? $user->id : null,
      ]);

      // Verifica si el usuario subió uno o varios archivos
      if ($request->hasFile('files')) {
        $archivos = $request->file('files');

        // Asegura que $archivos sea un array
        if (!is_array($archivos)) {
          $archivos = [$archivos];
        }
        foreach ($archivos as $file) {
          if ($file && $file->isValid()) {
            // Guarda el archivo en el almacenamiento público
            $path = $file->store("attachments/tickets/{$ticket->id}", 'public');
            // Registra el archivo en la tabla attachments_ticket
            AttachmentTicket::create([
              'ticket_id' => $ticket->id,
              'file_path' => $path,
              'file_type' => $file->getClientOriginalExtension(),
            ]);
          } else { /* Aquí podrías agregar un log si el archivo no es válido */ }
        }
      } else { /* No se adjuntaron archivos, se puede registrar si deseas. */}

      if (!$isInternalUser) {
        Log::openSession($ticket->id, Log::TYPE_CREATED, null, 1, 'Ticket creado sin asignar');
      } else {
        Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => $user->id, 'type' => Log::TYPE_ASSIGNED, 'description' => "Ticket autoasignado al {$user->role} {$user->name}"]);
        Log::openSession($ticket->id, Log::TYPE_ACTIVE_WORK, $user->id, 1, 'Trabajo iniciado automáticamente');
      }

      DB::commit();

      // Recupera los destinatarios de las notificaciones por correo desde .env
      //$destinatarios = explode(',', env('MAIL_TICKET_NOTIFICATION'));

      /*try {
        // Carga relaciones para usarlas en el correo (nombre del creador, categoría, etc.)
        $ticket->load(['creator.area.department', 'area.department', 'priority', 'category', 'status']);
        // Envia el correo de notificación de nuevo ticket
        //Mail::to($destinatarios)->send(new TicketCreatedMail($ticket));
      } catch (\Exception $e) {
        // Si falla el correo, no se interrumpe el flujo general
      }*/

      // Retorna respuesta JSON satisfactoria
      return response()->json(['message' => 'Ticket creado con éxito'], 200);

    } catch (\Exception $e) {
      // Reversión de la transacción si ocurre algún error
      DB::rollBack();
      return response()->json(['message' => 'Error al crear el ticket', 'error' => $e->getMessage()], 500);
    }
  }

  /* Asignamiento de ticket. */
  public function actualizarGestion(Request $request, $id)
  {
    // Validación de los campos enviados desde el formulario de gestión
    $request->validate([
      'assigned_to' => 'nullable|exists:users,id',
      'priority_id' => 'required|exists:priority,id',
    ]);

    try {
      // Buscar el ticket por su ID
      $ticket = Ticket::findOrFail($id);
      // No permitir reasignar tickets cerrados, resueltos o cancelados
      if (in_array($ticket->status_id, [5, 8, 6])) {
        return response()->json([
          'success' => false,
          'changed' => true,
          'message' => 'No se puede reasignar un ticket cerrado, resuelto, confirmado o cancelado.'
        ], 409);
      }
      // No permitir reasignar tickets pausados
      if ($ticket->status_id == 9 && $ticket->assigned_to != $request->assigned_to) {
        return response()->json([
          'success' => false,
          'changed' => true,
          'message' => 'No se puede reasignar un ticket pausado. Reanude el ticket antes de asignarlo a otro agente.'
        ], 409);
      }

      // Guardar los valores antiguos para compararlos
      $oldAssigned = $ticket->assigned_to;
      $oldPriority = $ticket->priority_id;
      $oldStatus = $ticket->status_id;

      //En caso sea el mismo agente informar
      if (!is_null($request->assigned_to) && $oldAssigned == $request->assigned_to) {
        return response()->json([
          'success' => false,
          'changed' => false,
          'message' => 'El ticket ya está asignado a este agente.'
        ], 409);
      }

      // Actualizar datos del ticket con los nuevos valores
      $ticket->assigned_to = $request->assigned_to;
      $ticket->priority_id = $request->priority_id;

      // Solo mover a En Progreso si estaba sin asignar o reabierto y existe agente asignado
      if (in_array($ticket->status_id, [1, 7]) && !is_null($request->assigned_to)) {
        $ticket->status_id = 2;
      } else if (is_null($request->assigned_to)) {
        // Sin asignar
        $ticket->status_id = 1;
      }

      $ticket->save();

      // Cargar relaciones necesarias para mostrar información completa
      $ticket->load(['creator', 'agent', 'priority', 'status']);

      // Obtener nombre del usuario que está realizando la acción
      $userName = auth()->user()->name;

      // Log si se asigna o cambia el agente asignado
      if ($oldAssigned != $ticket->assigned_to) {
        Log::closeOpenSession($ticket->id, Log::TYPE_CREATED);
        Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);
        Log::closeOpenSession($ticket->id, Log::TYPE_PAUSED);
        Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
        Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);

        //Movimiento de asignamiento
        $type = $oldAssigned ? Log::TYPE_REASSIGNED : Log::TYPE_ASSIGNED;

        //Registrar asignación/reasignación

        if ($ticket->assigned_to) {
          $assignedName = $ticket->agent ? $ticket->agent->name : 'Agente';
          Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'agent_id' => $ticket->assigned_to, 'type' => $type, 'description' => $oldAssigned ? "El usuario {$userName} reasignó el ticket al agente {$assignedName}." : "El usuario {$userName} asignó el ticket al agente {$assignedName}.", 'old_status_id' => $oldStatus, 'new_status_id' => $ticket->status_id,]);

          // Iniciar SLA primera respuesta
          Log::openSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT, $ticket->assigned_to, 1, 'Esperando primera respuesta del agente', $oldStatus, $ticket->status_id);
        } else {
          //Desasignación
          Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'type' => 'unassigned', 'description' => "El usuario {$userName} desasignó el ticket."]);
        }
      }

      // Cambio de prioridad
      if ($oldPriority != $ticket->priority_id) {
        $oldPriorityName = optional(Priority::find($oldPriority))->name ?? 'Desconocida';
        $newPriorityName = $ticket->priority->name ?? 'Desconocida';
        Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'type' => 'priority_change', 'description' => "El usuario {$userName} cambió la prioridad del ticket de {$oldPriorityName} a {$newPriorityName}."
        ]);
      }

      // Cambio automático de estado
      if ($oldStatus != $ticket->status_id) {
        $oldStatusName = optional(Status::find($oldStatus))->name ?? 'Desconocido';
        $newStatusName = $ticket->status->name ?? 'Desconocido';
        Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'type' => 'status_change', 'description' => "El usuario {$userName} actualizó el estado del ticket de {$oldStatusName} a {$newStatusName}.", 'old_status_id' => $oldStatus, 'new_status_id' => $ticket->status_id,]);
      }

      // Enviar correo al creador del ticket si tiene email
      //if ($ticket->creator && $ticket->creator->email) {
      //    Mail::to($ticket->creator->email)->send(new TicketAssignedToCreator($ticket));
      //}

      // Enviar correo al agente asignado si tiene email
      //if ($ticket->agent && $ticket->agent->email) {
       //   Mail::to($ticket->agent->email)->send(new TicketAssignedToAgent($ticket));
      //}

      return response()->json(['success' => true, 'message' => 'Ticket actualizado correctamente.']);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
  }

  /* Responder ticket. */
  public function responder(Request $request)
  {
      // Validación de los datos del formulario
      $request->validate([
          'ticket_id' => 'required|exists:tickets,id',
          'message'   => 'required|string',
          'files.*'   => 'nullable|file|max:2048'
      ]);

      try {

          DB::beginTransaction();

          $ticket = Ticket::with(['creator', 'agent', 'status'])
              ->findOrFail($request->ticket_id);

          // Validar estados finales
          if (in_array($ticket->status_id, [5, 6, 8])) {
              return response()->json([
                  'success' => false,
                  'closed' => true,
                  'message' => 'No se puede responder un ticket cerrado, resuelto o cancelado.'
              ], 409);
          }

          // No permitir responder tickets pausados
          if ($ticket->status_id == 9) {
              return response()->json([
                  'success' => false,
                  'paused' => true,
                  'message' => 'No se puede responder un ticket pausado. Reanude el ticket antes de continuar.'
              ], 409);
          }

          // Validar asignación
          if (is_null($ticket->assigned_to)) {
              return response()->json([
                  'success' => false,
                  'message' => 'El ticket aún no tiene un agente asignado.'
              ], 409);
          }

          // Usuario autenticado
          $responder = auth()->user();

          // Validar cliente
          if (
              $responder->role === 'client' &&
              $ticket->user_id != auth()->id()
          ) {
              return response()->json([
                  'success' => false,
                  'message' => 'No puedes responder un ticket que no te pertenece.'
              ], 403);
          }

          // Validar agente asignado
          if (
              $responder->role === 'agent' &&
              $ticket->assigned_to != auth()->id()
          ) {
              return response()->json([
                  'success' => false,
                  'message' => 'No puedes responder un ticket que no tienes asignado.'
              ], 403);
          }

          $userName = $responder->name;
          $oldStatusId = $ticket->status_id;

          // CASO ESPECIAL:
          // El creador y asignado son la misma persona
          $isSelfAssignedCreator =
              $ticket->user_id == $ticket->assigned_to;

          // Crear mensaje
          $mensaje = TicketMessage::create([
              'ticket_id' => $request->ticket_id,
              'user_id' => auth()->id(),
              'message' => $request->message,
          ]);

          // Adjuntos
          if ($request->hasFile('files')) {

              foreach ($request->file('files') as $file) {

                  $path = $file->store(
                      "attachments/messages/{$mensaje->id}",
                      'public'
                  );

                  AttachmentMessage::create([
                      'ticket_message_id' => $mensaje->id,
                      'file_path'         => $path,
                      'file_type'         => $file->getClientOriginalExtension(),
                  ]);
              }
          }

          /*
          |--------------------------------------------------------------------------
          | CASO ESPECIAL:
          | Usuario creador y agente asignado son la misma persona
          |--------------------------------------------------------------------------
          */

          if ($isSelfAssignedCreator) {

              // Cerrar sesiones anteriores
              Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);
              Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
              Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);

              // Mantener trabajo activo
              Log::openSession(
                  $ticket->id,
                  Log::TYPE_ACTIVE_WORK,
                  $ticket->assigned_to,
                  2,
                  'Trabajo activo'
              );

              // Mantener estado en progreso
              $ticket->status_id = 2;
              $ticket->save();

              // Log respuesta
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'reply',
                  'description' => "El usuario {$userName} respondió su propio ticket.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 2,
              ]);

              // Log cambio estado
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'status_change',
                  'description' => "El estado del ticket permanece en 'En Progreso'.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 2,
              ]);
          }

          /*
          |--------------------------------------------------------------------------
          | RESPUESTA DE AGENTE / ADMIN
          |--------------------------------------------------------------------------
          */

          elseif (
              $responder->role === 'agent' ||
              $responder->role === 'admin'
          ) {

              // Cerrar sesiones anteriores
              Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);
              Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
              Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);

              // Abrir espera cliente
              Log::openSession(
                  $ticket->id,
                  Log::TYPE_WAITING_CLIENT,
                  $ticket->assigned_to,
                  3,
                  'Esperando respuesta del cliente'
              );

              // Cambiar estado
              $ticket->status_id = 3;
              $ticket->save();

              // Log respuesta
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'reply',
                  'description' => "El agente {$userName} respondió el ticket.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 3,
              ]);

              // Log cambio estado
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'status_change',
                  'description' => "El estado del ticket cambió a 'En espera del cliente'.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 3,
              ]);
          }

          /*
          |--------------------------------------------------------------------------
          | RESPUESTA DEL CLIENTE
          |--------------------------------------------------------------------------
          */

          elseif ($responder->role === 'client') {

              // Cerrar espera cliente
              Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);

              // Evitar duplicados
              Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);

              // Abrir trabajo nuevamente
              Log::openSession(
                  $ticket->id,
                  Log::TYPE_ACTIVE_WORK,
                  $ticket->assigned_to,
                  2,
                  'Trabajo retomado por respuesta del cliente'
              );

              // Estado
              $ticket->status_id = 2;
              $ticket->save();

              // Log respuesta cliente
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'reply',
                  'description' => "El cliente {$userName} respondió el ticket.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 2,
              ]);

              // Log cambio estado
              Log::create([
                  'ticket_id' => $ticket->id,
                  'user_id' => auth()->id(),
                  'agent_id' => $ticket->assigned_to,
                  'type' => 'status_change',
                  'description' => "El estado del ticket cambió a 'En Progreso'.",
                  'old_status_id' => $oldStatusId,
                  'new_status_id' => 2,
              ]);
          }

          DB::commit();

          return response()->json([
              'success' => true,
              'message' => 'Respuesta registrada correctamente.',
          ]);

      } catch (\Exception $e) {

          DB::rollBack();

          return response()->json([
              'success' => false,
              'message' => $e->getMessage()
          ], 500);
      }
  }

  /* Pausar ticket. */
  public function pausar($id)
  {
    try {
      $ticket = Ticket::with('status')->findOrFail($id);
      // No permitir estados finales
      if (in_array($ticket->status_id, [5, 6, 8])) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede pausar un ticket resuelto, cerrado o cancelado.'
        ], 409);
      }
      // Ya pausado
      if ($ticket->status_id == 9) {
        return response()->json([
          'success' => false,
          'message' => 'El ticket ya está pausado.'
        ], 409);
      }
      // Validar permisos
      $user = auth()->user();

      // Cliente creador
      if ($user->role === 'client' && $ticket->user_id != auth()->id()) {
        return response()->json([
          'success' => false,
          'message' => 'No puedes pausar un ticket que no te pertenece.'
        ], 403);
      }

      // Agente/Admin asignado
      if ($user->role === 'agent' && $ticket->assigned_to != auth()->id()) {
        return response()->json([
          'success' => false,
          'message' => 'No puedes pausar un ticket que no tienes asignado.'
        ], 403);
      }

      DB::beginTransaction();
      $oldStatusId = $ticket->status_id;
      $oldStatusName = optional($ticket->status)->name ?? 'Desconocido';

      // Cerrar sesiones activas
      Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);
      Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);
      Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);

      // Abrir sesión pausada
       Log::openSession($ticket->id, Log::TYPE_PAUSED, $ticket->assigned_to, 9, 'Ticket pausado');

      //Cambiar estado
      $ticket->status_id = 9;
      $ticket->save();

      // Logs auditoría

      Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'agent_id' => $ticket->assigned_to, 'type' => 'status_change', 'description' => "El estado del ticket cambió de '{$oldStatusName}' a 'Pausado'.", 'old_status_id' => $oldStatusId, 'new_status_id' => 9,]);

      Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'agent_id' => $ticket->assigned_to, 'type' => 'pause', 'description' => "El usuario {$user->name} pausó el ticket '{$ticket->subject}' (ID #{$ticket->id}).",]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Ticket pausado correctamente.'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /* Reanudar ticket. */
  public function reanudar($id)
  {
    try {
      $ticket = Ticket::with(['status'])->findOrFail($id);
      // Solo pausados
      if ($ticket->status_id != 9) {
        return response()->json([
          'success' => false,
          'message' => 'El ticket no está pausado.'
        ], 409);
      }
      $user = auth()->user();
      // Cliente
      if ($user->role === 'client' && $ticket->user_id != auth()->id()) {
        return response()->json([
          'success' => false,
          'message' => 'No puedes reanudar un ticket que no te pertenece.'
        ], 403);
      }
      // Agente
      if ($user->role === 'agent' && $ticket->assigned_to != auth()->id()) {
        return response()->json([
          'success' => false,
          'message' => 'No puedes reanudar un ticket que no tienes asignado.'
        ], 403);
      }
      DB::beginTransaction();
      $oldStatusId = $ticket->status_id;
      $oldStatusName = optional($ticket->status)->name ?? 'Pausado';
      // Cerrar pausa
      Log::closeOpenSession($ticket->id, Log::TYPE_PAUSED);
      // Si tiene agente => En progreso
      if ($ticket->assigned_to) {
        $ticket->status_id = 2;
        Log::openSession($ticket->id, Log::TYPE_ACTIVE_WORK, $ticket->assigned_to, 2, 'Trabajo reanudado');
      } else {
        // Sin asignar
        $ticket->status_id = 1;
      }
      $ticket->save();
      $newStatusName = optional(Status::find($ticket->status_id))->name ?? 'Desconocido';
      // Log cambio estado
      Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'agent_id' => $ticket->assigned_to, 'type' => 'status_change', 'description' => "El estado del ticket cambió de '{$oldStatusName}' a '{$newStatusName}'.", 'old_status_id' => $oldStatusId, 'new_status_id' => $ticket->status_id,
      ]);

      // Log reanudación
      Log::create(['ticket_id' => $ticket->id, 'user_id' => auth()->id(), 'agent_id' => $ticket->assigned_to, 'type' => 'resume', 'description' => "El usuario {$user->name} reanudó el ticket.",]);
      DB::commit();
      return response()->json([
        'success' => true,
        'message' => 'Ticket reanudado correctamente.'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }

  /* Cerrar ticket. */
  public function cerrar($id)
  {
    try {
      DB::beginTransaction();
      $user = auth()->user();
      // Buscar el ticket por ID, si no existe lanzará una excepción
      $ticket = Ticket::with(['creator', 'agent', 'status'])->findOrFail($id);
      // Validar estados finales
      if (in_array($ticket->status_id, [5, 6, 8])) {
        return response()->json([
            'success' => false,
            'changed' => true,
            'message' => 'El ticket ya fue cerrado, resuelto o cancelado.'
        ], 409);
      }

      // No permitir cerrar tickets pausados
      if ($ticket->status_id == 9) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede cerrar un ticket pausado. Reanude el ticket antes de cerrarlo.'
        ], 409);
      }

      // Validar que tenga asignado
      if (is_null($ticket->assigned_to)) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede cerrar un ticket sin agente asignado.'
        ], 409);
      }

      $canClose = $user->role === 'admin' || $ticket->assigned_to == $user->id || $ticket->user_id == $user->id;

      if (!$canClose) {
        return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para cerrar este ticket.'
        ], 403);
      }

      $oldStatusId = $ticket->status_id;

      // CERRAR TODAS LAS SESIONES SLA ACTIVAS
      Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);
      Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
      Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);
      Log::closeOpenSession($ticket->id, Log::TYPE_PAUSED);

      // CAMBIAR ESTADO
      $ticket->status_id = 6; // Cerrado
      $ticket->closed_at = now();
      $ticket->save();
      $userName = $user->name;

      // LOG CIERRE
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => $ticket->assigned_to, 'type' => 'close', 'description' => "El usuario {$userName} cerró el ticket.", 'old_status_id' => $oldStatusId, 'new_status_id' => 6,]);

      // LOG CAMBIO ESTADO
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => $ticket->assigned_to, 'type' => 'status_change', 'description' => "El estado del ticket cambió a 'Cerrado'.", 'old_status_id' => $oldStatusId, 'new_status_id' => 6,]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'El ticket ha sido cerrado correctamente.'
      ]);

      //return redirect()->back()->with('success', 'El ticket ha sido cerrado correctamente.');
    } catch (\Exception $e) {
      // En caso de error, registrar el mensaje y redirigir con error
      //return redirect()->back()->with('error', 'Ocurrió un error al intentar cerrar el ticket.');

      DB::rollBack();

      return response()->json([
          'success' => false,
          'message' => 'Ocurrió un error al intentar cerrar el ticket.'
      ], 500);
    }
  }

  /* Reabrir ticket. */
  public function reabrir($id)
  {
    try {
      DB::beginTransaction();
      // Buscar el ticket con relaciones cargadas
      $ticket = Ticket::with(['creator', 'agent', 'status'])->findOrFail($id);

      // Validacion
      // Solo puede reabrirse si está cerrado
      if ($ticket->status_id != 6) {
        return response()->json([
          'success' => false,
          'changed' => true,
          'message' => 'Solo se pueden reabrir tickets cerrados.'
        ], 409);
      }

      // Debe existir agente asignado
      if (is_null($ticket->assigned_to)) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede reabrir un ticket sin agente asignado.'
        ], 409);
      }

      // Permisos
      $user = auth()->user();
      $canReopen = $user->role === 'admin' || $ticket->user_id == $user->id || $ticket->assigned_to == $user->id;

      if (!$canReopen) {
        return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para reabrir este ticket.'
        ], 403);
      }

      $oldStatusId = $ticket->status_id;

      // REABRIR TICKET
      $ticket->status_id = 7; // Reabierto
      $ticket->closed_at = null;
      $ticket->save();

      // Evitar sesiones abiertas duplicadas
      Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);
      Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
      Log::closeOpenSession($ticket->id, Log::TYPE_PAUSED);
      Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);

      // Abrir nueva sesión activa
      Log::openSession($ticket->id, Log::TYPE_ACTIVE_WORK, $ticket->assigned_to, 7, 'Trabajo retomado por reapertura del ticket');

      $userName = $user->name;

      // LOG REAPERTURA
      Log::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'agent_id' => $ticket->assigned_to,
        'type' => 'reopen',
        'description' => "El usuario {$userName} reabrió el ticket.",
        'old_status_id' => $oldStatusId,
        'new_status_id' => 7,
      ]);

      // LOG CAMBIO ESTADO
      Log::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'agent_id' => $ticket->assigned_to,
        'type' => 'status_change',
        'description' => "El estado del ticket cambió a 'Reabierto'.",
        'old_status_id' => $oldStatusId,
        'new_status_id' => 7,
      ]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'El ticket ha sido reabierto correctamente.'
      ]);
      //return redirect()->back()->with('success', 'El ticket ha sido reabierto correctamente.');
    } catch (\Exception $e) {
      DB::rollBack();
      // Redireccionar con mensaje de error
      return response()->json(['success' => false, 'message' => 'Ocurrió un error al intentar reabrir el ticket.' ], 500);
      //return redirect()->back()->with('error', 'Ocurrió un error al intentar reabrir el ticket.');
    }
  }

  /* Lista de tickets cerrados */
  public function conformidadData()
  {
    try {
      $tickets = Ticket::with(['status', 'priority', 'category', 'agent', 'creator', 'creator.area', 'creator.area.department'])->whereIn('status_id', [5, 6])->orderByDesc('created_at')->get();
      return response()->json([
        'data' => $tickets
      ]);
    } catch (\Exception $e) {
      LogFacade::error('TicketController@conformidadData -> ' .$e->getMessage());
      return response()->json([
        'data' => []
      ]);
    }
  }

  /* vista conformidad */
  public function conformidad()
  {
    return view('tickets.conformidad');
  }

  /* Marcar como resuelto */
  public function marcarResuelto($id)
  {
    try {
      DB::beginTransaction();
      // Buscar el ticket por su ID
      // Buscar ticket
      $ticket = Ticket::with(['creator', 'agent','status'])->findOrFail($id);
      // VALIDACIONES
      // Solo tickets cerrados
      if ($ticket->status_id != 6) {
        return response()->json([
          'success' => false,
          'message' => 'Solo se pueden marcar como resueltos los tickets cerrados.'
        ], 409);
      }
      // Permisos
      $user = auth()->user();
      $canResolve = $user->role === 'admin';

      if (!$canResolve) {
        return response()->json([
          'success' => false,
          'message' => 'No tienes permisos para marcar este ticket como resuelto.'
        ], 403);
      }

      $oldStatusId = $ticket->status_id;
      // CAMBIAR ESTADO
      $ticket->status_id = 5;
      $ticket->save();
      $userName = $user->name;

      // LOG RESOLUCIÓN
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => $ticket->assigned_to, 'type' => 'resolved', 'description' => "El usuario {$userName} marcó el ticket como resuelto.", 'old_status_id' => $oldStatusId, 'new_status_id' => 5,]);

      // LOG CAMBIO ESTADO
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => $ticket->assigned_to, 'type' => 'status_change', 'description' => "El estado del ticket cambió a 'Resuelto'.", 'old_status_id' => $oldStatusId, 'new_status_id' => 5,]);

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Ticket marcado como resuelto correctamente.'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Ocurrió un error al intentar marcar el ticket como resuelto.'
      ], 500);
    }
  }

  /* Cancelar ticket */
  public function cancelarticket($id)
  {
    try {
      $ticket = Ticket::with(['creator', 'agent', 'status'])->findOrFail($id);
      $user = auth()->user();
      // Solo creador o admin
      if ($user->role !== 'admin' && $ticket->user_id != $user->id) {
        return response()->json(['success' => false, 'message' => 'No tiene permisos para cancelar este ticket.'], 403);
      }

      // No permitir cancelar tickets cerrados
      if (in_array($ticket->status_id, [5,6,8])) {
        return response()->json([
          'success' => false,
          'message' => 'El ticket ya fue finalizado, resuelto o cancelado.'
        ], 409);
      }

      // Solo cancelar tickets sin asignar
      if (!is_null($ticket->assigned_to)) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede cancelar un ticket que ya fue asignado.'
        ], 409);
      }

      $oldStatusId = $ticket->status_id;
      // Cerrar sesiones abiertas
      Log::closeOpenSession($ticket->id, Log::TYPE_FIRST_RESPONSE_WAIT);
      Log::closeOpenSession($ticket->id, Log::TYPE_ACTIVE_WORK);
      Log::closeOpenSession($ticket->id, Log::TYPE_WAITING_CLIENT);
      Log::closeOpenSession($ticket->id, Log::TYPE_PAUSED);

      // Estado cancelado
      $ticket->status_id = 8;
      $ticket->save();

      $userName = $user->name;

      // Log cancelación
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => null, 'type' => 'ticket_cancelled', 'description' => "El usuario {$userName} canceló el ticket.", 'old_status_id' => $oldStatusId, 'new_status_id' => 8,]);

      // Log cambio estado
      Log::create(['ticket_id' => $ticket->id, 'user_id' => $user->id, 'agent_id' => null, 'type' => 'status_change', 'description' => "El estado del ticket cambió a 'Cancelado'.", 'old_status_id' => $oldStatusId, 'new_status_id' => 8,]);

      return response()->json([
        'success' => true,
        'message' => 'El ticket fue cancelado correctamente.'
      ]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Ocurrió un error al cancelar el ticket.'], 500);
    }
  }

  /* Generar PDF */
  public function generarPDF($id)
  {
    try {
      $ticketSimple = Ticket::select('id', 'subject')->findOrFail($id);
      $userName = auth()->user()->name;
      $ticket = Ticket::with(['creator.area.department', 'creator.area', 'agent', 'priority', 'status', 'category', 'messages.user', 'messages.attachments', 'attachments', 'logs.user'])->findOrFail($id);
      $pdf = Pdf::loadView('tickets.pdf', compact('ticket'))->setPaper('A4', 'portrait');
      $fecha = now()->format('d-m-Y');
      $asignado = $ticket->agent ? $ticket->agent->name : 'SinAsignar';
      $asignado = preg_replace('/[^A-Za-z0-9_\-]/', '_', $asignado);
      return $pdf->download("{$asignado}_{$fecha}.pdf");
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Ocurrió un error al generar el PDF del ticket.');
    }
  }

  /* vista con condicion */
  public function gestion()
  {
    return view('tickets.gestion');
  }

  /* Datos para gestion */
  public function gestionData()
  {
    try {
      $tickets = Ticket::with(['agent', 'priority', 'status', 'category', 'creator','creator.area', 'creator.area.department'])->orderByDesc('created_at')->get();
        return response()->json(['data' => $tickets]);
    } catch (\Exception $e) {
      LogFacade::error('TicketController@gestionData -> ' . $e->getMessage());
      return response()->json(['data' => []]);
    }
  }

  public function agentesData()
  {
    try {
      $users = User::whereIn('role', ['agent', 'admin'])->orderBy('name')->get();
      return response()->json([
        'results' => $users->map(function ($user) {
          return [
            'id' => $user->id,
            'text' => $user->name
          ];
        })
      ]);
    } catch (\Exception $e) {
      LogFacade::error('TicketController@agentesData -> ' . $e->getMessage());
      return response()->json(['results' => []]);
    }
  }

  public function prioridadesData()
  {
    try {
      $priorities = Priority::orderBy('name')->get();
      return response()->json([
        'results' => $priorities->map(function ($priority) {
          return [
            'id' => $priority->id,
            'text' => $priority->name
          ];
        })
      ]);
    } catch (\Exception $e) {
      LogFacade::error('TicketController@prioridadesData -> ' .$e->getMessage());
      return response()->json(['results' => []]);
    }
  }

  public function gestionTicketData($id)
  {
    try {
      $ticket = Ticket::with(['agent', 'priority', 'category', 'status', 'attachments'])->findOrFail($id);
      return response()->json(['ticket' => $ticket]);
    } catch (\Exception $e) {
      LogFacade::error('TicketController@gestionTicketData -> ' .$e->getMessage());
      return response()->json(['ticket' => null], 500);
    }
  }

  public function show($id)
  {
    try {
      $ticket = Ticket::findOrFail($id);
      $user = Auth::user();
      if ($user->role !== 'admin' &&
        $ticket->user_id !== $user->id &&
        $ticket->assigned_to !== $user->id) {
        abort(403, 'No tienes permiso para ver este ticket.');
      }
      return view('tickets.detalle', ['ticketId' => $ticket->id]);
    } catch (\Exception $e) {
      return redirect()->route('tickets.index')->with('error', 'No se pudo cargar el ticket.');
    }
  }

  public function showData($id)
  {
    try {
      $ticket = Ticket::with(['category', 'status', 'priority', 'agent', 'creator.area.department', 'attachments', 'messages.user', 'messages.attachments', 'logs.user'])->findOrFail($id);
      $user = Auth::user();
      if ($user->role !== 'admin' &&  $ticket->user_id !== $user->id && $ticket->assigned_to !== $user->id) {
        return response()->json([
          'success' => false,
          'message' => 'No autorizado.'
        ], 403);
      }
      return response()->json($ticket);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => 'Error al cargar ticket.'], 500);
    }
  }


  public function asignados()
  {
    $userId = auth()->id();

    $tickets = Ticket::with(['category', 'priority', 'status'])
                ->where('assigned_to', $userId)
                ->latest()
                ->get();

    return view('tickets.asignados', compact('tickets'));
}
   

    

      

      

    

 




}
