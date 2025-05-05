{{-- resources/views/deposits/partials/table_finalizados.blade.php --}}
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
      data-phone="{{ preg_replace('/\D+/', '', $deposit->client->phone) }}"
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
          @method('DELETE')
          <button type="button" class="btn btn-sm btn-danger btn-delete">
            Eliminar
          </button>
        </form>
        <button class="btn btn-sm btn-success btn-notify ms-1">
          Notificar
        </button>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<nav class="d-flex justify-content-end">
  {{ $deposits
      ->appends(request()->only(['per_page','search']))
      ->links('pagination::bootstrap-5', ['class'=>'pagination-sm'])
  }}
</nav>
