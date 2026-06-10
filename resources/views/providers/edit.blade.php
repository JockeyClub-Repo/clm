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
            <a href="{{ route('providers.index') }}" class="btn btn-sm btn-primary mb-3">Volver al Listado</a>
            <form action="{{ route('providers.update', $provider->id) }}" method="POST">
              @csrf
              @method('PUT')
              <div class="mb-3">
                <label for="ruc" class="form-label">Ruc <span class="text-danger">*</span></label>
                <input type="text" name="ruc" id="ruc" class="form-control" value="{{ old('ruc', $provider->ruc) }}" required>
                @error('ruc')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $provider->name) }}" required>
                @error('name')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $provider->description) }}</textarea>
                @error('description')
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
@endpush
