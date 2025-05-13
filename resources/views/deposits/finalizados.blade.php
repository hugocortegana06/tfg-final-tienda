{{-- resources/views/deposits/finalizados.blade.php --}}
@extends('layouts.app')

@section('title','Histórico de Finalizados')

@section('content')
<div class="container">
  <h1 class="mb-4">Histórico de Finalizados</h1>

  {{-- Top bar: per-page + búsqueda --}}
  <div class="row mb-3 align-items-center">
    <div class="col-auto d-flex align-items-center">
      <label class="me-2 mb-0">Mostrar</label>
      <select id="perPageSelect" class="form-select form-select-sm me-3">
        @foreach([5,10,15,20] as $n)
          <option value="{{ $n }}" {{ ($perPage == $n) ? 'selected' : '' }}>{{ $n }}</option>
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

  {{-- Flash messages --}}
  <div id="updateMessage" class="alert mb-3" style="display:none;"></div>
  @if(session('success'))
    <div id="serverSuccess" class="alert alert-success mb-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- AJAX-loaded table --}}
  <div id="tableContainer">
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Dispositivo</th>
          <th>Serial</th>
          <th>Problema</th>
          <th>Estado</th>
          <th>Fecha Entrada</th>
          <th>Fecha Salida</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($deposits as $deposit)
        <tr
          data-id="{{ $deposit->id }}"
          data-phone="{{ preg_replace('/\D+/','', $deposit->client->phone) }}"
          data-client="{{ $deposit->client->name.' '.$deposit->client->surname }}"
          data-device="{{ $deposit->brand.' '.$deposit->model }}"
        >
          <td>{{ $deposit->id }}</td>
          <td>{{ $deposit->client->name }} {{ $deposit->client->surname }}</td>
          <td>{{ $deposit->brand }} {{ $deposit->model }}</td>
          <td>{{ $deposit->serial_number }}</td>
          <td>{{ $deposit->problem_description }}</td>
          <td class="dep-status">{{ $deposit->status }}</td>
          <td>{{ $deposit->date_in }}</td>
          <td>{{ $deposit->date_out ?? 'N/A' }}</td>
          <td>
            <button class="btn btn-sm btn-info btn-edit">Editar</button>
            <form
              action="{{ route('deposits.destroy', $deposit) }}"
              method="POST"
              class="d-inline"
            >

              @csrf
              @if(auth()->user()->role === 'admin')

              @method('DELETE')
              <button type="button" class="btn btn-sm btn-danger btn-delete">Eliminar</button>
            </form>
              @endif
            <button class="btn btn-sm btn-success btn-notify ms-1">Notificar</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="d-flex justify-content-end">
      {{ $deposits->appends(['per_page' => $perPage, 'search' => $search])->links('pagination::bootstrap-5', ['class'=>'pagination-sm']) }}
    </div>
  </div>
</div>

{{-- Confirm deletion modal --}}
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const perPage   = document.getElementById('perPageSelect');
  const search    = document.getElementById('searchInput');
  const container = document.getElementById('tableContainer');
  const svr       = document.getElementById('serverSuccess');
  let timer;

  // Ocultar flash server
  if (svr) setTimeout(() => svr.style.display = 'none', 3000);

  // Helper flash
  function flash(msg, type = 'info') {
    const box = document.getElementById('updateMessage');
    box.className = 'alert mb-3 alert-' + type;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(() => box.style.display = 'none', 3000);
  }

  // Modal eliminar
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

  // Inline-edit estado
  function attachInlineEdit() {
    container.querySelectorAll('.btn-edit').forEach(btn => {
      btn.onclick = () => {
        const row = btn.closest('tr'),
              id  = row.dataset.id,
              td  = row.querySelector('.dep-status'),
              old = td.textContent.trim();

        td.innerHTML = `
          <select class="form-select form-select-sm">
            <option ${old==='Finalizado' ? 'selected':''}>Finalizado</option>
            <option ${old==='Entregado'  ? 'selected':''}>Entregado</option>
          </select>`;
        btn.textContent = 'Guardar';
        btn.classList.replace('btn-info','btn-success');

        const cancel = document.createElement('button');
        cancel.type = 'button'; cancel.textContent = 'Cancelar'; cancel.className = 'btn btn-sm btn-secondary ms-2';
        btn.after(cancel);

        btn.onclick    = save;
        cancel.onclick = () => { td.textContent = old; teardown(); };

        function teardown() {
          btn.textContent = 'Editar'; btn.classList.replace('btn-success','btn-info'); cancel.remove(); attachInlineEdit();
        }
        function save() {
          const nv = td.querySelector('select').value;
          fetch(`/deposits/${id}`, { method:'PATCH', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' }, body: JSON.stringify({ status: nv }) })
          .then(r=> r.ok? r.json(): Promise.reject())
          .then(json=>{ td.textContent = nv; teardown(); flash(json.message,'success'); })
          .catch(()=>{ teardown(); flash('Error al actualizar','danger'); });
        }
      };
    });
  }

  // Notificar por WhatsApp
  function attachNotify() {
    container.querySelectorAll('.btn-notify').forEach(btn => {
      btn.onclick = () => {
        const row    = btn.closest('tr');
        const phone  = row.dataset.phone;
        const client = row.dataset.client;
        const device = row.dataset.device;
        const id     = row.dataset.id;
        const text   = `Hola ${client}, tu depósito N.º ${id} (${device}) está listo.`;
        const url    = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`;
        window.open(url,'_blank');
        btn.textContent = 'Notificado'; btn.disabled = true;
      };
    });
  }

  // Recarga AJAX
  function fetchTable(page = null) {
    const params = new URLSearchParams();
    if (page) params.set('page', page);
    params.set('per_page', perPage.value);
    params.set('search',   search.value);

    fetch(`{{ route('deposits.finalizados.partial') }}?${params}`, { headers:{ 'X-Requested-With':'XMLHttpRequest' } })
      .then(r=> r.text())
      .then(html=>{
        container.innerHTML = html;
        attachModalDelete(); attachInlineEdit(); attachNotify();
      });
  }

  // Bind eventos
  perPage.onchange = () => fetchTable();
  search.oninput   = () => { clearTimeout(timer); timer = setTimeout(fetchTable,300); };
  container.addEventListener('click', e => {
    const link = e.target.closest('.pagination a');
    if (!link) return;
    e.preventDefault();
    const url = new URL(link.href);
    fetchTable(url.searchParams.get('page'));
  });

  // Init
  attachModalDelete(); attachInlineEdit(); attachNotify();
});
</script>
@endpush