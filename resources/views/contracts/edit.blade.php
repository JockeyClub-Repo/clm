@extends('layouts.app')

@section('title', 'Editar Área')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Editar Contrato</p>
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
            <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-primary mb-3">Volver al Listado</a>
<form action="{{ route('contracts.update', $contract->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
              @method('PUT')
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $contract->name) }}" required>
                    @error('name')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="provider_id" class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <select id="provider_id" name="provider_id" class="form-select" required>
                        <option value="{{ $contract->provider->id }}" selected>
                            {{ $contract->provider->name }}
                        </option>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="start_date" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')  
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="end_date" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $contract->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>    
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" value="{{ old('amount', $contract->amount) }}" required>
                    @error('amount')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>  
                  <div class="col-md-6 mb-3">
                    <label for="currency" class="form-label">Moneda <span class="text-danger">*</span></label>
                    <select name="currency" id="currency" class="form-select" required>
                      <option value="">Seleccione una moneda</option>
                      <option value="USD" {{ old('currency', $contract->currency) == 'USD' ? 'selected' : '' }}>USD</option>
                      <option value="PEN" {{ old('currency', $contract->currency) == 'PEN' ? 'selected' : '' }}>PEN</option>
                    </select>
                    @error('currency')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="renewal_notice_days" class="form-label">Avisar desde (días antes) <span class="text-danger">*</span></label>
                    <input type="number" name="renewal_notice_days" id="renewal_notice_days" class="form-control" value="{{ old('renewal_notice_days', $contract->renewal_notice_days) }}" required>
                    @error('renewal_notice_days')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="auto_renew" class="form-label">Autorenovar <span class="text-danger">*</span></label>
                    <select name="auto_renew" id="auto_renew" class="form-select" required>
                      <option value="1" {{ old('auto_renew', $contract->auto_renew) == '1' ? 'selected' : '' }}>Si</option>
                      <option value="0" {{ old('auto_renew', $contract->auto_renew) == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('auto_renew')
                      <span class="text-danger small">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
                <div class="mb-3">
  <label for="description" class="form-label">Descripción</label>
  <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $contract->description) }}</textarea>
  @error('description')
    <span class="text-danger small">{{ $message }}</span>
  @enderror
</div>

<div class="mb-3">
  <label for="archivo" class="form-label">PDF / Imagen</label>

  <input
    type="file"
    name="archivo"
    id="archivo"
    class="form-control"
    accept=".pdf,.jpg,.jpeg,.png">

  @error('archivo')
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
    $('#provider_id').select2({
        placeholder: 'Seleccione un proveedor',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '{{ route("providers.data") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { term: params.term };
            },
            processResults: function (response) {
                return {
                    results: response.data.map(function (item) {
                        return {
                            id: item.id,
                            text: item.name
                        };
                    })
                };
            }
        }
    });
});
  </script>

@endpush
