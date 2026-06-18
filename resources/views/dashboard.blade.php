@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <span class="mb-sm-0">Dashboard de Contratos</span>
            <div class="page-title-right">
              <ol class="breadcrumb m-0">
                <li class="breadcrumb-item">
                  <a href="#">Inicio</a>
                </li>
                <li class="breadcrumb-item active">
                  Dashboard
                </li>
              </ol>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-4">

    <div class="col-md-2">
        <div class="card bg-primary text-white kpi-card" data-filter="">
            <div class="card-body text-center">
                <h2 id="kpi-total" class="text-white">0</h2>
                <small>Total Contratos</small>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-success text-white kpi-card" data-filter="active">
            <div class="card-body text-center">
                <h2 id="kpi-active" class="text-white">0</h2>
                <small>Activos</small>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-info text-white kpi-card" data-filter="renewed">
            <div class="card-body text-center">
                <h2 id="kpi-renewed" class="text-white">0</h2>
                <small>Renovados</small>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-danger text-white kpi-card" data-filter="not_renewed">
            <div class="card-body text-center">
                <h2 id="kpi-not-renewed" class="text-white">0</h2>
                <small>No Renovados</small>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-warning text-white kpi-card" data-filter="expiring">
            <div class="card-body text-center">
                <h2 id="kpi-expiring" class="text-white">0</h2>
                <small>Por Vencer</small>
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-danger text-white kpi-card" data-filter="expired">
            <div class="card-body text-center">
                <h2 id="kpi-expired" class="text-white">0</h2>
                <small>Vencidos</small>
            </div>
        </div>
    </div>

</div>
      <div class="row">
        <div class="col-lg-3">
          <div class="card">
            <div class="card-header">
              Estado de Contratos
            </div>
            <div class="card-body">
              <canvas id="contractsChart"></canvas>
            </div>
          </div>
        </div>
        <div class="card mb-4 col-lg-9">
          <div class="card-header">
            Próximos vencimientos
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Contrato</th>
                  <th>Proveedor</th>
                  <th>Fin</th>
                  <th>Días</th>
                </tr>
              </thead>
              <tbody id="expirationTable"></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            Línea de Tiempo de Contratos
          </div>
          <div class="card-body">
            <div id="timelineContainer"></div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          Estado por proveedor
        </div>
        <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Proveedor</th>
              <th>Activos</th>
              <th>Renovados</th>
              <th>No Renovados</th>
            </tr>
          </thead>
          <tbody id="providerTable"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

    let contractsChart = null;

$(function () {

    loadDashboard();

    $('.kpi-card').click(function () {

        let filter = $(this).data('filter');

        loadDashboard(filter);

    });

});

function loadDashboard(filter = '')
{
    $.ajax({

        url: "{{ route('dashboard.data') }}",

        data: {
            filter: filter
        },

        success: function(response)
        {
            $('#kpi-total').text(response.kpis.total);
            $('#kpi-active').text(response.kpis.active);
            $('#kpi-renewed').text(response.kpis.renewed);
            $('#kpi-not-renewed').text(response.kpis.notRenewed);
            $('#kpi-expiring').text(response.kpis.expiring);
$('#kpi-expired').text(response.kpis.expired);
            renderChart(response.kpis);
renderTimeline(response.timeline);

            let providersHtml = '';

            response.providers.forEach(provider => {

                providersHtml += `
                    <tr>
                        <td>${provider.name}</td>
                        <td>${provider.active_count}</td>
                        <td>${provider.renewed_count}</td>
                        <td>${provider.not_renewed_count}</td>
                    </tr>
                `;
            });

            $('#providerTable').html(providersHtml);

            let expirationsHtml = '';

            response.nextExpirations.forEach(contract => {

                expirationsHtml += `
                    <tr>
                        <td>${contract.name}</td>
                        <td>${contract.provider?.name ?? ''}</td>
                        <td>${contract.end_date}</td>
                        <td>${contract.days_remaining}</td>
                    </tr>
                `;
            });

            $('#expirationTable').html(expirationsHtml);
        }
    });
}

function renderChart(kpis)
{
    const ctx = document
        .getElementById('contractsChart')
        .getContext('2d');

    if (contractsChart) {
        contractsChart.destroy();
    }

    contractsChart = new Chart(ctx, {

        type: 'doughnut',

        data: {
            labels: [
                'Activos',
                'Renovados',
                'No Renovados'
            ],

            datasets: [{
                data: [
                    kpis.active,
                    kpis.renewed,
                    kpis.notRenewed
                ]
            }]
        }
    });
}

function renderTimeline(timeline)
{
    let html = '';

    timeline.forEach(group => {

        html += `
            <div class="mb-4">

                <h5 class="mb-3">
                    ${group.provider}
                </h5>

                <div class="d-flex flex-wrap align-items-center gap-2">
        `;

        group.contracts.forEach((contract,index)=>{

    let statusText = '';
if(contract.status_label)
{
    statusText = contract.status_label;
}
else if(contract.status === 'active')
{
    statusText = 'Activo';
}
else if(contract.status === 'renewed')
{
    statusText = 'Renovado';
}
else if(contract.status === 'not_renewed')
{
    statusText = 'No Renovado';
}
else
{
    statusText = contract.status;
}

let arrowColor = '#6c757d';

if(contract.previous_amount){

    const currentAmount = parseFloat(contract.amount);
    const previousAmount = parseFloat(contract.previous_amount);

    if(currentAmount > previousAmount){
        arrowColor = '#dc3545';
    }
    else if(currentAmount < previousAmount){
        arrowColor = '#198754';
    }
}

let variation = '';

if(contract.previous_amount){

    const currentAmount = parseFloat(contract.amount);
    const previousAmount = parseFloat(contract.previous_amount);

    const pct =
        ((currentAmount - previousAmount)
        / previousAmount) * 100;

    variation = `
        <div
            class="fw-bold mt-1"
            style="color:${arrowColor}"
        >
            ${pct > 0 ? '+' : ''}
            ${pct.toFixed(2)}%
        </div>
    `;
}

    html += `
        <div class="card border" style="min-width:250px">
            <div class="card-body p-2">

                <strong>${contract.name}</strong>

                <br>

                <small>
                    ${contract.start_date}
                    →
                    ${contract.end_date}
                </small>

                <br>

                <span class="badge bg-info">
                    ${statusText}
                </span>

                <span class="badge bg-success">
                    ${contract.amount} ${contract.currency}
                </span>
${variation}
            </div>
        </div>
    `;
    if(index < group.contracts.length - 1)
            {
                html += `
                    <div style="
                        font-size:24px;
                        font-weight:bold;
                        color:${arrowColor};
                    ">
                        →
                    </div>
                `;
            }
        });

        html += `
                </div>
            </div>
        `;
});

            

    $('#timelineContainer').html(html);
}

</script>


@endpush