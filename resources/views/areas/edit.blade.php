@extends('layouts.app')

@section('title', 'Editar Área')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Editar Área</p>
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
            <a href="{{ route('areas.index') }}" class="btn btn-sm btn-primary mb-3">Volver al Listado</a>
            <form action="{{ route('areas.update', $area->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="mb-3">
                <label for="department_id" class="form-label">Departamento <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id" class="form-select" required></select>
                @error('department_id')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $area->name) }}" required>
                @error('name')
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

      // Departamento seleccionado
      let selectedDepartmentId = '{{ old("department_id", $area->department_id) }}';
      let selectedDepartmentText = '{{ $area->department->name ?? "" }}';
      
      if (selectedDepartmentId) {
        let option = new Option(selectedDepartmentText, selectedDepartmentId, true, true);
        $('#department_id').append(option).trigger('change');
      }
    });
  </script>
@endpush
