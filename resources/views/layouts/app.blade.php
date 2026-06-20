<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','BROCAR') — BROCAR Nepal</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
:root{
  --brand:#FF5500;--brand2:#FF7A30;--dark:#0A0A0F;--card:#111118;--card2:#16161F;--card3:#1C1C28;
  --border:rgba(255,255,255,.07);--text1:#F0F0F5;--text2:#A0A0B0;--text3:#666680;
  --green:#22C55E;--blue:#38BDF8;--amber:#F59E0B;--red:#EF4444;--purple:#A855F7;
  --sidebar-w:240px;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--dark);color:var(--text1);font-family:'Segoe UI',system-ui,sans-serif;display:flex;min-height:100vh}
a{text-decoration:none;color:inherit}
button{cursor:pointer;border:none;outline:none}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}

/* ── PAGE LOADER ─────────────────────────────────── */
#pageLoader{position:fixed;inset:0;background:var(--dark);z-index:99999;
  display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;
  transition:opacity .5s}
#pageLoader.hidden{opacity:0;pointer-events:none}
.loader-logo{font-size:36px;font-weight:900;background:linear-gradient(135deg,var(--brand),var(--brand2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:-1px}
.loader-spinner{width:40px;height:40px;border:3px solid rgba(255,85,0,.15);
  border-top-color:var(--brand);border-radius:50%;animation:spin .8s linear infinite}
.loader-dots{display:flex;gap:6px}
.loader-dots span{width:8px;height:8px;border-radius:50%;background:var(--brand);
  animation:dotPulse 1.2s ease-in-out infinite}
.loader-dots span:nth-child(2){animation-delay:.2s}
.loader-dots span:nth-child(3){animation-delay:.4s}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes dotPulse{0%,80%,100%{transform:scale(.6);opacity:.4}40%{transform:scale(1);opacity:1}}

/* ══════════════════════════════════════
   GLOBAL ANIMATION LIBRARY
══════════════════════════════════════ */
@keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideInLeft{from{opacity:0;transform:translateX(-24px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideInRight{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:translateX(0)}}
@keyframes scaleIn{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes shimmer{
  0%{background-position:-600px 0}
  100%{background-position:600px 0}
}
@keyframes countPop{0%{transform:scale(.8);opacity:0}60%{transform:scale(1.1)}100%{transform:scale(1);opacity:1}}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(255,85,0,.4)}50%{box-shadow:0 0 0 6px rgba(255,85,0,0)}}
@keyframes toastSlideIn{from{opacity:0;transform:translateX(120px)}to{opacity:1;transform:translateX(0)}}
@keyframes toastSlideOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(120px)}}
@keyframes navItemHover{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}
@keyframes ripple{0%{transform:scale(0);opacity:.5}100%{transform:scale(5);opacity:0}}
@keyframes ping{0%{transform:scale(1);opacity:1}75%,100%{transform:scale(2);opacity:0}}

/* ── PAGE CONTENT ENTRY ── */
.page-enter{animation:fadeUp .5s ease both}
.page-enter-fast{animation:fadeUp .35s ease both}

/* ── STAGGER CHILDREN ── */
.anim-stagger > *{opacity:0;transform:translateY(20px);animation:fadeUp .5s ease forwards}
.anim-stagger > *:nth-child(1){animation-delay:.05s}
.anim-stagger > *:nth-child(2){animation-delay:.10s}
.anim-stagger > *:nth-child(3){animation-delay:.15s}
.anim-stagger > *:nth-child(4){animation-delay:.20s}
.anim-stagger > *:nth-child(5){animation-delay:.25s}
.anim-stagger > *:nth-child(6){animation-delay:.30s}
.anim-stagger > *:nth-child(7){animation-delay:.35s}
.anim-stagger > *:nth-child(8){animation-delay:.40s}

/* ── SHIMMER SKELETON ── */
.skeleton{
  background:linear-gradient(90deg,var(--card2) 25%,rgba(255,255,255,.04) 37%,var(--card2) 63%);
  background-size:600px 100%;
  animation:shimmer 1.6s ease-in-out infinite;
  border-radius:8px;
}

/* ── STAT CARD ANIMATION ── */
.stat-card{animation:scaleIn .45s ease both}
.stat-card:nth-child(1){animation-delay:.05s}
.stat-card:nth-child(2){animation-delay:.12s}
.stat-card:nth-child(3){animation-delay:.19s}
.stat-card:nth-child(4){animation-delay:.26s}

/* ── NAV LINK ACTIVE GLOW ── */
.nav-link.active{position:relative}
.nav-link.active::before{
  content:'';position:absolute;left:-1px;top:25%;bottom:25%;
  width:3px;background:var(--brand);border-radius:0 3px 3px 0;
  animation:scaleIn .3s ease;
}

/* ── BUTTON RIPPLE ── */
.btn-ripple{position:relative;overflow:hidden}
.btn-ripple .ripple-wave{
  position:absolute;border-radius:50%;background:rgba(255,255,255,.25);
  transform:scale(0);animation:ripple .6s linear;pointer-events:none;
}

/* ── TOAST ENHANCED ── */
.toast-item{animation:toastSlideIn .35s ease both}
.toast-item.removing{animation:toastSlideOut .3s ease forwards}

/* ── PING DOT (notifications) ── */
.ping-dot{position:relative;display:inline-block}
.ping-dot::after{
  content:'';position:absolute;inset:0;border-radius:50%;
  background:var(--brand);animation:ping 1.5s ease-in-out infinite;
}

/* ── PULSE ONLINE DOT ── */
.online-pulse{
  width:10px;height:10px;border-radius:50%;background:var(--green);
  position:relative;display:inline-block;flex-shrink:0;
}
.online-pulse::after{
  content:'';position:absolute;inset:-3px;border-radius:50%;
  border:2px solid var(--green);opacity:.6;
  animation:ping 1.8s ease-in-out infinite;
}

/* ── CARD HOVER LIFT ── */
.card-lift{transition:transform .25s ease,box-shadow .25s ease}
.card-lift:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.4)}

/* ── NUMBER TICK ── */
.num-tick{animation:countPop .4s ease both}

/* ── BADGE PULSE ── */
.badge-live{animation:badgePulse 2s ease-in-out infinite}

/* ── SIDEBAR ─────────────────────────────────────── */
.sidebar{width:var(--sidebar-w);min-height:100vh;background:var(--card);
  border-right:1px solid var(--border);
  display:flex;flex-direction:column;position:fixed;top:0;left:0;z-index:100;transition:.3s}
.sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid var(--border)}
.logo-link{display:flex;flex-direction:column;cursor:pointer;text-decoration:none}
.logo-text{font-size:22px;font-weight:900;background:linear-gradient(135deg,var(--brand),var(--brand2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:-.5px;line-height:1}
.logo-sub{font-size:10px;color:var(--text3);letter-spacing:2px;font-weight:700;text-transform:uppercase;margin-top:3px}
.sidebar-nav{flex:1;padding:14px 10px;overflow-y:auto}
.nav-section{font-size:10px;font-weight:800;letter-spacing:2px;text-transform:uppercase;
  color:var(--text3);padding:10px 12px 6px;margin-top:8px}
.nav-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;
  font-size:13.5px;font-weight:500;color:var(--text2);transition:.2s;margin-bottom:2px}
.nav-link:hover{background:rgba(255,255,255,.04);color:var(--text1)}
.nav-link.active{background:linear-gradient(135deg,rgba(255,85,0,.18),rgba(255,85,0,.08));
  color:var(--brand);font-weight:700}
.nav-link .icon{font-size:17px;width:22px;text-align:center;flex-shrink:0}
.sidebar-user{padding:14px 16px;border-top:1px solid var(--border)}
.user-card{display:flex;align-items:center;gap:10px}
.user-avatar{width:36px;height:36px;border-radius:10px;object-fit:cover;
  background:linear-gradient(135deg,var(--brand),var(--purple));display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:800;color:#fff;flex-shrink:0}
.user-name{font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-role{font-size:11px;color:var(--text3);text-transform:capitalize}

/* ── MAIN ─────────────────────────────────────────── */
.main-wrap{margin-left:var(--sidebar-w);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{position:sticky;top:0;z-index:90;background:rgba(10,10,15,.95);
  backdrop-filter:blur(20px);border-bottom:1px solid var(--border);
  padding:0 24px;height:60px;display:flex;align-items:center;gap:12px}
.topbar-title{font-size:15px;font-weight:700;color:var(--text2)}
.topbar-actions{margin-left:auto;display:flex;align-items:center;gap:10px}
.icon-btn{width:38px;height:38px;border-radius:10px;background:transparent;
  display:flex;align-items:center;justify-content:center;font-size:17px;
  color:var(--text2);transition:.2s;position:relative}
.icon-btn:hover{background:rgba(255,255,255,.06);color:var(--text1)}
.notif-dot{position:absolute;top:6px;right:6px;width:8px;height:8px;
  border-radius:50%;background:var(--red);border:2px solid var(--dark)}
.main-content{flex:1;padding:24px}

/* ── NOTIFICATION PANEL ──────────────────────────── */
.notif-panel{position:fixed;top:60px;right:16px;width:340px;z-index:200;
  background:var(--card2);border:1px solid var(--border);border-radius:16px;
  box-shadow:0 20px 60px rgba(0,0,0,.6);display:none;overflow:hidden}
.notif-panel.open{display:block}
.notif-header{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.notif-header span{font-size:14px;font-weight:800}
.notif-list{max-height:340px;overflow-y:auto}
.notif-item{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;gap:10px}
.notif-item.unread{background:rgba(255,85,0,.04)}
.notif-icon{font-size:18px;flex-shrink:0;margin-top:2px}
.notif-body p:first-child{font-size:13px;font-weight:600;margin-bottom:3px}
.notif-body p:last-child{font-size:11px;color:var(--text3)}

/* ── TOAST ───────────────────────────────────────── */
#toastContainer{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column-reverse;gap:8px}
.toast{padding:12px 18px;border-radius:12px;font-size:13px;font-weight:600;
  display:flex;align-items:center;gap:8px;box-shadow:0 8px 30px rgba(0,0,0,.4);
  animation:slideIn .3s ease;max-width:320px}
.toast.success{background:#16301A;border:1px solid rgba(34,197,94,.25);color:var(--green)}
.toast.error  {background:#2A1010;border:1px solid rgba(239,68,68,.25);color:var(--red)}
.toast.info   {background:#101A2A;border:1px solid rgba(56,189,248,.25);color:var(--blue)}
@keyframes slideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}

/* ── COMPONENTS ──────────────────────────────────── */
.card{background:var(--card2);border:1px solid var(--border);border-radius:16px;padding:20px}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.card-title{font-size:15px;font-weight:800;color:var(--text1)}
.page-title{font-size:22px;font-weight:900;letter-spacing:-.5px}
.page-desc{font-size:13px;color:var(--text3);margin-top:3px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.stat-card{background:var(--card2);border:1px solid var(--border);border-radius:14px;padding:18px}
.stat-label{font-size:11px;font-weight:700;color:var(--text3);letter-spacing:1px;text-transform:uppercase;margin-bottom:8px}
.stat-val{font-size:28px;font-weight:900;letter-spacing:-.5px}
.input{background:var(--card3);border:1px solid var(--border);border-radius:10px;
  padding:10px 14px;font-size:14px;color:var(--text1);width:100%;transition:.2s;outline:none}
.input:focus{border-color:rgba(255,85,0,.35);background:var(--card)}
.input option{background:var(--card);color:var(--text1)}
.label{display:block;font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:10px;
  font-size:13px;font-weight:700;transition:.2s;border:none;cursor:pointer;white-space:nowrap}
.btn:hover{opacity:.88;transform:translateY(-1px)}
.btn-brand{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 4px 16px rgba(255,85,0,.25)}
.btn-green{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.2)}
.btn-blue{background:rgba(56,189,248,.12);color:var(--blue);border:1px solid rgba(56,189,248,.18)}
.btn-red{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.18)}
.btn-amber{background:rgba(245,158,11,.12);color:var(--amber);border:1px solid rgba(245,158,11,.18)}
.btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border)}
.btn-sm{padding:7px 14px;font-size:12px}
.btn-xs{padding:4px 10px;font-size:11px;border-radius:7px}
.badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:11px;font-weight:700;letter-spacing:.3px}
.badge-green{background:rgba(34,197,94,.12);color:var(--green)}
.badge-blue{background:rgba(56,189,248,.12);color:var(--blue)}
.badge-red{background:rgba(239,68,68,.12);color:var(--red)}
.badge-amber{background:rgba(245,158,11,.12);color:var(--amber)}
.badge-brand{background:rgba(255,85,0,.12);color:var(--brand)}
.badge-muted{background:rgba(255,255,255,.06);color:var(--text2)}
.badge-purple{background:rgba(168,85,247,.12);color:var(--purple)}
.table-wrap{overflow-x:auto;border-radius:10px}
table{width:100%;border-collapse:collapse}
thead tr th{padding:10px 14px;text-align:left;font-size:11px;font-weight:800;color:var(--text3);
  text-transform:uppercase;letter-spacing:.8px;background:rgba(255,255,255,.02);white-space:nowrap}
tbody tr td{padding:11px 14px;font-size:13px;border-top:1px solid var(--border)}
tbody tr:hover td{background:rgba(255,255,255,.02)}
.text-sm{font-size:12px}.text-xs{font-size:11px}.text-muted{color:var(--text3)}
.pagination{margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.pagination a,.pagination span{padding:5px 10px;border-radius:8px;font-size:12px;
  background:var(--card3);border:1px solid var(--border);color:var(--text2)}
.pagination .active span{background:var(--brand);color:#fff;border-color:var(--brand)}
/* Spinner utility */
.spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,85,0,.2);
  border-top-color:var(--brand);border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
/* Skeleton */
.skeleton{background:linear-gradient(90deg,var(--card3) 25%,rgba(255,255,255,.04) 50%,var(--card3) 75%);
  background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
/* NPR currency badge */
.npr-tag{background:rgba(255,85,0,.1);color:var(--brand);padding:2px 7px;border-radius:6px;
  font-size:11px;font-weight:800;letter-spacing:.5px}

/* ── MOBILE ──────────────────────────────────────── */
.mobile-topbar{display:none;position:fixed;top:0;left:0;right:0;z-index:200;
  background:var(--card);border-bottom:1px solid var(--border);
  padding:0 16px;height:56px;align-items:center;gap:12px}
.hamburger{font-size:22px;background:none;border:none;color:var(--text1);cursor:pointer}
.mobile-logo{font-size:18px;font-weight:900;background:linear-gradient(135deg,var(--brand),var(--brand2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:99}
@media(max-width:900px){
  .sidebar{transform:translateX(-100%)}
  .sidebar.open{transform:translateX(0)}
  .sidebar-overlay.open{display:block}
  .main-wrap{margin-left:0;padding-top:56px}
  .mobile-topbar{display:flex}
  .topbar{display:none}
  .grid-4{grid-template-columns:1fr 1fr}
  .grid-3{grid-template-columns:1fr}
  .grid-2{grid-template-columns:1fr}
  .main-content{padding:14px}
}
@media(max-width:480px){
  .grid-4{grid-template-columns:1fr}
}

/* ── LEAFLET OVERRIDES (hide attribution) ─────── */
.leaflet-control-attribution{display:none !important}
.leaflet-control-zoom{border-color:var(--border) !important}
.leaflet-control-zoom a{background:var(--card2) !important;color:var(--text1) !important;border-color:var(--border) !important}
.leaflet-bar a:hover{background:var(--card3) !important}
</style>
@stack('styles')
</head>
<body>

<!-- Page Loader -->
<div id="pageLoader">
  <div style="display:flex;flex-direction:column;align-items:center;gap:0">
    <div style="display:flex;align-items:center;gap:0;position:relative">
      <div class="loader-logo" style="font-size:42px;letter-spacing:-2px">BROCAR</div>
      <span id="loaderCar" style="font-size:22px;opacity:0;margin-left:4px;
        animation:loaderCarAnim 1.2s .3s ease-out forwards;display:inline-block">🚗</span>
    </div>
    <div style="font-size:10px;letter-spacing:4px;color:var(--text3);text-transform:uppercase;margin-top:2px;
      opacity:0;animation:fadeIn .5s .5s ease both">RIDE NEPAL</div>
  </div>
  <div style="width:180px;height:3px;background:rgba(255,85,0,.12);border-radius:99px;margin-top:24px;
    overflow:hidden;opacity:0;animation:fadeIn .4s .6s both">
    <div style="height:100%;background:linear-gradient(90deg,var(--brand),var(--brand2));
      border-radius:99px;animation:loaderBarFill 1.4s .6s ease-out both"></div>
  </div>
  <div class="loader-dots" style="margin-top:18px;opacity:0;animation:fadeIn .4s .8s both">
    <span></span><span></span><span></span>
  </div>
</div>
<style>
@keyframes loaderCarAnim{from{opacity:0;transform:translateX(-40px)}to{opacity:1;transform:translateX(0)}}
@keyframes loaderBarFill{from{width:0}to{width:100%}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
</style>

@auth
<!-- Mobile topbar -->
<div class="mobile-topbar">
  <button class="hamburger" id="hamburgerBtn">☰</button>
  <a href="javascript:location.reload()" class="mobile-logo" title="Refresh page">BROCAR</a>
  <div style="margin-left:auto;display:flex;align-items:center;gap:8px">
    <button class="icon-btn" onclick="toggleNotifPanel()">🔔<span class="notif-dot" id="notifDotMobile" style="display:none"></span></button>
  </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    {{-- Logo = click to reload/go home --}}
    <a href="javascript:location.reload()" onclick="showPageLoader()" class="logo-link" title="Refresh page">
      <div class="logo-text">🚗 BROCAR</div>
      <div class="logo-sub">Ride Nepal · Jhapa</div>
    </a>
  </div>

  <nav class="sidebar-nav">
    @php $role = auth()->user()->role; @endphp

    @if($role === 'rider')
    <div class="nav-section">Main</div>
    <a href="{{ route('rider.dashboard') }}"    class="nav-link @active('rider/dashboard')"><span class="icon">🏠</span>Dashboard</a>
    <a href="{{ route('rider.request-ride') }}" class="nav-link @active('rider/request-ride')"><span class="icon">🚗</span>Book a Ride</a>
    <a href="{{ route('rider.active-ride') }}"  class="nav-link @active('rider/active-ride')"><span class="icon">📍</span>Active Ride</a>
    <div class="nav-section">History & More</div>
    <a href="{{ route('rider.history') }}"      class="nav-link @active('rider/history')"><span class="icon">📋</span>My Trips</a>
    <a href="{{ route('rider.schedule') }}"     class="nav-link @active('rider/schedule')"><span class="icon">📅</span>Schedule</a>
    <div class="nav-section">Account</div>
    <a href="{{ route('wallet.index') }}"       class="nav-link @active('wallet')"><span class="icon">👛</span>Wallet</a>
    <a href="{{ route('delivery.index') }}"     class="nav-link @active('delivery')"><span class="icon">📦</span>Delivery</a>
    <a href="{{ route('support.index') }}"      class="nav-link @active('support')"><span class="icon">🎧</span>Support</a>
    <a href="{{ route('rider.profile') }}"      class="nav-link @active('rider/profile')"><span class="icon">👤</span>Profile</a>
    @endif

    @if($role === 'driver')
    @php $dp = auth()->user()->driverProfile; @endphp
    @if($dp && $dp->status === 'approved')
    {{-- Vehicle type badge --}}
    <div style="margin:10px 12px;padding:10px 14px;background:rgba(255,85,0,.08);border:1px solid rgba(255,85,0,.2);border-radius:12px">
      <p style="font-size:10px;font-weight:800;color:var(--text3);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Your Vehicle</p>
      <p style="font-size:15px;font-weight:900;color:var(--brand)">
        {{ match($dp->vehicle_type ?? '') {
          'bike'  => '🏍️ Bike',
          'auto'  => '🛺 Auto Rickshaw',
          'car'   => '🚗 Car',
          'suv'   => '🚙 SUV',
          'truck' => '🚚 Truck',
          default => '🚗 ' . ucfirst($dp->vehicle_type ?? 'Unknown')
        } }}
      </p>
      <p style="font-size:11px;color:var(--text3);margin-top:2px">{{ $dp->vehicle_number ?? '' }} · {{ $dp->vehicle_model ?? '' }}</p>
      <div style="display:flex;align-items:center;gap:6px;margin-top:6px">
        <div style="width:7px;height:7px;border-radius:50%;background:{{ $dp->is_online ? 'var(--green)':'var(--text3)' }}"></div>
        <span style="font-size:11px;font-weight:700;color:{{ $dp->is_online ? 'var(--green)':'var(--text3)' }}">
          {{ $dp->is_online ? 'Online' : 'Offline' }}
        </span>
      </div>
    </div>
    <div class="nav-section">Main</div>
    <a href="{{ route('driver.dashboard') }}"   class="nav-link @active('driver/dashboard')"><span class="icon">🏠</span>Dashboard</a>
    <a href="{{ route('driver.active-ride') }}" class="nav-link @active('driver/active-ride')"><span class="icon">📍</span>Active Ride</a>
    <a href="{{ route('driver.requests') }}"    class="nav-link @active('driver/requests')"><span class="icon">📋</span>My Bids</a>
    <div class="nav-section">Earnings</div>
    <a href="{{ route('driver.earnings') }}"    class="nav-link @active('driver/earnings')"><span class="icon">💰</span>Earnings</a>
    <a href="{{ route('wallet.index') }}"       class="nav-link @active('wallet')"><span class="icon">👛</span>Wallet</a>
    <div class="nav-section">Account</div>
    <a href="{{ route('driver.documents') }}"   class="nav-link @active('driver/documents')"><span class="icon">📄</span>Documents</a>
    <a href="{{ route('driver.profile') }}"     class="nav-link @active('driver/profile')"><span class="icon">👤</span>Profile</a>
    <a href="{{ route('support.index') }}"      class="nav-link @active('support')"><span class="icon">🎧</span>Support</a>
    @else
    <a href="{{ route('driver.pending') }}"   class="nav-link active"><span class="icon">⏳</span>Pending Approval</a>
    <a href="{{ route('driver.documents') }}" class="nav-link"><span class="icon">📄</span>Documents</a>
    <a href="{{ route('driver.profile') }}"   class="nav-link"><span class="icon">👤</span>Profile</a>
    @endif
    @endif

    @if($role === 'admin')
    <div class="nav-section">Overview</div>
    <a href="{{ route('admin.dashboard') }}"  class="nav-link @active('admin/dashboard')"><span class="icon">🏠</span>Dashboard</a>
    <a href="{{ route('admin.analytics') }}"  class="nav-link @active('admin/analytics')"><span class="icon">📊</span>Analytics</a>
    <a href="{{ route('admin.finance') }}"    class="nav-link @active('admin/finance')"><span class="icon">💰</span>Finance</a>
    <div class="nav-section">Management</div>
    <a href="{{ route('admin.drivers') }}"    class="nav-link @active('admin/drivers')"><span class="icon">🧑‍✈️</span>Drivers</a>
    <a href="{{ route('admin.users') }}"      class="nav-link @active('admin/users')"><span class="icon">👥</span>Users</a>
    <a href="{{ route('admin.rides') }}"      class="nav-link @active('admin/rides')"><span class="icon">🚗</span>Rides</a>
    <div class="nav-section">Safety & Support</div>
    <a href="{{ route('admin.sos') }}"        class="nav-link @active('admin/sos')"><span class="icon">🆘</span>SOS Alerts</a>
    <a href="{{ route('admin.tickets') }}"    class="nav-link @active('admin/tickets')"><span class="icon">🎧</span>Tickets</a>
    <a href="{{ route('admin.complaints') }}" class="nav-link @active('admin/complaints')"><span class="icon">😠</span>Complaints</a>
    <a href="{{ route('admin.promos') }}"     class="nav-link @active('admin/promos')"><span class="icon">🎁</span>Promos</a>
    @endif
  </nav>

  <div class="sidebar-user">
    <div class="user-card">
      @php $u = auth()->user(); @endphp
      @if($u->avatar_url)
      <img src="{{ asset('storage/'.$u->avatar_url) }}" class="user-avatar" style="display:block">
      @else
      <div class="user-avatar">{{ strtoupper(substr($u->first_name ?? 'U', 0, 1)) }}</div>
      @endif
      <div style="flex:1;min-width:0">
        <div class="user-name">{{ $u->first_name }} {{ $u->last_name }}</div>
        <div class="user-role">{{ $u->role }} · <span class="npr-tag">NPR {{ number_format($u->wallet_balance) }}</span></div>
      </div>
      <form method="POST" action="{{ route('auth.logout') }}" style="flex-shrink:0">
        @csrf <button type="submit" style="background:none;border:none;font-size:17px;cursor:pointer;color:var(--text3)" title="Logout">🚪</button>
      </form>
    </div>
  </div>
</aside>
@endauth

<!-- Main -->
<div class="main-wrap">
  @auth
  <div class="topbar">
    <a href="{{ url('/') }}" onclick="showPageLoader()" style="font-size:18px;font-weight:900;background:linear-gradient(135deg,var(--brand),var(--brand2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-right:12px;cursor:pointer">BROCAR</a>
    <div class="topbar-title">@yield('title','Dashboard')</div>
    <div class="topbar-actions">
      <button class="icon-btn" onclick="toggleNotifPanel()">
        🔔<span class="notif-dot" id="notifDot" style="display:none"></span>
      </button>
      <div style="font-size:12px;color:var(--text3);background:rgba(255,85,0,.08);padding:5px 10px;border-radius:8px;font-weight:700">
        NPR {{ number_format(auth()->user()->wallet_balance) }}
      </div>
    </div>
  </div>
  @endauth

  <!-- Notification Panel -->
  <div class="notif-panel" id="notifPanel">
    <div class="notif-header">
      <span>🔔 Notifications</span>
      <button onclick="toggleNotifPanel()" style="background:none;color:var(--text3);font-size:18px;line-height:1">×</button>
    </div>
    <div class="notif-list" id="notifList">
      <div style="padding:24px;text-align:center;color:var(--text3);font-size:13px">Loading…</div>
    </div>
  </div>

  <main class="main-content" id="mainContent" style="opacity:0;transform:translateY(16px);transition:opacity .4s ease,transform .4s ease">
    @if(session('flash_success'))
    <script>document.addEventListener('DOMContentLoaded',()=>showToast('{{ addslashes(session('flash_success')) }}','success'))</script>
    @endif
    @if(session('flash_info'))
    <script>document.addEventListener('DOMContentLoaded',()=>showToast('{{ addslashes(session('flash_info')) }}','info'))</script>
    @endif
    @if($errors->any())
    <script>document.addEventListener('DOMContentLoaded',()=>showToast('{{ addslashes($errors->first()) }}','error'))</script>
    @endif
    @yield('content')
  </main>
</div>

<div id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const CSRF_TOKEN = document.querySelector('meta[name=csrf-token]')?.content ?? '';

// ── Page Loader ──────────────────────────────────────────────────
function showPageLoader() {
  const loader = document.getElementById('pageLoader');
  if (loader) { loader.style.opacity = '1'; loader.style.pointerEvents = 'all'; }
}
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    const loader = document.getElementById('pageLoader');
    if (loader) {
      loader.style.opacity = '0';
      loader.style.pointerEvents = 'none';
      setTimeout(() => { if(loader) loader.style.display='none'; }, 500);
    }
    // Animate main content in after loader hides
    const main = document.getElementById('mainContent');
    if (main) {
      setTimeout(() => {
        main.style.opacity = '1';
        main.style.transform = 'translateY(0)';
      }, 200);
    }
  }, 900);
});
// Show loader on any nav link click
document.addEventListener('DOMContentLoaded', () => {
  // ── Nav loader on page navigate
  document.querySelectorAll('.nav-link, .btn-brand[href], a.btn').forEach(a => {
    if (a.href && !a.href.includes('#') && !a.getAttribute('onclick')?.includes('toggle')) {
      a.addEventListener('click', (e) => {
        showPageLoader();
      });
    }
  });

  // ── Ripple effect on any .btn-ripple
  document.querySelectorAll('.btn-ripple, .btn, .btn-brand, .btn-ghost, .btn-red').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      const wave = document.createElement('span');
      wave.className = 'ripple-wave';
      wave.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px;position:absolute;border-radius:50%;background:rgba(255,255,255,.2);transform:scale(0);animation:ripple .55s linear;pointer-events:none`;
      if (!this.style.position || this.style.position === 'static') this.style.position = 'relative';
      this.style.overflow = 'hidden';
      this.appendChild(wave);
      setTimeout(() => wave.remove(), 600);
    });
  });

  // ── Stat number tick-up animation (counters in dashboard)
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count) || 0;
    if (!target) return;
    let cur = 0;
    const step = Math.max(1, Math.floor(target / 40));
    const timer = setInterval(() => {
      cur = Math.min(cur + step, target);
      el.textContent = cur.toLocaleString();
      if (cur >= target) clearInterval(timer);
    }, 30);
  });

  // ── Animate .anim-stagger children
  document.querySelectorAll('.anim-stagger').forEach(parent => {
    Array.from(parent.children).forEach((child, i) => {
      child.style.animationDelay = (i * 0.07) + 's';
    });
  });

  // ── Sidebar link active highlight animate
  document.querySelectorAll('.nav-link.active').forEach(el => {
    el.style.animation = 'slideInLeft .3s ease both';
  });
});

// ── Toast (animated slide-in/out) ────────────────────────────────
function showToast(msg, type='success') {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.style.cssText = 'transform:translateX(120px);opacity:0;transition:transform .35s ease,opacity .35s ease';
  const icons = {success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
  el.innerHTML = `<span>${icons[type]||'ℹ️'}</span><span>${msg}</span>`;
  document.getElementById('toastContainer').appendChild(el);
  // slide in
  requestAnimationFrame(() => requestAnimationFrame(() => {
    el.style.transform = 'translateX(0)';
    el.style.opacity = '1';
  }));
  // slide out before remove
  setTimeout(() => {
    el.style.transform = 'translateX(120px)';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 380);
  }, 4200);
}

// ── Leaflet helpers ──────────────────────────────────────────────
function addLeafletDarkTiles(map) {
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',{
    attribution:'', maxZoom:19
  }).addTo(map);
}

// ── Sidebar ──────────────────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
document.getElementById('hamburgerBtn')?.addEventListener('click', () => {
  sidebar?.classList.toggle('open');
  overlay?.classList.toggle('open');
});
function closeSidebar() {
  sidebar?.classList.remove('open');
  overlay?.classList.remove('open');
}

// ── Notification panel ───────────────────────────────────────────
function toggleNotifPanel() {
  const p = document.getElementById('notifPanel');
  if (!p) return;
  p.classList.toggle('open');
  if (p.classList.contains('open')) loadNotifications();
}
async function loadNotifications() {
  try {
    const r = await fetch('/notifications');
    const data = await r.json();
    const list = document.getElementById('notifList');
    if (!data.length) { list.innerHTML='<div style="padding:24px;text-align:center;color:var(--text3);font-size:13px">No notifications</div>'; return; }
    list.innerHTML = data.map(n=>`
      <div class="notif-item ${n.is_read?'':'unread'}">
        <span class="notif-icon">🔔</span>
        <div class="notif-body">
          <p>${n.title}</p>
          <p>${(n.body||'').substring(0,80)} · ${n.created_at}</p>
        </div>
      </div>`).join('');
  } catch(e) {}
}
async function pollNotifCount() {
  try {
    const r = await fetch('/notifications/count');
    const { count } = await r.json();
    ['notifDot','notifDotMobile'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.style.display = count > 0 ? 'block' : 'none';
    });
  } catch(e) {}
}
@auth
pollNotifCount(); setInterval(pollNotifCount, 30000);
@endauth
document.addEventListener('click', e => {
  const panel = document.getElementById('notifPanel');
  if (panel?.classList.contains('open') && !panel.contains(e.target) && !e.target.closest('.icon-btn')) {
    panel.classList.remove('open');
  }
});
</script>
@stack('scripts')
</body>
</html>
