{{-- resources/views/deposits/partials/table_entregados.blade.php --}}
<table class="table table-bordered align-middle">
  <thead>
    <tr>
      <th>ID</th>
      <th>Estado</th>
      <th>Dispositivo</th>
      <th>Cliente</th>
      <th>Fecha Entrada</th>
      <th>Fecha Salida</th>
      <th>Creado por</th>
      <th>Entregado por</th>
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
      <td>{{ \Illuminate\Support\Carbon::parse($d->date_in)->format('d/m/Y') }}</td>
      <td>{{ $d->date_out ? \Illuminate\Support\Carbon::parse($d->date_out)->format('d/m/Y') : '—' }}</td>
      <td>{{ optional($d->creator)->name ?? '—' }}</td>
      <td>{{ optional($d->deliverer)->name ?? '—' }}</td>
      <td>
        <button class="btn btn-sm btn-info btn-edit">Editar</button>
        <form action="{{ route('deposits.destroy', $d->id) }}"
              method="POST"
              class="deleteForm d-inline">

          @if(auth()->user()->role === 'admin')
          @csrf @method('DELETE')
          <button type="button" class="btn btn-sm btn-danger btn-delete">
            Eliminar
          </button>
        </form>
          @endif
      </td>
    </tr>
  @endforeach
  </tbody>
</table>

<div class="d-flex justify-content-end">
  {{ $deposits
      ->links('pagination::bootstrap-5', [
        'paginator' => $deposits,
        'class'     => 'pagination-sm'
      ])
  }}
</div>
