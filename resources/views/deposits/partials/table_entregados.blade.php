{{-- resources/views/deposits/partials/table_entregados.blade.php --}}
<table class="table table-bordered align-middle">
  <thead>
    <tr>
      <th>ID</th>
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
      @php
        $details = [
          'id'               => $d->id,
          'client'           => $d->client->name.' '.$d->client->surname,
          'dispositivo'      => $d->brand.' '.$d->model,
          'serial_number'    => $d->serial_number,
          'problem_description' => $d->problem_description,
          'more_info'        => $d->more_info,
          'unlock_password'  => $d->unlock_password,
          'pin_or_password'  => $d->pin_or_password,
          'work_notes'       => $d->work_notes,
          'status'           => $d->status,
          'under_warranty'   => (bool)$d->under_warranty,
          'date_in'          => $d->date_in,
          'date_out'         => $d->date_out,
          'budget'           => $d->budget,
          'creator'          => optional($d->creator)->name,
          'deliverer'        => optional($d->deliverer)->name,
          'last_modifier'    => optional($d->lastModifier)->name,
        ];
        $jsonDetails = json_encode($details, JSON_HEX_APOS|JSON_UNESCAPED_UNICODE);
      @endphp

      <tr 
        data-id="{{ $d->id }}"
        data-details='{!! $jsonDetails !!}'
      >
        <td>{{ $d->id }}</td>
        <td>{{ $d->brand }} {{ $d->model }}</td>
        <td>{{ $d->client->name }} {{ $d->client->surname }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($d->date_in)->format('d/m/Y') }}</td>
        <td>
          {{ $d->date_out 
              ? \Illuminate\Support\Carbon::parse($d->date_out)->format('d/m/Y') 
              : '—' 
          }}
        </td>
        <td>{{ optional($d->creator)->name ?? '—' }}</td>
        <td>{{ optional($d->deliverer)->name ?? '—' }}</td>
        <td>
          {{-- + Información (siempre visible) --}}
          <button class="btn btn-sm btn-secondary btn-info-detail me-1">
            + Información
          </button>

          @if(auth()->user()->role === 'admin')
            
            {{-- Eliminar --}}
            <form 
              action="{{ route('deposits.destroy', $d->id) }}" 
              method="POST" 
              class="d-inline deleteForm"
            >
              @csrf 
              @method('DELETE')
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
      ->links('pagination::bootstrap-5', ['class'=>'pagination-sm'])
  }}
</div>
