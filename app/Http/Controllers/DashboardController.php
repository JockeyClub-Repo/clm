<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Status;
use App\Models\Category;
use App\Models\User;
use App\Models\Department;
use App\Models\Area;

class DashboardController extends Controller
{
    public function dashboardRouter(Request $request)
    {
        $role = auth()->user()->role;

        return match ($role) {
            'client' => $this->clientDashboard($request),
            'agent'  => $this->agentDashboard($request),
            'admin'  => $this->adminDashboard($request),
            default  => abort(403, 'Rol no autorizado.'),
        };
    }

    public function adminDashboard(Request $request)
    {
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        $departmentId = $request->input('department_id');
        $areaId = $request->input('area_id');
        $employeeId = $request->input('employee_id');

        $query = Ticket::with([
            'category',
            'priority',
            'status',
            'creator.area.department',
            'agent',
            'messages'
            ])
        ->whereMonth('created_at', $mes)
        ->whereYear('created_at', $anio);


/*
|--------------------------------------------------------------------------
| FILTRO POR DEPARTAMENTO
|--------------------------------------------------------------------------
*/
if ($departmentId) {
    $query->whereHas('area.department', function ($q) use ($departmentId) {
        $q->where('id', $departmentId);
    });
}

/*
|--------------------------------------------------------------------------
| FILTRO POR AREA
|--------------------------------------------------------------------------
*/

if ($areaId) {
    $query->where('area_id', $areaId);
}

/*
|--------------------------------------------------------------------------
| FILTRO POR EMPLEADO / AGENTE
|--------------------------------------------------------------------------
*/
if ($employeeId) {
    $query->where('assigned_to', $employeeId);
}

$tickets = $query->get();

        $totalTickets = $tickets->count();

        $statusList = Status::orderBy('id')->get();
        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            return [
                'id'         => $status->id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$status->id]['color'] ?? 'secondary',
                'icono'      => $estilos[$status->id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values();

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            $prioridad = $group->first()->priority;
            return [
                'nombre' => $prioridad->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
                'color'  => $prioridad->color ?? '#6c757d',
            ];
        })->values();

        $eventosCalendar = Ticket::with('creator:id,name')
            ->select('id', 'user_id', 'created_at')
            ->get()
            ->map(function ($ticket) {
                return [
                    'title' => "ID {$ticket->id} - {$ticket->creator->name}",
                    'start' => $ticket->created_at->toDateString(),
                    'url' => route('tickets.detalle', $ticket->id),
                ];
            });

        $departamentosData = $tickets->groupBy(function ($ticket) {
            return optional(optional(optional($ticket->creator)->area)->department)->id;
        })->map(function ($group) {
            $department = optional(optional($group->first()->creator)->area)->department;

            return [
                'nombre' => optional($department)->name ?? 'Sin Departamento',
                'total'  => $group->count(),
            ];
        })->values();

        $areasData = $tickets->groupBy(function ($ticket) {
            return optional(optional($ticket->creator)->area)->id;
        })->map(function ($group) {
            $area = optional($group->first()->creator)->area;

            return [
                'nombre' => optional($area)->name ?? 'Sin Área',
                'total'  => $group->count(),
            ];
        })->values();

       $agentesData = User::where('role', 'agent')
    ->orderBy('name')
    ->get()
    ->map(function ($agente) use ($tickets) {

        $ticketsAgente = $tickets->where('assigned_to', $agente->id);

        $total = $ticketsAgente->count();

        $cerrados = $ticketsAgente->filter(function ($ticket) {
            return in_array($ticket->status_id, [5, 6]) || !is_null($ticket->closed_at);
        })->count();

        $pendientes = $total - $cerrados;

        $minutosRespuesta = [];
        $minutosSolucion = [];

        foreach ($ticketsAgente as $ticket) {

            $primerMensajeAgente = $ticket->messages
                ->where('user_id', $agente->id)
                ->sortBy('created_at')
                ->first();

            if ($primerMensajeAgente) {
                $minutosRespuesta[] = $ticket->created_at->diffInMinutes($primerMensajeAgente->created_at);
            }

            if ($ticket->closed_at) {
                $minutosSolucion[] = $ticket->created_at->diffInMinutes($ticket->closed_at);
            }
        }

        $promedioRespuesta = count($minutosRespuesta) > 0
            ? round(array_sum($minutosRespuesta) / count($minutosRespuesta))
            : null;

        $promedioSolucion = count($minutosSolucion) > 0
            ? round(array_sum($minutosSolucion) / count($minutosSolucion))
            : null;

        return [
            'nombre' => $agente->name,
            'total' => $total,
            'cerrados' => $cerrados,
            'pendientes' => $pendientes,
            'promedio_respuesta' => $promedioRespuesta !== null ? $this->formatearMinutos($promedioRespuesta) : 'Sin respuesta',
            'promedio_solucion' => $promedioSolucion !== null ? $this->formatearMinutos($promedioSolucion) : 'Sin cierre',
        ];
    });
           
/*
|--------------------------------------------------------------------------
| COMBOS DE FILTRO
|--------------------------------------------------------------------------
*/

        $departments = Department::with('areas')
    ->orderBy('name')
    ->get();

        $areas = Area::when($departmentId, function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->orderBy('name')
            ->get();

        $employees = User::where('role', 'agent')
            ->orderBy('name')
            ->get();

        return view('dashboard', [
            'view' => 'admin',
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
            'eventosCalendar' => $eventosCalendar,
            'departamentosData' => $departamentosData,
            'areasData' => $areasData,
            'agentesData' => $agentesData,

            'departments' => $departments,
            'areas' => $areas,
            'employees' => $employees,

            'departmentId' => $departmentId,
            'areaId' => $areaId,
            'employeeId' => $employeeId,
        ]);
    }

    public function clientDashboard(Request $request)
    {
        $userId = auth()->id();
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        // Obtener todos los estados
        $statusList = Status::orderBy('id')->get();

        // Obtener los tickets del usuario filtrados por mes y año
        $tickets = Ticket::where('user_id', $userId)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $totalTickets = $tickets->count();

        // Estilos personalizados por ID de estado
        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        // Armar la colección de estados con los datos contados
        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            $id = $status->id;

            return [
                'id'         => $id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$id]['color'] ?? 'secondary',
                'icono'      => $estilos[$id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values(); // limpia claves

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            return [
                'nombre' => optional($group->first()->priority)->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
            ];
        })->values(); // limpia claves

        return view('dashboard', [
            'view' => 'client',
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
        ]);
    }

    public function agentDashboard(Request $request)
    {
        $userId = auth()->id();
        $mes = $request->input('month', now()->month);
        $anio = $request->input('year', now()->year);

        $tickets = Ticket::with(['category', 'priority', 'status'])
            ->where('assigned_to', $userId)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $anio)
            ->get();

        $totalTickets = $tickets->count();

        // Listado de estados
        $statusList = Status::orderBy('id')->get();

        $estilos = [
            1 => ['color' => 'warning',   'icono' => 'bx bx-help-circle'],
            2 => ['color' => 'primary',   'icono' => 'bx bx-loader-circle'],
            3 => ['color' => 'info',      'icono' => 'bx bx-user-voice'],
            4 => ['color' => 'info',      'icono' => 'bx bx-user-pin'],
            5 => ['color' => 'success',   'icono' => 'bx bx-check-circle'],
            6 => ['color' => 'dark',      'icono' => 'bx bx-lock-alt'],
            7 => ['color' => 'secondary', 'icono' => 'bx bx-history'],
            8 => ['color' => 'danger',    'icono' => 'bx bx-x-circle'],
        ];

        $estados = $statusList->map(function ($status) use ($tickets, $totalTickets, $estilos) {
            $cantidad = $tickets->where('status_id', $status->id)->count();
            return [
                'id'         => $status->id,
                'nombre'     => $status->name,
                'total'      => $cantidad,
                'color'      => $estilos[$status->id]['color'] ?? 'secondary',
                'icono'      => $estilos[$status->id]['icono'] ?? 'bx bx-circle',
                'porcentaje' => $totalTickets > 0 ? round(($cantidad / $totalTickets) * 100, 2) : 0,
            ];
        });

        $categoriasData = $tickets->groupBy('category_id')->map(function ($group, $catId) {
            return [
                'nombre' => optional($group->first()->category)->name ?? 'Sin Categoría',
                'total'  => $group->count(),
            ];
        })->values();

        $prioridadesData = $tickets->groupBy('priority_id')->map(function ($group, $priorityId) {
            $prioridad = $group->first()->priority;
            return [
                'nombre' => $prioridad->name ?? 'Sin Prioridad',
                'total'  => $group->count(),
                'color'  => $prioridad->color ?? '#6c757d',
            ];
        })->values();

        // Calendario: todos los tickets asignados al agente (sin filtro de mes/año)
        $eventosCalendar = Ticket::with('creator:id,name')
        ->where('assigned_to', $userId)
        ->select('id', 'user_id', 'created_at')
        ->get()
        ->map(function ($ticket) {
            return [
                'title' => "ID {$ticket->id} - {$ticket->creator->name}",
                'start' => $ticket->created_at->toDateString(),
                'url' => route('tickets.detalle', $ticket->id),
            ];
        });

        return view('dashboard', [
            'view' => 'agent',
            'eventosCalendar' => $eventosCalendar,
            'estados' => $estados,
            'categoriasData' => $categoriasData,
            'prioridadesData' => $prioridadesData,
        ]);
    }

        private function formatearMinutos($minutos)
{
    if ($minutos < 60) {
        return $minutos . ' min';
    }

    $horas = floor($minutos / 60);
    $minutosRestantes = $minutos % 60;

    if ($horas < 24) {
        return $horas . ' h ' . $minutosRestantes . ' min';
    }

    $dias = floor($horas / 24);
    $horasRestantes = $horas % 24;

    return $dias . ' d ' . $horasRestantes . ' h';
}

}
