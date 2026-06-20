@extends('layouts.app')
@section('title','Active Ride')
@push('styles')
<style>.leaflet-routing-container{display:none!important}.leaflet-control-attribution{display:none!important}</style>
<style>
  #riderMap { height: 280px; border-radius: 12px; overflow: hidden; }
  .status-step { display: flex; align-items: center; gap: 10px; padding: 10px 0; }
  .step-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
  .step-line { width: 2px; height: 20px; margin-left: 15px; background: var(--border); }
  .chat-box { height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding: 8px; }
  .chat-msg { max-width: 78%; padding: 8px 12px; border-radius: 12px; font-size: 12px; line-height: 1.4; }
  .chat-mine  { align-self: flex-end; background: rgba(255,85,0,.18); }
  .chat-other { align-self: flex-start; background: var(--card2); }
</style>
@endpush
@section('content')

@if(!isset($ride) || !$ride)
{{-- No active ride --}}
<div style="max-width:480px;margin:0 auto;text-align:center;padding:60px 20px">
  <div style="font-size:64px;margin-bottom:16px">🚗</div>
  <h2 style="font-size:22px;font-weight:900;margin-bottom:8px">No Active Ride</h2>
  <p style="font-size:14px;color:var(--text2);margin-bottom:24px">You don't have an active ride right now.</p>
  <a href="{{ route('rider.request-ride') }}" class="btn btn-brand" style="margin-right:10px">Book a Ride →</a>
  <a href="{{ route('rider.dashboard') }}" class="btn btn-ghost">Dashboard</a>
</div>

@else
{{-- Active ride --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
  <div>
    <div class="page-title">Ride #{{ $ride->id }}</div>
    <div class="page-desc">Booked {{ $ride->created_at->diffForHumans() }}</div>
  </div>
  <span class="badge {{ match($ride->status) {
    'pending'       => 'badge-muted',
    'accepted'      => 'badge-blue',
    'driver_arrived'=> 'badge-amber',
    'in_progress'   => 'badge-green',
    'completed'     => 'badge-green',
    'cancelled'     => 'badge-red',
    default         => 'badge-muted'
  } }}" style="font-size:13px;padding:7px 16px">
    {{ str_replace('_',' ',ucfirst($ride->status)) }}
  </span>
</div>

<div class="grid-2" style="align-items:start">
{{-- Left column --}}
<div style="display:flex;flex-direction:column;gap:14px">

  {{-- Map --}}
  <div class="card" style="padding:12px">
    <div id="riderMap"></div>
  </div>

  {{-- Status steps --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Trip Progress</div>
    @php
    $steps = [
      ['pending',        '🕐', 'Searching for drivers', 'Finding the best driver for you…'],
      ['accepted',       '✅', 'Driver Assigned',       'Your driver is heading to your location.'],
      ['driver_arrived', '📍', 'Driver Arrived',        'Your driver is at the pickup point.'],
      ['in_progress',    '🚗', 'Ride in Progress',      'Sit back and enjoy your ride!'],
      ['completed',      '🏁', 'Completed',             'You have reached your destination.'],
    ];
    $statusOrder = ['pending'=>0,'accepted'=>1,'driver_arrived'=>2,'in_progress'=>3,'completed'=>4];
    $current = $statusOrder[$ride->status] ?? 0;
    @endphp
    @foreach($steps as $i => [$key, $icon, $label, $desc])
    <div class="status-step" style="{{ $i > 0 ? 'margin-top:0' : '' }}">
      <div class="step-circle" style="{{ $i <= $current ? 'background:rgba(255,85,0,.15);color:var(--brand)' : 'background:rgba(255,255,255,.04);color:var(--text3)' }}">
        {{ $icon }}
      </div>
      <div>
        <p style="font-size:13px;font-weight:{{ $i === $current ? '800' : '500' }};color:{{ $i <= $current ? 'var(--text1)' : 'var(--text3)' }}">{{ $label }}</p>
        @if($i === $current)
        <p style="font-size:11px;color:var(--text3)">{{ $desc }}</p>
        @endif
      </div>
    </div>
    @if(!$loop->last)<div class="step-line"></div>@endif
    @endforeach
  </div>

  {{-- Cancel button (only pending/accepted) --}}
  @if(in_array($ride->status, ['pending','accepted']))
  <form method="POST" action="{{ route('rider.cancel-ride',$ride->id) }}" onsubmit="return confirm('Cancel this ride?')">
    @csrf
    <button type="submit" class="btn btn-red" style="width:100%;justify-content:center">❌ Cancel Ride</button>
  </form>
  @endif

  {{-- Rate link after completed --}}
  @if($ride->status === 'completed')
  @php $alreadyRated = $ride->ratings->where('rater_id', auth()->id())->first(); @endphp
  @if(!$alreadyRated)
  <a href="{{ route('rider.rate',$ride->id) }}" class="btn btn-brand" style="width:100%;justify-content:center">⭐ Rate Your Driver</a>
  @else
  <div class="btn btn-ghost" style="width:100%;justify-content:center;cursor:default">✅ Already Rated</div>
  @endif
  @endif
</div>

{{-- Right column --}}
<div style="display:flex;flex-direction:column;gap:14px">

  {{-- Driver info (if assigned) --}}
  @if($ride->driver)
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Your Driver</div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <img src="{{ $ride->driver->avatar_url ? asset('storage/'.$ride->driver->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($ride->driver->full_name).'&background=FF5500&color=fff&size=80&bold=true' }}"
           style="width:52px;height:52px;border-radius:12px;object-fit:cover">
      <div>
        <p style="font-size:15px;font-weight:800">{{ $ride->driver->full_name }}</p>
        <p style="font-size:12px;color:var(--text3)">⭐ {{ $ride->driver->avg_rating ?? 'New' }} · {{ $ride->driver->driverProfile?->vehicle_model }}</p>
        <p style="font-size:12px;color:var(--text3)">🚗 {{ $ride->driver->driverProfile?->vehicle_number }}</p>
      </div>
    </div>
    <div style="display:flex;gap:8px">
      <a href="tel:{{ $ride->driver->phone }}" class="btn btn-blue btn-sm" style="flex:1;justify-content:center">📞 Call</a>
      <a href="{{ route('rider.share-trip',$ride->id) }}" class="btn btn-ghost btn-sm" style="flex:1;justify-content:center">📡 Share</a>
    </div>
  </div>

  {{-- Chat --}}
  <div class="card" style="padding:14px">
    <div class="card-title" style="margin-bottom:8px">💬 Chat with Driver</div>
    <div class="chat-box" id="chatBox">
      <p style="text-align:center;font-size:12px;color:var(--text3)">Start chatting…</p>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <input type="text" id="chatInput" class="input" placeholder="Type a message…" style="flex:1;font-size:13px" onkeydown="if(event.key==='Enter')sendMsg()">
      <button onclick="sendMsg()" class="btn btn-brand btn-sm">Send</button>
    </div>
  </div>
  @endif

  {{-- Bids section (only when pending) --}}
  @if($ride->status === 'pending')
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏷️ Driver Bids</div>
      <span class="badge badge-muted" id="bidCount">{{ $ride->bids->where('status','pending')->count() }} bids</span>
    </div>
    <div id="bidsContainer">
      @forelse($ride->bids->where('status','pending')->sortBy('bid_amount') as $bid)
        @include('rider.partials.bid-card', ['bid'=>$bid,'rideId'=>$ride->id])
      @empty
      <p style="text-align:center;color:var(--text3);font-size:13px;padding:20px">Waiting for drivers to bid…</p>
      @endforelse
    </div>
  </div>
  @endif

  {{-- Fare + route summary --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Trip Summary</div>
    <div style="font-size:13px;display:flex;flex-direction:column;gap:8px">
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Offered Fare</span><span style="font-weight:700">NPR {{ number_format($ride->offered_fare) }}</span></div>
      @if($ride->agreed_fare)
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Agreed Fare</span><span style="font-weight:800;color:var(--green);font-size:15px">NPR {{ number_format($ride->agreed_fare) }}</span></div>
      @endif
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Vehicle</span><span>{{ ucfirst($ride->vehicle_category) }}</span></div>
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Payment</span><span>{{ ucfirst($ride->payment_method) }}</span></div>
      <hr class="divider">
      <div>
        <p class="text-muted" style="font-size:11px;margin-bottom:3px">PICKUP</p>
        <p style="font-size:12px">{{ $ride->pickup_address }}</p>
      </div>
      <div>
        <p class="text-muted" style="font-size:11px;margin-bottom:3px">DESTINATION</p>
        <p style="font-size:12px">{{ $ride->destination_address }}</p>
      </div>
    </div>
  </div>

  {{-- SOS --}}
  <div class="card" style="text-align:center">
    <p style="font-size:11px;color:var(--text3);margin-bottom:10px">Emergency? Alert our safety team immediately.</p>
    <button class="sos-btn" onclick="triggerSOS()">🆘 SOS EMERGENCY</button>
  </div>
</div>
</div>
@endif
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
@if(isset($ride) && $ride)
const RIDE_ID = {{ $ride->id }};
let lastMsgId = 0;
let driverMarker = null;

// Map
const map = L.map('riderMap', { attributionControl: false });
addLeafletDarkTiles(map);
@if($ride->pickup_lat)
  map.setView([{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}], 14);
  const pickIcon = L.divIcon({ className:'', html:'<div style="width:14px;height:14px;border-radius:50%;background:#22c55e;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>', iconSize:[14,14] });
  L.marker([{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}], { icon: pickIcon }).addTo(map).bindPopup('Pickup');
@else
  map.setView([27.7172, 85.3240], 13);
@endif
@if($ride->destination_lat)
  const dropIcon = L.divIcon({ className:'', html:'<div style="width:14px;height:14px;border-radius:50%;background:#FF5500;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>', iconSize:[14,14] });
  L.marker([{{ $ride->destination_lat }}, {{ $ride->destination_lng }}], { icon: dropIcon }).addTo(map).bindPopup('Destination');
@endif
@if($ride->pickup_lat && $ride->destination_lat)
// Draw road route
L.Routing.control({
  waypoints:[L.latLng({{ $ride->pickup_lat }},{{ $ride->pickup_lng }}),L.latLng({{ $ride->destination_lat }},{{ $ride->destination_lng }})],
  routeWhileDragging:false,addWaypoints:false,draggableWaypoints:false,createMarker:()=>null,
  lineOptions:{styles:[{color:'#FF5500',weight:4,opacity:.7}]},
  router:L.Routing.osrmv1({serviceUrl:'https://router.project-osrm.org/route/v1',profile:'driving'})
}).addTo(map);
@endif

// Driver live location poll
@if($ride->driver)
async function pollDriverLocation() {
  try {
    const r = await fetch('/rider/driver-location/{{ $ride->id }}');
    const d = await r.json();
    if (d.lat && d.lng) {
      const pos = [d.lat, d.lng];
      const drIcon = L.divIcon({ className:'', html:'<div style="width:18px;height:18px;border-radius:50%;background:#FF5500;border:3px solid #fff;box-shadow:0 4px 12px rgba(255,85,0,.5)"></div>', iconSize:[18,18] });
      if (driverMarker) { driverMarker.setLatLng(pos); } else { driverMarker = L.marker(pos, { icon: drIcon }).addTo(map).bindPopup('Driver'); }
    }
  } catch(e) {}
}
pollDriverLocation(); setInterval(pollDriverLocation, 5000);
@endif

// Bid polling (pending rides only)
@if($ride->status === 'pending')
async function pollBids() {
  try {
    const r = await fetch('/rider/bids/{{ $ride->id }}');
    const d = await r.json();
    document.getElementById('bidCount').textContent = d.count + ' bids';
    if (d.html) document.getElementById('bidsContainer').innerHTML = d.html;
  } catch(e) {}
}
pollBids(); setInterval(pollBids, 5000);
@endif

// Chat
async function pollChat() {
  try {
    const r = await fetch(`/chat/${RIDE_ID}/${lastMsgId}`);
    const msgs = await r.json();
    if (msgs.length) {
      const box = document.getElementById('chatBox');
      if (lastMsgId === 0 && msgs.length) box.innerHTML = '';
      msgs.forEach(m => {
        const el = document.createElement('div');
        el.className = 'chat-msg ' + (m.mine ? 'chat-mine' : 'chat-other');
        el.innerHTML = `${m.message}<br><span style="font-size:10px;opacity:.4">${m.time}</span>`;
        box.appendChild(el);
        lastMsgId = m.id;
      });
      box.scrollTop = box.scrollHeight;
    }
  } catch(e) {}
}
@if($ride->driver)
pollChat(); setInterval(pollChat, 4000);
@endif

async function sendMsg() {
  const input = document.getElementById('chatInput');
  const msg = input.value.trim();
  if (!msg) return;
  input.value = '';
  try {
    await fetch(`/chat/${RIDE_ID}`, {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ message: msg })
    });
    await pollChat();
  } catch(e) {}
}

// SOS
async function triggerSOS() {
  if (!confirm('Send SOS alert to emergency team?')) return;
  navigator.geolocation.getCurrentPosition(async pos => {
    await fetch('/sos', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ ride_id: RIDE_ID, lat: pos.coords.latitude, lng: pos.coords.longitude })
    });
    showToast('🆘 SOS sent! Help is on the way.', 'error');
  }, () => {
    fetch('/sos', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
      body: JSON.stringify({ ride_id: RIDE_ID })
    });
    showToast('🆘 SOS sent!', 'error');
  });
}
@endif
</script>
@endpush
