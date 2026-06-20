@extends('layouts.app')
@section('title','Scheduled Rides')
@section('content')
<div class="page-title" style="margin-bottom:18px">⏰ Scheduled Rides</div>
<div class="grid-2" style="align-items:start">
<div class="card">
  <div class="card-title" style="margin-bottom:14px">Book in Advance</div>
  <form method="POST" action="{{ route('rider.schedule.store') }}" style="display:flex;flex-direction:column;gap:12px">
    @csrf
    <div><label class="label">Pickup Location</label><input type="text" name="pickup_address" class="input" placeholder="Start typing…" required></div>
    <div><label class="label">Destination</label><input type="text" name="destination_address" class="input" placeholder="Where to?" required></div>
    <div class="grid-2">
      <div><label class="label">Vehicle</label>
        <select name="vehicle_category" class="input">
          @foreach(['bike'=>'🏍️ Bike','auto'=>'🛺 Auto','car'=>'🚗 Car','suv'=>'🚙 SUV'] as $v=>$l)
          <option value="{{ $v }}">{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div><label class="label">Offered Fare (NPR)</label><input type="number" name="offered_fare" class="input" min="50" placeholder="150" required></div>
    </div>
    <div><label class="label">Schedule Date & Time</label><input type="datetime-local" name="scheduled_at" class="input" min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}" required></div>
    <div><label class="label">Payment</label>
      <select name="payment_method" class="input">
        <option value="cash">💵 Cash</option>
        <option value="wallet">💳 Wallet (NPR {{ number_format(auth()->user()->wallet_balance) }})</option>
      </select>
    </div>
    <button type="submit" class="btn btn-brand btn-sm">⏰ Schedule Ride</button>
  </form>
</div>
<div class="card">
  <div class="card-title" style="margin-bottom:12px">Upcoming Rides ({{ $scheduled->count() }})</div>
  @forelse($scheduled as $ride)
  <div style="padding:12px;background:rgba(255,255,255,.02);border-radius:12px;border:1px solid var(--border);margin-bottom:10px">
    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
      <span class="badge badge-amber">⏰ Scheduled</span>
      <span style="font-size:12px;color:var(--text3)">{{ $ride->scheduled_at?->format('d M Y · H:i') }}</span>
    </div>
    <p style="font-size:13px;font-weight:700">{{ Str::limit($ride->pickup_address,40) }}</p>
    <p style="font-size:12px;color:var(--text3)">→ {{ Str::limit($ride->destination_address,40) }}</p>
    <div style="display:flex;justify-content:space-between;margin-top:8px">
      <span style="font-size:13px;font-weight:700;color:var(--brand)">NPR {{ number_format($ride->offered_fare) }}</span>
      <form method="POST" action="{{ route('rider.rides.cancel',$ride->id) }}">@csrf
        <button class="btn btn-red btn-xs">Cancel</button>
      </form>
    </div>
  </div>
  @empty
  <p style="text-align:center;color:var(--text3);font-size:13px;padding:24px">No upcoming scheduled rides</p>
  @endforelse
</div>
</div>
@endsection
