@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
@endpush

<div class="card card-body">
    <h5 class="text-primary">Bienvenido, {{ auth()->user()->name }}</h5>
    <p>Este es tu panel de administrador. Aquí podrás ver el estado de tus tickets y tus solicitudes.</p>
</div>

<form method="GET" action="{{ route('dashboard') }}">
    <div class="row mb-4">

        {{-- MES --}}
        <div class="col-md-2">
            <label for="month" class="form-label">Mes</label>
            <select name="month" id="month" class="form-select">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- AÑO --}}
        <div class="col-md-2">
            <label for="year" class="form-label">Año</label>
            <select name="year" id="year" class="form-select">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>

        {{-- DEPARTAMENTO --}}
        <div class="col-md-2">
            <label for="department_id" class="form-label">Departamento</label>
            <select name="department_id" id="department_id" class="form-select">
                <option value="">Todos</option>

                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- AREA --}}
        <div class="col-md-2">
            <label for="area_id" class="form-label">Área</label>
            <select name="area_id" id="area_id" class="form-select">
                <option value="">Todas</option>

                @foreach($areas as $area)
                    <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>
                        {{ $area->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- EMPLEADO / AGENTE --}}
        <div class="col-md-2">
            <label for="employee_id" class="form-label">Empleado</label>
            <select name="employee_id" id="employee_id" class="form-select">
                <option value="">Todos</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $employeeId == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- BOTON --}}
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>

    </div>
</form>

<div class="row">
    @foreach($estados as $estado)
    <div class="col-xl-3 col-md-6 gap-6">
        <div class="card card-animate" style="height: 90%;">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden "  style="min-height: 48px; display: flex; align-items: flex-start;">
                        <p class="text-uppercase fw-medium text-muted">{{ $estado['nombre'] }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <h5 class="text-muted fs-14 mb-0">
                            {{ $estado['porcentaje'] }}%
                        </h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="{{ $estado['total'] }}">0</span>
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-{{ $estado['color'] }} rounded fs-3">
                            <i class="{{ $estado['icono'] }} text-{{ $estado['color'] }}"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Categoría</h4>
                <span class="text-muted">Mes actual: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_categoria" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Prioridad</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_prioridad" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Departamento</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_departamento" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header border-0 d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Tickets por Área</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_area" class="apex-charts" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Distribución por Estado</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>

            <div class="card-body">
                <div id="grafico_tickets_estado" class="apex-charts" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tickets Asignados por Agente</h4>
                <span class="text-muted">Mes: {{ request('month', now()->month) }}/{{ request('year', now()->year) }}</span>
            </div>
            <div class="card-body">
                <div id="grafico_tickets_agentes" class="apex-charts" style="min-height: 400px;"></div>
                <div class="table-responsive mt-4">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Agente</th>
                <th class="text-center">Tickets</th>
                <th class="text-center">Cerrados</th>
                <th class="text-center">Pendientes</th>
                <th class="text-center">Prom. Respuesta</th>
                <th class="text-center">Prom. Solución</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agentesData as $agente)
                <tr>
                    <td>{{ $agente['nombre'] }}</td>
                    <td class="text-center">{{ $agente['total'] }}</td>
                    <td class="text-center">{{ $agente['cerrados'] }}</td>
                    <td class="text-center">{{ $agente['pendientes'] }}</td>
                    <td class="text-center">{{ $agente['promedio_respuesta'] }}</td>
                    <td class="text-center">{{ $agente['promedio_solucion'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
            </div>
        </div>
    </div>

</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Calendario de Tickets Asignados</h4>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>


@push('dashboard-scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
            const departmentsData = @json($departments);
const selectedAreaId = "{{ $areaId }}";

const departmentSelect = document.getElementById('department_id');
const areaSelect = document.getElementById('area_id');

if (departmentSelect && areaSelect) {

    function cargarAreasPorDepartamento() {
        const departmentId = departmentSelect.value;

        areaSelect.innerHTML = '<option value="">Todas</option>';

        if (!departmentId) {
            departmentsData.forEach(function (department) {
                if (department.areas) {
                    department.areas.forEach(function (area) {
                        const option = document.createElement('option');
                        option.value = area.id;
                        option.textContent = area.name;

                        if (selectedAreaId == area.id) {
                            option.selected = true;
                        }

                        areaSelect.appendChild(option);
                    });
                }
            });

            return;
        }

        const department = departmentsData.find(function (item) {
            return item.id == departmentId;
        });

        if (!department || !department.areas || department.areas.length === 0) {
            areaSelect.innerHTML = '<option value="">Sin áreas</option>';
            return;
        }

        department.areas.forEach(function (area) {
            const option = document.createElement('option');
            option.value = area.id;
            option.textContent = area.name;

            if (selectedAreaId == area.id) {
                option.selected = true;
            }

            areaSelect.appendChild(option);
        });
    }

    departmentSelect.addEventListener('change', function () {
        cargarAreasPorDepartamento();
    });

    cargarAreasPorDepartamento();
}

        // Gráfico por Categoría
        new ApexCharts(document.querySelector("#grafico_tickets_categoria"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tickets',
                data: @json($categoriasData->pluck('total'))
            }],
            xaxis: {
                categories: @json($categoriasData->pluck('nombre')),
                labels: { rotate: -45 }
            },
            colors: ['#556ee6']
        }).render();

        // Gráfico por Prioridad
        new ApexCharts(document.querySelector("#grafico_tickets_prioridad"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tickets',
                data: @json($prioridadesData->pluck('total'))
            }],
            xaxis: {
                categories: @json($prioridadesData->pluck('nombre')),
                labels: { rotate: -45 }
            },
            colors: ['#34c38f']
        }).render();

        // Gráfico por Departamento
        new ApexCharts(document.querySelector("#grafico_tickets_departamento"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tickets',
                data: @json($departamentosData->pluck('total'))
            }],
            xaxis: {
                categories: @json($departamentosData->pluck('nombre')),
                labels: { rotate: -45 }
            },
            colors: ['#f1b44c']
        }).render();

        // Gráfico por Área
        new ApexCharts(document.querySelector("#grafico_tickets_area"), {
            chart: {
                type: 'bar',
                height: 300
            },
            series: [{
                name: 'Tickets',
                data: @json($areasData->pluck('total'))
            }],
            xaxis: {
                categories: @json($areasData->pluck('nombre')),
                labels: { rotate: -45 }
            },
            colors: ['#34c38f'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " ticket(s)";
                    }
                }
            }
        }).render();

        //Calendar
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 500,
            events: @json($eventosCalendar),
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                }
            }
        });

        calendar.render();

        // Gráfico de Dona por Estado
        new ApexCharts(document.querySelector("#grafico_tickets_estado"), {
            chart: {
                type: 'donut',
                height: 400
            },
            series: @json($estados->pluck('total')),
            labels: @json($estados->pluck('nombre')),
            colors: [
                @foreach($estados as $estado)
                    getComputedStyle(document.documentElement)
                        .getPropertyValue('--vz-{{ $estado["color"] }}') || '{{ $estado["color"] }}',
                @endforeach
            ],
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " ticket(s)";
                    }
                }
            }
        }).render();

        // Gráfico de Barras Horizontales por Agente
        new ApexCharts(document.querySelector("#grafico_tickets_agentes"), {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4
                }
            },
            series: [{
                name: 'Tickets asignados',
                data: @json($agentesData->pluck('total'))
            }],
            xaxis: {
                categories: @json($agentesData->pluck('nombre')),
                title: { text: 'Cantidad de Tickets' }
            },
            colors: ['#3b76e1'],
            tooltip: {
                y: {
                    formatter: val => `${val} ticket(s)`
                }
            }
        }).render();

    });
</script>
@endpush
