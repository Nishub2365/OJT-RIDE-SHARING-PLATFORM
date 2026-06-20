@extends('layouts.app')
@section('title','My Profile')
@section('content')
<div class="page-title" style="margin-bottom:20px">👤 My Profile</div>
<div class="grid-2" style="align-items:start">

{{-- Profile form --}}
<div class="card">
  <div class="card-title" style="margin-bottom:14px">Personal Information</div>
  <form method="POST" action="{{ route('rider.profile.update') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:12px">
    @csrf
    <div style="display:flex;align-items:center;gap:14px;padding:12px;background:rgba(255,255,255,.02);border-radius:12px;border:1px solid var(--border)">
      <img src="{{ $user->avatar_url ? asset('storage/'.$user->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($user->full_name).'&background=FF5500&color=fff&size=80&bold=true' }}"
           style="width:56px;height:56px;border-radius:12px;object-fit:cover">
      <div>
        <p style="font-size:15px;font-weight:800">{{ $user->full_name }}</p>
        <p style="font-size:12px;color:var(--text3)">📱 {{ $user->phone }}</p>
        <p style="font-size:12px;color:var(--text3)">⭐ {{ $user->avg_rating ?? 'No ratings yet' }}</p>
      </div>
    </div>
    <div class="grid-2">
      <div><label class="label">First Name</label><input type="text" name="first_name" class="input" value="{{ $user->first_name }}" required></div>
      <div><label class="label">Last Name</label><input type="text" name="last_name" class="input" value="{{ $user->last_name }}" required></div>
      <div><label class="label">Email</label><input type="email" name="email" class="input" value="{{ $user->email }}" placeholder="email@example.com"></div>
    </div>
    <div><label class="label">Profile Photo</label><input type="file" name="avatar" class="input" accept="image/*" style="padding:8px 14px"></div>
    <button type="submit" class="btn btn-brand btn-sm">Save Profile</button>
  </form>
</div>

<div style="display:flex;flex-direction:column;gap:14px">
  {{-- Trusted contacts --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">👨‍👩‍👧 Trusted Contacts</div>
      <span class="badge badge-muted">{{ $user->trustedContacts->count() }}/2</span>
    </div>
    @foreach($user->trustedContacts as $c)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--border)">
      <div>
        <p style="font-size:13px;font-weight:700">{{ $c->name }}</p>
        <p style="font-size:12px;color:var(--text3)">{{ $c->phone }}</p>
      </div>
      <a href="tel:{{ $c->phone }}" class="btn btn-blue btn-xs">📞</a>
    </div>
    @endforeach
    @if($user->trustedContacts->count() < 2)
    <form method="POST" action="{{ route('rider.profile.update') }}" style="display:flex;flex-direction:column;gap:8px;margin-top:10px">
      @csrf <input type="hidden" name="add_contact" value="1">
      <div class="grid-2">
        <input type="text" name="contact_name" class="input" placeholder="Contact name" required style="font-size:13px">
        <input type="text" name="contact_phone" class="input" placeholder="Phone number" required style="font-size:13px">
      </div>
      <button type="submit" class="btn btn-ghost btn-sm">+ Add Contact</button>
    </form>
    @endif
  </div>

  {{-- Saved locations --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:10px">📍 Saved Locations</div>
    @foreach($user->savedLocations ?? [] as $loc)
    <div style="padding:8px 0;border-bottom:1px solid var(--border);font-size:13px">
      <span class="badge badge-muted" style="margin-right:8px">{{ ucfirst($loc->label) }}</span>
      {{ Str::limit($loc->address,40) }}
    </div>
    @endforeach
    <form method="POST" action="{{ route('rider.profile.update') }}" style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
      @csrf <input type="hidden" name="add_location" value="work">
      <select name="add_location" class="input" style="width:90px;flex-shrink:0">
        <option value="home">🏠 Home</option>
        <option value="work">🏢 Work</option>
        <option value="other">📍 Other</option>
      </select>
      <input type="text" name="location_address" class="input" placeholder="Address" style="flex:1" required>
      <button type="submit" class="btn btn-ghost btn-sm">Save</button>
    </form>
  </div>

  {{-- Account stats --}}
  <div class="card">
    <div class="card-title" style="margin-bottom:10px">📊 Account Stats</div>
    <div style="font-size:13px;display:flex;flex-direction:column;gap:8px">
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Wallet Balance</span><span style="font-weight:700;color:var(--green)">NPR {{ number_format($user->wallet_balance) }}</span></div>
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Average Rating</span><span>{{ $user->avg_rating ? '⭐ '.$user->avg_rating : 'No ratings' }}</span></div>
      <div style="display:flex;justify-content:space-between"><span class="text-muted">Member Since</span><span>{{ $user->created_at->format('M Y') }}</span></div>
    </div>
  </div>
</div>
</div>
@endsection
