@extends('layouts.app')
@section('title','Forgot Password')
@push('styles')
<style>
.auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(ellipse at 60% 40%,rgba(255,85,0,.07) 0%,transparent 60%)}
.auth-box{width:100%;max-width:440px}
.auth-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0}
.auth-orb-1{width:500px;height:500px;background:rgba(255,85,0,.06);top:-150px;right:-150px;animation:orbFloat 14s ease-in-out infinite}
.auth-orb-2{width:360px;height:360px;background:rgba(168,85,247,.05);bottom:-100px;left:-100px;animation:orbFloat2 18s ease-in-out infinite}
@keyframes orbFloat{0%,100%{transform:translate(0,0)}50%{transform:translate(20px,-15px)}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0)}50%{transform:translate(-15px,20px)}}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.fp-card{animation:fadeUp .5s .1s both}
</style>
@endpush
@section('content')
<div class="auth-orb auth-orb-1"></div>
<div class="auth-orb auth-orb-2"></div>

<div class="auth-wrap">
  <div class="auth-box">
    <div style="text-align:center;margin-bottom:28px;animation:fadeUp .5s both">
      <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;box-shadow:0 10px 32px rgba(255,85,0,.3)">🔑</div>
      <h2 style="font-size:24px;font-weight:900;margin-bottom:8px">Forgot Password?</h2>
      <p style="font-size:14px;color:var(--text2);line-height:1.6">No worries — enter your phone number and our admin team will contact you directly to reset your password.</p>
    </div>

    @if(session('flash_success'))
    <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);border-radius:12px;padding:16px;margin-bottom:20px;text-align:center">
      <p style="font-size:14px;color:#16a34a;font-weight:600">{{ session('flash_success') }}</p>
      <a href="{{ route('auth.phone') }}" style="font-size:13px;color:var(--brand);margin-top:8px;display:inline-block">← Back to Sign In</a>
    </div>
    @endif

    <div class="card fp-card" style="padding:24px">
      <form method="POST" action="{{ route('auth.forgot-password.submit') }}" style="display:flex;flex-direction:column;gap:16px">
        @csrf

        <div>
          <label class="label">Phone Number <span style="color:var(--red)">*</span></label>
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
          <label class="label">Email (optional — for faster contact)</label>
          <input type="email" name="email" class="input" placeholder="you@example.com" value="{{ old('email') }}">
          @error('email')<p style="font-size:12px;color:var(--red);margin-top:4px">⚠ {{ $message }}</p>@enderror
        </div>

        <div style="background:rgba(255,85,0,.05);border:1px solid rgba(255,85,0,.15);border-radius:10px;padding:12px">
          <p style="font-size:12px;color:var(--text2);line-height:1.7">
            📋 <strong>How it works:</strong><br>
            1. Submit this form — your request goes directly to our admin panel.<br>
            2. An admin will review and contact you on your registered phone.<br>
            3. They will verify your identity and reset your password for you.
          </p>
        </div>

        <button type="submit" class="btn btn-brand" style="width:100%;justify-content:center;padding:14px;font-size:15px">
          📨 Send Reset Request
        </button>
      </form>
    </div>

    <p style="text-align:center;margin-top:20px">
      <a href="{{ route('auth.phone') }}" style="font-size:13px;color:var(--text2);text-decoration:none;transition:.15s" onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text2)'">
        ← Back to Sign In
      </a>
    </p>
  </div>
</div>
@endsection