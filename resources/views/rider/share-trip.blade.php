@extends('layouts.app')
@section('title','Share Trip')
@section('content')
@php
use Illuminate\Support\Str;
@endphp
<div style="max-width:520px;margin:0 auto">
<div class="card" style="text-align:center">
  <div style="font-size:48px;margin-bottom:12px">📡</div>
  <h2 style="font-size:18px;font-weight:900;margin-bottom:6px">Share Your Live Trip</h2>
  <p style="font-size:13px;color:var(--text2);margin-bottom:24px">Let your trusted contacts follow your journey in real time.</p>

  @if($ride->driver)
  <div style="background:rgba(255,255,255,.03);border-radius:14px;padding:16px;margin-bottom:20px;text-align:left">
    <p style="font-size:12px;font-weight:700;color:var(--text3);margin-bottom:4px">YOUR DRIVER</p>
    <div style="display:flex;align-items:center;gap:10px">
      <img src="https://ui-avatars.com/api/?name={{ urlencode($ride->driver->full_name) }}&background=FF5500&color=fff&size=60&bold=true" style="width:40px;height:40px;border-radius:9px">
      <div><p style="font-size:14px;font-weight:700">{{ $ride->driver->full_name }}</p><p style="font-size:12px;color:var(--text3)">{{ $ride->driver->driverProfile?->vehicle_model }}</p></div>
    </div>
  </div>
  @endif

  <div style="background:rgba(255,255,255,.03);border-radius:14px;padding:16px;margin-bottom:20px;text-align:left;font-size:13px">
    <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span class="text-muted">From</span><span>{{ Str::limit($ride->pickup_address,35) }}</span></div>
    <div style="display:flex;justify-content:space-between"><span class="text-muted">To</span><span>{{ Str::limit($ride->destination_address,35) }}</span></div>
  </div>

  <div style="background:rgba(56,189,248,.06);border:1px solid rgba(56,189,248,.15);border-radius:12px;padding:14px;margin-bottom:20px">
    <p style="font-size:11px;font-weight:700;color:var(--blue);margin-bottom:6px">SHARE THIS LINK</p>
    <p style="font-size:12px;color:var(--text2);word-break:break-all" id="shareUrl">{{ url("/rider/share-trip/{$ride->id}") }}</p>
  </div>

  <div style="display:flex;gap:8px;flex-direction:column">
    <button class="btn btn-brand" style="width:100%;justify-content:center" onclick="share()">📤 Share Trip Link</button>
    <a href="tel:" class="btn btn-ghost" style="width:100%;justify-content:center">📞 Call Trusted Contact</a>
    <a href="{{ route('rider.active-ride',$ride->id) }}" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center">← Back to Ride</a>
  </div>
</div>
</div>
@endsection
@push('scripts')
<script>
function share(){
  const url=document.getElementById('shareUrl').textContent.trim();
  if(navigator.share){navigator.share({title:'My BROCAR Trip',text:'Track my live ride on BROCAR',url})}
  else{navigator.clipboard.writeText(url).then(()=>showToast('Link copied to clipboard!','success'))}
}
</script>
@endpush