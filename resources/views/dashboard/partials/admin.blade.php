<div class="row">

    <div class="col-xl-3 col-md-6">

        <div class="card card-animate">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <p class="text-uppercase fw-medium text-muted mb-0">
                            Proveedores
                        </p>

                        <h2 class="mt-4 ff-secondary fw-semibold">
                            {{ $totalProviders }}
                        </h2>
                    </div>

                    <div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-3">
                                <i class="ri-building-2-line"></i>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card card-animate">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <p class="text-uppercase fw-medium text-muted mb-0">
                            Contratos Activos
                        </p>

                        <h2 class="mt-4 ff-secondary fw-semibold">
                            {{ $activeContracts }}
                        </h2>
                    </div>

                    <div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-success text-success rounded-circle fs-3">
                                <i class="ri-file-list-3-line"></i>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card card-animate">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <p class="text-uppercase fw-medium text-muted mb-0">
                            Por Vencer
                        </p>

                        <h2 class="mt-4 ff-secondary fw-semibold text-warning">
                            {{ $expiringContracts }}
                        </h2>
                    </div>

                    <div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-warning text-warning rounded-circle fs-3">
                                <i class="ri-alarm-warning-line"></i>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card card-animate">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <p class="text-uppercase fw-medium text-muted mb-0">
                            Vencidos
                        </p>

                        <h2 class="mt-4 ff-secondary fw-semibold text-danger">
                            {{ $expiredContracts }}
                        </h2>
                    </div>

                    <div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-danger text-danger rounded-circle fs-3">
                                <i class="ri-close-circle-line"></i>
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- GRAFICO --}}
<div class="row mt-4">

    <div class="col-xl-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">
                    Comparativo de Contratos por Año
                </h4>
            </div>

            <div class="card-body">
                <div id="contracts_chart"></div>
            </div>

        </div>

    </div>

</div>

{{-- TABLA --}}
<div class="row mt-4">

    <div class="col-12">

        <div class="card">

            <div class="card-header">
                <h4 class="card-title mb-0">
                    Contratos Próximos a Vencer
                </h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Proveedor</th>
                                <th>Contrato</th>
                                <th>Monto</th>
                                <th>Fecha Final</th>
                                <th>Días Restantes</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($contractsToExpire as $contract)

                            <tr>

                                <td>
                                    {{ $contract->provider->name }}
                                </td>

                                <td>
                                    {{ $contract->name }}
                                </td>

                                <td>
                                    {{ $contract->amount }}
                                </td>

                                <td>
                                    {{ $contract->end_date }}
                                </td>

                                <td>
                                    {{ now()->diffInDays($contract->end_date, false) }}
                                </td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@push('dashboard-scripts')

<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    new ApexCharts(document.querySelector("#contracts_chart"), {

        chart: {
            type: 'line',
            height: 350
        },

        series: [{
            name: 'Monto Contratos',
            data: @json($chartData)
        }],

        xaxis: {
            categories: @json($chartYears)
        }

    }).render();

});

</script>

@endpush