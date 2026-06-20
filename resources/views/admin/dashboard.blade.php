@extends('layouts.app')
@section('title','Admin Dashboard')
@push('styles')
<style>#rideChart{height:200px}#revenueChart{height:200px}</style>
@endpush
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div>
    <div class="page-title">🏠 Admin Dashboard</div>
    <div class="page-desc">{{ now()->format('l, d M Y · H:i') }}</div>
  </div>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <form method="POST" action="{{ route('admin.broadcast') }}">
      @csrf
      <div style="display:flex;gap:8px">
        <input type="text" name="message" class="input" placeholder="Broadcast to all users…" style="width:260px">
        <button type="submit" class="btn btn-brand btn-sm">📢 Send</button>
      </div>
    </form>
    <form method="POST" action="{{ route('auth.logout') }}">
      @csrf
      <button type="submit" class="btn btn-ghost btn-sm">🚪 Logout</button>
    </form>
  </div>
</div>

{{-- Key Metrics --}}
<div class="grid-4" style="margin-bottom:20px">
  @foreach([
    ['👥','Total Users',     $stats['total_users']??0,    'var(--blue)'],
    ['🧑‍✈️','Active Drivers',  $stats['active_drivers']??0, 'var(--green)'],
    ['🚗','Rides Today',     $stats['rides_today']??0,    'var(--brand)'],
    ['💰','Revenue Today',  "NPR".number_format($stats['revenue_today']??0), 'var(--amber)'],
  ] as [$icon,$label,$val,$color])
  <div class="stat-card">
    <div class="stat-label">{{ $icon }} {{ $label }}</div>
    <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
  </div>
  @endforeach
</div>

<div class="grid-4" style="margin-bottom:20px">
  @foreach([
    ['🏁','Completed',     $stats['completed']??0,  'var(--green)'],
    ['❌','Cancelled',     $stats['cancelled']??0,  'var(--red)'],
    ['⏳','Pending Drivers',$stats['pending_drivers']??0, 'var(--amber)'],
    ['🆘','Open SOS',      $stats['open_sos']??0,   'var(--red)'],
  ] as [$icon,$label,$val,$color])
  <div class="stat-card">
    <div class="stat-label">{{ $icon }} {{ $label }}</div>
    <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
  </div>
  @endforeach
</div>

<div class="grid-2" style="margin-bottom:20px">
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">📈 Rides (7 days)</div>
    <canvas id="rideChart"></canvas>
  </div>
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">💰 Revenue (7 days)</div>
    <canvas id="revenueChart"></canvas>
  </div>
</div>

<div class="grid-2">
  {{-- Recent rides --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">🚗 Recent Rides</div>
      <a href="{{ route('admin.rides') }}" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>Rider</th><th>Fare</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($recentRides as $r)
          <tr>
            <td class="text-xs text-muted">#{{ $r->id }}</td>
            <td class="text-sm">{{ $r->rider->full_name }}</td>
            <td style="font-weight:700">NPR {{ number_format($r->agreed_fare??$r->offered_fare) }}</td>
            <td>
              <span class="badge {{ match($r->status) {
                'completed'=>'badge-green','cancelled'=>'badge-red',
                'in_progress'=>'badge-blue',default=>'badge-muted'
              } }}">{{ ucfirst($r->status) }}</span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- SOS Alerts --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">🆘 Active SOS Alerts</div>
      <a href="{{ route('admin.sos') }}" class="btn btn-red btn-xs">Manage</a>
    </div>
    @forelse($sosList as $sos)
    <div style="background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.15);border-radius:10px;padding:12px;margin-bottom:8px">
      <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:6px">
        <div>
          <p style="font-size:13px;font-weight:700;color:var(--red)">🆘 {{ $sos->user->full_name }}</p>
          <p style="font-size:11px;color:var(--text3)">{{ $sos->created_at->diffForHumans() }}
            @if($sos->lat) · <a href="https://maps.google.com/?q={{ $sos->lat }},{{ $sos->lng }}" target="_blank" style="color:var(--blue)">Map</a>@endif
          </p>
        </div>
        <form method="POST" action="{{ route('admin.sos.resolve',$sos->id) }}">
          @csrf <button type="submit" class="btn btn-green btn-xs">✓ Resolve</button>
        </form>
      </div>
    </div>
    @empty
    <p style="text-align:center;color:var(--text3);font-size:13px;padding:20px">✅ No active SOS alerts</p>
    @endforelse
  </div>
</div>
@endsection
@push('scripts')
<script>
const gc = 'rgba(255,255,255,.04)', tc = '#666';
[['rideChart','bar',@json($rideLabels??[]),@json($rideData??[]),'rgba(56,189,248,.6)','#38BDF8'],
 ['revenueChart','line',@json($revLabels??[]),@json($revData??[]),'rgba(255,85,0,.12)','#FF5500']
].forEach(([id,type,labels,data,bg,bc]) => {
  const ctx = document.getElementById(id)?.getContext('2d');
  if (!ctx) return;
  new Chart(ctx, {
    type,
    data: { labels, datasets: [{ data, backgroundColor: bg, borderColor: bc,
      borderRadius: type==='bar'?5:undefined, fill: type==='line', tension: type==='line'?.4:undefined, borderWidth: 2 }]},
    options: { responsive: true, plugins:{ legend:{display:false} },
      scales:{ x:{grid:{color:gc},ticks:{color:tc,font:{size:10}}}, y:{grid:{color:gc},ticks:{color:tc,font:{size:10}}}}}
  });
});
</script>
@endpush