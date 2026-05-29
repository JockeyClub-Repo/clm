@extends('layouts.app')

@section('title', 'Gestión de Tickets')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Gestión de Tickets</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Soporte</li>
              <li class="breadcrumb-item active">Gestionar Tickets</li>
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
            <div class="table-responsive">
              <table id="gestion-tickets-table" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
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
                    <th>Asignado a</th>
                    <th>Fecha de creación</th>
                    <th>Acción</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- Modal de Gestión --}}
    <div class="modal fade" id="modalGestionar" tabindex="-1" aria-labelledby="modalGestionarLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalGestionarContent">
        <!-- Aquí se insertará el contenido vía AJAX -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
  const table = $('#gestion-tickets-table').DataTable({
    processing: true,
    responsive: true,
    ajax: {
      url: "{{ route('tickets.gestion.data') }}",
      dataSrc: 'data'
    },
    order: [[0, 'desc']],
    columns: [
      {
        data: 'id',
      },
      {
        data: 'creator.email',
      },
      {
        data: 'creator.area.name',
      },
      {
        data: 'creator.area.department.name',
      },
      {          
        data: 'subject',
      },
      {
        data: 'status',
        render: function (data) {
          if (!data) { return '-'; }
          return `<span class="badge ${data.color}">${data.name}</span>`;
        }
      },
      {
        data: 'priority',
        render: function (data) {
          if (!data) { return '-'; }
          return `<span class="badge ${data.color}">${data.name}</span>`;
        }
      },
      {
        data: 'category',
        render: function (data) {
          return data ? data.name : '-';
        }
      },
      {
        data: 'agent',
        render: function (data) {
          if (data) {
            return `<span class="badge bg-light text-dark">${data.name}</span>`;
          }
          return `<span class="badge bg-danger">No asignado</span>`;
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
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data) {
          let botones = '';
          if (data.status_id == 8) {
            botones += `<button class="btn btn-sm btn-info disabled">Ver</button>`;
          } else {
            botones += `<a href="{{ url('tickets') }}/${data.id}/detalle" class="btn btn-sm btn-info">Ver</a>`;
          }
          if (data.status_id == 8 || data.status_id == 5) {
            botones += `<button class="btn btn-sm btn-primary disabled ms-1">Gestionar</button>`;
          } else {
            botones += `<button class="btn btn-sm btn-primary btn-gestionar ms-1" data-id="${data.id}">Gestionar</button>`;
          }
          if (data.status_id == 1 && data.assigned_to == null) {
            botones += `<button class="btn btn-sm btn-danger btn-cancelar-ticket ms-1" data-id="${data.id}" title="Cancelar Ticket"><i class="ri-close-circle-line"></i></button>`;
          }
          return botones;
        }
      }
    ],
    language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    }
  });

  $(document).on('click', '.btn-gestionar', function () {
    const ticketId = $(this).data('id');
    $.ajax({
      url: `{{ url('tickets/gestion/data') }}/${ticketId}`,
      type: 'GET',

        success: function (response) {

            const ticket = response.ticket;

            let archivosHtml = '';

            if (ticket.attachments.length > 0) {

                archivosHtml += `
                    <hr>
                    <h6>Archivos Adjuntos:</h6>
                    <ul>
                `;

                ticket.attachments.forEach(file => {

                    archivosHtml += `
                        <li>
                            <a href="/storage/${file.file_path}" target="_blank">
                                ${file.file_path}
                            </a>
                        </li>
                    `;
                });

                archivosHtml += `</ul>`;
            }

            const modalHtml = `

                <div class="modal-header">
                    <h5 class="modal-title">
                        Gestionar Ticket N° ${ticket.id}
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <form id="formGestionTicket">

                    <input
                        type="hidden"
                        name="ticket_id"
                        value="${ticket.id}">

                    <div class="modal-body">

                        <p>
                            <strong>Asunto:</strong>
                            ${ticket.subject}
                        </p>

                        <p>
                            <strong>Descripción:</strong>
                            ${ticket.description}
                        </p>

                        <p>
                            <strong>Categoría:</strong>
                            ${ticket.category?.name ?? '-'}
                        </p>

                        <p>
                            <strong>Estado:</strong>
                            ${ticket.status?.name ?? '-'}
                        </p>

                        <div class="mb-3">

                            <label class="form-label">
                                Asignar a:
                            </label>

                            <select
                                name="assigned_to"
                                id="assigned_to"
                                class="form-select select2-modal">
                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Prioridad:
                            </label>

                            <select
                                name="priority_id"
                                id="priority_id"
                                class="form-select select2-modal">
                            </select>

                        </div>

                        ${archivosHtml}

                    </div>

                    <div class="modal-footer">

                        <button
                            type="submit"
                            class="btn btn-primary">

                            Guardar Cambios

                        </button>

                    </div>

                </form>
            `;

            $('#modalGestionarContent').html(modalHtml);

            $('#modalGestionar').modal('show');

            /*
            |--------------------------------------------------------------------------
            | Select2 Agentes/Admins
            |--------------------------------------------------------------------------
            */

            $('#assigned_to').select2({

                dropdownParent: $('#modalGestionar'),

                width: '100%',

                placeholder: '-- Seleccionar --',

                ajax: {

                    url: "{{ route('tickets.agentes.data') }}",

                    dataType: 'json',

                    delay: 250,

                    data: function (params) {

                        return {
                            search: params.term
                        };
                    },

                    processResults: function (data) {

                        return {
                            results: data.results
                        };
                    },

                    cache: true
                }
            });

            /*
            |--------------------------------------------------------------------------
            | Valor inicial agente
            |--------------------------------------------------------------------------
            */

            if (ticket.agent) {

                const option = new Option(
                    ticket.agent.name,
                    ticket.agent.id,
                    true,
                    true
                );

                $('#assigned_to')
                    .append(option)
                    .trigger('change');
            }

            /*
            |--------------------------------------------------------------------------
            | Select2 Prioridades
            |--------------------------------------------------------------------------
            */

            $('#priority_id').select2({

                dropdownParent: $('#modalGestionar'),

                width: '100%',

                placeholder: '-- Seleccionar --',

                ajax: {

                    url: "{{ route('tickets.prioridades.data') }}",

                    dataType: 'json',

                    delay: 250,

                    data: function (params) {

                        return {
                            search: params.term
                        };
                    },

                    processResults: function (data) {

                        return {
                            results: data.results
                        };
                    },

                    cache: true
                }
            });

            /*
            |--------------------------------------------------------------------------
            | Valor inicial prioridad
            |--------------------------------------------------------------------------
            */

            if (ticket.priority) {

                const optionPriority = new Option(
                    ticket.priority.name,
                    ticket.priority.id,
                    true,
                    true
                );

                $('#priority_id')
                    .append(optionPriority)
                    .trigger('change');
            }
        },

        error: function () {

            Swal.fire(
                'Error',
                'No se pudo cargar la información del ticket.',
                'error'
            );
        }
    });
});

  $(document).on('submit', '#formGestionTicket', function (e) {
    e.preventDefault();
    const id = $(this).find('input[name="ticket_id"]').val();
      Swal.fire({
        title: 'Guardando cambios...',
        html: `
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <br><br>
          Por favor espera mientras se actualiza el ticket.
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      $.ajax({
        url: `{{ url('tickets/actualizar') }}/${id}`,
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        data: $(this).serialize(),
        success: function (response) {
            Swal.close();
          if (response.success) {
            $('#modalGestionar').modal('hide');
            Swal.fire('Éxito', response.message,'success');
              table.ajax.reload(null, false);
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function () {
          Swal.close();
          Swal.fire('Error', 'No se pudo actualizar el ticket ya que cambió de estado.', 'error');
        }
      });
    });

  $(document).on('click', '.btn-cancelar-ticket', function () {
      const ticketId = $(this).data('id');
      Swal.fire({
        title: '¿Cancelar este ticket?',
        text: 'Esta acción marcará el ticket como cancelado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'No',
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: 'Guardando cambios...',
            html: `
              <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
              </div>
              <br><br>
              Por favor espera mientras se actualiza el ticket.
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
          });
          fetch(`{{ url('tickets') }}/${ticketId}/cancelar`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
          }).then(res => res.json()).then(data => {
            Swal.close();
            if (data.success) {
              Swal.fire('Cancelado', data.message, 'success');
              table.ajax.reload(null, false);
            } else {
              Swal.fire('Error', data.message, 'error');
            }
          }).catch(() => {
            Swal.close();
            Swal.fire('Error', 'No se pudo cancelar el ticket.', 'error');
          });
        }
    });
  });
});
</script>
@endpush
