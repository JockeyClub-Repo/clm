@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

<style>
.timeline-provider{
    border-left:3px solid #405189;
    padding-left:20px;
    margin-bottom:30px;
}

.timeline-contract{
    position:relative;
    margin-bottom:15px;
    padding:15px;
    border:1px solid #e9ebec;
    border-radius:8px;
    background:#fff;
}

.timeline-contract::before{
    content:'';
    position:absolute;
    left:-28px;
    top:18px;
    width:14px;
    height:14px;
    border-radius:50%;
    background:#405189;
}
</style>
@endpush

<div class="card card-body mb-4">
    <h5 class="text-primary">
        Bienvenido, {{ auth()->user()->name }}
    </h5>

    <p class="mb-0">
        Resumen general de contratos y vencimientos.
    </p>
</div>

{{-- FILTRO --}}
<div class="row mb-4">

    <div class="col-md-4">

        <label class="form-label">
            Mostrar
        </label>

        <select id="filterContracts" class="form-select">

            <option value="active">
                Contratos activos
            </option>

            <option value="expiring">
                Próximos a vencer
            </option>

            <option value="expired">
                Contratos vencidos
            </option>

            <option value="not_renewed">
                No renovados
            </option>

            <option value="all">
                Todos
            </option>

        </select>

    </div>

</div>

{{-- KPIS --}}
<div class="row">

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body text-center">
                <h5>Total Contratos</h5>
                <h2 id="kpi_total">0</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body text-center">
                <h5>Activos</h5>
                <h2 id="kpi_active">0</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body text-center">
                <h5>Por vencer</h5>
                <h2 id="kpi_expiring">0</h2>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body text-center">
                <h5>Vencidos</h5>
                <h2 id="kpi_expired">0</h2>
            </div>
        </div>
    </div>

</div>

{{-- CALENDARIO --}}
<div class="row mt-4">

    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">
                    Calendario de vencimientos
                </h4>
            </div>

            <div class="card-body">
                <div id="calendar"></div>
            </div>

        </div>

    </div>

</div>

{{-- TIMELINE --}}
<div class="row mt-4">

    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">
                    Historial de contratos por proveedor
                </h4>
            </div>

            <div class="card-body">

                <div id="timelineContainer"></div>

            </div>

        </div>

    </div>

</div>

@push('dashboard-scripts')

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const filterContracts =
        document.getElementById('filterContracts');

    let calendar = null;

    async function loadDashboard() {

        const filter = filterContracts.value;

        const response = await fetch(
            `/dashboard/admin/data?filter=${filter}`
        );

        const data = await response.json();

        /*
        |--------------------------------------------------------------------------
        | KPIS
        |--------------------------------------------------------------------------
        */

        document.getElementById('kpi_total').innerHTML =
            data.kpis.total;

        document.getElementById('kpi_active').innerHTML =
            data.kpis.active;

        document.getElementById('kpi_expiring').innerHTML =
            data.kpis.expiring;

        document.getElementById('kpi_expired').innerHTML =
            data.kpis.expired;

        /*
        |--------------------------------------------------------------------------
        | CALENDAR
        |--------------------------------------------------------------------------
        */

        if (calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(
            document.getElementById('calendar'),
            {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 650,
                events: data.calendar
            }
        );

        calendar.render();

        /*
        |--------------------------------------------------------------------------
        | TIMELINE
        |--------------------------------------------------------------------------
        */

        let html = '';

        data.providersTimeline.forEach(provider => {

            html += `
                <div class="timeline-provider">

                    <h4 class="mb-4">
                        ${provider.provider}
                    </h4>
            `;

            provider.contracts.forEach(contract => {

                let badge = 'success';

                if(contract.status === 'Vencido')
                    badge = 'danger';

                if(contract.status === 'No renovado')
                    badge = 'dark';

                html += `
                    <div class="timeline-contract">

                        <div class="row">

                            <div class="col-md-6">

                                <h5>
                                    ${contract.name}
                                </h5>

                                <div>
                                    ${contract.start_date}
                                    →
                                    ${contract.end_date}
                                </div>

                            </div>

                            <div class="col-md-3 text-center">

                                <h5>
                                    ${contract.currency}
                                    ${contract.amount}
                                </h5>

                            </div>

                            <div class="col-md-3 text-end">

                                <span class="badge bg-${badge}">
                                    ${contract.status}
                                </span>

                            </div>

                        </div>

                    </div>
                `;
            });

            html += `
                </div>
            `;
        });

        document.getElementById('timelineContainer').innerHTML = html;
    }

    filterContracts.addEventListener(
        'change',
        loadDashboard
    );

    loadDashboard();
});
</script>

@endpush