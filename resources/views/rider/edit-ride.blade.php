@extends('layouts.app')
@section('title','Edit Ride')
@push('styles')
<style>
  .suggest-list{position:absolute;top:100%;left:0;right:0;background:var(--card);border:1px solid var(--border);border-radius:0 0 12px 12px;z-index:500;max-height:200px;overflow-y:auto;display:none}
  .suggest-item{padding:10px 14px;font-size:13px;cursor:pointer;border-bottom:1px solid var(--border)}
  .suggest-item:hover{background:rgba(255,255,255,.04)}
  .suggest-wrap{position:relative}
  .v-card{cursor:pointer;border:2px solid var(--border);border-radius:12px;padding:12px 8px;text-align:center;transition:.2s;background:var(--card)}
  .v-card.selected{border-color:var(--brand);background:rgba(255,85,0,.07)}
  .v-card input{display:none}
</style>
@endpush
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
  <a href="{{ route('rider.ride-detail',$ride->id) }}" class="btn btn-ghost btn-sm">← Back</a>
  <div class="page-title">✏️ Edit Ride #{{ $ride->id }}</div>
</div>

@if(session('flash_success'))
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:12px;padding:14px;margin-bottom:16px;color:var(--green);font-size:13px;font-weight:700">
  ✅ {{ session('flash_success') }}
</div>
@endif

<div class="grid-2" style="align-items:start">
<form method="POST" action="{{ route('rider.update-ride',$ride->id) }}">
  @csrf @method('PATCH')
  <div style="display:flex;flex-direction:column;gap:14px">

    {{-- Vehicle --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">🚌 Vehicle Type</div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
        @foreach(['bike'=>['🏍️','Bike',80,200],'auto'=>['🛺','Auto',100,300],'car'=>['🚗','Car',150,500],'suv'=>['🚙','SUV',200,700]] as $type=>[$icon,$name,$min,$max])
        <label class="v-card {{ $ride->vehicle_category===$type?'selected':'' }}" id="vc-{{$type}}"
               onclick="selVeh('{{$type}}',{{$min}},{{$max}})">
          <input type="radio" name="vehicle_category" value="{{$type}}" {{$ride->vehicle_category===$type?'checked':''}}>
          <div style="font-size:22px;margin-bottom:4px">{{$icon}}</div>
          <div style="font-size:11px;font-weight:800">{{$name}}</div>
          <div style="font-size:10px;color:var(--text3)">NPR {{$min}}–{{$max}}</div>
        </label>
        @endforeach
      </div>
    </div>

    {{-- Route --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">📍 Route</div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <label class="label">Pickup Location</label>
          <div class="suggest-wrap">
            <input type="text" id="pickupInput" class="input" value="{{ $ride->pickup_address }}"
              placeholder="Search pickup…" autocomplete="off">
            <div class="suggest-list" id="pickupList"></div>
          </div>
          <input type="hidden" name="pickup_address" id="pickupAddress" value="{{ $ride->pickup_address }}">
          <input type="hidden" name="pickup_lat"     id="pickupLat"     value="{{ $ride->pickup_lat }}">
          <input type="hidden" name="pickup_lng"     id="pickupLng"     value="{{ $ride->pickup_lng }}">
        </div>
        <div>
          <label class="label">Destination</label>
          <div class="suggest-wrap">
            <input type="text" id="destInput" class="input" value="{{ $ride->destination_address }}"
              placeholder="Destination in Jhapa…" autocomplete="off">
            <div class="suggest-list" id="destList"></div>
          </div>
          <input type="hidden" name="destination_address" id="destAddress" value="{{ $ride->destination_address }}">
          <input type="hidden" name="destination_lat"     id="destLat"     value="{{ $ride->destination_lat }}">
          <input type="hidden" name="destination_lng"     id="destLng"     value="{{ $ride->destination_lng }}">
        </div>
      </div>
    </div>

    {{-- Fare & Payment --}}
    <div class="card" style="padding:16px">
      <div class="card-title" style="margin-bottom:14px">💰 Fare & Payment <span class="npr-tag">NPR</span></div>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div>
          <label class="label">Offered Fare (NPR)</label>
          <div style="display:flex;align-items:center;gap:8px">
            <button type="button" class="btn btn-ghost btn-xs" onclick="adjFare(-10)">−</button>
            <input type="number" name="offered_fare" id="fareInput" class="input"
              style="text-align:center;font-size:18px;font-weight:900;color:var(--brand)"
              value="{{ $ride->offered_fare }}" min="50" required>
            <button type="button" class="btn btn-ghost btn-xs" onclick="adjFare(10)">+</button>
          </div>
        </div>
        <div>
          <label class="label">Payment Method</label>
          <select name="payment_method" class="input">
            <option value="cash" {{ $ride->payment_method==='cash'?'selected':'' }}>💵 Cash</option>
            <option value="wallet" {{ $ride->payment_method==='wallet'?'selected':'' }}>💳 Wallet (NPR {{ number_format(auth()->user()->wallet_balance) }})</option>
          </select>
        </div>
        <div>
          <label class="label">Scheduled Time (optional)</label>
          <input type="datetime-local" name="scheduled_at" class="input"
            value="{{ $ride->scheduled_at ? \Carbon\Carbon::parse($ride->scheduled_at)->format('Y-m-d\TH:i') : '' }}"
            min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}">
        </div>
        <div>
          <label class="label">Promo Code (optional)</label>
          <input type="text" name="promo_code" class="input"
            value="{{ $ride->promo_code }}"
            placeholder="e.g. BROCAR20" style="text-transform:uppercase">
        </div>
      </div>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:12px">
      @foreach($errors->all() as $e)<p style="font-size:12px;color:var(--red)">⚠ {{ $e }}</p>@endforeach
    </div>
    @endif

    <div style="display:flex;gap:10px">
      <button type="submit" class="btn btn-brand" style="flex:1;justify-content:center;padding:14px">
        💾 Save Changes
      </button>
      <a href="{{ route('rider.ride-detail',$ride->id) }}" class="btn btn-ghost" style="padding:14px">Cancel</a>
    </div>
  </div>
</form>

{{-- Right info card --}}
<div style="display:flex;flex-direction:column;gap:14px">
  <div class="card" style="padding:16px">
    <div class="card-title" style="margin-bottom:12px">ℹ️ Ride Info</div>
    <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text3)">
      <p>Status: <strong style="color:var(--amber)">{{ strtoupper(str_replace('_',' ',$ride->status)) }}</strong></p>
      <p>Requested: <strong style="color:var(--text1)">{{ $ride->created_at->diffForHumans() }}</strong></p>
      @if($ride->bids->count())
      <p>Bids received: <strong style="color:var(--brand)">{{ $ride->bids->count() }}</strong></p>
      @endif
    </div>
  </div>
  <div class="card" style="padding:16px;background:rgba(245,158,11,.05);border-color:rgba(245,158,11,.2)">
    <p style="font-size:12px;font-weight:800;color:var(--amber);margin-bottom:8px">⚠ Edit Notice</p>
    <ul style="font-size:12px;color:var(--text3);line-height:2;list-style:none;padding:0">
      <li>• You can only edit rides with <strong>Pending</strong> or <strong>Scheduled</strong> status</li>
      <li>• Changing fare may affect driver bids</li>
      <li>• BROCAR covers <strong style="color:var(--text2)">Jhapa district</strong> only</li>
    </ul>
  </div>
</div>
</div>
@endsection
@push('scripts')
<script>
function selVeh(type, min, max) {
  document.querySelectorAll('.v-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('vc-'+type)?.classList.add('selected');
  document.querySelector(`input[value="${type}"]`).checked=true;
  const fare = Math.round((min+max)/2);
  document.getElementById('fareInput').value = fare;
}
function adjFare(d) {
  const i = document.getElementById('fareInput');
  i.value = Math.max(50, parseInt(i.value||100)+d);
}
// Nominatim autocomplete
let t;
function setupAC(inpId, listId, addrId, latId, lngId) {
  const inp = document.getElementById(inpId);
  const lst = document.getElementById(listId);
  inp.addEventListener('input', () => {
    clearTimeout(t);
    const q = inp.value.trim();
    if (q.length < 3) { lst.style.display='none'; return; }
    t = setTimeout(async () => {
      const r = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q+' Jhapa Nepal')}&limit=5`);
      const d = await r.json();
      if (!d.length) { lst.style.display='none'; return; }
      lst.innerHTML = d.map(x=>`<div class="suggest-item" data-lat="${x.lat}" data-lng="${x.lon}" data-name="${x.display_name}">${x.display_name.substring(0,60)}</div>`).join('');
      lst.style.display='block';
      lst.querySelectorAll('.suggest-item').forEach(item=>{
        item.addEventListener('click',()=>{
          inp.value=item.dataset.name.split(',')[0];
          document.getElementById(addrId).value=item.dataset.name;
          document.getElementById(latId).value=item.dataset.lat;
          document.getElementById(lngId).value=item.dataset.lng;
          lst.style.display='none';
        });
      });
    }, 350);
  });
  document.addEventListener('click',e=>{ if(!e.target.closest('.suggest-wrap')) lst.style.display='none'; });
}
setupAC('pickupInput','pickupList','pickupAddress','pickupLat','pickupLng');
setupAC('destInput','destList','destAddress','destLat','destLng');
</script>
@endpush
