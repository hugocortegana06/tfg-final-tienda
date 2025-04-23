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
          <option value="{{ $n }}" {{ ($perPage==$n)?'selected':'' }}>
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
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        Cancelar
      </button>
      <button type="button" class="btn btn-danger" id="btnConfirmDelete">
        Eliminar
      </button>
    </div>
  </div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // 1) Oculta flash del servidor
  const svr = document.getElementById('serverSuccess');
  if (svr) setTimeout(()=>svr.style.display='none',3000);

  // 2) Helper flash
  function flash(msg,type='info'){
    const box = document.getElementById('updateMessage');
    box.className = 'alert mb-3 alert-'+type;
    box.textContent = msg;
    box.style.display='block';
    setTimeout(()=>box.style.display='none',3000);
  }

  // 3) Attach modal delete
  let curForm = null;
  function attachModalDelete(){
    document.querySelectorAll('.btn-delete').forEach(b=>{
      b.onclick = ()=>{
        curForm = b.closest('form');
        new bootstrap.Modal(
          document.getElementById('confirmDeleteModal')
        ).show();
      };
    });
    document.getElementById('btnConfirmDelete').onclick = ()=> curForm && curForm.submit();
  }

  // 4) Attach inline-edit status
  function attachInlineEdit(){
    document.querySelectorAll('.btn-edit').forEach(b=>{
      b.onclick = ()=>{
        const row = b.closest('tr'),
              id  = row.dataset.id,
              td  = row.querySelector('.dep-status'),
              old = td.textContent.trim();

        td.innerHTML = `
          <select class="form-select form-select-sm">
            <option ${old==='En curso'    ? 'selected':''}>En curso</option>
            <option ${old==='Electrónico' ? 'selected':''}>Electrónico</option>
            <option ${old==='Finalizado'  ? 'selected':''}>Finalizado</option>
            <option ${old==='Entregado'   ? 'selected':''}>Entregado</option>
          </select>`;

        b.textContent = 'Guardar';
        b.classList.replace('btn-info','btn-success');
        const cancel = document.createElement('button');
        cancel.type='button';
        cancel.textContent='Cancelar';
        cancel.className='btn btn-sm btn-secondary ms-2';
        b.after(cancel);

        b.onclick    = save;
        cancel.onclick = ()=> { td.textContent = old; teardown(); };

        function teardown(){
          b.textContent='Editar';
          b.classList.replace('btn-success','btn-info');
          cancel.remove();
          attachInlineEdit();
        }

        function save(){
          const nv = td.querySelector('select').value;
          fetch(`/deposits/${id}`, {
            method:'PATCH',
            headers: {
              'Content-Type':'application/json',
              'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: nv })
          })
          .then(r=>r.ok?r.json():Promise.reject())
          .then(json=>{
            td.textContent = nv;
            teardown();
            flash(json.message,'success');
          })
          .catch(()=>{
            teardown();
            flash('Error al actualizar','danger');
          });
        }
      };
    });
  }

  // 5) AJAX reload per_page + search
  const perPage = document.getElementById('perPageSelect'),
        search  = document.getElementById('searchInput'),
        cont    = document.getElementById('tableContainer');
  let timer;
  function reload(){
    const qs = new URLSearchParams({
      per_page: perPage.value,
      search:   search.value
    });
    fetch(`{{ route('deposits.entregados.partial') }}?${qs}`, {
      headers:{ 'X-Requested-With':'XMLHttpRequest' }
    })
    .then(r=>r.text())
    .then(html=>{
      cont.innerHTML = html;
      attachModalDelete();
      attachInlineEdit();
    });
  }
  perPage.onchange = reload;
  search.oninput   = ()=>{ clearTimeout(timer); timer=setTimeout(reload,300); };

  // 6) Inicializar
  attachModalDelete();
  attachInlineEdit();
});
</script>
@endpush
