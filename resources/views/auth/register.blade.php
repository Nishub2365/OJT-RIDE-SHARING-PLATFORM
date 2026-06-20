@extends('layouts.app')
@section('title','Create Account')
@push('styles')
<style>
.pwd-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:16px;line-height:1}
</style>
@endpush
@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:20px">
  <div style="width:100%;max-width:520px">
    <div style="text-align:center;margin-bottom:28px">
      <h2 style="font-size:26px;font-weight:900;margin-bottom:6px">Create Your Account</h2>
      <p style="font-size:14px;color:var(--text2)">Phone: <strong>+977 {{ session('otp_phone') }}</strong> · Role: <strong>{{ ucfirst(session('otp_role','rider')) }}</strong></p>
    </div>
    <div class="card">
      <form method="POST" action="{{ route('auth.do-register') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:14px">
        @csrf

        {{-- Name --}}
        <div class="grid-2">
          <div><label class="label">First Name</label><input type="text" name="first_name" class="input" value="{{ old('first_name') }}" required></div>
          <div><label class="label">Last Name</label><input type="text" name="last_name" class="input" value="{{ old('last_name') }}" required></div>
        </div>

        {{-- Email --}}
        <div><label class="label">Email (optional)</label><input type="email" name="email" class="input" value="{{ old('email') }}" placeholder="you@example.com"></div>

        {{-- Password --}}
        <div>
          <label class="label">Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative">
            <input type="password" name="password" id="regPwd" class="input"
              placeholder="Min 6 characters" required style="padding-right:44px">
            <button type="button" class="pwd-toggle" onclick="togglePwd('regPwd','eye2')" id="eye2">👁</button>
          </div>
          @error('password')<p style="font-size:12px;color:var(--red);margin-top:4px">⚠ {{ $message }}</p>@enderror
        </div>

        {{-- Confirm Password --}}
        <div>
          <label class="label">Confirm Password <span style="color:var(--red)">*</span></label>
          <div style="position:relative">
            <input type="password" name="password_confirmation" id="regPwdConfirm" class="input"
              placeholder="Re-enter password" required style="padding-right:44px">
            <button type="button" class="pwd-toggle" onclick="togglePwd('regPwdConfirm','eye3')" id="eye3">👁</button>
          </div>
        </div>

        {{-- Avatar --}}
        <div>
          <label class="label">Profile Photo (optional)</label>
          <input type="file" name="avatar" class="input" accept="image/*" style="padding:8px">
        </div>

        {{-- Driver vehicle fields --}}
        @if(session('otp_role') === 'driver')
        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
          <p style="font-size:12px;font-weight:800;color:var(--brand);letter-spacing:1px;text-transform:uppercase;margin-bottom:14px">🚗 Vehicle Details</p>
          <div class="grid-2" style="gap:12px">
            <div>
              <label class="label">Vehicle Type</label>
              <select name="vehicle_type" class="input" required>
                @foreach(['bike'=>'🏍️ Bike','auto'=>'🛺 Auto','car'=>'🚗 Car','suv'=>'🚙 SUV','truck'=>'🚚 Truck'] as $v=>$l)
                <option value="{{ $v }}" {{ old('vehicle_type')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
              </select>
            </div>
            <div><label class="label">Vehicle Model</label><input type="text" name="vehicle_model" class="input" placeholder="e.g. Honda CB Shine" value="{{ old('vehicle_model') }}" required></div>
            <div><label class="label">Plate Number</label><input type="text" name="vehicle_number" class="input" placeholder="BA 1 PA 1234" value="{{ old('vehicle_number') }}" required style="text-transform:uppercase"></div>
            <div><label class="label">Color</label><input type="text" name="vehicle_color" class="input" placeholder="Red" value="{{ old('vehicle_color') }}"></div>
          </div>
        </div>
        @endif

        {{-- Terms --}}
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px;background:rgba(255,255,255,.02);border-radius:10px;border:1px solid var(--border)">
          <input type="checkbox" name="agree_terms" value="1" required style="margin-top:2px;width:16px;height:16px;accent-color:var(--brand)">
          <span style="font-size:12px;color:var(--text2);line-height:1.6">I agree to BROCAR's <a href="#" style="color:var(--brand)">Terms of Service</a> and <a href="#" style="color:var(--brand)">Privacy Policy</a></span>
        </label>

        @if($errors->any())
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:12px">
          @foreach($errors->all() as $e)<p style="font-size:12px;color:var(--red)">• {{ $e }}</p>@endforeach
        </div>
        @endif

        <button type="submit" class="btn btn-brand" style="width:100%;justify-content:center;padding:14px">
          🎉 Create Account
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
function togglePwd(inputId, btnId) {
  const inp = document.getElementById(inputId);
  const btn = document.getElementById(btnId);
  if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
  else { inp.type = 'password'; btn.textContent = '👁'; }
}
</script>
@endpush