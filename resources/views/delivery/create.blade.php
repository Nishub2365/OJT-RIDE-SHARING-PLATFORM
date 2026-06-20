@extends('layouts.app')
@section('title','New Delivery')
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
  <a href="{{ route('delivery.index') }}" class="btn btn-ghost btn-xs">← Back</a>
  <div class="page-title">📦 New Delivery</div>
</div>
<div style="max-width:580px">
  <div class="card">
    <form method="POST" action="{{ route('delivery.store') }}" style="display:flex;flex-direction:column;gap:14px">
      @csrf
      <div><label class="label">Pickup Address</label><input type="text" name="pickup_address" class="input" placeholder="Where to pick up from?" required></div>
      <div><label class="label">Delivery Address</label><input type="text" name="delivery_address" class="input" placeholder="Where to deliver?" required></div>
      <div class="grid-2">
        <div>
          <label class="label">Item Type</label>
          <select name="item_type" class="input" onchange="updateFare(this.value, document.getElementById('weightInput').value)" required>
            <option value="document">📄 Document (NPR 60)</option>
            <option value="parcel">📦 Parcel (NPR 100+)</option>
            <option value="freight">🚚 Freight (NPR 500+)</option>
          </select>
        </div>
        <div>
          <label class="label">Weight (kg)</label>
          <input type="number" id="weightInput" name="weight_kg" class="input" placeholder="0" min="0" step="0.1"
            oninput="updateFare(document.querySelector('[name=item_type]').value, this.value)">
        </div>
      </div>
      <div><label class="label">Item Description</label><input type="text" name="item_description" class="input" placeholder="What are you sending?" required></div>
      <div class="grid-2">
        <div><label class="label">Recipient Name</label><input type="text" name="recipient_name" class="input" required></div>
        <div><label class="label">Recipient Phone</label><input type="tel" name="recipient_phone" class="input" placeholder="98XXXXXXXX" required></div>
      </div>
      <div style="background:rgba(255,85,0,.06);border:1px solid rgba(255,85,0,.15);border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;color:var(--text2)">Estimated Fare</span>
        <span id="fareDisplay" style="font-size:22px;font-weight:900;color:var(--brand)">NPR 60</span>
      </div>
      <div>
        <label class="label">Payment Method</label>
        <select name="payment_method" class="input">
          <option value="cash">💵 Cash on Pickup</option>
          <option value="wallet">👛 BROCAR Wallet</option>
        </select>
      </div>
      <button type="submit" class="btn btn-brand" style="justify-content:center;padding:14px">📦 Place Delivery Order</button>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function updateFare(type, weight) {
  const w = parseFloat(weight)||0;
  let fare = type==='document' ? 60 : type==='parcel' ? 100+w*15 : 500+w*20;
  document.getElementById('fareDisplay').textContent = 'NPR '+Math.round(fare);
}
</script>
@endpush
