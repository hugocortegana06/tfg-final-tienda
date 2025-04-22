{{-- resources/views/clients/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lista de Clientes')

@section('content')
<div class="container">
    <h1 class="mb-4">Clientes</h1>

    {{-- Top row: per‑page selector + “Añadir cliente” --}}
    <div class="row mb-3 align-items-center">
        <div class="col-auto">
            <form id="perPageForm" method="GET" class="d-flex align-items-center">
                <label class="me-2 mb-0">Mostrar</label>
                <select
                    name="per_page"
                    id="per_page"
                    class="form-select form-select-sm me-2"
                    onchange="this.form.submit()"
                >
                    @foreach([5,10,15,20] as $n)
                        <option
                            value="{{ $n }}"
                            {{ ($perPage ?? 10) == $n ? 'selected' : '' }}
                        >
                            {{ $n }}
                        </option>
                    @endforeach
                </select>
                <label class="mb-0">por página</label>
            </form>
        </div>
        <div class="col text-end">
            <a
                href="{{ route('clients.create') }}"
                class="btn btn-sm btn-primary"
            >Añadir cliente</a>
        </div>
    </div>

    {{-- Flash messages --}}
    <div id="updateMessage" class="alert mb-3" style="display:none;"></div>
    @if(session('success'))
        <div id="serverSuccess" class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Clientes table --}}
    <table class="table table-bordered align-middle" id="clientsTable">
        <thead>
            <tr>
                <th>Teléfono</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Info Adicional</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr data-phone="{{ $client->phone }}">
                <td class="client-phone">{{ $client->phone }}</td>
                <td class="client-name">{{ $client->name }}</td>
                <td class="client-surname">{{ $client->surname }}</td>
                <td class="client-info">{{ $client->additional_info }}</td>
                <td>{{ $client->created_at->format('d-m-Y H:i') }}</td>
                <td>
                    <button class="btn btn-sm btn-info btn-edit">Editar</button>
                    <form
                        action="{{ route('clients.destroy', $client->phone) }}"
                        method="POST"
                        class="deleteForm d-inline"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="button"
                            class="btn btn-sm btn-danger btn-delete"
                        >Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Paginación pequeña a la derecha --}}
    <div class="d-flex justify-content-end">
        {{ $clients
            ->appends(['per_page' => $perPage ?? 10])
            ->links('pagination::bootstrap-5', [
                'paginator' => $clients,
                'class'     => 'pagination-sm'
            ])
        }}
    </div>
</div>

{{-- Confirm deletion modal --}}
<div
  class="modal fade"
  id="confirmDeleteModal"
  tabindex="-1"
  aria-labelledby="confirmDeleteModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">
          Confirmar Eliminación
        </h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que deseas eliminar este cliente?
      </div>
      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal"
        >Cancelar</button>
        <button
          type="button"
          class="btn btn-danger"
          id="btnConfirmDelete"
        >Eliminar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <!-- Bootstrap JS for modal -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1) Hide server flash after 3s
    const svr = document.getElementById('serverSuccess');
    if (svr) setTimeout(() => svr.style.display = 'none', 3000);

    // 2) Helper for ephemeral messages
    function showMsg(msg, type='info') {
      const box = document.getElementById('updateMessage');
      box.className = 'alert mb-3 alert-' + type;
      box.textContent = msg;
      box.style.display = 'block';
      setTimeout(() => box.style.display = 'none', 3000);
    }

    // 3) Delete with modal confirmation
    let currentForm = null;
    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        currentForm = btn.closest('.deleteForm');
        new bootstrap.Modal(
          document.getElementById('confirmDeleteModal')
        ).show();
      });
    });
    document.getElementById('btnConfirmDelete')
      .addEventListener('click', () => currentForm && currentForm.submit());

    // 4) Inline edit “Nombre”, “Apellidos” and “Info Adicional”
    document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', onEdit));

    function onEdit(e) {
      const btn = e.target;
      const row = btn.closest('tr');
      const phone = row.dataset.phone;

      const tdName    = row.querySelector('.client-name');
      const tdSurname = row.querySelector('.client-surname');
      const tdInfo    = row.querySelector('.client-info');

      const oldName    = tdName.textContent.trim();
      const oldSurname = tdSurname.textContent.trim();
      const oldInfo    = tdInfo.textContent.trim();

      // Switch to inputs
      tdName.innerHTML    = `<input type="text" class="form-control form-control-sm" name="name" value="${oldName}" />`;
      tdSurname.innerHTML = `<input type="text" class="form-control form-control-sm" name="surname" value="${oldSurname}" />`;
      tdInfo.innerHTML    = `<input type="text" class="form-control form-control-sm" name="additional_info" value="${oldInfo}" />`;

      // Change buttons to Save + Cancel
      btn.textContent = 'Guardar';
      btn.classList.replace('btn-info','btn-success');
      const btnCancel = document.createElement('button');
      btnCancel.type = 'button';
      btnCancel.textContent = 'Cancelar';
      btnCancel.className = 'btn btn-sm btn-secondary ms-2';
      btn.insertAdjacentElement('afterend', btnCancel);

      btn.removeEventListener('click', onEdit);
      btn.addEventListener('click', onSave);
      btnCancel.addEventListener('click', onCancel);

      function teardown() {
        btnCancel.remove();
        btn.textContent = 'Editar';
        btn.classList.replace('btn-success','btn-info');
        btn.removeEventListener('click', onSave);
        btn.addEventListener('click', onEdit);
      }

      function onSave() {
        const newName    = tdName.querySelector('input').value.trim();
        const newSurname = tdSurname.querySelector('input').value.trim();
        const newInfo    = tdInfo.querySelector('input').value.trim();

        if (!newName || !newSurname) {
          showMsg('Nombre y apellidos no pueden ir vacíos','warning');
          return;
        }

        fetch(`/clients/${phone}`, {
          method: 'PATCH',
          headers: {
            'Content-Type':'application/json',
            'Accept':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
          },
          body: JSON.stringify({
            name: newName,
            surname: newSurname,
            additional_info: newInfo
          })
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(data => {
          tdName.textContent    = newName;
          tdSurname.textContent = newSurname;
          tdInfo.textContent    = newInfo;
          teardown();
          showMsg(data.message || 'Cliente actualizado correctamente','success');
        })
        .catch(() => {
          teardown();
          showMsg('Hubo un error al actualizar el cliente.','danger');
        });
      }

      function onCancel() {
        tdName.textContent    = oldName;
        tdSurname.textContent = oldSurname;
        tdInfo.textContent    = oldInfo;
        teardown();
      }
    }
  });
  </script>
@endpush
