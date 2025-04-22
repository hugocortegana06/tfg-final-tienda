{{-- resources/views/deposits/partials/table.blade.php --}}
<table class="table table-bordered align-middle" id="depositsTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Estado</th>
      <th>Dispositivo</th>
      <th>Cliente</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    @foreach($deposits as $d)
    <tr data-id="{{ $d->id }}">
      <td>{{ $d->id }}</td>
      <td class="dep-status">{{ $d->status }}</td>
      <td>{{ $d->brand }} {{ $d->model }}</td>
      <td>{{ $d->client->name }} {{ $d->client->surname }}</td>
      <td>
        <button class="btn btn-sm btn-info btn-edit">Editar</button>
        <form class="deleteForm d-inline" method="POST"
              action="{{ route('deposits.destroy', $d->id) }}">
          @csrf @method('DELETE')
          <button type="button" class="btn btn-sm btn-danger btn-delete">
            Eliminar
          </button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-end">
  {{ $deposits
      ->appends(['per_page' => request('per_page')])
      ->links('pagination::bootstrap-5', [
          'paginator' => $deposits,
          'class'     => 'pagination-sm'
      ])
  }}
</div>
