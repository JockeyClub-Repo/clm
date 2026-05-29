@extends('layouts.app')

@section('title', 'Conformidad de Tickets')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Tickets con Cierre/Resolución</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Soporte</li>
              <li class="breadcrumb-item active">Conformidad</li>
            </ol>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-body">
                <div class="table-responsive">
                  <table id="conformidad-table" class="table table-bordered " style="width:100%">
                    <thead>
                      <tr>
                        <th>N°</th>
                        <th>Creador</th>
                        <th>Area</th>
                        <th>Departamento</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Categoría</th>
                        <th>Asignado</th>
                        <th>Creado</th>
                        <th>Ver</th>
                        <th>Acción</th>
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
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function () {
    let table = $('#conformidad-table').DataTable({
      processing: true,
      ajax: '{{ route("tickets.conformidad.data") }}',
      order: [[0, 'desc']],
      columns: [
        // ID
        {
          data: 'id',
        },
        {
          data: 'creator.email'
        },
        {
          data: 'creator.area.name'
        },
        {
          data: 'creator.area.department.name'
        },
        {
          data: 'subject'
        },
        {
          data: 'status',
          render: function (data) {
            return `<span class="badge ${data.color}">${data.name}</span>`;
          }
        },
        { data: 'priority',
          render: function (data) {
            return `<span class="badge ${data.color}">${data.name}</span>`;
          }
        },
        { data: 'category.name', defaultContent: '-' },
        { data: 'agent',
          render: function (data) {
            if (!data) { return `<span class="badge bg-danger">No asignado</span>`; }
            return `<span class="badge bg-light text-dark"> ${data.name} </span>`;
          }
        },
        { data: 'created_at',
          render: function (data) {
            let fecha = new Date(data);
            let fechaTexto = fecha.toLocaleDateString('es-PE');
            let horaTexto = fecha.toLocaleTimeString('es-PE', {
              hour: '2-digit',
              minute: '2-digit'
            });
            return fechaTexto + ' ' + horaTexto;
          }
        },
        { data: null, orderable: false, searchable: false,
          render: function (data, type, row) {
            if (row.status_id == 8) {
              return `<button class="btn btn-sm btn-info disabled">Ver</button>`;
            }
            return `<a href="/helpdesk/public/tickets/${row.id}/detalle" class="btn btn-sm btn-info">Ver</a>`;
          }
        },
        { data: null, orderable: false, searchable: false,
          render: function (data, type, row) {
            if (row.status_id == 6) {
              return `<button class="btn btn-success btn-sm btn-conformidad" data-id="${row.id}"> Conformidad</button>`;
            }
          return `<span class="text-muted">—</span>`;}
        }
      ],
      responsive: true,
      pageLength: 10,
      language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    });

    $(document).on('click', '.btn-conformidad',function () {
      const id = $(this).data('id');
      Swal.fire({
        title: '¿Marcar como resuelto?',
        text: 'Este ticket se marcará como resuelto.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        confirmButtonColor: '#14A44D',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post(
            `{{ url('tickets') }}/${id}/marcar-resuelto`, { _token: '{{ csrf_token() }}' },
            function (response) {
              if (response.success) {
                Swal.fire('¡Actualizado!', response.message, 'success');
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Error', response.message, 'error'); 
              }
            }
          );
        }
      });
    }
  );
});
</script>
@endpush
