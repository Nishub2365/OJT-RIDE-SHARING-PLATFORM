@extends('layouts.app')
@section('title','Drivers')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">🧑‍✈️ Drivers</div>
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
    <select name="status" class="input" style="width:140px" onchange="this.form.submit()">
      <option value="">All Status</option>
      @foreach(['pending','approved','rejected','suspended'] as $s)
      <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
      @endforeach
    </select>
    <input type="text" name="search" class="input" placeholder="Search name / phone…" value="{{ request('search') }}" style="width:200px">
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Driver</th><th>Phone</th><th>Vehicle</th><th>Status</th>
          <th>Trips</th><th>Rating</th><th>Joined</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($drivers as $d)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <img src="{{ $d->avatar_url ? asset('storage/'.$d->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($d->full_name).'&background=1F1F3E&color=666&size=40&bold=true' }}"
                   style="width:32px;height:32px;border-radius:8px;object-fit:cover">
              <div>
                <p style="font-size:13px;font-weight:700">{{ $d->full_name }}</p>
                <p style="font-size:11px;color:var(--text3)">{{ $d->email }}</p>
              </div>
            </div>
          </td>
          <td class="text-sm text-muted">{{ $d->phone }}</td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ $d->driverProfile?->vehicle_model ?? '—' }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ $d->driverProfile?->vehicle_number }}</p>
          </td>
          <td>
            <span class="badge {{ match($d->driverProfile?->status??'') {
              'approved'=>'badge-green','rejected'=>'badge-red','suspended'=>'badge-red',default=>'badge-amber'
            } }}">
              {{ ucfirst($d->driverProfile?->status??'pending') }}
            </span>
          </td>
          <td class="text-sm">{{ $d->driverProfile?->total_trips ?? 0 }}</td>
          <td class="text-sm">{{ $d->avg_rating ? '⭐ '.$d->avg_rating : '—' }}</td>
          <td class="text-xs text-muted">{{ $d->created_at->format('d M Y') }}</td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <a href="{{ route('admin.driver-detail', $d->id) }}" class="btn btn-blue btn-xs">View</a>
              @if($d->driverProfile?->status === 'pending')
              <form method="POST" action="{{ route('admin.drivers.approve', $d->id) }}" style="display:inline">
                @csrf <button type="submit" class="btn btn-green btn-xs">✓</button>
              </form>
              <form method="POST" action="{{ route('admin.drivers.reject', $d->id) }}" style="display:inline">
                @csrf <button type="submit" class="btn btn-red btn-xs" onclick="return confirm('Reject?')">✗</button>
              </form>
              @elseif($d->driverProfile?->status === 'approved')
              <form method="POST" action="{{ route('admin.drivers.suspend', $d->id) }}" style="display:inline">
                @csrf <button type="submit" class="btn btn-amber btn-xs" onclick="return confirm('Suspend?')">⏸</button>
              </form>
              @elseif($d->driverProfile?->status === 'suspended')
              <form method="POST" action="{{ route('admin.drivers.approve', $d->id) }}" style="display:inline">
                @csrf <button type="submit" class="btn btn-green btn-xs">▶ Reactivate</button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text3);padding:36px">No drivers found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $drivers->withQueryString()->links() }}</div>
</div>
@endsection
