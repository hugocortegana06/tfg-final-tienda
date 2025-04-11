@extends('layouts.app')

@section('title','Lista de Clientes')

@section('content')
<div class="container">
    <h1 class="mb-4">Clientes</h1>

    <!-- Contenedor para mensajes efímeros (desde JS) -->
    <div id="updateMessage" style="display: none;" class="alert mb-3"></div>

    <!-- Mensaje de éxito del servidor (por ejemplo, tras eliminar o crear cliente) -->
    @if(session('success'))
        <div id="serverSuccess" class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <!-- Botón para crear un cliente nuevo -->
    <div class="mb-3 text-end">
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            Añadir cliente
        </a>
    </div>

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
                    <!-- Botón Editar (inline) -->
                    <button class="btn btn-sm btn-info btn-edit">Editar</button>

                    <!-- Botón Eliminar con modal -->
                    <form action="{{ route('clients.destroy', $client->phone) }}" method="POST" class="deleteForm" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-danger btn-delete">
                            Eliminar
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Modal de Confirmación (eliminación) -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Seguro que deseas eliminar este cliente?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
// =====================================================================
// 1) Función para mostrar mensajes efímeros en #updateMessage
// =====================================================================
function showEphemeralMessage(msg, type = 'info') {
    const msgDiv = document.getElementById('updateMessage');
    if (!msgDiv) return;

    // alert-success, alert-danger, etc.
    msgDiv.className = 'alert mb-3 alert-' + type;
    msgDiv.textContent = msg;
    msgDiv.style.display = 'block';

    // Ocultar tras 3 segundos
    setTimeout(() => {
        msgDiv.style.display = 'none';
    }, 3000);
}

// =====================================================================
// 2) Ocultar el mensaje de éxito del servidor tras 3 segundos
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const serverMsg = document.getElementById('serverSuccess');
    if (serverMsg) {
        setTimeout(() => {
            serverMsg.style.display = 'none';
        }, 3000);
    }

    // =================================================================
    // Eliminar con modal
    // =================================================================
    let currentDeleteForm = null;

    // Selecciona todos los botones "Eliminar"
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDeleteForm = this.closest('.deleteForm');
            const myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            myModal.show();
        });
    });

    // Botón "Eliminar" del modal
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    btnConfirmDelete.addEventListener('click', function() {
        if (currentDeleteForm) {
            currentDeleteForm.submit();
        }
    });

    // =================================================================
    // Edición inline
    // =================================================================
    const table = document.getElementById('clientsTable');
    table.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', onEditClick);
    });

    function onEditClick(e) {
        const btn = e.target;
        const row = btn.closest('tr');
        const clientPhone = row.dataset.phone;

        // celdas
        const tdPhone   = row.querySelector('.client-phone');
        const tdName    = row.querySelector('.client-name');
        const tdSurname = row.querySelector('.client-surname');
        const tdInfo    = row.querySelector('.client-info');

        // valores actuales
        const currPhone   = tdPhone.textContent.trim();
        const currName    = tdName.textContent.trim();
        const currSurname = tdSurname.textContent.trim();
        const currInfo    = tdInfo.textContent.trim();

        // Pasa a modo edición
        // (Si phone es PK y no editable, puedes dejarlo en modo lectura)
        // Si lo quieres editable, añade un input en tdPhone
        tdName.innerHTML = `<input type="text" class="form-control form-control-sm" name="name" value="${currName}" />`;
        tdSurname.innerHTML = `<input type="text" class="form-control form-control-sm" name="surname" value="${currSurname}" />`;
        tdInfo.innerHTML = `<input type="text" class="form-control form-control-sm" name="additional_info" value="${currInfo}" />`;

        // Cambia el botón "Editar" a "Guardar"
        btn.textContent = 'Guardar';
        btn.classList.remove('btn-info');
        btn.classList.add('btn-success');

        // Creamos botón "Cancelar"
        const btnCancel = document.createElement('button');
        btnCancel.textContent = 'Cancelar';
        btnCancel.className = 'btn btn-sm btn-secondary ms-2';
        btn.parentNode.insertBefore(btnCancel, btn.nextSibling);

        // Reemplazamos el evento "Editar"
        btn.removeEventListener('click', onEditClick);
        btn.addEventListener('click', onSaveClick);
        btnCancel.addEventListener('click', onCancelClick);

        function onSaveClick() {
            const newName    = tdName.querySelector('input[name="name"]').value.trim();
            const newSurname = tdSurname.querySelector('input[name="surname"]').value.trim();
            const newInfo    = tdInfo.querySelector('input[name="additional_info"]').value.trim();

            if (!newName || !newSurname) {
                showEphemeralMessage('Nombre y Apellidos no pueden estar vacíos', 'warning');
                return;
            }

            // Llamada AJAX a /clients/{phone} (PATCH)
            fetch(`/clients/${clientPhone}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: newName,
                    surname: newSurname,
                    additional_info: newInfo
                })
            })
            .then(resp => {
                if (!resp.ok) {
                    throw new Error('HTTP error, status = ' + resp.status);
                }
                return resp.json();
            })
            .then(data => {
                // Actualiza la fila y mensaje
                tdName.textContent = newName;
                tdSurname.textContent = newSurname;
                tdInfo.textContent = newInfo;
                btn.textContent = 'Editar';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-info');
                btnCancel.remove();
                btn.removeEventListener('click', onSaveClick);
                btn.addEventListener('click', onEditClick);

                showEphemeralMessage(data.message || 'Actualizado correctamente', 'success');
            })
            .catch(err => {
                console.error(err);
                showEphemeralMessage('Hubo un error al actualizar el cliente.', 'danger');
            });
        }

        function onCancelClick() {
            // Revertir
            tdName.textContent    = currName;
            tdSurname.textContent = currSurname;
            tdInfo.textContent    = currInfo;
            btn.textContent = 'Editar';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-info');
            btnCancel.remove();
            btn.removeEventListener('click', onSaveClick);
            btn.addEventListener('click', onEditClick);
        }
    }
});
</script>
@endsection
