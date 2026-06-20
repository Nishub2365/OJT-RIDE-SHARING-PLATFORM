@extends('layouts.app')
@section('title','SOS Alerts')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">🆘 SOS Alerts</div>
  <span class="badge badge-red" style="font-size:13px;padding:8px 18px">
    {{ $sosList->where('status','active')->count() }} Active
  </span>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>User</th><th>Role</th><th>Ride</th><th>Location</th><th>Status</th><th>Time</th><th>Action</th></tr>
      </thead>
      <tbody>
        @forelse($sosList as $sos)
        <tr>
          <td>
            <p style="font-size:13px;font-weight:700">{{ $sos->user->full_name }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ $sos->user->phone }}</p>
          </td>
          <td><span class="badge badge-muted">{{ ucfirst($sos->user->role) }}</span></td>
          <td class="text-sm text-muted">{{ $sos->ride_id ? '#'.$sos->ride_id : '—' }}</td>
          <td>
            @if($sos->lat)
            <a href="https://maps.google.com/?q={{ $sos->lat }},{{ $sos->lng }}" target="_blank" class="btn btn-blue btn-xs">📍 View Map</a>
            @else
            <span class="text-xs text-muted">No GPS</span>
            @endif
          </td>
          <td>
            <span class="badge {{ $sos->status==='active'?'badge-red':'badge-green' }}">
              {{ ucfirst($sos->status) }}
            </span>
          </td>
          <td class="text-xs text-muted">{{ $sos->created_at->diffForHumans() }}</td>
          <td>
            @if($sos->status === 'active')
            <form method="POST" action="{{ route('admin.sos.resolve', $sos->id) }}">
              @csrf <button type="submit" class="btn btn-green btn-xs">✓ Resolve</button>
            </form>
            @else
            <span class="text-xs text-muted">Resolved</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:36px">✅ No SOS alerts</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $sosList->links() }}</div>
</div>
@endsection
