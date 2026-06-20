@extends('layouts.app')
@section('title','Driver Dashboard')
@push('styles')
<style>
  #earningsChart { height: 180px; }
  .ride-req-card {
    background:var(--card3); border:1px solid var(--border); border-radius:14px;
    padding:16px; margin-bottom:10px; transition:.2s; cursor:default;
  }
  .ride-req-card:hover { border-color:rgba(255,85,0,.3); }
  .veh-badge {
    display:inline-flex;align-items:center;gap:6px;padding:6px 14px;
    border-radius:50px;font-size:13px;font-weight:900;
    background:rgba(255,85,0,.1);color:var(--brand);border:1px solid rgba(255,85,0,.2);
  }
  .pulse-dot {
    width:10px;height:10px;border-radius:50%;
    background:var(--green);box-shadow:0 0 0 rgba(34,197,94,.4);
    animation:pulseDot 1.5s infinite;flex-shrink:0;
  }
  @keyframes pulseDot{0%{box-shadow:0 0 0 0 rgba(34,197,94,.5)}70%{box-shadow:0 0 0 8px rgba(34,197,94,0)}100%{box-shadow:0 0 0 0 rgba(34,197,94,0)}}
</style>
@endpush
@section('content')
@php
  use Illuminate\Support\Str;

  $profile = auth()->user()->driverProfile;
  $vType   = $profile?->vehicle_type ?? 'car';
  $vIcons  = ['bike'=>'🏍️','auto'=>'🛺','car'=>'🚗','suv'=>'🚙','truck'=>'🚚'];
  $vNames  = ['bike'=>'Bike','auto'=>'Auto Rickshaw','car'=>'Car','suv'=>'SUV','truck'=>'Truck'];
  $vBaseNPR = ['bike'=>80,'auto'=>100,'car'=>150,'suv'=>200,'truck'=>250];
@endphp

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div>
    <div class="page-title">🏠 Driver Dashboard</div>
    <div class="page-desc" style="display:flex;align-items:center;gap:10px;margin-top:6px">
      <span>{{ now()->format('l, d M Y') }}</span>
      {{-- Vehicle type badge --}}
      <span class="veh-badge">
        {{ $vIcons[$vType] ?? '🚗' }} {{ $vNames[$vType] ?? ucfirst($vType) }}
      </span>
      <span class="badge badge-muted">{{ $profile?->vehicle_number ?? '' }}</span>
    </div>
  </div>

  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    {{-- Online Toggle --}}
    <form method="POST" action="{{ route('driver.toggle-online') }}">
      @csrf
      <button type="submit" class="btn {{ $profile?->is_online ? 'btn-green' : 'btn-ghost' }}"
        style="padding:12px 22px;font-size:14px;gap:10px">
        @if($profile?->is_online)
          <div class="pulse-dot"></div> Online — Tap to Go Offline
        @else
          <div style="width:10px;height:10px;border-radius:50%;background:var(--text3);flex-shrink:0"></div>
          Offline — Tap to Go Online
        @endif
      </button>
    </form>

    {{-- Logout --}}
    <form method="POST" action="{{ route('auth.logout') }}">
      @csrf
      <button type="submit" class="btn btn-ghost btn-sm">🚪 Logout</button>
    </form>
  </div>
</div>

{{-- Active ride alert --}}
@if($activeRide)
<div style="background:rgba(255,85,0,.08);border:1px solid rgba(255,85,0,.25);border-radius:14px;padding:16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
  <div>
    <p style="font-size:14px;font-weight:800;color:var(--brand)">🚗 Active Ride #{{ $activeRide->id }}</p>
    <p style="font-size:12px;color:var(--text2);margin-top:3px">
      {{ $activeRide->rider->first_name ?? 'Rider' }} · {{ ucfirst($activeRide->status) }}
      · <strong style="color:var(--brand)">NPR {{ number_format($activeRide->agreed_fare ?: $activeRide->offered_fare) }}</strong>
    </p>
  </div>
  <a href="{{ route('driver.active-ride',$activeRide->id) }}" class="btn btn-brand btn-sm">Continue Ride →</a>
</div>
@endif

{{-- Stats --}}
<div class="grid-4 anim-stagger" style="margin-bottom:20px">
  @foreach([
    ['Today',      'NPR '.number_format($todayEarnings),   'var(--green)'],
    ['This Week',  'NPR '.number_format($weekEarnings),    'var(--blue)'],
    ['This Month', 'NPR '.number_format($monthEarnings),   'var(--brand)'],
    ['Total Trips', $profile?->total_trips ?? 0,           'var(--amber)'],
  ] as [$l,$v,$c])
  <div class="stat-card">
    <div class="stat-label">{{ $l }}</div>
    <div class="stat-val" style="color:{{ $c }}">{{ $v }}</div>
  </div>
  @endforeach
</div>

<div class="grid-2" style="align-items:start">
  {{-- Available rides (filtered by this driver's vehicle type) --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">📨 Available Rides</div>
        <p style="font-size:11px;color:var(--text3);margin-top:3px">
          Showing rides requesting a <strong style="color:var(--brand)">{{ $vNames[$vType] ?? ucfirst($vType) }}</strong>
        </p>
      </div>
      @if(!$profile?->is_online)
      <span class="badge badge-muted">Go Online to See</span>
      @else
      <span class="badge badge-green" id="rideCount">{{ $pendingRides->count() }} requests</span>
      @endif
    </div>

    @if(!$profile?->is_online)
    {{-- Offline state --}}
    <div style="text-align:center;padding:36px 20px;color:var(--text3)">
      <p style="font-size:40px;margin-bottom:12px">😴</p>
      <p style="font-size:15px;font-weight:700;margin-bottom:6px">You're Offline</p>
      <p style="font-size:13px">Go online to start receiving ride requests for your {{ $vNames[$vType] ?? ucfirst($vType) }}</p>
    </div>
    @else
    <div id="ridesList">
    @forelse($pendingRides as $ride)
    <div class="ride-req-card" id="ride-{{ $ride->id }}">
      <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px;flex-wrap:wrap;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="badge badge-muted">#{{ $ride->id }}</span>
          <span class="badge badge-brand">{{ $vIcons[$ride->vehicle_category] ?? '🚗' }} {{ ucfirst($ride->vehicle_category) }}</span>
          @if($ride->scheduled_at)
          <span class="badge badge-purple">📅 Scheduled</span>
          @endif
        </div>
        <span style="font-size:18px;font-weight:900;color:var(--brand)">NPR {{ number_format($ride->offered_fare) }}</span>
      </div>

      <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px">
        <div style="display:flex;align-items:start;gap:8px">
          <span style="color:var(--green);font-size:13px;flex-shrink:0;margin-top:1px">●</span>
          <p style="font-size:12px;color:var(--text2)">{{ Str::limit($ride->pickup_address, 55) }}</p>
        </div>
        <div style="width:1px;height:10px;background:var(--border);margin-left:6px"></div>
        <div style="display:flex;align-items:start;gap:8px">
          <span style="color:var(--brand);font-size:13px;flex-shrink:0;margin-top:1px">■</span>
          <p style="font-size:12px;color:var(--text3)">{{ Str::limit($ride->destination_address, 55) }}</p>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;font-size:11px;color:var(--text3)">
        <span>👤 {{ $ride->rider->first_name ?? 'Rider' }}</span>
        <span>·</span>
        <span>⭐ {{ number_format($ride->rider->avg_rating ?? 0, 1) }}</span>
        <span>·</span>
        <span>💳 {{ ucfirst($ride->payment_method) }}</span>
        <span>·</span>
        <span>🕐 {{ $ride->created_at->diffForHumans() }}</span>
      </div>

      <form method="POST" action="{{ route('driver.place-bid',$ride->id) }}"
        style="display:flex;gap:8px;align-items:center">
        @csrf
        <div style="flex:1;position:relative">
          <div style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--text3);font-weight:700;pointer-events:none">NPR</div>
          <input type="number" name="bid_amount" class="input"
            placeholder="{{ $vBaseNPR[$vType] ?? 100 }}"
            min="{{ $vBaseNPR[$vType] ?? 50 }}"
            value="{{ $ride->offered_fare }}"
            style="padding-left:46px;font-weight:800" required>
        </div>
        <button type="submit" class="btn btn-brand btn-sm" style="flex-shrink:0">Place Bid</button>
        <a href="{{ route('driver.active-ride', $ride->id) }}" class="btn btn-ghost btn-sm" style="flex-shrink:0">Details</a>
      </form>
    </div>
    @empty
    <div style="text-align:center;padding:36px 20px;color:var(--text3)">
      <p style="font-size:40px;margin-bottom:12px">🔍</p>
      <p style="font-size:15px;font-weight:700;margin-bottom:6px">No Pending Rides</p>
      <p style="font-size:13px">No one has requested a {{ $vNames[$vType] ?? ucfirst($vType) }} right now.<br>Refreshing every 15 seconds…</p>
    </div>
    @endforelse
    </div>
    @endif
  </div>

  {{-- Right column --}}
  <div style="display:flex;flex-direction:column;gap:14px">
    {{-- Earnings chart --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">📊 Weekly Earnings</div>
        <span class="badge badge-muted">NPR</span>
      </div>
      <canvas id="earningsChart"></canvas>
    </div>

    {{-- Vehicle info card --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">🚗 My Vehicle</div>
      <div style="display:flex;align-items:center;gap:16px">
        <div style="font-size:44px;line-height:1">{{ $vIcons[$vType] ?? '🚗' }}</div>
        <div style="flex:1">
          <p style="font-size:16px;font-weight:900;color:var(--text1)">{{ $vNames[$vType] ?? ucfirst($vType) }}</p>
          <p style="font-size:13px;color:var(--text2);margin-top:2px">{{ $profile?->vehicle_model ?? 'N/A' }}</p>
          <p style="font-size:12px;color:var(--text3);margin-top:4px">
            🔑 {{ $profile?->vehicle_number ?? 'No plate' }} · 🎨 {{ $profile?->vehicle_color ?? 'N/A' }}
          </p>
          <div style="margin-top:8px">
            <span style="font-size:11px;color:var(--text3)">Base fare:</span>
            <strong style="color:var(--brand);font-size:13px;margin-left:4px">NPR {{ $vBaseNPR[$vType] ?? 100 }}+</strong>
          </div>
        </div>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <a href="{{ route('driver.documents') }}" class="btn btn-ghost btn-sm" style="justify-content:center">📄 Docs</a>
          <a href="{{ route('driver.profile') }}" class="btn btn-ghost btn-sm" style="justify-content:center">✏️ Edit</a>
        </div>
      </div>
    </div>

    {{-- Quick links --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:12px">⚡ Quick Actions</div>
      <div style="display:flex;flex-direction:column;gap:6px">
        @foreach([
          ['driver.earnings',  '💰 View Earnings',   'btn-ghost'],
          ['driver.requests',  '📋 Bid History',     'btn-ghost'],
          ['support.index',    '🎧 Get Support',     'btn-ghost'],
        ] as [$route,$label,$cls])
        <a href="{{ route($route) }}" class="btn {{ $cls }} btn-sm" style="justify-content:start">{{ $label }}</a>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
// Earnings chart
const ctx = document.getElementById('earningsChart')?.getContext('2d');
if (ctx) new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($chartLabels ?? []),
    datasets: [{
      label: 'Earnings (NPR)',
      data: @json($chartData ?? []),
      backgroundColor: 'rgba(255,85,0,.5)',
      borderColor: '#FF5500',
      borderRadius: 6,
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: v => 'NPR ' + v.formattedValue } }
    },
    scales: {
      x: { grid:{ color:'rgba(255,255,255,.04)' }, ticks:{ color:'#666', fontSize:11 }},
      y: { grid:{ color:'rgba(255,255,255,.04)' }, ticks:{ color:'#666', fontSize:11, callback: v => 'NPR '+v }}
    }
  }
});

// Auto-refresh ride list every 15s when online
@if($profile?->is_online)
setInterval(() => {
  if(document.visibilityState === 'visible') {
    fetch(window.location.href)
      .then(r => r.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newList = doc.getElementById('ridesList');
        const newCount = doc.getElementById('rideCount');
        const curList = document.getElementById('ridesList');
        const curCount = document.getElementById('rideCount');
        if (newList && curList) curList.innerHTML = newList.innerHTML;
        if (newCount && curCount) curCount.textContent = newCount.textContent;
      }).catch(() => {});
  }
}, 15000);
@endif
</script>
@endpush