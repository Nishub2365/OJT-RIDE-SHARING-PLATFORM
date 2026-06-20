@extends('layouts.app')
@section('title','Driver Detail')
@section('content')
@php
use Illuminate\Support\Str;
$dp = $driver->driverProfile;
@endphp
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
  <a href="{{ route('admin.drivers') }}" class="btn btn-ghost btn-xs">← Back</a>
  <div class="page-title">Driver: {{ $driver->full_name }}</div>
</div>

<div class="grid-2" style="align-items:start">
<div style="display:flex;flex-direction:column;gap:14px">
  <div class="card">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
      <img src="{{ $driver->avatar_url ? asset('storage/'.$driver->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($driver->full_name).'&background=FF5500&color=fff&size=80&bold=true' }}"
           style="width:60px;height:60px;border-radius:14px">
      <div>
        <p style="font-size:18px;font-weight:900">{{ $driver->full_name }}</p>
        <p style="font-size:13px;color:var(--text3)">{{ $driver->phone }} · {{ $driver->email }}</p>
        <span class="badge {{ match($dp?->status??'') {
          'approved'=>'badge-green','rejected'=>'badge-red','suspended'=>'badge-red',default=>'badge-amber'
        } }}" style="margin-top:4px">{{ ucfirst($dp?->status??'pending') }}</span>
      </div>
    </div>
    <div style="font-size:13px;display:flex;flex-direction:column;gap:8px">
      @foreach([
        ['Vehicle',  $dp?->vehicle_type.' · '.$dp?->vehicle_model],
        ['Plate',    $dp?->vehicle_number],
        ['Color',    $dp?->vehicle_color],
        ['Total Trips', $dp?->total_trips ?? 0],
        ['Total Earned', "NPR".number_format($dp?->total_earned??0)],
        ['Avg Rating', $driver->avg_rating ?? 'No ratings'],
        ['Member Since', $driver->created_at->format('d M Y')],
      ] as [$k,$v])
      <div style="display:flex;justify-content:space-between;padding:8px;background:rgba(255,255,255,.02);border-radius:8px">
        <span class="text-muted">{{ $k }}</span><span style="font-weight:600">{{ $v }}</span>
      </div>
      @endforeach
    </div>
  </div>

  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Admin Actions</div>
    <div style="display:flex;flex-direction:column;gap:8px">
      @if($dp?->status === 'pending' || $dp?->status === 'suspended')
      <form method="POST" action="{{ route('admin.drivers.approve', $driver->id) }}">
        @csrf <button type="submit" class="btn btn-green btn-sm" style="width:100%;justify-content:center">✅ Approve Driver</button>
      </form>
      @endif
      @if($dp?->status === 'pending' || $dp?->status === 'approved')
      <form method="POST" action="{{ route('admin.drivers.reject', $driver->id) }}">
        @csrf
        <input type="text" name="rejection_reason" class="input" placeholder="Rejection reason…" style="margin-bottom:8px">
        <button type="submit" class="btn btn-red btn-sm" style="width:100%;justify-content:center" onclick="return confirm('Reject?')">❌ Reject Driver</button>
      </form>
      @endif
      @if($dp?->status === 'approved')
      <form method="POST" action="{{ route('admin.drivers.suspend', $driver->id) }}">
        @csrf <button type="submit" class="btn btn-amber btn-sm" style="width:100%;justify-content:center" onclick="return confirm('Suspend?')">⏸ Suspend Driver</button>
      </form>
      @endif
    </div>
  </div>
</div>

<div style="display:flex;flex-direction:column;gap:14px">
  {{-- Documents --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">📄 Documents</div>
    @foreach(['citizenship'=>'ID/Citizenship','driving_license'=>'Driving License','vehicle_registration'=>'Vehicle Registration','vehicle_photo'=>'Vehicle Photo'] as $type=>$label)
    @php $doc = $driver->documents->where('doc_type',$type)->first(); @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
      <div>
        <p style="font-size:13px;font-weight:600">{{ $label }}</p>
        @if($doc) <p style="font-size:11px;color:var(--text3)">Uploaded {{ $doc->created_at->format('d M Y') }}</p>@endif
      </div>
      <div style="display:flex;gap:6px;align-items:center">
        <span class="badge {{ $doc?($doc->status==='verified'?'badge-green':($doc->status==='rejected'?'badge-red':'badge-amber')):'badge-muted' }}">
          {{ $doc ? ucfirst($doc->status) : 'Missing' }}
        </span>
        @if($doc && $doc->status === 'pending')
        <form method="POST" action="{{ route('admin.documents.verify', $doc->id) }}" style="display:inline">
          @csrf <input type="hidden" name="status" value="verified">
          <button type="submit" class="btn btn-green btn-xs">✓</button>
        </form>
        <form method="POST" action="{{ route('admin.documents.verify', $doc->id) }}" style="display:inline">
          @csrf <input type="hidden" name="status" value="rejected">
          <button type="submit" class="btn btn-red btn-xs">✗</button>
        </form>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  {{-- Recent rides --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">Recent Rides</div>
    @forelse($driver->ridesAsDriver->take(5) as $r)
    <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border);font-size:13px">
      <div>
        <p style="font-weight:600">#{{ $r->id }} · {{ Str::limit($r->pickup_address,28) }}</p>
        <p style="font-size:11px;color:var(--text3)">{{ $r->created_at->format('d M Y') }}</p>
      </div>
      <div style="text-align:right">
        <span class="badge {{ $r->status==='completed'?'badge-green':($r->status==='cancelled'?'badge-red':'badge-muted') }}">{{ ucfirst($r->status) }}</span>
        <p style="font-size:12px;font-weight:700;margin-top:3px">NPR {{ number_format($r->agreed_fare??$r->offered_fare) }}</p>
      </div>
    </div>
    @empty
    <p style="font-size:13px;color:var(--text3);text-align:center;padding:16px">No rides yet</p>
    @endforelse
  </div>
</div>
</div>
@endsection