@extends('layouts.app')

@section('title','Lista de Usuarios')

@section('content')
<div class="container">
    <h1 class="mb-4">Usuarios</h1>

    <!-- Contenedor para mensajes efímeros (desde JS) -->
    <div id="updateMessage" style="display: none;" class="alert mb-3"></div>

    <!-- Mensaje de éxito del servidor (por ejemplo, tras eliminar o crear usuario) -->
    @if(session('success'))
        <div id="serverSuccess" class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    <!-- Botón para crear un usuario nuevo -->
    <div class="mb-3 text-end">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            Añadir usuario
        </a>
    </div>

    <table class="table table-bordered align-middle" id="usersTable">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr data-id="{{ $user->id }}">
                <td class="user-name">{{ $user->name }}</td>
                <td class="user-email">{{ $user->email }}</td>
                <td>{{ $user->created_at->format('d-m-Y H:i') }}</td>
                <td>
                    <!-- Botón Editar (edición inline) -->
                    <button class="btn btn-sm btn-info btn-edit">Editar</button>

                    <!-- Botón Eliminar con modal -->
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="deleteForm" style="display:inline-block;">
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
        ¿Seguro que deseas eliminar este usuario?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
// 1) Función para mostrar mensajes efímeros
function showEphemeralMessage(msg, type = 'info') {
    const msgDiv = document.getElementById('updateMessage');
    if (!msgDiv) return;

    // Asigna clase de Bootstrap (alert-success, alert-danger, etc.)
    msgDiv.className = 'alert mb-3 alert-' + type;
    msgDiv.textContent = msg;
    msgDiv.style.display = 'block';

    // Oculta tras 3 segundos
    setTimeout(() => {
        msgDiv.style.display = 'none';
    }, 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    // 2) Si hay un mensaje de éxito del servidor, lo ocultamos tras 3s
    const serverMsg = document.getElementById('serverSuccess');
    if (serverMsg) {
        setTimeout(() => {
            serverMsg.style.display = 'none';
        }, 3000);
    }

    // --------- Lógica ELIMINAR con modal ---------
    let currentDeleteForm = null;

    // Cuando pulsamos botón Eliminar en la tabla
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            // Guardamos el form al que pertenece
            currentDeleteForm = this.closest('.deleteForm');
            // Mostramos el modal de Bootstrap
            const myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            myModal.show();
        });
    });

    // Botón "Eliminar" dentro del modal
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    btnConfirmDelete.addEventListener('click', function() {
        if (currentDeleteForm) {
            currentDeleteForm.submit();
        }
    });

    // --------- Lógica EDICIÓN INLINE ---------
    const table = document.getElementById('usersTable');
    table.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', onEditClick);
    });

    function onEditClick(e) {
        const btn = e.target;
        const row = btn.closest('tr');
        const userId = row.dataset.id;
        
        // celdas
        const tdName  = row.querySelector('.user-name');
        const tdEmail = row.querySelector('.user-email');

        // valores actuales
        const currentName  = tdName.textContent.trim();
        const currentEmail = tdEmail.textContent.trim();

        // pasa a modo edición
        tdName.innerHTML = `<input type="text" class="form-control form-control-sm" name="name" value="${currentName}" />`;
        tdEmail.innerHTML = `<input type="email" class="form-control form-control-sm" name="email" value="${currentEmail}" />`;

        // Cambia el botón "Editar" a "Guardar"
        btn.textContent = 'Guardar';
        btn.classList.remove('btn-info');
        btn.classList.add('btn-success');

        // Creamos botón "Cancelar"
        const btnCancel = document.createElement('button');
        btnCancel.textContent = 'Cancelar';
        btnCancel.className = 'btn btn-sm btn-secondary ms-2';
        btn.parentNode.insertBefore(btnCancel, btn.nextSibling);

        // Quita el evento de "Editar"
        btn.removeEventListener('click', onEditClick);
        // Añade evento de "Guardar"
        btn.addEventListener('click', onSaveClick);
        // Evento "Cancelar"
        btnCancel.addEventListener('click', onCancelClick);

        function onSaveClick() {
            // Lee los nuevos valores
            const newName = tdName.querySelector('input[name="name"]').value.trim();
            const newEmail = tdEmail.querySelector('input[name="email"]').value.trim();
            
            if (!newName || !newEmail) {
                showEphemeralMessage('Nombre y Email no pueden estar vacíos', 'warning');
                return;
            }

            fetch(`/users/${userId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: newName, email: newEmail })
            })
            .then(resp => {
                if (!resp.ok) {
                    throw new Error('HTTP error, status = ' + resp.status);
                }
                return resp.json();
            })
            .then(data => {
                // Actualiza la fila y muestra mensaje
                tdName.textContent = newName;
                tdEmail.textContent = newEmail;
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
                showEphemeralMessage('Hubo un error al actualizar el usuario.', 'danger');
            });
        }

        function onCancelClick() {
            // revertir
            tdName.textContent = currentName;
            tdEmail.textContent = currentEmail;
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
