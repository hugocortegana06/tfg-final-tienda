
{{-- resources/views/deposits/index.blade.php --}}
@extends('layouts.app')

@section('title','Lista de Depósitos')

@section('content')
<div class="container">
    <h1 class="mb-4">Depósitos</h1>

    {{-- Top bar: per-page + búsqueda + añadir --}}
    <div class="row mb-3 align-items-center">
        <div class="col-auto d-flex align-items-center">
            <label class="me-2 mb-0">Mostrar</label>
            <select id="perPageSelect" class="form-select form-select-sm me-3">
                @foreach([5,10,15,20] as $n)
                    <option value="{{ $n }}" {{ ($perPage == $n) ? 'selected' : '' }}>
                        {{ $n }}
                    </option>
                @endforeach
            </select>

            <input
              type="text"
              id="searchInput"
              class="form-control form-control-sm"
              placeholder="Buscar por cliente..."
              value="{{ $search ?? '' }}"
              style="max-width:200px;"
            >
        </div>
        <div class="col text-end">
            <a href="{{ route('deposits.create') }}" class="btn btn-sm btn-primary">
               Añadir depósito
            </a>
        </div>
    </div>

    {{-- Mensajes --}}
    <div id="updateMessage" class="alert mb-3" style="display:none;"></div>
    @if(session('success'))
      <div id="serverSuccess" class="alert alert-success mb-3">
        {{ session('success') }}
      </div>
    @endif

    {{-- Tabla y paginación recargable vía AJAX --}}
    <div id="tableContainer">
      @include('deposits.partials.table', [
        'deposits' => $deposits,
        'perPage'  => $perPage,
        'search'   => $search
      ])
    </div>
</div>

{{-- Modal Confirmar Eliminación --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Confirmar Eliminación</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">¿Seguro que deseas eliminar este depósito?</div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      <button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar</button>
    </div>
  </div></div>
</div>

{{-- Modal + Información --}}
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Detalles del Depósito</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <dl class="row" id="infoDetails"></dl>
      <div class="d-flex justify-content-center my-3">
        <canvas id="patternCanvas" width="150" height="150" style="display:none;"></canvas>
      </div>
    </div>
    <div class="modal-footer">
      <a id="btnGenerateLabel" class="btn btn-sm btn-success" href="#" target="_blank">
        Generar etiqueta
      </a>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
    </div>
  </div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Helper flash
  const flash = (msg, type = 'info') => {
    const box = document.getElementById('updateMessage');
    box.className = 'alert mb-3 alert-' + type;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(() => box.style.display = 'none', 3000);
  };
  // Hide server success
  const svr = document.getElementById('serverSuccess');
  if (svr) setTimeout(() => svr.style.display = 'none', 3000);

  // Delete confirmation
  let curForm = null;
  document.addEventListener('click', e => {
    if (e.target.matches('.btn-delete')) {
      curForm = e.target.closest('form');
      new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
    }
  });
  document.getElementById('btnConfirmDelete')
    .addEventListener('click', () => curForm && curForm.submit());

  // Inline‐edit status
  function attachInlineEdit() {
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.onclick = () => {
      const row       = btn.closest('tr'),
            id        = row.dataset.id,
            tdStatus  = row.querySelector('.dep-status'),
            tdNotes   = row.querySelector('.work-notes'),
            oldStatus = tdStatus.textContent.trim(),
            oldNotes  = tdNotes.textContent.trim();

      // 1) Convertimos el estado en un <select>
      tdStatus.innerHTML = `
        <select class="form-select form-select-sm">
          <option ${oldStatus==='En curso'    ?'selected':''}>En curso</option>
          <option ${oldStatus==='Electrónico' ?'selected':''}>Electrónico</option>
          <option ${oldStatus==='Finalizado'  ?'selected':''}>Finalizado</option>
          <option ${oldStatus==='Entregado'   ?'selected':''}>Entregado</option>
        </select>`;

      // 2) Convertimos la nota en un <input>, vacío si antes era 'N/A'
      tdNotes.innerHTML = `
        <input
          type="text"
          class="form-control form-control-sm"
          value="${oldNotes==='N/A' ? '' : oldNotes}"
          placeholder="Notas de trabajo"
        >`;

      // 3) Cambiamos el botón a 'Guardar' y añadimos 'Cancelar'
      btn.textContent = 'Guardar';
      btn.classList.replace('btn-info','btn-success');
      const cancel = document.createElement('button');
      cancel.type = 'button';
      cancel.textContent = 'Cancelar';
      cancel.className = 'btn btn-sm btn-secondary ms-2';
      btn.after(cancel);

      // 4) Hijack del click para guardar
      btn.onclick    = save;
      cancel.onclick = () => {
        // si cancelas, restauras lo anterior
        tdStatus.textContent = oldStatus;
        tdNotes.textContent  = oldNotes;
        teardown();
      };

      function teardown() {
        btn.textContent = 'Editar';
        btn.classList.replace('btn-success','btn-info');
        cancel.remove();
        // vuelves a enganchar a todos los Editar
        attachInlineEdit();
      }

      function save() {
        const newStatus = tdStatus.querySelector('select').value,
              newNotes  = tdNotes.querySelector('input').value.trim();

        fetch(`/deposits/${id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
          },
          body: JSON.stringify({
            status:     newStatus,
            work_notes: newNotes || null   // si queda vacío, mandamos null
          })
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          // reflejas los nuevos valores
          tdStatus.textContent = newStatus;
          tdNotes.textContent  = newNotes || 'N/A';
          teardown();
          flash(data.message,'success');
        })
        .catch(() => {
          // en caso de error, restauras original
          tdStatus.textContent = oldStatus;
          tdNotes.textContent  = oldNotes;
          teardown();
          flash('Error al actualizar','danger');
        });
      }
    };
  });
}


  // “+ Información” button
  document.addEventListener('click', e => {
    if (!e.target.matches('.btn-info-detail')) return;
    const row    = e.target.closest('tr'),
          info   = JSON.parse(row.getAttribute('data-details')),
          dl     = document.getElementById('infoDetails'),
          canvas = document.getElementById('patternCanvas'),
          ctx    = canvas.getContext('2d');

    // Detalles
    dl.innerHTML = `
      <dt class="col-sm-3">ID</dt><dd class="col-sm-9">${info.id}</dd>
      <dt class="col-sm-3">Cliente</dt><dd class="col-sm-9">${info.client}</dd>
      <dt class="col-sm-3">Dispositivo</dt><dd class="col-sm-9">${info.dispositivo}</dd>
      <dt class="col-sm-3">N.º Serie</dt><dd class="col-sm-9">${info.serial_number}</dd>
      <dt class="col-sm-3">Problema</dt><dd class="col-sm-9">${info.problem_description}</dd>
      <dt class="col-sm-3">Info Adic.</dt><dd class="col-sm-9">${info.more_info ?? 'N/A'}</dd>
      <dt class="col-sm-3">Patrón</dt><dd class="col-sm-9">${info.unlock_password ? info.unlock_password : 'N/A'}</dd>
      <dt class="col-sm-3">Pin o contraseña</dt><dd class="col-sm-9">${info.pin_or_password ?? 'N/A'}</dd>
      <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">${info.status}</dd>
      <dt class="col-sm-3">Garantía</dt>
      <dd class="col-sm-9 text-danger">${info.under_warranty ? 'Sí' : 'No'}</dd>
      <dt class="col-sm-3">Fecha Entrada</dt><dd class="col-sm-9">${info.date_in}</dd>
      <dt class="col-sm-3">Fecha Salida</dt><dd class="col-sm-9">${info.date_out ?? 'N/A'}</dd>
      <dt class="col-sm-3">Creado por</dt><dd class="col-sm-9">${info.creator ?? 'N/A'}</dd>
      <dt class="col-sm-3">Última modif.</dt><dd class="col-sm-9">${info.last_modifier ?? 'N/A'}</dd>
      
      <!-- NUEVO: Presupuesto -->
      <dt class="col-sm-3">Presupuesto</dt>
      <dd class="col-sm-9">
        ${info.budget !== null ? info.budget.toFixed(2) + ' €' : 'N/A'}
      </dd>
    `;

    // Dibuja patrón si existe, resaltando inicio en verde y mostrando índice
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (info.unlock_password) {
      const seq = info.unlock_password.split('').map(n => parseInt(n,10));
      const margin  = 20;
      const spacing = (canvas.width - 2*margin) / 2;
      const startColor = '#198754'; // verde Bootstrap
      const dotColor   = '#0d6efd'; // azul

      // Configuración texto
      ctx.font = '10px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';

      // Dibujar nodos y etiquetas
      for (let i = 1; i <= 9; i++) {
        const idx  = i - 1,
              rowI = Math.floor(idx/3),
              colI = idx % 3,
              x    = margin + colI * spacing,
              y    = margin + rowI * spacing;
        // Nodo
        ctx.beginPath();
        ctx.arc(x, y, 8, 0, 2*Math.PI);
        if (seq.includes(i)) {
          ctx.fillStyle = (i === seq[0]) ? startColor : dotColor;
        } else {
          ctx.fillStyle = '#ccc';
        }
        ctx.fill();
        // Índice debajo
        ctx.fillStyle = '#000';
        ctx.fillText(i, x, y + 10);
      }

      // Dibujar líneas en verde
      ctx.strokeStyle = startColor;
      ctx.lineWidth = 4;
      ctx.beginPath();
      seq.forEach((n, i) => {
        const idx  = n - 1,
              r    = Math.floor(idx/3),
              c    = idx % 3,
              x    = margin + c * spacing,
              y    = margin + r * spacing;
        if (i === 0) ctx.moveTo(x, y);
        else         ctx.lineTo(x, y);
      });
      ctx.stroke();
      canvas.style.display = 'block';
    } else {
      canvas.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('infoModal')).show();
    document.getElementById('btnGenerateLabel').href = `/deposits/${info.id}/label`;
  });

  // AJAX live-search + per-page reload
  const perPage   = document.getElementById('perPageSelect'),
        search    = document.getElementById('searchInput'),
        container = document.getElementById('tableContainer');
  let timer;
  function reload() {
    const params = new URLSearchParams({
      per_page: perPage.value,
      search:   search.value
    });
    fetch(`{{ url('deposits') }}?${params}`, {
      headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.text())
    .then(html => {
      container.innerHTML = html;
      attachInlineEdit();
    });
  }
  perPage.onchange = reload;
  search.oninput   = () => {
    clearTimeout(timer);
    timer = setTimeout(reload, 300);
  };

  // Iniciar inline-edit
  attachInlineEdit();
});
</script>
@endpush

