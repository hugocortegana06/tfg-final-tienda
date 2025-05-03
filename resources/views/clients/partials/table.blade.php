{{-- resources/views/clients/partials/table.blade.php --}}
<table class="table table-bordered align-middle">
  <thead>
    <tr>
      <th>Teléfono</th>
      <th>Teléfono alternativo</th>
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
      <td class="client-phone2">{{ $client->phone_2 ?? '—' }}</td>
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
          <button type="button" class="btn btn-sm btn-danger btn-delete">
            Eliminar
          </button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{-- Paginación --}}
<div class="d-flex justify-content-end">
  {{ $clients
      ->appends(['per_page'=>$perPage,'search'=>$search])
      ->links('pagination::bootstrap-5', ['class'=>'pagination-sm'])
  }}
</div>
