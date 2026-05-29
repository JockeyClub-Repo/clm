@extends('layouts.app')

@section('title', 'Mantenimiento de Usuarios')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Editar Usuario</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Mantenimiento</li>
              <li class="breadcrumb-item active">Editar</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
      </div>
    @endif
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="container mt-4 mb-4">
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary mb-3">Volver al Listado</a>
              <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                  <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                  <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                  @error('name')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>          
                <div class="mb-3">
                  <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                  <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                  @error('email')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="mb-3">
                  <label for="department_id" class="form-label">Departamento <span class="text-danger">*</span></label>
                  <select id="department_id" class="form-select" required></select>
                </div>
                <div class="mb-3">
                  <label for="area_id" class="form-label">Área <span class="text-danger">*</span></label>
                  <select name="area_id" id="area_id" class="form-select" required></select>
                  @error('area_id')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="mb-3">
                  <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                  <select name="role" id="role" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                    <option value="agent" {{ old('role', $user->role) == 'agent' ? 'selected' : '' }}>Agente</option>
                    <option value="client" {{ old('role', $user->role) == 'client' ? 'selected' : '' }}>Cliente</option>
                  </select>
                  @error('role')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Contraseña (dejar en blanco si no desea cambiarla)</label>
                  <input type="password" name="password" id="password" class="form-control">
                  @error('password')
                    <span class="text-danger small">{{ $message }}</span>
                  @enderror
                </div>
                <div class="text-end">
                  <button type="submit" class="btn btn-sm btn-warning">Actualizar</button>
                </div>
              </form>
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
  $('#department_id').select2({
    placeholder: 'Seleccione un departamento',
      allowClear: true,
      width: '100%',
      ajax: {
        url: '{{ route("departments.data") }}',
        dataType: 'json',
        delay: 250,
        processResults: function (response) {
          return {
            results: response.data.map(function (item) {
              return {
                id: item.id,
                text: item.name
              };
            })
          };
        },
      cache: true
    }
  });

  $('#area_id').select2({
    placeholder: 'Seleccione un área',
    allowClear: true,
    width: '100%'
  });

  let selectedDepartmentId = '{{ old("department_id", $user->area->department->id ?? "") }}';
  let selectedDepartmentText = '{{ $user->area->department->name ?? "" }}';
  let selectedAreaId = '{{ old("area_id", $user->area_id) }}';
  let selectedAreaText = '{{ $user->area->name ?? "" }}';

  if (selectedDepartmentId) {
    let departmentOption = new Option(selectedDepartmentText, selectedDepartmentId, true, true);
    $('#department_id').append(departmentOption).trigger('change');
    loadAreas(selectedDepartmentId, selectedAreaId, selectedAreaText);
  }

  $('#department_id').change(function () {
    let departmentId = $(this).val();
    $('#area_id').empty().trigger('change');
      if (!departmentId) { return; }
      loadAreas(departmentId, null, null
      );
    });

    function loadAreas(departmentId, selectedAreaId = null, selectedAreaText = null) {
      $.ajax({
        url: `{{ url("departments") }}/${departmentId}/areas`,
        type: 'GET',
        success: function (response) {
          let areas = response.data ?? response;
          $('#area_id').empty();
          areas.forEach(function (area) {
            let isSelected = selectedAreaId == area.id;
            let option = new Option(area.name, area.id, isSelected, isSelected);
            $('#area_id').append(option);
          });
          $('#area_id').trigger('change');
        },
        error: function () {
          Swal.fire('Error', 'No se pudieron cargar las áreas.', 'error');
        }
      });
    }
  });
</script>

@endpush
