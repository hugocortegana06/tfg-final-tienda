{{-- resources/views/deposits/entregados.blade.php --}}
@extends('layouts.app')

@section('title','Histórico de Entregas')

@section('content')
<div class="container">
  <h1 class="mb-4">Histórico de Entregas</h1>

  {{-- Top bar: per-page + búsqueda + añadir --}}
  <div class="row mb-3 align-items-center">
    <div class="col-auto d-flex align-items-center">
      <label class="me-2 mb-0">Mostrar</label>
      <select id="perPageSelect" class="form-select form-select-sm me-3">
        @foreach([5,10,15,20] as $n)
          <option value="{{ $n }}" {{ ($perPage==$n)?'selected':'' }}>{{ $n }}</option>
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

  {{-- Mensajes flash --}}
  <div id="updateMessage" class="alert mb-3" style="display:none;"></div>
  @if(session('success'))
    <div id="serverSuccess" class="alert alert-success mb-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Contenedor de la tabla (se recarga vía AJAX) --}}
  <div id="tableContainer">
    @include('deposits.partials.table_entregados', ['deposits' => $deposits])
  </div>
</div>

{{-- Modal de confirmación de eliminación --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Confirmar Eliminación</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">¿Deseas eliminar este depósito?</div>
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
  const flash = (msg, type='info') => {
    const box = document.getElementById('updateMessage');
    box.className = 'alert mb-3 alert-' + type;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(() => box.style.display = 'none', 3000);
  };
  // Hide server success
  const svr = document.getElementById('serverSuccess');
  if (svr) setTimeout(() => svr.style.display = 'none', 3000);

  // Globals
  const perPage   = document.getElementById('perPageSelect'),
        search    = document.getElementById('searchInput'),
        container = document.getElementById('tableContainer');
  let timer, currentPage = 1;

  // Reload partial table
  function reloadTable() {
    const params = new URLSearchParams({
      per_page: perPage.value,
      search:   search.value,
      page:     currentPage
    });
    fetch(`{{ route('deposits.entregados.partial') }}?${params}`, {
      headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
      .then(r => r.text())
      .then(html => {
        container.innerHTML = html;
        attachModalDelete();
        attachInlineEdit();
        attachInfoDetail();
      })
      .catch(console.error);
  }

  // Event bindings for per-page, search, pagination links
  perPage.onchange = () => { currentPage = 1; reloadTable(); };
  search.oninput   = () => {
    clearTimeout(timer);
    timer = setTimeout(() => { currentPage = 1; reloadTable(); }, 300);
  };
  container.addEventListener('click', e => {
    const link = e.target.closest('.pagination a');
    if (!link) return;
    e.preventDefault();
    currentPage = new URL(link.href).searchParams.get('page') || 1;
    reloadTable();
  });

  // Delete modal
  let curForm = null;
  function attachModalDelete() {
    container.querySelectorAll('.btn-delete').forEach(btn => {
      btn.onclick = () => {
        curForm = btn.closest('form');
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
      };
    });
    document.getElementById('btnConfirmDelete')
      .onclick = () => curForm && curForm.submit();
  }

  // Inline-edit status
  function attachInlineEdit() {
    container.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = () => {
        const row   = btn.closest('tr'),
              id    = row.dataset.id,
              td     = row.querySelector('.dep-status'),
              old    = td.textContent.trim();

        td.innerHTML = `
          <select class="form-select form-select-sm">
            <option ${old==='En curso'   ? 'selected':''}>En curso</option>
            <option ${old==='Electrónico'? 'selected':''}>Electrónico</option>
            <option ${old==='Finalizado' }? 'selected':''}>Finalizado</option>
            <option ${old==='Entregado'  }? 'selected':''}>Entregado</option>
          </select>`;
        btn.textContent = 'Guardar';
        btn.classList.replace('btn-info','btn-success');
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.textContent = 'Cancelar';
        cancel.className = 'btn btn-sm btn-secondary ms-2';
        btn.after(cancel);

        btn.onclick    = save;
        cancel.onclick = () => { td.textContent = old; teardown(); };

        function teardown() {
          btn.textContent = 'Editar';
          btn.classList.replace('btn-success','btn-info');
          cancel.remove();
          attachInlineEdit();
        }
        function save() {
          const nv = td.querySelector('select').value;
          fetch(`/deposits/${id}`, {
            method: 'PATCH',
            headers: {
              'Content-Type':'application/json',
              'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: nv })
          })
          .then(r => r.ok ? r.json() : Promise.reject())
          .then(data => {
            td.textContent = nv;
            teardown();
            flash(data.message,'success');
          })
          .catch(() => {
            teardown();
            flash('Error al actualizar','danger');
          });
        }
      };
    });
  }

  // + Información detail modal
  function attachInfoDetail() {
    container.querySelectorAll('.btn-info-detail').forEach(btn => {
      btn.onclick = () => {
        const row    = btn.closest('tr'),
              info   = JSON.parse(row.getAttribute('data-details')),
              dl     = document.getElementById('infoDetails'),
              canvas = document.getElementById('patternCanvas'),
              ctx    = canvas.getContext('2d');

        // Fill details
        dl.innerHTML = `
          <dt class="col-sm-3">ID</dt><dd class="col-sm-9">${info.id}</dd>
          <dt class="col-sm-3">Cliente</dt><dd class="col-sm-9">${info.client}</dd>
          <dt class="col-sm-3">Dispositivo</dt><dd class="col-sm-9">${info.dispositivo}</dd>
          <dt class="col-sm-3">N.º Serie</dt><dd class="col-sm-9">${info.serial_number}</dd>
          <dt class="col-sm-3">Problema</dt><dd class="col-sm-9">${info.problem_description}</dd>
          <dt class="col-sm-3">Info Adic.</dt><dd class="col-sm-9">${info.more_info ?? 'N/A'}</dd>
          <dt class="col-sm-3">Patrón</dt><dd class="col-sm-9">${info.unlock_password ?? 'N/A'}</dd>
          <dt class="col-sm-3">Pin/Contraseña</dt><dd class="col-sm-9">${info.pin_or_password ?? 'N/A'}</dd>
          <dt class="col-sm-3">Notas Trabajo</dt><dd class="col-sm-9">${info.work_notes ?? 'N/A'}</dd>
          <dt class="col-sm-3">Estado</dt><dd class="col-sm-9">${info.status}</dd>
          <dt class="col-sm-3">Garantía</dt><dd class="col-sm-9 text-danger">${info.under_warranty ? 'Sí' : 'No'}</dd>
          <dt class="col-sm-3">Fecha Entrada</dt><dd class="col-sm-9">${info.date_in}</dd>
          <dt class="col-sm-3">Fecha Salida</dt><dd class="col-sm-9">${info.date_out ?? 'N/A'}</dd>
          <dt class="col-sm-3">Presupuesto</dt><dd class="col-sm-9">$${info.budget !== null ? info.budget.toFixed(2)+' €' : 'N/A'}</dd>
          <dt class="col-sm-3">Creado por</dt><dd class="col-sm-9">${info.creator ?? 'N/A'}</dd>
          <dt class="col-sm-3">Entregado por</dt><dd class="col-sm-9">${info.deliverer ?? 'N/A'}</dd>
          <dt class="col-sm-3">Última modif.</dt><dd class="col-sm-9">${info.last_modifier ?? 'N/A'}</dd>
        `;

        // Draw pattern if present...
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (info.unlock_password) {
          // same pattern‐drawing code as before...
          const seq     = info.unlock_password.split('').map(n=>+n),
                margin  = 20,
                spacing = (canvas.width - 2*margin)/2,
                startC  = '#198754',
                dotC    = '#0d6efd';
          ctx.font = '10px sans-serif';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'top';
          for (let i=1;i<=9;i++){
            const idx=i-1, r=Math.floor(idx/3), c=idx%3,
                  x=margin+c*spacing, y=margin+r*spacing;
            ctx.beginPath();
            ctx.arc(x,y,8,0,2*Math.PI);
            ctx.fillStyle = seq.includes(i)?(i===seq[0]?startC:dotC):'#ccc';
            ctx.fill();
            ctx.fillStyle = '#000'; ctx.fillText(i, x, y+10);
          }
          ctx.strokeStyle=startC; ctx.lineWidth=4; ctx.beginPath();
          seq.forEach((n,i)=>{
            const idx=n-1, r=Math.floor(idx/3), c=idx%3,
                  x=margin+c*spacing, y=margin+r*spacing;
            i===0?ctx.moveTo(x,y):ctx.lineTo(x,y);
          });
          ctx.stroke();
          canvas.style.display='block';
        } else canvas.style.display='none';

        new bootstrap.Modal(document.getElementById('infoModal')).show();
        document.getElementById('btnGenerateLabel')
          .href = `/deposits/${info.id}/label`;
      };
    });
  }

  // Initialize handlers
  attachModalDelete();
  attachInlineEdit();
  attachInfoDetail();
});
</script>
@endpush
