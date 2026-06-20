@extends('layouts.app')
@section('title','Active Ride')
@push('styles')
<style>.leaflet-routing-container{display:none!important}.leaflet-control-attribution{display:none!important}</style>
<style>
  #driverMap { height: 300px; border-radius: 12px; overflow: hidden; }
  .chat-box { height: 160px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; padding: 8px; }
  .chat-msg { max-width: 80%; padding: 8px 12px; border-radius: 12px; font-size: 12px; line-height: 1.4; }
  .chat-mine  { align-self: flex-end; background: rgba(255,85,0,.18); }
  .chat-other { align-self: flex-start; background: var(--card2); }
</style>
@endpush
@section('content')

@if(!isset($ride) || !$ride)
<div style="max-width:480px;margin:0 auto;text-align:center;padding:60px 20px">
  <div style="font-size:64px;margin-bottom:16px">😴</div>
  <h2 style="font-size:22px;font-weight:900;margin-bottom:8px">No Active Ride</h2>
  <p style="font-size:14px;color:var(--text2);margin-bottom:24px">You don't have an ongoing ride right now.</p>
  <a href="{{ route('driver.dashboard') }}" class="btn btn-brand">← Go to Dashboard</a>
</div>
@else

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">
  <div>
    <div class="page-title">Ride #{{ $ride->id }}</div>
    <div class="page-desc">{{ $ride->created_at->diffForHumans() }}</div>
  </div>
  <span class="badge {{ match($ride->status) {
    'accepted'=>'badge-blue','driver_arrived'=>'badge-amber',
    'in_progress'=>'badge-green','completed'=>'badge-green',
    default=>'badge-muted'
  } }}" style="font-size:13px;padding:7px 16px">
    {{ str_replace('_',' ', ucfirst($ride->status)) }}
  </span>
</div>

<div class="grid-2" style="align-items:start">
{{-- Left --}}
<div style="display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:12px">
    <div id="driverMap"></div>
  </div>

  {{-- Rider info --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Your Rider</div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
      <img src="{{ $ride->rider->avatar_url ? asset('storage/'.$ride->rider->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($ride->rider->full_name).'&background=1F1F3E&color=888&size=80&bold=true' }}"
           style="width:48px;height:48px;border-radius:12px">
      <div>
        <p style="font-size:15px;font-weight:800">{{ $ride->rider->full_name }}</p>
        <p style="font-size:12px;color:var(--text3)">⭐ {{ $ride->rider->avg_rating ?? 'New rider' }}</p>
      </div>
    </div>
    <div style="display:flex;gap:8px">
      <a href="tel:{{ $ride->rider->phone }}" class="btn btn-blue btn-sm" style="flex:1;justify-content:center">📞 Call Rider</a>
    </div>
  </div>

  {{-- Chat --}}
  <div class="card" style="padding:14px">
    <div class="card-title" style="margin-bottom:8px">💬 Chat</div>
    <div class="chat-box" id="chatBox">
      <p style="text-align:center;font-size:12px;color:var(--text3)">Chat with rider…</p>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px">
      <input type="text" id="chatInput" class="input" placeholder="Type a message…" style="flex:1;font-size:13px" onkeydown="if(event.key==='Enter')sendMsg()">
      <button onclick="sendMsg()" class="btn btn-brand btn-sm">Send</button>
    </div>
  </div>
</div>

{{-- Right --}}
<div style="display:flex;flex-direction:column;gap:14px">
  {{-- Trip details --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">📋 Trip Details</div>
    <div style="font-size:13px;display:flex;flex-direction:column;gap:8px">
      <div style="padding:10px;background:rgba(255,255,255,.02);border-radius:8px">
        <p style="font-size:11px;color:var(--text3);margin-bottom:3px">PICKUP</p>
        <p style="font-weight:600">{{ $ride->pickup_address }}</p>
      </div>
      <div style="padding:10px;background:rgba(255,255,255,.02);border-radius:8px">
        <p style="font-size:11px;color:var(--text3);margin-bottom:3px">DESTINATION</p>
        <p style="font-weight:600">{{ $ride->destination_address }}</p>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px;background:rgba(255,85,0,.06);border-radius:8px">
        <span class="text-muted">Agreed Fare</span>
        <span style="font-size:16px;font-weight:900;color:var(--brand)">NPR {{ number_format($ride->agreed_fare ?? $ride->offered_fare) }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px;background:rgba(255,255,255,.02);border-radius:8px">
        <span class="text-muted">Payment</span>
        <span>{{ ucfirst($ride->payment_method) }}</span>
      </div>
    </div>
  </div>

  {{-- Status actions --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">🎯 Update Status</div>
    <div style="display:flex;flex-direction:column;gap:8px">
      @if($ride->status === 'accepted')
      <form method="POST" action="{{ route('driver.update-ride-status', $ride->id) }}">
        @csrf <input type="hidden" name="status" value="driver_arrived">
        <button type="submit" class="btn btn-amber" style="width:100%;justify-content:center">📍 I Have Arrived</button>
      </form>
      @endif
      @if($ride->status === 'driver_arrived')
      <form method="POST" action="{{ route('driver.update-ride-status', $ride->id) }}">
        @csrf <input type="hidden" name="status" value="in_progress">
        <button type="submit" class="btn btn-brand" style="width:100%;justify-content:center">🚗 Start Ride</button>
      </form>
      @endif
      @if($ride->status === 'in_progress')
      <form method="POST" action="{{ route('driver.update-ride-status', $ride->id) }}" onsubmit="return confirm('Complete this ride?')">
        @csrf <input type="hidden" name="status" value="completed">
        <button type="submit" class="btn btn-green" style="width:100%;justify-content:center">🏁 Complete Ride</button>
      </form>
      @endif
      @if(in_array($ride->status,['accepted','driver_arrived']))
      <form method="POST" action="{{ route('driver.update-ride-status', $ride->id) }}" onsubmit="return confirm('Cancel this ride?')">
        @csrf <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="btn btn-red" style="width:100%;justify-content:center">❌ Cancel Ride</button>
      </form>
      @endif
      @if($ride->status === 'completed')
      @php $alreadyRated = $ride->ratings->where('rater_id', auth()->id())->first(); @endphp
      @if(!$alreadyRated)
      <a href="{{ route('driver.rate', $ride->id) }}" class="btn btn-amber" style="width:100%;justify-content:center">⭐ Rate Rider</a>
      @else
      <div class="btn btn-ghost" style="width:100%;justify-content:center;cursor:default">✅ Already Rated</div>
      @endif
      @endif
    </div>
  </div>

  {{-- Share live location note --}}
  <div class="card" style="padding:14px;text-align:center">
    <p style="font-size:12px;color:var(--text3);margin-bottom:8px">📡 Share your live location with the platform</p>
    <button class="btn btn-blue btn-sm" style="width:100%;justify-content:center" onclick="startLocationShare()">Start Sharing Location</button>
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
let lastMsgId = 0, locationInterval = null;

const map = L.map('driverMap', { attributionControl: false });
addLeafletDarkTiles(map);
@if($ride->pickup_lat)
  map.setView([{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}], 14);
  L.marker([{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}], {
    icon: L.divIcon({ className:'', html:'<div style="width:14px;height:14px;border-radius:50%;background:#22c55e;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>', iconSize:[14,14] })
  }).addTo(map).bindPopup('Pickup');
@else
  map.setView([27.7172, 85.3240], 13);
@endif
@if($ride->destination_lat)
  L.marker([{{ $ride->destination_lat }}, {{ $ride->destination_lng }}], {
    icon: L.divIcon({ className:'', html:'<div style="width:14px;height:14px;border-radius:50%;background:#FF5500;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>', iconSize:[14,14] })
  }).addTo(map).bindPopup('Destination');
@endif
@if($ride->pickup_lat && $ride->destination_lat)
L.Routing.control({
  waypoints:[L.latLng({{ $ride->pickup_lat }},{{ $ride->pickup_lng }}),L.latLng({{ $ride->destination_lat }},{{ $ride->destination_lng }})],
  routeWhileDragging:false,addWaypoints:false,draggableWaypoints:false,createMarker:()=>null,
  lineOptions:{styles:[{color:'#FF5500',weight:4,opacity:.7}]},
  router:L.Routing.osrmv1({serviceUrl:'https://router.project-osrm.org/route/v1',profile:'driving'})
}).addTo(map);
@endif

// Chat poll
async function pollChat() {
  try {
    const r = await fetch(`/chat/${RIDE_ID}/${lastMsgId}`);
    const msgs = await r.json();
    if (msgs.length) {
      const box = document.getElementById('chatBox');
      if (lastMsgId === 0) box.innerHTML = '';
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
pollChat(); setInterval(pollChat, 4000);

async function sendMsg() {
  const inp = document.getElementById('chatInput');
  const msg = inp.value.trim();
  if (!msg) return;
  inp.value = '';
  await fetch(`/chat/${RIDE_ID}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
    body: JSON.stringify({ message: msg })
  });
  await pollChat();
}

// Live location sharing
function startLocationShare() {
  if (!navigator.geolocation) { showToast('Geolocation not supported', 'error'); return; }
  showToast('Location sharing started', 'success');
  locationInterval = setInterval(() => {
    navigator.geolocation.getCurrentPosition(pos => {
      fetch(`/driver/location`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude, ride_id: RIDE_ID })
      });
    });
  }, 5000);
}
@endif
</script>
@endpush
