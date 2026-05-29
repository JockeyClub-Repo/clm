@extends('layouts.app')

@section('title', 'Detalle del Ticket')

@section('content')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <p class="mb-sm-0">Detalle del Ticket N° <span id="ticket-id"></span></p>
          <div class="page-title-right">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item">Soporte</li>
              <li class="breadcrumb-item active">Detalle</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 mb-3">
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalLogs">
            <i class="fas fa-eye"></i> Ver Logs
          </button>
          <a id="btn-pdf" href="" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print"></i>   Imprimir
          </a>
        </div>
      </div>
      <br/>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Información del Ticket</p>
            <p><strong>Asunto:</strong><span id="ticket-subject"></span></p>
            <p><strong>Estado:</strong> <span id="ticket-status"></span></p>
            <p><strong>Prioridad:</strong> <span id="ticket-priority"></span></p>
            <p><strong>Categoría:</strong><span id="ticket-category"></span></p>
            <p><strong>Asignado a:</strong><span id="ticket-agent"></span></p>
            <p><strong>Creado por:</strong><span id="ticket-creator"></span></p>
            <p><strong>Departamento:</strong> <span class="badge bg-info"><span id="ticket-department"></span></span></p>
            <p><strong>Area:</strong> <span class="badge bg-info"><span id="ticket-area"></span></span></p>
            <p><strong>Fecha:</strong><span id="ticket-date"></span></p>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Descripción del Ticket</p>
            <div class="quill-description" id="ticket-description"></div>
          </div>
        </div>
      </div>
      </div>
      <div id="ticket-attachments"></div>
      {{-- 💬 Comentarios (Mensajes del ticket) en formato Timeline --}}
      <div class="row">
        <div class="col-lg-12">
          <p class="card-title">Historial de Mensajes</p>
          <div class="timeline" id="ticket-messages"></div>
        </div>
      </div>
      {{-- 📨 Formulario para responder el ticket --}}
      <div class="card mt-4">
        <div class="card-body" id="ticket-response-container"></div>
      </div>
    </div>
  </div>

  {{-- MODAL LOGS --}}
  <div class="modal fade" id="modalLogs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <p class="modal-title">Historial de Logs del Ticket</p>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <ul class="list-group" id="ticket-logs"></ul>
        </div>
      </div>
    </div>
</div>
 
@endsection

@push('scripts')
 <script>
    const ticketId = @json($ticketId);
    
    document.addEventListener('DOMContentLoaded', function () {
      Dropzone.autoDiscover = false;
      loadTicket();
    });

    let quill = null;
    let arrDocument = [];
    async function loadTicket() {
      try {
        const response = await fetch(`{{ url('/tickets') }}/${ticketId}/data`);
        const data = await response.json();
        if (!response.ok) { throw new Error(data.message); }
        renderTicket(data);
      } catch (error) {
        console.error(error);
        Swal.fire('Error', 'No se pudo cargar el ticket.', 'error');
      }
    }

    function renderTicket(ticket) {
      document.getElementById('ticket-id').textContent = ticket.id;
      document.getElementById('ticket-subject').textContent = ticket.subject;
      const statusBadge = document.getElementById('ticket-status');
      statusBadge.textContent = ticket.status.name;
      statusBadge.className = `badge ${ticket.status.color}`;
      const priorityBadge = document.getElementById('ticket-priority');
      priorityBadge.textContent = ticket.priority.name;
      priorityBadge.className = `badge ${ticket.priority.color}`;
      document.getElementById('ticket-category').textContent = ticket.category.name;
      document.getElementById('ticket-agent').textContent = ticket.agent ? ticket.agent.name : 'No asignado';
      document.getElementById('ticket-creator').textContent = ticket.creator.name;
      document.getElementById('ticket-department').textContent = ticket.creator.area.department.name;
      document.getElementById('ticket-area').textContent = ticket.creator.area.name;
      document.getElementById('ticket-date').textContent = ticket.created_at;
      document.getElementById('ticket-description').innerHTML = ticket.description;
      document.getElementById('btn-pdf').href = `{{ url('/tickets') }}/${ticket.id}/pdf`;
      renderAttachments(ticket.attachments);
      renderMessages(ticket.messages);
      renderLogs(ticket.logs);
      renderResponseSection(ticket);
      // 🔥 IMPORTANTE
      bindTicketEvents(ticket);
    }

    function renderAttachments(files) {
      const container = document.getElementById('ticket-attachments');
      if (!files.length) {
        container.innerHTML = '';
        return;
      }

      let html =  `<div class="card">
                    <div class="card-body">
                      <p class="card-title"> Adjuntos</p>
                      <ul>
                  `;

                      files.forEach(file => {
                        html += `
                          <li>
                            <a href="{{ asset('storage') }}/${file.file_path}" target="_blank">
                              ${file.file_path.split('/').pop()}
                            </a>
                          </li>
                        `;
                      });

            html += ` </ul>
                    </div>
                  </div>`;

    container.innerHTML = html;
    }

    function renderMessages(messages) {
      const container = document.getElementById('ticket-messages');
      if (!messages.length) {
        container.innerHTML = '<p>No hay mensajes aún.</p>';
        return;
      }
      let html = '';
      messages.forEach(message => {
      const isAgent = ['agent', 'admin'].includes(message.user.role);
      const position = isAgent ? 'right' : 'left';
      const icon = isAgent ? 'ri-user-star-line' : 'ri-user-line';
      const avatar = message.user.avatar ?? '/assets/images/users/user-dummy-img.jpg';
      let attachmentsHtml = '';
      if (message.attachments && message.attachments.length > 0) {
        attachmentsHtml += `
          <div class="mt-3">
            <p class="mb-2 fw-semibold">
              Adjuntos
            </p>
        `;
        message.attachments.forEach(file => {
          const filename = file.file_path.split('/').pop();
          attachmentsHtml += `
            <div class="mb-1">
              <a
                href="{{ asset('storage') }}/${file.file_path}"
                target="_blank"
                class="btn btn-sm btn-light"
              >
                <i class="ri-attachment-2"></i>
                ${filename}
              </a>
            </div>
          `;
        });

        attachmentsHtml += `</div>`;
      }

      html += `
        <div class="timeline-item ${position}">
          <i class="icon ${icon}"></i>
          <div class="date">${message.created_at}</div>
          <div class="content">
          <div class="d-flex">
            <div class="flex-shrink-0">
              <img src="${avatar}" class="avatar-sm rounded">
            </div>
            <div class="flex-grow-1 ms-3">
              <p class="fs-5">@${message.user.name}</p>
              <div class="text-muted mb-2">${message.message}</div>
              ${attachmentsHtml}
            </div>
          </div>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
}

    function renderLogs(logs) {
      const container = document.getElementById('ticket-logs');
      let html = '';
      logs.forEach(log => {
        html += `<li class="list-group-item">
                    <strong>${log.created_at ?? '—'}</strong>
                      : ${log.action}
                    <br>
                    <small class="text-muted">
                      Por: ${log.user?.name ?? 'Sistema'}
                    </small>
                  </li>`;
      });
      container.innerHTML = html;
    }

    function renderResponseSection(ticket) {
      const container = document.getElementById('ticket-response-container');
      if ([5, 6].includes(ticket.status_id)) {
        let html = `
          <div class="alert alert-warning">Este ticket está cerrado y/o resuelto.</div>
        `;
        if (ticket.status_id === 6) {
          html += `<button type="button" class="btn btn-warning" id="btnReabrirTicket">Reabrir Ticket</button>`;
        }

        container.innerHTML = html;
        return;
      }
      container.innerHTML = `
        <h5 class="card-title">Responder Ticket</h5>
        <form id="response-form" enctype="multipart/form-data">
          <input type="hidden" name="ticket_id" value="${ticket.id}">
          <input type="hidden" name="message" id="message">
          <div class="mb-3">
            <label class="form-label">Mensaje</label>
            <div class="snow-editor" style="height:200px;"></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Adjuntar Archivos</label>
            <div class="dropzone"></div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              Enviar Respuesta
            </button>
            <button type="button" class="btn ${ticket.status_id == 9 ? 'btn-success' : 'btn-info'}" id="btnPausarTicket" data-paused="${ticket.status_id == 9 ? 1 : 0}">
              ${ ticket.status_id == 9  ? 'Reanudar Ticket' : 'Pausar Ticket' }
            </button>
            <button type="button" class="btn btn-danger" id="btnCerrarTicket">
              Cerrar Ticket
            </button>
          </div>
        </form> `;

      initQuill();
      initDropzone();
      initResponseForm();
    }

    function initQuill() {
      const quillEl = document.querySelector('.snow-editor');
      if (!quillEl) return;
      quill = new Quill(quillEl, {
        theme: 'snow'
      });
    }

    function initDropzone() {
      arrDocument = [];
      const dropzoneEl = document.querySelector('.dropzone');
      if (!dropzoneEl) return;
      new Dropzone(dropzoneEl, {
        url: '#',
        autoProcessQueue: false,
        uploadMultiple: true,
        paramName: 'files[]',
        maxFilesize: 2,
        acceptedFiles:'.jpg,.jpeg,.png,.pdf,.doc,.docx',
        addRemoveLinks: true,
        init: function () {
          this.on('addedfile', file => {
            arrDocument.push(file);
          });
          this.on('removedfile', file => {
            arrDocument = arrDocument.filter(x => x !== file);
          });
        }
      });
    }

    function initResponseForm() {
      const form = document.getElementById('response-form');
      if (!form) return;
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        document.getElementById('message').value =quill.root.innerHTML;
        const formData = new FormData(form);
        arrDocument.forEach(file => {
          formData.append('files[]', file);
        });
        Swal.fire({
          title: 'Enviando...',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => Swal.showLoading()
        });
        try {
          const response = await fetch("{{ route('tickets.responder') }}",
            {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
              body: formData
            }
          );
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message);
          }
          Swal.close();
          await Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: data.message
          });
          await loadTicket();
        } catch (error) {
          Swal.close();
          Swal.fire('Error', error.message, 'error');
        }
      });
    }

    function bindTicketEvents(ticket) {
      document.getElementById('btnCerrarTicket')?.addEventListener('click', async function () {
        const result = await Swal.fire({
          title: '¿Cerrar ticket?',
          text: 'No podrás responder más, hasta que sea reabierto.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, cerrar'
        });
        if (!result.isConfirmed) return;
          try {
            const response = await fetch(`{{ url('/tickets') }}/${ticket.id}/cerrar`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          });
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message);
          }
          Swal.fire('Éxito', data.message, 'success');
          loadTicket();
        } catch (error) {
          Swal.fire('Error', error.message, 'error');
        }
      });

      document.getElementById('btnPausarTicket')?.addEventListener('click', async function () {
        const isPaused = this.dataset.paused === "1";
        const route = isPaused ? `{{ url('/tickets') }}/${ticket.id}/reanudar` : `{{ url('/tickets') }}/${ticket.id}/pausar`;
        try {
          const response = await fetch(route, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          });
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message);
          }
          Swal.fire('Éxito', data.message, 'success');
          loadTicket();
        } catch (error) {
          Swal.fire('Error', error.message, 'error');
        }
      });

      document.getElementById('btnReabrirTicket')?.addEventListener('click', async function () {
        const result = await Swal.fire({
          title: '¿Reabrir ticket?',
          text: 'El ticket volverá a estar activo.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Sí, reabrir'
        });
        if (!result.isConfirmed) return;
        try {
          const response = await fetch(`{{ url('/tickets') }}/${ticket.id}/reabrir`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            }
          });
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message);
          }
          Swal.fire('Éxito', data.message, 'success');
          loadTicket();
        } catch (error) {
          Swal.fire('Error', error.message, 'error');
        }
      });


    }

  </script>
@endpush

