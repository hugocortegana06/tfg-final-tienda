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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Helpers
  const flash = (msg, type='info') => {
    const box = document.getElementById('updateMessage');
    box.className = 'alert mb-3 alert-' + type;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(() => box.style.display = 'none', 3000);
  };
  const svr = document.getElementById('serverSuccess');
  if (svr) setTimeout(() => svr.style.display = 'none', 3000);

  // Variables globales
  const perPage  = document.getElementById('perPageSelect'),
        search   = document.getElementById('searchInput'),
        container= document.getElementById('tableContainer');
  let timer, currentPage = 1;

  // Función que recarga SOLO la tabla parcial
  function reloadTable() {
    const params = new URLSearchParams({
      per_page: perPage.value,
      search:   search.value,
      page:     currentPage
    });
    fetch(`{{ route('deposits.entregados.partial') }}?${params.toString()}`, {
      headers: { 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r => r.text())
    .then(html => {
      container.innerHTML = html;
      attachModalDelete();
      attachInlineEdit();
    })
    .catch(console.error);
  }

  // Captura cambios de per-page y búsqueda
  perPage.onchange = () => { currentPage = 1; reloadTable(); };
  search.oninput   = () => {
    clearTimeout(timer);
    timer = setTimeout(() => { currentPage = 1; reloadTable(); }, 300);
  };

  // Captura clicks en la paginación de la tabla parcial
  container.addEventListener('click', e => {
    const link = e.target.closest('.pagination a');
    if (!link) return;
    e.preventDefault();
    // Extraemos el ?page=X de la URL
    const url = new URL(link.href);
    currentPage = url.searchParams.get('page') || 1;
    reloadTable();
  });

  // Confirmación de eliminación
  let curForm = null;
  function attachModalDelete() {
    container.querySelectorAll('.btn-delete').forEach(btn => {
      btn.onclick = () => {
        curForm = btn.closest('form');
        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
      };
    });
    document.getElementById('btnConfirmDelete').onclick = () => curForm && curForm.submit();
  }

  // Inline‐edit de estado
  function attachInlineEdit() {
    container.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = () => {
        const row = btn.closest('tr'),
              id  = row.dataset.id,
              td  = row.querySelector('.dep-status'),
              old = td.textContent.trim();

        td.innerHTML = `
          <select class="form-select form-select-sm">
            <option ${old==='En curso'    ?'selected':''}>En curso</option>
            <option ${old==='Electrónico' ?'selected':''}>Electrónico</option>
            <option ${old==='Finalizado'  ?'selected':''}>Finalizado</option>
            <option ${old==='Entregado'   ?'selected':''}>Entregado</option>
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
          .then(r=>r.ok? r.json():Promise.reject())
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

  // Inicializamos
  attachModalDelete();
  attachInlineEdit();
});
</script>
@endpush
