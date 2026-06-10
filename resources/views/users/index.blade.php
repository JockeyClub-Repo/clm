@extends('layouts.app')

@section('title', 'Mantenimiento de Usuarios')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Listado de Usuarios</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="javascript: void(0);">Mantenimiento</a></li>
              <li class="breadcrumb-item active">Usuarios</li>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
              <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">Nuevo Registro</a>
              <button class="btn btn-sm btn-success" id="btnRefresh"><i class="ri-refresh-line"></i> Recargar</button>
            </div>
            <div class="table-responsive">
              <table id="users-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                  <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Teléfono</th>
                    <th>Notificaciones</th>
                    <th>Creación</th>
                    <th>Actualización</th>
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
@endsection

@push('scripts')

<script>

$(document).ready(function () {
  let table = $('#users-table').DataTable({
    processing: true,
    ajax: '{{ route("users.data") }}',
      responsive: false,
      scrollX: true,
      autoWidth: false,
        columns: [
          { data: 'name' },
          { data: 'email' },
          { data: 'role',
            render: function (data) {
              if (!data) { return ''; }
              return data.charAt(0) .toUpperCase() + data.slice(1);
            }
          },
          {
            data: 'phone',
            render: function (data) {
              return data ? data : '-';
            }
          },
          { data: 'receive_notifications',
            render: function (data) {
              if (data === true || data === '1' || data === 1) {
                return `<span class="badge bg-success">Si</span>`
              } else {
                return `<span class="badge bg-danger">No</span>`
              }
            }
          },
          { data: 'created_at',
            render: function (data) {
              let fecha = new Date(data);
              return fecha.toLocaleString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              });
            }
          },
          { data: 'updated_at',
            render: function (data) {
              if (!data || data.startsWith('1969')) {
                  return '-';
              }
              let fecha = new Date(data);
              return fecha.toLocaleString('es-PE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
              });
            }
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
              let editUrl = `{{ url("users") }}/${row.id}/edit`;
              return `
                <div class="d-flex gap-1">
                  <a href="${editUrl}" class="btn btn-warning btn-sm">Editar</a>
                  <button class="btn btn-danger btn-sm btn-delete" data-id="${row.id}">Eliminar</button>
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

      $('#btnRefresh').click(function () {
        table.ajax.reload(null, false);
      });


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
              url: `{{ url("users") }}/${id}`,
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
    });

</script>

@endpush
