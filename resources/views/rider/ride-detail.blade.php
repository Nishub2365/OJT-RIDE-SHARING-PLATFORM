@extends('layouts.app')
@section('title','Ride Detail')
@push('styles')
<style>
  #detailMap { height: 280px; border-radius: 12px; }
  .leaflet-control-attribution { display: none !important; }
  .leaflet-routing-container { display: none !important; }
  .info-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border); }
  .info-row:last-child { border-bottom:none; }
  .info-label { font-size:12px; font-weight:600; color:var(--text3); }
  .info-value { font-size:13px; font-weight:700; color:var(--text1); text-align:right; max-width:60%; }
  .timeline-dot { width:12px;height:12px;border-radius:50%;flex-shrink:0;border:2px solid var(--dark) }
  .timeline-line { width:2px;flex:1;min-height:20px;background:var(--border) }
</style>
@endpush
@section('content')
@php
  $statusColors = ['pending'=>'var(--amber)','accepted'=>'var(--blue)','in_progress'=>'var(--brand)','completed'=>'var(--green)','cancelled'=>'var(--red)'];
  $statusColor = $statusColors[$ride->status] ?? 'var(--text2)';
  $vIcons = ['bike'=>'🏍️','auto'=>'🛺','car'=>'🚗','suv'=>'🚙','truck'=>'🚚'];
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap">
  <a href="{{ route('rider.history') }}" class="btn btn-ghost btn-sm">← Back</a>
  <div class="page-title">Ride #{{ $ride->id }}</div>
  <span class="badge" style="background:rgba(255,85,0,.1);color:{{ $statusColor }}">
    {{ strtoupper(str_replace('_',' ',$ride->status)) }}
  </span>
  @if(in_array($ride->status,['pending','scheduled']))
  <a href="{{ route('rider.edit-ride',$ride->id) }}" class="btn btn-amber btn-sm" style="margin-left:auto">✏️ Edit Ride</a>
  @endif
</div>

<div class="grid-2" style="align-items:start;gap:16px">
  {{-- Left: Details --}}
  <div style="display:flex;flex-direction:column;gap:14px">

    {{-- Route --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">📍 Route</div>
      <div style="display:flex;flex-direction:column;gap:0">
        <div style="display:flex;gap:14px;align-items:start">
          <div style="display:flex;flex-direction:column;align-items:center;padding-top:2px">
            <div class="timeline-dot" style="background:var(--green)"></div>
            <div class="timeline-line"></div>
          </div>
          <div style="flex:1;padding-bottom:14px">
            <p style="font-size:11px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Pickup</p>
            <p style="font-size:13px;color:var(--text1);line-height:1.5">{{ $ride->pickup_address }}</p>
          </div>
        </div>
        <div style="display:flex;gap:14px;align-items:start">
          <div style="display:flex;flex-direction:column;align-items:center;padding-top:2px">
            <div class="timeline-dot" style="background:var(--brand)"></div>
          </div>
          <div style="flex:1">
            <p style="font-size:11px;font-weight:700;color:var(--brand);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px">Destination</p>
            <p style="font-size:13px;color:var(--text1);line-height:1.5">{{ $ride->destination_address }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Map --}}
    @if($ride->pickup_lat && $ride->destination_lat)
    <div class="card" style="padding:12px;overflow:hidden">
      <div id="detailMap"></div>
    </div>
    @endif

    {{-- Fare Details --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">💰 Fare Details <span class="npr-tag">NPR</span></div>
      <div class="info-row">
        <span class="info-label">Vehicle Type</span>
        <span class="info-value">{{ $vIcons[$ride->vehicle_category] ?? '' }} {{ ucfirst($ride->vehicle_category) }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Offered Fare</span>
        <span class="info-value" style="color:var(--text2)">NPR {{ number_format($ride->offered_fare) }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Agreed Fare</span>
        <span class="info-value" style="color:var(--brand);font-size:16px">
          {{ $ride->agreed_fare ? 'NPR '.number_format($ride->agreed_fare) : '—' }}
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">Payment Method</span>
        <span class="info-value">{{ ucfirst($ride->payment_method) }}</span>
      </div>
      @if($ride->promo_code)
      <div class="info-row">
        <span class="info-label">Promo Code</span>
        <span class="info-value" style="color:var(--green)">🎁 {{ $ride->promo_code }}</span>
      </div>
      @endif
      @if($ride->discount_amount > 0)
      <div class="info-row">
        <span class="info-label">Discount Applied</span>
        <span class="info-value" style="color:var(--green)">− NPR {{ number_format($ride->discount_amount) }}</span>
      </div>
      @endif
    </div>

    {{-- Timeline --}}
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">🕐 Trip Timeline</div>
      <div style="font-size:12px;color:var(--text3);line-height:2;display:flex;flex-direction:column;gap:4px">
        <div class="info-row">
          <span class="info-label">Requested</span>
          <span class="info-value">{{ $ride->created_at->format('d M Y, h:i A') }}</span>
        </div>
        @if($ride->scheduled_at)
        <div class="info-row">
          <span class="info-label">Scheduled For</span>
          <span class="info-value" style="color:var(--purple)">{{ \Carbon\Carbon::parse($ride->scheduled_at)->format('d M Y, h:i A') }}</span>
        </div>
        @endif
        @if($ride->status === 'completed')
        <div class="info-row">
          <span class="info-label">Completed</span>
          <span class="info-value" style="color:var(--green)">{{ $ride->updated_at->format('d M Y, h:i A') }}</span>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Right: Driver & Actions --}}
  <div style="display:flex;flex-direction:column;gap:14px">
    {{-- Driver info --}}
    @if($ride->driver)
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">🧑‍✈️ Your Driver</div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
        <img src="{{ $ride->driver->avatar_url ? asset('storage/'.$ride->driver->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($ride->driver->full_name).'&background=FF5500&color=fff&size=80&bold=true' }}"
          style="width:56px;height:56px;border-radius:14px;object-fit:cover">
        <div>
          <p style="font-size:16px;font-weight:900">{{ $ride->driver->full_name }}</p>
          <p style="font-size:12px;color:var(--text3);margin-top:2px">⭐ {{ number_format($ride->driver->avg_rating ?? 0, 1) }} rating</p>
        </div>
        @if($ride->status === 'in_progress')
        <a href="tel:+977{{ $ride->driver->phone }}" class="btn btn-green btn-sm" style="margin-left:auto">
          📞 Call
        </a>
        @endif
      </div>
      @if($ride->driver->driverProfile)
      <div class="info-row">
        <span class="info-label">Vehicle</span>
        <span class="info-value">{{ $vIcons[$ride->driver->driverProfile->vehicle_type] ?? '🚗' }} {{ $ride->driver->driverProfile->vehicle_model }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Plate Number</span>
        <span class="info-value">{{ $ride->driver->driverProfile->vehicle_number }}</span>
      </div>
      @endif
    </div>
    @endif

    {{-- Bids --}}
    @if($ride->bids && $ride->bids->count() > 0)
    <div class="card">
      <div class="card-title" style="margin-bottom:14px">💬 Driver Bids ({{ $ride->bids->count() }})</div>
      @foreach($ride->bids as $bid)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--purple));
          display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
          {{ strtoupper(substr($bid->driver->first_name ?? 'D', 0, 1)) }}
        </div>
        <div style="flex:1">
          <p style="font-size:13px;font-weight:700">{{ $bid->driver->full_name }}</p>
          <p style="font-size:11px;color:var(--text3)">{{ $bid->message ?? 'Ready to ride' }}</p>
        </div>
        <div style="text-align:right;flex-shrink:0">
          <p style="font-size:16px;font-weight:900;color:var(--brand)">NPR {{ number_format($bid->bid_amount) }}</p>
          <span class="badge badge-{{ $bid->status === 'accepted' ? 'green' : 'muted' }}" style="font-size:10px">{{ ucfirst($bid->status) }}</span>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    {{-- Actions --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:12px">⚡ Actions</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        @if($ride->status === 'in_progress')
        <a href="{{ route('rider.active-ride',$ride->id) }}" class="btn btn-brand" style="justify-content:center">
          📍 Track Live →
        </a>
        @endif
        @if($ride->status === 'completed' && !$ride->ratings->where('rater_id',auth()->id())->count())
        <a href="{{ route('rider.rate',$ride->id) }}" class="btn btn-amber" style="justify-content:center">
          ⭐ Rate Driver
        </a>
        @endif
        @if(in_array($ride->status,['pending','scheduled']))
        <a href="{{ route('rider.edit-ride',$ride->id) }}" class="btn btn-ghost" style="justify-content:center">
          ✏️ Edit Ride Details
        </a>
        @endif
        @if($ride->status === 'completed')
        <a href="{{ route('rider.share-trip',$ride->id) }}" class="btn btn-ghost" style="justify-content:center">
          🔗 Share Trip
        </a>
        @endif
        @if(in_array($ride->status,['pending','scheduled']) && !$ride->driver_id)
        <form method="POST" action="{{ route('rider.cancel-ride',$ride->id) }}"
          onsubmit="return confirm('Cancel this ride?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-red" style="width:100%;justify-content:center">
            ✕ Cancel Ride
          </button>
        </form>
        @endif
        <a href="{{ route('rider.history') }}" class="btn btn-ghost" style="justify-content:center">
          ← All Trips
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script>
@if($ride->pickup_lat && $ride->destination_lat)
const map = L.map('detailMap', { attributionControl:false, zoomControl:true })
  .setView([{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}], 13);
addLeafletDarkTiles(map);
if (map.attributionControl) map.removeControl(map.attributionControl);

function markerIcon(color, label) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${color};color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5);font-weight:900">${label}</div>`,
    iconSize:[28,28], iconAnchor:[14,14]
  });
}

L.marker([{{ $ride->pickup_lat }},{{ $ride->pickup_lng }}], {icon:markerIcon('#22c55e','P')}).addTo(map)
  .bindPopup('📍 Pickup').openPopup();
L.marker([{{ $ride->destination_lat }},{{ $ride->destination_lng }}], {icon:markerIcon('#FF5500','D')}).addTo(map)
  .bindPopup('🏁 Destination');

L.Routing.control({
  waypoints: [
    L.latLng({{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}),
    L.latLng({{ $ride->destination_lat }}, {{ $ride->destination_lng }})
  ],
  routeWhileDragging:false, addWaypoints:false, draggableWaypoints:false,
  createMarker: () => null,
  lineOptions: { styles: [{ color:'#FF5500', weight:4, opacity:.8 }] },
  router: L.Routing.osrmv1({ serviceUrl:'https://router.project-osrm.org/route/v1', profile:'driving' })
}).addTo(map);
@endif
</script>
@endpush
