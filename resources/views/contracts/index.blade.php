@extends('layouts.app')

@section('title', 'Mantenimiento de Contratos')
@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Listado De Contratos</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Mantenimiento</li>
              <li class="breadcrumb-item active">Contratos</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    @elseif (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    @endif
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="row mb-3" id="dashboardContratos">
  <div class="col-md-3">
    <div class="card border-primary shadow-sm">
      <div class="card-body text-center">
        <h6>Total Contratos</h6>
        <h3 id="kpiTotal">0</h3>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-success shadow-sm">
      <div class="card-body text-center">
        <h6>Vigentes</h6>
        <h3 id="kpiVigentes">0</h3>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-warning shadow-sm">
      <div class="card-body text-center">
        <h6>Por Vencer</h6>
        <h3 id="kpiPorVencer">0</h3>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card border-danger shadow-sm">
      <div class="card-body text-center">
        <h6>Vencidos</h6>
        <h3 id="kpiVencidos">0</h3>
      </div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-12">
    <div class="card bg-light shadow-sm">
      <div class="card-body text-center">
        <h6>Monto Total Contratado</h6>
        <h3 id="kpiMontoTotal">0.00</h3>
      </div>
    </div>
  </div>
</div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <a href="{{ route('contracts.create') }}" class="btn btn-sm btn-primary">Nuevo Registro</a>
              <button class="btn btn-sm btn-success" id="btnRefresh"><i class="ri-refresh-line"></i> Recargar</button>
            </div>
            <div class="table-responsive">
<table id="contracts-table" class="table table-bordered table-striped table-sm align-middle" style="width:100%">                <thead>
                  <tr>
                    <th>Contrato</th>
                    <th>Descripción</th>
                    <th>Proveedor</th>
                    <th>Moneda</th>
                    <th>Monto</th>
                    <th>Inicio</th>
                    <th style=" white-space: nowrap">Avisar desde</th>
                    <th style=" white-space: nowrap">Días restantes</th> 
                    <th>Fin</th>
                    <th>Estado</th>
                    <th style="white-space: nowrap">PDF / IMG</th>
                    <th style=" white-space: nowrap">Estado Contrato</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="renewModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="renewForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Renovar Contrato</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="renew_contract_id">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Fecha Inicio</label>
              <input type="date" class="form-control" id="renew_start_date" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Fecha Fin</label>
              <input type="date"  class="form-control" id="renew_end_date"required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Moneda</label>
              <select id="renew_currency" class="form-select" required>
                <option value="USD">USD</option>
                <option value="PEN">PEN</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label>Monto</label>
              <input type="number" class="form-control" id="renew_amount" step="0.01" required>
            </div>
          </div>
          <div class="mb-3">
            <label>Avisar desde (días antes): <span id="renewal_notice_days" class="fw-bold"></span></label>
            <input type="number" class="form-control" id="renew_renewal_notice_days" required>
          </div>
          <div class="mb-3">
            <label>Descripción</label>
            <textarea class="form-control" id="renew_description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Renovar</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
  <script>
  $(document).ready(function () {
    function actualizarDashboardContratos(data) {
    let total = data.length;
    let vigentes = 0;
    let porVencer = 0;
    let vencidos = 0;
    let montoTotal = 0;

    data.forEach(function (item) {
        let estado = item.status_label ?? '';
        let monto = parseFloat(item.amount ?? 0);

        montoTotal += monto;

        if (estado === 'Vigente') {
            vigentes++;
        } else if (
            estado === 'Próximo a Vencer' ||
            estado === 'Próximo a Renovar' ||
            estado === 'Renovación Pendiente' ||
            estado === 'Vence Hoy'
        ) {
            porVencer++;
        } else if (estado === 'Vencido') {
            vencidos++;
        }
    });

    $('#kpiTotal').text(total);
    $('#kpiVigentes').text(vigentes);
    $('#kpiPorVencer').text(porVencer);
    $('#kpiVencidos').text(vencidos);

    $('#kpiMontoTotal').text(
        montoTotal.toLocaleString('es-PE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })
    );
}

    let renewModal = new bootstrap.Modal(
      document.getElementById('renewModal')
    );

    // Renovar contrato - Modal
    $(document).on('click', '.btn-renew', function () {
      console.log($(this).data());
      $('#renew_contract_id').val($(this).data('id')); 
      $('#renew_currency').val($(this).data('currency'));
      renewModal.show();
    });

    //No renovar contrato
    $(document).on('click', '.btn-not-renew', function () {
      const id = $(this).data('id');
      Swal.fire({
        title: '¿Marcar como no renovado?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí'
      }).then((result) => {
      if (!result.isConfirmed) return;
      $.post(`{{ url('contracts') }}/${id}/not-renew`, {
          _token: '{{ csrf_token() }}'
        },
        function(response){
          Swal.fire('Correcto', response.message, 'success');
          table.ajax.reload(null, false);
        });
      });
    });

    let table = $('#contracts-table').DataTable({
    processing: true,
ajax: {
    url: '{{ route("contracts.data") }}',
    dataSrc: function (json) {
        actualizarDashboardContratos(json.data);
        return json.data;
    }
},    responsive: false,
    scrollX: true,
    scrollCollapse: true,
    autoWidth: false,

    columnDefs: [
        { targets: 9, width: '90px', className: 'text-center' },   // Estado
        { targets: 10, width: '80px', className: 'text-center' },  // PDF / IMG
        { targets: 11, width: '140px', className: 'text-center' }, // Estado Contrato
        { targets: 12, width: '180px', className: 'text-center' }  // Acciones
    ],
    
      columns: [
        { data: 'name' },
        { data: 'description'},
        { data: 'provider.name' },
        { data: 'currency'},
        { data: 'amount'},
        { data: 'start_date',
          render: function (data) {
            let fecha = new Date(data);
            return fecha.toLocaleString('es-PE', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
            });
          }
        },
        {
          data: 'renewal_date',
          render: function(data, type, row) {
            const today = new Date();
            const renewalDate = new Date(data.split('/').reverse().join('-'));
            if (renewalDate <= today && row.status_label !== 'Vencido') {
                return `<span class="badge bg-warning text-dark">${data}</span>`;
            } else if (row.status_label === 'Vencido') {
                return `<span class="badge bg-danger">${data}</span>`;
            } 
            return data;
          }
        },  
        {
          data: 'days_remaining',
          render: function(data) {
            if (data < 0) {
                return `<span class="badge bg-danger">${data}</span>`;
            }
            if (data <= 30) {
                return `<span class="badge bg-warning text-dark">${data}</span>`;
            }
            return `<span class="badge bg-success">${data}</span>`;
          }
        },
        { data: 'end_date',
          render: function (data) {
            let fecha = new Date(data);
            return fecha.toLocaleString('es-PE', {
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
            });
          }
        },
        {
          data: 'status_badge',
          orderable: false,
          searchable: false
          },
{
  data: 'files',
  orderable: false,
  searchable: false,
  render: function (data) {

    if (!data || data.length === 0) {
      return `<span class="badge bg-secondary">S/A</span>`;
    }

    let html = '';

    data.forEach(function(file) {
      let fileName = file.file_name ?? '';
      let filePath = file.file_path ?? '';

      let ext = fileName.split('.').pop().toLowerCase();
let baseStorageUrl = "{{ asset('storage') }}";
let url = `${baseStorageUrl}/${filePath}`;
      if (ext === 'pdf') {

    html += `
        <a href="${url}" target="_blank" class="badge bg-danger me-1">PDF</a>

        <button
            class="btn btn-outline-danger btn-sm delete-file"
            data-id="${file.id}">
            🗑️
        </button>
    `;

} else {

    html += `
        <a href="${url}" target="_blank" class="badge bg-info me-1">IMG</a>

        <button
            class="btn btn-outline-danger btn-sm delete-file"
            data-id="${file.id}">
            🗑️
        </button>
    `;
}
    });

    return html;
  }
},
{
          data: 'status',
          render: function (data) {
            if(data === 'active') {
              return `<span class="badge bg-success">Activo</span>`;
            } else if (data === 'renewed') {
              return `<span class="badge bg-primary">Renovado</span>`;
            } else if (data === 'not_renewed') {
              return `<span class="badge bg-danger">No Renovado</span>`;
            } else {
              return `<span class="badge bg-secondary">${data}</span>`;
            }
          }        
        },
        {
          data: null,
          orderable: false,
          searchable: false,
          render: function (data, type, row) {
            let editUrl = `{{ url("contracts") }}/${row.id}/edit`;
            let renewButtons = '';
            if (row.status === 'active' && row.days_remaining <= row.renewal_notice_days) {
              renewButtons = `
                <button class="btn btn-success btn-sm btn-renew" data-id="${row.id}" data-currency="${row.currency}"">
                  Renovar
                </button>

                <button class="btn btn-secondary btn-sm btn-not-renew" data-id="${row.id}">
                  No Renovar
                </button>
              `;
            }
            return `
  <div class="d-flex gap-1 flex-wrap">

    <a href="${editUrl}" class="btn btn-warning btn-sm">
      Editar
    </a>

    <a href="/contracts/${row.id}/pdf"
       target="_blank"
       class="btn btn-danger btn-sm">
       PDF
    </a>

    ${renewButtons}

    <button
        class="btn btn-danger btn-sm btn-delete"
        data-id="${row.id}">
        Eliminar
    </button>

  </div>
`;
          }
        }
      ],
      pageLength: 10,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
      }
    });

    // Recargar tabla
    $('#btnRefresh').click(function () {
      table.ajax.reload(null, false);
    });

  $(document).on('click', '.delete-file', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: '¿Eliminar archivo?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({

                url: '/contract-files/' + id,
                type: 'DELETE',

                data: {
                    _token: '{{ csrf_token() }}'
                },

                success: function () {

                    Swal.fire(
                        'Eliminado',
                        'Archivo eliminado correctamente',
                        'success'
                    );

                    table.ajax.reload();
                },

                error: function (xhr) {

                    console.log(xhr.responseText);

                    Swal.fire(
                        'Error',
                        'No se pudo eliminar el archivo',
                        'error'
                    );
                }
            });
        }
    });
});


    // Eliminar contrato
    $(document).on('click', '.btn-delete', function () {
      const id = $(this).data('id');
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `{{ url("contracts") }}/` + id,
            type: 'DELETE',
            data: { 
              _token: '{{ csrf_token() }}' 
            },
            success: function (response) {
              if (response.success) {
                Swal.fire('Eliminado', response.message, 'success');
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            },
            error: function () {
              Swal.fire('Error', 'No se pudo eliminar.', 'error');
            }
          });
        }
      });
    });

    // Renovar contrato - Enviar datos
    $('#renewForm').submit(function(e){
      e.preventDefault();
      const id = $('#renew_contract_id').val();
      $.ajax({
        url: `{{ url('contracts') }}/${id}/renew`,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          start_date: $('#renew_start_date').val(),
          end_date: $('#renew_end_date').val(),
          amount: $('#renew_amount').val(),
          currency: $('#renew_currency').val(),
          description: $('#renew_description').val()
        },
        success: function(response){
          renewModal.hide();
          Swal.fire('Correcto', response.message, 'success');
          table.ajax.reload(null, false);
        }
      });
    });
  });

</script>
@endpush
