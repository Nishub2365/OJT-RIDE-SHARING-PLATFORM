@extends('layouts.app')
@section('title','Book a Ride')
@push('styles')
<style>
  #rideMap { height: 340px; border-radius: 12px; }
  /* Leaflet routing overrides */
  .leaflet-routing-container { display:none !important; }
  /* Vehicle cards */
  .v-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
  .v-card {
    cursor:pointer; border:2px solid var(--border); border-radius:14px;
    padding:14px 10px; text-align:center; transition:.25s; background:var(--card);
    position:relative;
  }
  .v-card:hover { border-color:rgba(255,85,0,.4); background:rgba(255,85,0,.04); }
  .v-card.selected { border-color:var(--brand); background:rgba(255,85,0,.08); box-shadow:0 0 0 1px var(--brand); }
  .v-card input { display:none; }
  .v-card .v-icon { font-size:26px; margin-bottom:6px; }
  .v-card .v-name { font-size:12px; font-weight:800; margin-bottom:2px; }
  .v-card .v-price { font-size:11px; color:var(--text3); }
  .v-card .online-badge {
    position:absolute; top:6px; right:6px; background:rgba(34,197,94,.15);
    color:var(--green); border-radius:50px; font-size:9px; font-weight:800;
    padding:2px 6px; line-height:1.4;
  }
  .v-card .offline-badge {
    position:absolute; top:6px; right:6px; background:rgba(239,68,68,.1);
    color:var(--red); border-radius:50px; font-size:9px; font-weight:800;
    padding:2px 6px; line-height:1.4;
  }
  .v-card.no-driver { opacity:.55; }
  /* Suggest list */
  .suggest-list { position:absolute;top:100%;left:0;right:0;background:var(--card);
    border:1px solid var(--border);border-radius:0 0 12px 12px;z-index:500;
    max-height:200px;overflow-y:auto; display:none; }
  .suggest-item { padding:10px 14px;font-size:13px;cursor:pointer;border-bottom:1px solid var(--border); }
  .suggest-item:hover { background:rgba(255,255,255,.04); }
  .suggest-wrap { position:relative; }
  /* Online drivers panel */
  .drivers-panel { background:var(--card3); border:1px solid var(--border); border-radius:12px; padding:14px; margin-top:10px; }
  .driver-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); }
  .driver-row:last-child { border-bottom:none; }
  /* Coming soon overlay */
  .coming-soon-banner { background:linear-gradient(135deg,rgba(239,68,68,.12),rgba(239,68,68,.06));
    border:1px solid rgba(239,68,68,.25); border-radius:12px; padding:16px;
    display:none; text-align:center; margin-top:10px; }
  /* Spinner overlay on form submit */
  .submit-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7);
    z-index:9998; align-items:center; justify-content:center; flex-direction:column; gap:16px; }
  .submit-overlay.active { display:flex; }
  .big-spinner { width:56px; height:56px; border:4px solid rgba(255,85,0,.2);
    border-top-color:var(--brand); border-radius:50%; animation:spin .8s linear infinite; }
</style>
@endpush
@section('content')

<!-- Submit overlay -->
<div class="submit-overlay" id="submitOverlay">
  <div class="big-spinner"></div>
  <p style="font-size:16px;font-weight:800;color:var(--text1)">Finding Drivers…</p>
  <p style="font-size:13px;color:var(--text3)">Please wait, connecting you with nearby drivers</p>
</div>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
  <div class="page-title">🚗 Book a Ride</div>
  <span class="badge badge-brand">Jhapa, Nepal</span>
</div>

<form method="POST" action="{{ route('rider.store-ride') }}" id="rideForm">
  @csrf
  <div class="grid-2" style="align-items:start">

  {{-- Left Column --}}
  <div style="display:flex;flex-direction:column;gap:14px">

    {{-- STEP 1: Vehicle Type --}}
    <div class="card" style="padding:16px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div class="card-title">🚌 Choose Vehicle</div>
        <div id="onlineStatusBadge" style="font-size:12px;color:var(--text3)">
          <span class="spinner"></span> Checking…
        </div>
      </div>

      <div class="v-grid" id="vehicleGrid">
        @php
          $vehicles = [
            'bike' => ['icon'=>'🏍️', 'name'=>'Bike',  'base'=>80,  'per_km'=>15, 'max'=>200],
            'auto' => ['icon'=>'🛺', 'name'=>'Auto',  'base'=>100, 'per_km'=>20, 'max'=>300],
            'car'  => ['icon'=>'🚗', 'name'=>'Car',   'base'=>150, 'per_km'=>30, 'max'=>500],
            'suv'  => ['icon'=>'🚙', 'name'=>'SUV',   'base'=>200, 'per_km'=>40, 'max'=>700],
          ];
        @endphp

        @foreach($vehicles as $type => $v)
        <label class="v-card {{ $type==='car'?'selected':'' }}" id="vcard-{{ $type }}"
               onclick="selectVehicle('{{ $type }}')">
          <input type="radio" name="vehicle_category" value="{{ $type }}" {{ $type==='car'?'checked':'' }}>
          <div class="online-badge" id="obadge-{{ $type }}">…</div>
          <div class="v-icon">{{ $v['icon'] }}</div>
          <div class="v-name">{{ $v['name'] }}</div>
          <div class="v-price" id="vprice-{{ $type }}">NPR {{ $v['base'] }}+</div>
        </label>
        @endforeach
      </div>

      {{-- Online drivers for selected vehicle --}}
      <div id="onlineDriversPanel" class="drivers-panel" style="display:none">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
          <p style="font-size:12px;font-weight:800;color:var(--green)">🟢 Online Drivers</p>
          <span id="onlineCount" class="badge badge-green">0</span>
        </div>
        <div id="onlineDriversList"></div>
      </div>

      {{-- No drivers warning --}}
      <div id="noDriverWarn" style="display:none;margin-top:10px;background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:12px;text-align:center">
        <p style="font-size:13px;font-weight:700;color:var(--amber)">⚠ No online drivers for this vehicle type</p>
        <p style="font-size:12px;color:var(--text3);margin-top:4px">Try a different vehicle or wait a moment</p>
      </div>
    </div>

    {{-- STEP 2: Route --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">📍 Set Your Route</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <label class="label">📍 Pickup Location</label>
          <div class="suggest-wrap">
            <input type="text" id="pickupInput" class="input"
              placeholder="Search pickup in Jhapa…" autocomplete="off" required>
            <div class="suggest-list" id="pickupList"></div>
          </div>
          <input type="hidden" name="pickup_address" id="pickupAddress">
          <input type="hidden" name="pickup_lat"     id="pickupLat">
          <input type="hidden" name="pickup_lng"     id="pickupLng">
          <button type="button" class="btn btn-ghost btn-xs" style="margin-top:6px" onclick="useMyLocation()">
            📡 Use My Location
          </button>
        </div>

        <div>
          <label class="label">🏁 Destination</label>
          <div class="suggest-wrap">
            <input type="text" id="destInput" class="input"
              placeholder="Where in Jhapa?" autocomplete="off" required>
            <div class="suggest-list" id="destList"></div>
          </div>
          <input type="hidden" name="destination_address" id="destAddress">
          <input type="hidden" name="destination_lat"     id="destLat">
          <input type="hidden" name="destination_lng"     id="destLng">
        </div>

        {{-- Jhapa Coming Soon Banner --}}
        <div class="coming-soon-banner" id="comingSoonBanner">
          <p style="font-size:20px;margin-bottom:8px">🚧</p>
          <p style="font-size:15px;font-weight:900;color:var(--red)">Coming Soon!</p>
          <p style="font-size:13px;color:var(--text2);margin-top:6px">
            BROCAR currently serves <strong>Jhapa district</strong> only.<br>
            We're expanding soon — stay tuned!
          </p>
          <p style="font-size:11px;color:var(--text3);margin-top:8px">📍 Covered area: Birtamod, Mechinagar, Damak, Kankai, Bhadrapur, Arjundhara</p>
        </div>

        {{-- Distance & fare estimate --}}
        <div id="routeInfo" style="display:none;background:rgba(255,85,0,.06);border:1px solid rgba(255,85,0,.15);border-radius:10px;padding:12px">
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
            <div>
              <p style="font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase">Distance</p>
              <p style="font-size:16px;font-weight:900;color:var(--text1)" id="routeDistance">—</p>
            </div>
            <div>
              <p style="font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase">Est. Time</p>
              <p style="font-size:16px;font-weight:900;color:var(--text1)" id="routeTime">—</p>
            </div>
            <div>
              <p style="font-size:10px;color:var(--text3);font-weight:700;text-transform:uppercase">Est. Fare</p>
              <p style="font-size:16px;font-weight:900;color:var(--brand)" id="routeFare">—</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- STEP 3: Fare & Payment --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">💰 Fare & Payment <span class="npr-tag">NPR</span></div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <label class="label">Your Offered Fare (NPR)</label>
          <div style="display:flex;align-items:center;gap:8px">
            <button type="button" class="btn btn-ghost btn-xs" onclick="adjustFare(-10)">−</button>
            <input type="number" name="offered_fare" id="fareInput" class="input"
              style="text-align:center;font-size:20px;font-weight:900;color:var(--brand)"
              min="50" value="150" required>
            <button type="button" class="btn btn-ghost btn-xs" onclick="adjustFare(+10)">+</button>
          </div>
          <p id="fareHint" style="font-size:11px;color:var(--text3);margin-top:4px">Suggested: NPR 150–500 for Car</p>
        </div>

        <div>
          <label class="label">Payment Method</label>
          <select name="payment_method" class="input">
            <option value="cash">💵 Cash (Pay directly to driver)</option>
            <option value="wallet">💳 BROCAR Wallet (NPR {{ number_format(auth()->user()->wallet_balance) }})</option>
          </select>
        </div>

        <div>
          <label class="label">Promo Code (optional)</label>
          <div style="display:flex;gap:8px">
            <input type="text" name="promo_code" id="promoInput" class="input"
              placeholder="e.g. BROCAR20" style="flex:1;text-transform:uppercase">
            <button type="button" class="btn btn-ghost btn-sm" onclick="validatePromo()">Apply</button>
          </div>
          <p id="promoMsg" style="font-size:11px;margin-top:4px"></p>
        </div>

        <div>
          <label class="label">Schedule (optional)</label>
          <input type="datetime-local" name="scheduled_at" class="input"
            min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}">
          <p style="font-size:11px;color:var(--text3);margin-top:4px">Leave blank to book immediately</p>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-brand"
      style="width:100%;justify-content:center;padding:16px;font-size:16px;letter-spacing:.3px"
      id="submitBtn">
      🚗 Find Drivers →
    </button>
  </div>

  {{-- Right Column: Map --}}
  <div style="position:sticky;top:80px;display:flex;flex-direction:column;gap:14px">
    <div class="card" style="padding:12px;overflow:hidden">
      <div id="rideMap"></div>
    </div>

    {{-- Tips --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:10px">💡 Booking Tips</div>
      <ul style="font-size:13px;color:var(--text3);line-height:2;list-style:none;padding:0">
        <li>✅ Offer a fair fare to get faster bids</li>
        <li>✅ Drivers bid — you choose the best</li>
        <li>✅ BROCAR operates within <strong style="color:var(--text2)">Jhapa</strong> only</li>
        <li>✅ Cash or wallet payments accepted</li>
        <li>✅ Use promo codes for discounts</li>
      </ul>
    </div>
  </div>

  </div>
</form>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script>
// ── JHAPA BOUNDING BOX ──────────────────────────────────────────────
// Jhapa district approx bounds (Nepal)
const JHAPA_BOUNDS = {
  minLat: 26.40, maxLat: 26.85,
  minLng: 87.60, maxLng: 88.20
};
const JHAPA_CENTER = [26.63, 87.93];

function isInJhapa(lat, lng) {
  return lat >= JHAPA_BOUNDS.minLat && lat <= JHAPA_BOUNDS.maxLat &&
         lng >= JHAPA_BOUNDS.minLng && lng <= JHAPA_BOUNDS.maxLng;
}

// ── VEHICLE PRICING ─────────────────────────────────────────────────
const vehicleData = {
  bike: { icon:'🏍️', name:'Bike',  base:80,  perKm:15, min:80,  max:200 },
  auto: { icon:'🛺', name:'Auto',  base:100, perKm:20, min:100, max:300 },
  car:  { icon:'🚗', name:'Car',   base:150, perKm:30, min:150, max:500 },
  suv:  { icon:'🚙', name:'SUV',   base:200, perKm:40, min:200, max:700 },
};
let selectedVehicle = 'car';
let routeDistanceKm = 0;

function selectVehicle(type) {
  selectedVehicle = type;
  document.querySelectorAll('.v-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('vcard-'+type)?.classList.add('selected');
  document.querySelector(`input[value="${type}"]`).checked = true;
  updateFareFromDistance();
  loadOnlineDrivers(type);
}

function calcFare(type, km) {
  const v = vehicleData[type];
  if (!v) return 150;
  const f = v.base + (km * v.perKm);
  return Math.min(Math.max(Math.round(f), v.min), v.max * 2);
}

function updateFareFromDistance() {
  const v = vehicleData[selectedVehicle];
  const km = routeDistanceKm || 3;
  const fare = calcFare(selectedVehicle, km);
  document.getElementById('fareInput').value = fare;
  document.getElementById('fareHint').textContent =
    `Suggested: NPR ${v.min}–${v.max} for ${v.name} (${km.toFixed(1)} km)`;
}

// ── MAP INIT ────────────────────────────────────────────────────────
const map = L.map('rideMap', { attributionControl: false, zoomControl: true })
  .setView(JHAPA_CENTER, 12);
addLeafletDarkTiles(map);

// Remove any residual attribution
map.attributionControl?.setPrefix('');
if (map.attributionControl) map.removeControl(map.attributionControl);

let pickMarker, destMarker, routingControl;

function markerIcon(color, label) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${color};color:#fff;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:14px;border:3px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.5);font-weight:800">${label}</div>`,
    iconSize:[32,32], iconAnchor:[16,16]
  });
}

function drawRoute(pickLat, pickLng, destLat, destLng) {
  if (routingControl) { map.removeControl(routingControl); routingControl = null; }

  routingControl = L.Routing.control({
    waypoints: [
      L.latLng(pickLat, pickLng),
      L.latLng(destLat, destLng)
    ],
    routeWhileDragging: false,
    addWaypoints: false,
    draggableWaypoints: false,
    fitSelectedRoutes: true,
    showAlternatives: false,
    lineOptions: {
      styles: [{ color: '#FF5500', weight: 5, opacity: 0.85 }]
    },
    createMarker: () => null, // use our own markers
    router: L.Routing.osrmv1({
      serviceUrl: 'https://router.project-osrm.org/route/v1',
      profile: 'driving'
    })
  }).addTo(map);

  routingControl.on('routesfound', function(e) {
    const routes = e.routes;
    if (!routes || !routes.length) return;
    const r = routes[0];
    const distKm = (r.summary.totalDistance / 1000).toFixed(1);
    const mins   = Math.round(r.summary.totalTime / 60);
    routeDistanceKm = parseFloat(distKm);
    const fare = calcFare(selectedVehicle, routeDistanceKm);

    document.getElementById('routeDistance').textContent = distKm + ' km';
    document.getElementById('routeTime').textContent     = mins + ' min';
    document.getElementById('routeFare').textContent     = 'NPR ' + fare;
    document.getElementById('routeInfo').style.display   = 'block';

    // Update vehicle prices
    Object.entries(vehicleData).forEach(([type, v]) => {
      const f = calcFare(type, routeDistanceKm);
      const el = document.getElementById('vprice-'+type);
      if (el) el.textContent = 'NPR ' + f;
    });
    updateFareFromDistance();
  });

  routingControl.on('routingerror', function() {
    // Fallback to straight line
    const dist = map.distance([pickLat, pickLng],[destLat, destLng]) / 1000;
    routeDistanceKm = parseFloat(dist.toFixed(1));
    updateFareFromDistance();
  });
}

function setMarkerAndRoute(type, lat, lng, label) {
  if (type === 'pickup') {
    if (pickMarker) map.removeLayer(pickMarker);
    pickMarker = L.marker([lat,lng],{icon:markerIcon('#22c55e','P')}).addTo(map)
      .bindPopup(`📍 <strong>Pickup</strong><br>${label.substring(0,50)}`).openPopup();
  } else {
    if (destMarker) map.removeLayer(destMarker);
    destMarker = L.marker([lat,lng],{icon:markerIcon('#FF5500','D')}).addTo(map)
      .bindPopup(`🏁 <strong>Destination</strong><br>${label.substring(0,50)}`).openPopup();
  }
  if (pickMarker && destMarker) {
    const pll = pickMarker.getLatLng();
    const dll = destMarker.getLatLng();
    drawRoute(pll.lat, pll.lng, dll.lat, dll.lng);
  }
}

// ── NOMINATIM AUTOCOMPLETE (Jhapa focus) ────────────────────────────
let searchTimeout;
function setupAutocomplete(inputId, listId, addrId, latId, lngId, markerType) {
  const input = document.getElementById(inputId);
  const list  = document.getElementById(listId);

  input.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = input.value.trim();
    if (q.length < 3) { list.style.display='none'; return; }
    searchTimeout = setTimeout(async () => {
      list.innerHTML = `<div class="suggest-item" style="color:var(--text3)"><span class="spinner"></span> Searching…</div>`;
      list.style.display = 'block';
      try {
        // Search within Jhapa viewbox first
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q+' Jhapa Nepal')}&limit=6&viewbox=${JHAPA_BOUNDS.minLng},${JHAPA_BOUNDS.maxLat},${JHAPA_BOUNDS.maxLng},${JHAPA_BOUNDS.minLat}&bounded=0`;
        const r = await fetch(url);
        const data = await r.json();
        if (!data.length) {
          list.innerHTML = `<div class="suggest-item" style="color:var(--text3)">No results found</div>`;
          return;
        }
        list.innerHTML = data.map(d => {
          const inJhapa = isInJhapa(parseFloat(d.lat), parseFloat(d.lon));
          return `<div class="suggest-item" data-lat="${d.lat}" data-lng="${d.lon}" data-name="${d.display_name}" style="${!inJhapa?'opacity:.6':''}">
            ${inJhapa ? '📍' : '⚠️'} ${d.display_name.substring(0,60)}
          </div>`;
        }).join('');
        list.style.display = 'block';

        list.querySelectorAll('.suggest-item[data-lat]').forEach(item => {
          item.addEventListener('click', () => {
            const lat = parseFloat(item.dataset.lat);
            const lng = parseFloat(item.dataset.lng);
            const name = item.dataset.name;
            input.value = name.split(',')[0];
            document.getElementById(addrId).value = name;
            document.getElementById(latId).value = lat;
            document.getElementById(lngId).value = lng;
            list.style.display = 'none';

            // Jhapa check for destination
            if (markerType === 'destination' && !isInJhapa(lat, lng)) {
              document.getElementById('comingSoonBanner').style.display = 'block';
              document.getElementById('submitBtn').disabled = true;
              document.getElementById('submitBtn').style.opacity = '.5';
              showToast('⚠ This destination is outside Jhapa — Coming Soon!', 'error');
              return;
            } else {
              document.getElementById('comingSoonBanner').style.display = 'none';
              document.getElementById('submitBtn').disabled = false;
              document.getElementById('submitBtn').style.opacity = '1';
            }
            setMarkerAndRoute(markerType, lat, lng, name);
          });
        });
      } catch(e) { list.style.display='none'; }
    }, 350);
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('.suggest-wrap')) list.style.display = 'none';
  });
}

setupAutocomplete('pickupInput','pickupList','pickupAddress','pickupLat','pickupLng','pickup');
setupAutocomplete('destInput',  'destList',  'destAddress',  'destLat',  'destLng',  'destination');

// ── MY LOCATION ─────────────────────────────────────────────────────
function useMyLocation() {
  if (!navigator.geolocation) { showToast('Geolocation not supported','error'); return; }
  showToast('Getting your location…','info');
  navigator.geolocation.getCurrentPosition(async pos => {
    const { latitude:lat, longitude:lng } = pos.coords;
    try {
      const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
      const d = await r.json();
      const name = d.display_name || 'Current Location';
      document.getElementById('pickupInput').value   = name.split(',')[0];
      document.getElementById('pickupAddress').value = name;
      document.getElementById('pickupLat').value     = lat;
      document.getElementById('pickupLng').value     = lng;
      setMarkerAndRoute('pickup', lat, lng, name);
      showToast('📍 Location set!', 'success');
    } catch(e) { showToast('Could not get address','error'); }
  }, () => showToast('Location access denied','error'));
}

// ── FARE ADJUSTER ───────────────────────────────────────────────────
function adjustFare(delta) {
  const inp = document.getElementById('fareInput');
  inp.value = Math.max(50, parseInt(inp.value || 100) + delta);
}

// ── ONLINE DRIVERS ──────────────────────────────────────────────────
const onlineCounts = {};

async function loadAllVehicleCounts() {
  const types = ['bike','auto','car','suv'];
  for (const type of types) {
    try {
      const r = await fetch(`/online-drivers?type=${type}`);
      const d = await r.json();
      onlineCounts[type] = d.count;
      updateVehicleBadge(type, d.count);
    } catch(e) { onlineCounts[type] = 0; }
  }
  document.getElementById('onlineStatusBadge').innerHTML =
    `<span style="color:var(--green)">● Live</span>`;
}

function updateVehicleBadge(type, count) {
  const card   = document.getElementById('vcard-'+type);
  const badge  = document.getElementById('obadge-'+type);
  if (!card || !badge) return;
  if (count > 0) {
    badge.className = 'online-badge';
    badge.textContent = count + (count===1?' driver':' drivers');
    card.classList.remove('no-driver');
  } else {
    badge.className = 'offline-badge';
    badge.textContent = 'Offline';
    card.classList.add('no-driver');
  }
}

async function loadOnlineDrivers(type) {
  const panel = document.getElementById('onlineDriversPanel');
  const list  = document.getElementById('onlineDriversList');
  const warn  = document.getElementById('noDriverWarn');
  try {
    list.innerHTML = `<div style="text-align:center;padding:12px"><span class="spinner"></span></div>`;
    panel.style.display = 'block';
    const r = await fetch(`/online-drivers?type=${type}`);
    const d = await r.json();
    document.getElementById('onlineCount').textContent = d.count;

    if (d.count === 0) {
      panel.style.display = 'none';
      warn.style.display = 'block';
      return;
    }
    warn.style.display = 'none';

    const icons = {bike:'🏍️',auto:'🛺',car:'🚗',suv:'🚙',truck:'🚚'};
    list.innerHTML = (d.drivers || []).slice(0,5).map(dr => `
      <div class="driver-row">
        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--purple));
          display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">
          ${icons[dr.vehicle_type]||'🚗'}
        </div>
        <div style="flex:1;min-width:0">
          <p style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${dr.name||'Driver'}</p>
          <p style="font-size:11px;color:var(--text3)">${dr.vehicle_model||''} · ⭐ ${dr.rating?parseFloat(dr.rating).toFixed(1):'New'} · ${dr.total_trips||0} trips</p>
        </div>
        <div style="font-size:9px;padding:2px 7px;background:rgba(34,197,94,.1);color:var(--green);border-radius:50px;font-weight:800;flex-shrink:0">ONLINE</div>
      </div>
    `).join('');
    if (d.count > 5) {
      list.innerHTML += `<p style="font-size:11px;color:var(--text3);text-align:center;padding-top:8px">+${d.count-5} more drivers available</p>`;
    }
  } catch(e) {
    panel.style.display = 'none';
  }
}

// ── PROMO ────────────────────────────────────────────────────────────
async function validatePromo() {
  const code = document.getElementById('promoInput').value.trim();
  if (!code) return;
  const msg = document.getElementById('promoMsg');
  msg.textContent = 'Checking…';
  msg.style.color = 'var(--text3)';
  try {
    const r = await fetch('/promo/validate', {
      method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN},
      body: JSON.stringify({ code })
    });
    const d = await r.json();
    msg.textContent = d.message;
    msg.style.color = d.valid ? 'var(--green)' : 'var(--red)';
    if (d.valid) showToast('Promo applied: ' + d.message, 'success');
  } catch(e) { msg.textContent = 'Error checking promo'; msg.style.color = 'var(--red)'; }
}

// ── FORM SUBMIT ──────────────────────────────────────────────────────
document.getElementById('rideForm').addEventListener('submit', e => {
  if (!document.getElementById('pickupAddress').value) {
    e.preventDefault(); showToast('Please select a pickup address', 'error'); return;
  }
  if (!document.getElementById('destAddress').value) {
    e.preventDefault(); showToast('Please select a destination', 'error'); return;
  }
  // Check if outside Jhapa
  const destLat = parseFloat(document.getElementById('destLat').value);
  const destLng = parseFloat(document.getElementById('destLng').value);
  if (destLat && destLng && !isInJhapa(destLat, destLng)) {
    e.preventDefault();
    document.getElementById('comingSoonBanner').style.display = 'block';
    showToast('⚠ Destination outside Jhapa — Coming Soon!', 'error');
    return;
  }
  document.getElementById('submitOverlay').classList.add('active');
});

// ── INIT ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadAllVehicleCounts();
  loadOnlineDrivers('car');
  // Refresh every 30s
  setInterval(() => { loadAllVehicleCounts(); loadOnlineDrivers(selectedVehicle); }, 30000);
});
</script>
@endpush
