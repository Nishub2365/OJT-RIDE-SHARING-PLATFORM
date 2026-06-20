@extends('layouts.app')
@section('title','Driver Profile')
@section('content')
<div class="page-title" style="margin-bottom:20px">👤 Driver Profile</div>
@php $user = auth()->user(); $dp = $user->driverProfile; @endphp
<div class="grid-2" style="align-items:start">
<div class="card">
  <div class="card-title" style="margin-bottom:14px">Personal & Vehicle Info</div>
  <form method="POST" action="{{ route('driver.profile.update') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px">
    @csrf
    <div style="display:flex;align-items:center;gap:14px;padding:12px;background:rgba(255,255,255,.02);border-radius:12px;border:1px solid var(--border)">
      <img src="{{ $user->avatar_url ? asset('storage/'.$user->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($user->full_name).'&background=FF5500&color=fff&size=80&bold=true' }}"
           style="width:52px;height:52px;border-radius:12px">
      <div>
        <p style="font-size:15px;font-weight:800">{{ $user->full_name }}</p>
        <p style="font-size:12px;color:var(--text3)">{{ $user->phone }}</p>
        <p style="font-size:12px;color:var(--text3)">⭐ {{ $user->avg_rating ?? 'No ratings yet' }} · {{ $dp?->total_trips ?? 0 }} trips</p>
      </div>
    </div>
    <div class="grid-2">
      <div><label class="label">First Name</label><input type="text" name="first_name" class="input" value="{{ $user->first_name }}" required></div>
      <div><label class="label">Last Name</label><input type="text" name="last_name" class="input" value="{{ $user->last_name }}" required></div>
      <div><label class="label">Email</label><input type="email" name="email" class="input" value="{{ $user->email }}" placeholder="email@example.com"></div>
    </div>
    <div><label class="label">Profile Photo</label><input type="file" name="avatar" class="input" accept="image/*" style="padding:8px 14px"></div>
    <hr class="divider">
    <div class="grid-2">
      <div><label class="label">Vehicle Type</label>
        <select name="vehicle_type" class="input">
          @foreach(['bike'=>'🏍️ Bike','auto'=>'🛺 Auto','car'=>'🚗 Car','suv'=>'🚙 SUV','truck'=>'🚛 Truck'] as $v=>$l)
          <option value="{{ $v }}" {{ $dp?->vehicle_type===$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div><label class="label">Vehicle Model</label><input type="text" name="vehicle_model" class="input" value="{{ $dp?->vehicle_model }}"></div>
    </div>
    <div class="grid-2">
      <div><label class="label">Plate Number</label><input type="text" name="vehicle_number" class="input" value="{{ $dp?->vehicle_number }}"></div>
      <div><label class="label">Vehicle Color</label><input type="text" name="vehicle_color" class="input" value="{{ $dp?->vehicle_color }}"></div>
    </div>
    <button type="submit" class="btn btn-brand btn-sm">Save Changes</button>
  </form>
</div>
<div style="display:flex;flex-direction:column;gap:14px">
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">📊 Performance</div>
    <div style="font-size:13px;display:flex;flex-direction:column;gap:10px">
      @foreach([
        ['Total Trips',   $dp?->total_trips ?? 0,    'var(--blue)'],
        ['Total Earned',  "NPR".number_format($dp?->total_earned??0), 'var(--green)'],
        ['Avg Rating',    $user->avg_rating ?? 'N/A', 'var(--amber)'],
        ['Status',        ucfirst($dp?->status??'pending'), $dp?->status==='approved'?'var(--green)':'var(--amber)'],
        ['Wallet',        "NPR".number_format($user->wallet_balance), 'var(--brand)'],
      ] as [$l,$v,$c])
      <div style="display:flex;justify-content:space-between;padding:9px;background:rgba(255,255,255,.02);border-radius:8px">
        <span class="text-muted">{{ $l }}</span>
        <span style="font-weight:700;color:{{ $c }}">{{ $v }}</span>
      </div>
      @endforeach
    </div>
  </div>
  <a href="{{ route('driver.documents') }}" class="btn btn-ghost" style="justify-content:center">📄 Manage Documents →</a>
</div>
</div>
@endsection
