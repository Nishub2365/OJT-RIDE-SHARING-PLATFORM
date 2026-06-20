<div style="background:rgba(255,255,255,.02);border:1px solid var(--border);border-radius:12px;padding:14px;margin-bottom:8px">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
    <div style="display:flex;align-items:center;gap:10px">
      <img src="{{ $bid->driver->avatar_url ? asset('storage/'.$bid->driver->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($bid->driver->full_name).'&background=1F1F3E&color=888&size=60&bold=true' }}"
           style="width:38px;height:38px;border-radius:9px;object-fit:cover">
      <div>
        <p style="font-size:13px;font-weight:700">{{ $bid->driver->full_name }}</p>
        <p style="font-size:11px;color:var(--text3)">
          ⭐ {{ $bid->driver->avg_rating ?? 'New' }} &nbsp;·&nbsp;
          {{ $bid->driver->driverProfile?->vehicle_model ?? '—' }} &nbsp;·&nbsp;
          {{ $bid->driver->driverProfile?->total_trips ?? 0 }} trips
        </p>
      </div>
    </div>
    <div style="text-align:right">
      <div style="font-size:20px;font-weight:900;color:var(--brand)">NPR {{ number_format($bid->bid_amount) }}</div>
      @if($bid->message)
      <p style="font-size:11px;color:var(--text3);margin-top:2px">{{ Str::limit($bid->message,40) }}</p>
      @endif
    </div>
  </div>
  <form method="POST" action="{{ route('rider.accept-bid',[$rideId,$bid->id]) }}" style="margin-top:10px">
    @csrf
    <button type="submit" class="btn btn-brand btn-sm" style="width:100%;justify-content:center">
      ✅ Accept This Driver
    </button>
  </form>
</div>
