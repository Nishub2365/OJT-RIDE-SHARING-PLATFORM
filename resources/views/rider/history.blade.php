@extends('layouts.app')
@section('title','Trip History')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
  <div class="page-title">🕐 Trip History</div>
  <a href="{{ route('rider.request-ride') }}" class="btn btn-brand btn-sm">+ Book New Ride</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Route</th>
          <th>Driver</th>
          <th>Fare</th>
          <th>Vehicle</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rides as $ride)
        <tr>
          <td style="font-weight:700;color:var(--text3)">#{{ $ride->id }}</td>
          <td>
            {{-- FIXED: Used full root namespace path for Str --}}
            <p style="font-size:12px;font-weight:600">{{ \Illuminate\Support\Str::limit($ride->pickup_address,28) }}</p>
            <p style="font-size:11px;color:var(--text3)">→ {{ \Illuminate\Support\Str::limit($ride->destination_address,28) }}</p>
          </td>
          <td class="text-sm text-muted">{{ $ride->driver?->full_name ?? '—' }}</td>
          <td style="font-weight:700">NPR {{ number_format($ride->agreed_fare ?? $ride->offered_fare) }}</td>
          <td class="text-sm">{{ ucfirst($ride->vehicle_category) }}</td>
          <td>
            <span class="badge {{ match($ride->status) {
              'completed' => 'badge-green',
              'cancelled' => 'badge-red',
              'in_progress','driver_arrived','accepted' => 'badge-blue',
              default => 'badge-muted'
            } }}">
              {{-- FIXED: Casing fixed so strings like 'in_progress' become 'In Progress' --}}
              {{ ucwords(str_replace('_',' ',$ride->status)) }}
            </span>
          </td>
          <td class="text-xs text-muted">{{ $ride->created_at->format('d M Y') }}</td>
          <td>
            @if($ride->status === 'completed')
              @php $rated = $ride->ratings->where('rater_id',auth()->id())->first(); @endphp
              @if(!$rated)
              <a href="{{ route('rider.rate',$ride->id) }}" class="btn btn-amber btn-xs">⭐ Rate</a>
              @else
              <span style="font-size:11px;color:var(--text3)">Rated ⭐{{ $rated->rating }}</span>
              @endif
            @elseif(in_array($ride->status,['pending','accepted','in_progress']))
            <a href="{{ route('rider.active-ride',$ride->id) }}" class="btn btn-blue btn-xs">Track</a>
            @endif
            <a href="{{ route('rider.ride-detail',$ride->id) }}" class="btn btn-ghost btn-xs">Details</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;color:var(--text3);padding:40px">
            <p style="font-size:32px;margin-bottom:8px">🚗</p>
            No trips yet. <a href="{{ route('rider.request-ride') }}" style="color:var(--brand)">Book your first ride!</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $rides->links() }}</div>
</div>
@endsection