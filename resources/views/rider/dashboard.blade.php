@extends('layouts.app')
@section('title','My Dashboard')
@push('styles')
<style>
.quick-link{display:flex;align-items:center;gap:12px;padding:13px 14px;
  background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:12px;
  text-decoration:none;transition:.18s;cursor:pointer}
.quick-link:hover{border-color:rgba(255,85,0,.3);background:rgba(255,85,0,.04);transform:translateX(3px)}
.ride-row{display:flex;align-items:start;justify-content:space-between;padding:12px 0;
  border-bottom:1px solid var(--border);gap:10px;flex-wrap:wrap}
.ride-row:last-child{border-bottom:none}
</style>
@endpush
@section('content')
@php $user = auth()->user(); @endphp

{{-- Active ride banner --}}
@if($activeRide)
<div style="background:rgba(255,85,0,.08);border:1px solid rgba(255,85,0,.3);border-radius:16px;padding:18px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <p style="font-size:15px;font-weight:900;color:var(--brand)">🚗 Active Ride #{{ $activeRide->id }}</p>
    <p style="font-size:13px;color:var(--text2);margin-top:4px">
      {{ ucfirst(str_replace('_',' ',$activeRide->status)) }}
      @if($activeRide->driver) · Driver: {{ $activeRide->driver->full_name }}@endif
    </p>
    <p style="font-size:12px;color:var(--text3);margin-top:3px">
      {{-- FIXED HERE --}}
      📍 {{ \Illuminate\Support\Str::limit($activeRide->pickup_address, 40) }} → 🏁 {{ \Illuminate\Support\Str::limit($activeRide->destination_address, 40) }}
    </p>
  </div>
  <a href="{{ route('rider.active-ride',$activeRide->id) }}" class="btn btn-brand" style="padding:12px 22px">
    📍 Track Live →
  </a>
</div>
@endif

{{-- Welcome --}}
<div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
  <div>
    <div class="page-title">Hey, {{ $user->first_name ?? 'Rider' }}! 👋</div>
    <div class="page-desc">{{ now()->format('l, d M Y') }} · Jhapa, Nepal</div>
  </div>
  <a href="{{ route('rider.request-ride') }}" class="btn btn-brand" style="padding:14px 26px;font-size:15px">
    🚗 Book a Ride
  </a>
</div>

{{-- Stats --}}
<div class="grid-4 anim-stagger" style="margin-bottom:24px">
  <div class="stat-card card-lift">
    <div class="stat-label">💳 Wallet Balance</div>
    <div class="stat-val" style="color:var(--green)">NPR {{ number_format($user->wallet_balance) }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">🚗 Total Rides</div>
    <div class="stat-val" style="color:var(--blue)" data-count="{{ $totalRides }}">0</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">📅 This Month</div>
    <div class="stat-val" style="color:var(--brand)" data-count="{{ $monthRides }}">0</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">⭐ Your Rating</div>
    <div class="stat-val" style="color:var(--amber)">
      {{ $user->avg_rating ? '⭐ '.$user->avg_rating : 'New' }}
    </div>
  </div>
</div>

<div class="grid-2" style="align-items:start">
  {{-- Recent Rides --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">🕐 Recent Trips</div>
      <a href="{{ route('rider.history') }}" class="btn btn-ghost btn-xs">View All →</a>
    </div>

    @forelse($recentRides as $ride)
    <div class="ride-row">
      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap">
          <span class="badge {{ $ride->status==='completed'?'badge-green':($ride->status==='cancelled'?'badge-red':'badge-blue') }}">
            {{ ucfirst(str_replace('_',' ',$ride->status)) }}
          </span>
          <span class="badge badge-muted">{{ $ride->vehicle_category }}</span>
          <span style="font-size:11px;color:var(--text3)">{{ $ride->created_at->format('d M Y') }}</span>
        </div>
        {{-- FIXED HERE --}}
        <p style="font-size:12px;font-weight:600;color:var(--text2)">📍 {{ \Illuminate\Support\Str::limit($ride->pickup_address,35) }}</p>
        <p style="font-size:11px;color:var(--text3)">🏁 {{ \Illuminate\Support\Str::limit($ride->destination_address,35) }}</p>
      </div>
      <div style="text-align:right;flex-shrink:0">
        <p style="font-size:15px;font-weight:900;color:var(--brand)">NPR {{ number_format($ride->agreed_fare ?? $ride->offered_fare) }}</p>
        <a href="{{ route('rider.ride-detail',$ride->id) }}" class="btn btn-ghost btn-xs" style="margin-top:6px">Details</a>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:32px;color:var(--text3)">
      <p style="font-size:36px;margin-bottom:10px">🚗</p>
      <p style="font-size:14px;font-weight:700;margin-bottom:6px">No trips yet</p>
      <p style="font-size:13px">Book your first ride and explore Jhapa!</p>
    </div>
    @endforelse
  </div>

  {{-- Quick Actions --}}
  <div style="display:flex;flex-direction:column;gap:14px">
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">⚡ Quick Actions</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        @foreach([
          ['rider.request-ride', '🚗', 'Book a Ride',     'Find drivers near you in Jhapa'],
          ['rider.schedule',     '📅', 'Schedule Ride',   'Plan ahead up to 7 days'],
          ['delivery.create',    '📦', 'Send Package',    'Documents, parcels & freight'],
          ['wallet.index',       '💳', 'My Wallet',       'NPR '.number_format($user->wallet_balance).' balance'],
          ['rider.history',      '🕐', 'Trip History',    'View all past rides'],
          ['support.index',      '🎧', 'Support',         'Report issues or get help'],
        ] as [$route,$icon,$label,$desc])
        <a href="{{ route($route) }}" class="quick-link">
          <span style="font-size:22px;width:30px;text-align:center;flex-shrink:0">{{ $icon }}</span>
          <div style="flex:1;min-width:0">
            <p style="font-size:13px;font-weight:700;color:var(--text1)">{{ $label }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ $desc }}</p>
          </div>
          <span style="color:var(--text3);font-size:14px">›</span>
        </a>
        @endforeach
      </div>
    </div>

    {{-- Promo banner --}}
    <div style="background:linear-gradient(135deg,rgba(255,85,0,.15),rgba(255,122,48,.08));border:1px solid rgba(255,85,0,.2);border-radius:16px;padding:18px;text-align:center">
      <p style="font-size:22px;margin-bottom:8px">🎁</p>
      <p style="font-size:14px;font-weight:900;margin-bottom:4px">Use Code <span style="color:var(--brand)">BROCAR20</span></p>
      <p style="font-size:12px;color:var(--text3)">Get 20% off your next ride!</p>
    </div>
  </div>
</div>
@endsection