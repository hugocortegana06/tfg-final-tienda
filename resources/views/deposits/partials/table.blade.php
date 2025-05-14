{{-- resources/views/deposits/partials/table.blade.php --}}
<table class="table table-bordered align-middle">
  <thead>
    <tr>
      <th>ID</th>
      <th>Estado</th>
      <th>Dispositivo</th>
      <th>Cliente</th>
      <th>Notas de trabajo</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    @foreach($deposits as $d)
      @php
        // Preparamos un JSON seguro para el modal de información
        $details = [
          'id'                  => $d->id,
          'client'              => $d->client->name.' '.$d->client->surname,
          'dispositivo'         => $d->brand.' '.$d->model,
          'serial_number'       => $d->serial_number,
          'problem_description' => $d->problem_description,
          'more_info'           => $d->more_info,
          'unlock_password'     => $d->unlock_password,
          'status'              => $d->status,
          'date_in'             => $d->date_in,
          'date_out'            => $d->date_out,
          'creator'             => optional($d->creator)->name,
          'last_modifier'       => optional($d->lastModifier)->name,
          'budget'              => $d->budget,
          'pin_or_password'     => $d->pin_or_password,
          'work_notes'          => $d->work_notes,
          'under_warranty'      => $d->under_warranty,     


        ];
        $json = json_encode($details, JSON_HEX_APOS|JSON_UNESCAPED_UNICODE);
      @endphp

      <tr
        data-id="{{ $d->id }}"
        data-details='{!! $json !!}'
      >
        <td>{{ $d->id }}</td>
        <td class="dep-status">{{ $d->status }}</td>
        <td>{{ $d->brand }} {{ $d->model }}</td>
        <td>{{ $d->client->name }} {{ $d->client->surname }}</td>
        <td class="work-notes">{{ $d->work_notes ?? 'N/A' }}</td>
        <td>
          <button class="btn btn-sm btn-secondary btn-info-detail me-1">
            + Información
          </button>
          <button class="btn btn-sm btn-info btn-edit me-1">Editar</button>

          {{-- Solo muestra “Eliminar” a admin --}}
          @if(auth()->user()->role === 'admin')
            <form action="{{ route('deposits.destroy', $d) }}" method="POST" class="d-inline">
              @csrf
              @method('DELETE')
              <button type="button" class="btn btn-sm btn-danger btn-delete">
                Eliminar
              </button>
            </form>
          @endif

          <a href="{{ route('deposits.invoice', $d->id) }}" class="btn btn-sm btn-warning">
            Generar Factura
          </a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<nav class="d-flex justify-content-end">
  {{ $deposits
      ->appends(request()->only(['per_page','search']))
      ->links('pagination::bootstrap-5')
  }}
</nav>
