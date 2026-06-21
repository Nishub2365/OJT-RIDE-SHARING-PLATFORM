@extends('layouts.app')
@section('title','Login')
@push('styles')
<style>
.auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at 60% 40%,rgba(255,85,0,.07) 0%,transparent 60%)}
.auth-box{width:100%;max-width:440px}
.auth-hero{text-align:center;margin-bottom:32px}
.auth-icon{width:72px;height:72px;border-radius:20px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 16px;box-shadow:0 12px 40px rgba(255,85,0,.3)}
.role-tab-group{display:flex;gap:6px;background:var(--card3);border-radius:12px;padding:4px;margin-bottom:22px}
.role-tab{flex:1;text-align:center;padding:10px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;color:var(--text2)}
.role-tab.active{background:var(--brand);color:#fff;box-shadow:0 4px 12px rgba(255,85,0,.3)}
@keyframes authFadeUp{from{opacity:0;transform:translateY(32px)}to{opacity:1;transform:translateY(0)}}
@keyframes authScaleIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:scale(1)}}
@keyframes authSlideRight{from{opacity:0;transform:translateX(-24px)}to{opacity:1;transform:translateX(0)}}
@keyframes orbFloat{0%,100%{transform:translate(0,0)}50%{transform:translate(20px,-15px)}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0)}50%{transform:translate(-15px,20px)}}
@keyframes gradMove{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
@keyframes pulseDot{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.3);opacity:.6}}
@keyframes shimmer{0%{background-position:-400px 0}100%{background-position:400px 0}}
.auth-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0}
.auth-orb-1{width:500px;height:500px;background:rgba(255,85,0,.06);top:-150px;right:-150px;animation:orbFloat 14s ease-in-out infinite}
.auth-orb-2{width:360px;height:360px;background:rgba(168,85,247,.05);bottom:-100px;left:-100px;animation:orbFloat2 18s ease-in-out infinite}
.auth-card-animate{animation:authScaleIn .55s cubic-bezier(.175,.885,.32,1.275) both}
input:focus{box-shadow:0 0 0 3px rgba(255,85,0,.18) !important;transform:translateY(-1px)}
input{transition:box-shadow .2s ease,transform .2s ease}
.auth-logo-animate{animation:authFadeUp .7s .1s both}
.auth-field-1{animation:authFadeUp .5s .2s both}
.auth-field-2{animation:authFadeUp .5s .3s both}
.auth-field-3{animation:authFadeUp .5s .4s both}
.auth-field-4{animation:authFadeUp .5s .5s both}
.auth-field-5{animation:authFadeUp .5s .6s both}
.btn-submit::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.12);
  transform:translateX(-100%) skewX(-20deg);transition:.5s}
.btn-submit:hover::after{transform:translateX(200%) skewX(-20deg)}
</style>
@endpush
@section('content')
<div class="auth-orb auth-orb-1"></div>
<div class="auth-orb auth-orb-2"></div>

<div class="auth-wrap">
  <div class="auth-box auth-card-animate">
    <div class="auth-hero auth-logo-animate">
      <div class="auth-icon" style="animation:authScaleIn .6s .2s both">🚗</div>
      <h1 style="font-size:28px;font-weight:900;margin-bottom:8px;animation:authFadeUp .5s .3s both">Welcome to BROCAR</h1>
      <p style="color:var(--text2);font-size:14px;animation:authFadeUp .5s .4s both">Nepal's smartest ride-hailing service</p>
    </div>

    <div class="card" style="padding:24px">
      <div class="role-tab-group" id="roleTabs">
      @foreach(['rider'=>['🧑','Rider'],'driver'=>['🏍️','Driver']] as $r=>[$icon,$label])
        <div class="role-tab {{ (request('role')===$r||(!request('role')&&$r==='rider'))?'active':'' }}"
             onclick="setRole('{{ $r }}')">{{ $icon }} {{ $label }}</div>
        @endforeach
      </div>

      <form method="POST" action="{{ route('auth.send-otp') }}" id="phoneForm" style="display:flex;flex-direction:column;gap:16px">
        @csrf
        <input type="hidden" name="role" id="roleField" value="{{ request('role','rider') }}">

        <div>
          <label class="label">Phone Number</label>
          <div style="position:relative">
            <div style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text3);font-weight:700;display:flex;align-items:center;gap:4px">
              🇳🇵 <span style="color:var(--text2)">+977</span>
            </div>
            <input type="tel" name="phone" class="input" placeholder="98XXXXXXXX"
              style="padding-left:76px;font-size:16px;font-weight:600;letter-spacing:1px"
              maxlength="10" pattern="[0-9]{10}"
              value="{{ old('phone') }}" required autofocus
              oninput="this.value=this.value.replace(/\D/g,'')">
          </div>
          @error('phone')<p style="font-size:12px;color:var(--red);margin-top:4px">⚠ {{ $message }}</p>@enderror
        </div>

        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <label class="label" style="margin-bottom:0">Password</label>
            <a href="{{ route('auth.forgot-password') }}" style="font-size:12px;color:var(--brand);font-weight:600">Forgot Password?</a>
          </div>
          <div style="position:relative">
            <input type="password" name="password" id="loginPwd" class="input" placeholder="Enter your password"
              style="padding-right:44px" required>
            <button type="button" onclick="togglePhonePwd()"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:15px;line-height:1">
              <span id="loginPwdEye">👁</span>
            </button>
          </div>
          @error('password')<p style="font-size:12px;color:var(--red);margin-top:4px">⚠ {{ $message }}</p>@enderror
        </div>

        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
          <input type="checkbox" name="remember" id="rememberMe" value="1"
            style="width:17px;height:17px;accent-color:var(--brand);cursor:pointer"
            {{ old('remember') ? 'checked' : '' }}>
          <span style="font-size:13px;color:var(--text2);font-weight:500">Remember me on this device</span>
        </label>

        <button type="submit" class="btn btn-brand" style="width:100%;justify-content:center;padding:14px;font-size:15px" id="otpBtn">
          <span id="otpBtnText">Continue →</span>
          <span id="otpBtnSpinner" style="display:none" class="spinner" style="width:18px;height:18px;border-width:2px"></span>
        </button>

        @error('phone')<p style="font-size:12px;color:var(--red)">{{ $message }}</p>@enderror
      </form>
    </div>

    <p style="text-align:center;font-size:12px;color:var(--text3);margin-top:16px">
      By continuing you agree to our <a href="#" style="color:var(--brand)">Terms</a> &amp; <a href="#" style="color:var(--brand)">Privacy Policy</a>
    </p>
  </div>
</div>
@endsection
@push('scripts')
<script>
function setRole(r) {
  document.getElementById('roleField').value = r;
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  event.currentTarget.classList.add('active');
}

function togglePhonePwd() {
  const f = document.getElementById('loginPwd');
  const e = document.getElementById('loginPwdEye');
  if (f.type === 'password') { f.type = 'text'; e.textContent = '🙈'; }
  else { f.type = 'password'; e.textContent = '👁'; }
}

document.getElementById('phoneForm')?.addEventListener('submit', () => {
  document.getElementById('otpBtnText').textContent = 'Continuing…';
  document.getElementById('otpBtnSpinner').style.display = 'inline-block';
  document.getElementById('otpBtn').disabled = true;
});
</script>
@endpush