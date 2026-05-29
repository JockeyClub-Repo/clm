@extends('layouts.app')

@section('title', 'Crear Ticket')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Nuevo Ticket de Soporte</p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Tickets</li>
              <li class="breadcrumb-item active">Nuevo</li>
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
            <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-primary mb-3">Volver al Listado</a>
            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticket-form" class="needs-validation" novalidate>
              @csrf
              <div class="mb-3">
                <label for="subject" class="form-label">¿En qué podemos ayudarte? <span class="text-danger">*</span></label>
                <input type="text" name="subject" id="subject" class="form-control" required placeholder="Ejemplo: No puedo ingresar al sistema">
                @error('subject')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label for="category_id" class="form-label">Selecciona el tema relacionado <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-select" required></select>
                @error('category_id')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Cuéntanos qué ocurrió <span class="text-danger">*</span></label>
                <div class="snow-editor" style="height: 200px;">{!! old('description') !!}</div>
                <input type="hidden" name="description" id="description">
                @error('description')
                  <span class="text-danger small">{{ $message }}</span>
                @enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Adjunta archivos o capturas del problema.</label>
                  <!-- Dropzone -->
                <div class="dropzone">
                  <div class="fallback">
                    <input name="files[]" type="file" multiple />
                  </div>
                  <div class="dz-message needsclick">
                    <div class="mb-3">
                      <i class="display-4 text-muted ri-upload-cloud-2-line"></i>
                    </div>
                    <h4>Arrastra tus archivos aquí o haz clic para subirlos</h4>
                    <h5 class="text-muted">Las capturas o archivos nos ayudarán a entender mejor el inconveniente.</h5>
                  </div>
                </div>
                <!-- Template para previsualización -->
                <div class="dropzone-previews mt-3" id="dropzone-preview">
                  <div class="border rounded" id="dropzone-preview-list">
                    <div class="d-flex p-2">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm bg-light rounded">
                          <img data-dz-thumbnail class="img-fluid rounded d-block" src="#" alt="Preview" />
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <div class="pt-1">
                          <h5 class="fs-14 mb-1" data-dz-name>&nbsp;</h5>
                          <p class="fs-13 text-muted mb-0" data-dz-size></p>
                          <strong class="error text-danger" data-dz-errormessage></strong>
                        </div>
                      </div>
                      <div class="flex-shrink-0 ms-3">
                        <button data-dz-remove class="btn btn-sm btn-danger">Eliminar</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-success">Crear Ticket</button>
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
  $('#category_id').select2({
    placeholder: 'Seleccione una categoría',
    allowClear: true,
    width: '100%',
    ajax: {
      url: "{{ route('categories.data') }}",
      type: 'GET',
      dataType: 'json',
      delay: 250,
       processResults: function (response) {
        return {
            results: response.data.map(category => ({
                id: category.id,
                text: category.name
            }))
        };
      },
      cache: true
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  let arrDocument = [];
  Dropzone.autoDiscover = false;
  let quill;

  const snowEditor = document.querySelector(".snow-editor");
    if (snowEditor) {
      quill = new Quill(snowEditor, {
        theme: "snow",
        modules: {
          toolbar: [
            [{ font: [] }, { size: [] }],
            ["bold", "italic", "underline", "strike"],
            [{ color: [] }, { background: [] }],
            [{ script: "super" }, { script: "sub" }],
            [{ header: [false, 1, 2, 3, 4, 5, 6] }, "blockquote", "code-block", ],
            [{ list: "ordered" }, { list: "bullet" }, { indent: "-1" }, { indent: "+1" },],
            ["direction", { align: [] }],
            ["link", "image", "video"],
            ["clean"],
          ]
        }
      });
    }

    let previewTemplate;
    let dropzonePreviewNode = document.querySelector("#dropzone-preview-list");

    if (dropzonePreviewNode) {
      dropzonePreviewNode.id = "";
      previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
      dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);

      const dropzone = new Dropzone(".dropzone", {
        url: "#",
        autoProcessQueue: false,
        uploadMultiple: true,
        previewsContainer: "#dropzone-preview",
        previewTemplate: previewTemplate,
        paramName: "files[]",
        maxFiles: 5,
        maxFilesize: 2,
        acceptedFiles: ".jpg,.jpeg,.png,.pdf,.doc,.docx"
      });

      dropzone.on("addedfile", function (file) {
        if (file.size > 2 * 1024 * 1024) {
          Swal.fire({ title: "Archivo muy grande", text: "El archivo excede los 2 MB.", icon: "error"});
          dropzone.removeFile(file);
        } else {
          arrDocument.push(file);
        }
      });

      dropzone.on("maxfilesexceeded", function (file) {
        Swal.fire({title: "Límite excedido", text: "Solo se permiten 5 archivos.", icon: "error"});
        dropzone.removeFile(file);
      });

      dropzone.on("removedfile", function (file) {
        const index = arrDocument.indexOf(file);
        if (index > -1) {
          arrDocument.splice(index, 1);
        }
      });
    }

    document.getElementById("ticket-form").addEventListener("submit", async function (e) {
      e.preventDefault();
      const form = document.getElementById("ticket-form");
      // Copiar contenido Quill al hidden
      document.getElementById('description').value = quill.root.innerHTML;
      // Validar descripción vacía
      const descripcionTexto = quill.getText().trim();
      if (descripcionTexto === "") {
        Swal.fire({title: "Campo requerido", text: "La descripción es obligatoria.", icon: "warning"});
        return;
      }

      // Validación HTML5
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        Swal.fire({title: "Campos requeridos", text: "Completa todos los campos obligatorios.", icon: "warning"});
        return;
      }

      // FormData
      const formData = new FormData(form);

      // Archivos
      arrDocument.forEach((file) => {
        formData.append("files[]", file);
      });

      // Loader
      Swal.fire({
        title: 'Enviando ticket...',
        html: `
          <div class="spinner-border text-primary" role="status"></div>
          <br><br>
          Por favor espera mientras se procesa el ticket.
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Accept': 'application/json'
          },
          body: formData
        });

        const data = await response.json();
        Swal.close();

        // VALIDACIONES LARAVEL
        if (!response.ok) {
          let errores = [];
          if (data.errors) {
            Object.values(data.errors).forEach(errorArray => {
              errores.push(...errorArray);
          });
        }

        Swal.fire({
          title: "Error de validación",
          html: errores.join('<br>'),
          icon: "error"
        });
      return;
    }

    // ÉXITO
    Swal.fire({
      title: "Éxito",
      text: "El ticket fue creado correctamente.",
      icon: "success",
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false
    }).then(() => {
      window.location.href = "{{ route('tickets.index') }}";
    });
  } catch (error) {
    Swal.close();
    console.error(error);
    Swal.fire({ title: "Error", text: "Ocurrió un error inesperado.", icon: "error"});
  }
});
});

</script>
@endpush
