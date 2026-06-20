<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BROCAR — Jhapa's #1 Ride-Hailing Service</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
/* ═══════════════════════ PAGE ENTRANCE LOADER ═══════════════════════ */
#pageLoader{
  position:fixed;inset:0;z-index:99999;background:#0A0A0F;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  transition:opacity .7s ease,visibility .7s ease;
}
#pageLoader.hide{opacity:0;visibility:hidden;pointer-events:none}
.loader-logo-wrap{display:flex;align-items:center}
.loader-logo{
  font-size:54px;font-weight:900;letter-spacing:-2px;
  background:linear-gradient(135deg,#FF5500,#FFB347);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  animation:logoPop .9s cubic-bezier(.175,.885,.32,1.275) both;
}
.loader-car{
  font-size:28px;opacity:0;margin-left:6px;
  animation:carDrive 1.4s .5s ease-in-out forwards;
}
.loader-sub{font-size:11px;letter-spacing:4px;color:#666680;margin-top:6px;text-transform:uppercase;animation:fadeIn .6s .4s both}
.loader-bar{width:200px;height:3px;background:rgba(255,85,0,.15);border-radius:99px;margin-top:30px;overflow:hidden;animation:fadeIn .4s .5s both}
.loader-fill{height:100%;background:linear-gradient(90deg,#FF5500,#FFB347);border-radius:99px;animation:loaderFill 1.5s .5s ease-out both}
@keyframes logoPop{from{opacity:0;transform:scale(.6) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes carDrive{0%{opacity:0;transform:translateX(-60px)}50%{opacity:1;transform:translateX(10px)}100%{opacity:1;transform:translateX(0)}}
@keyframes loaderFill{from{width:0}to{width:100%}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}

/* ═══════════════════════ ROOT TOKENS ═══════════════════════ */
:root{
  --brand:#FF5500;--brand2:#FF7A30;--dark:#0A0A0F;--card:#111118;
  --card2:#16161F;--border:rgba(255,255,255,.07);--text1:#F0F0F5;
  --text2:#A0A0B0;--text3:#666680;--green:#22C55E;--blue:#38BDF8;
  --amber:#F59E0B;--red:#EF4444;--purple:#A855F7;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--dark);color:var(--text1);font-family:'Segoe UI',system-ui,sans-serif;overflow-x:hidden}
a{text-decoration:none;color:inherit}

/* ═══════════════════════ ANIMATIONS ═══════════════════════ */
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes scaleIn{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.05)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-16px)}}
@keyframes orbFloat{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-20px) scale(1.05)}66%{transform:translate(-15px,15px) scale(.97)}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0)}33%{transform:translate(-25px,18px)}66%{transform:translate(20px,-12px)}}
@keyframes ripple{0%{transform:scale(0);opacity:.5}100%{transform:scale(4.5);opacity:0}}
@keyframes carMove{0%{transform:translateX(-130px) scaleX(-1);opacity:0}8%{opacity:1}92%{opacity:1}100%{transform:translateX(calc(100vw + 130px)) scaleX(-1);opacity:0}}
@keyframes carMove2{0%{transform:translateX(calc(100vw + 90px));opacity:0}8%{opacity:.65}92%{opacity:.65}100%{transform:translateX(-130px);opacity:0}}
@keyframes gradMove{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
@keyframes spinSlow{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
@keyframes borderGlow{0%,100%{box-shadow:0 0 0 0 rgba(255,85,0,.35)}50%{box-shadow:0 0 24px 4px rgba(255,85,0,.15)}}
@keyframes ping{0%{transform:scale(1);opacity:1}75%,100%{transform:scale(2.2);opacity:0}}
@keyframes shimmer{0%{background-position:-600px 0}100%{background-position:600px 0}}

/* ── Scroll Reveal ── */
.reveal{opacity:0;transform:translateY(40px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:translateY(0)}
.reveal-left{opacity:0;transform:translateX(-40px);transition:opacity .7s ease,transform .7s ease}
.reveal-left.visible{opacity:1;transform:translateX(0)}
.reveal-right{opacity:0;transform:translateX(40px);transition:opacity .7s ease,transform .7s ease}
.reveal-right.visible{opacity:1;transform:translateX(0)}
.reveal-scale{opacity:0;transform:scale(.88);transition:opacity .6s ease,transform .6s ease}
.reveal-scale.visible{opacity:1;transform:scale(1)}
.stagger>*{opacity:0;transform:translateY(28px);transition:opacity .55s ease,transform .55s ease}
.stagger.visible>*{opacity:1;transform:translateY(0)}
.stagger.visible>*:nth-child(1){transition-delay:.05s}
.stagger.visible>*:nth-child(2){transition-delay:.12s}
.stagger.visible>*:nth-child(3){transition-delay:.19s}
.stagger.visible>*:nth-child(4){transition-delay:.26s}
.stagger.visible>*:nth-child(5){transition-delay:.33s}
.stagger.visible>*:nth-child(6){transition-delay:.40s}
.stagger.visible>*:nth-child(7){transition-delay:.47s}
.stagger.visible>*:nth-child(8){transition-delay:.54s}
.stagger.visible>*:nth-child(9){transition-delay:.61s}
.stagger.visible>*:nth-child(10){transition-delay:.68s}
.stagger.visible>*:nth-child(11){transition-delay:.75s}
.stagger.visible>*:nth-child(12){transition-delay:.82s}

/* ═══════════════════════ NAVBAR ═══════════════════════ */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:999;
  background:rgba(10,10,15,.01);backdrop-filter:blur(0px);
  border-bottom:1px solid transparent;padding:0 5%;
  transition:all .4s ease;
}
.nav.scrolled{background:rgba(10,10,15,.9);backdrop-filter:blur(24px);border-bottom:1px solid var(--border)}
.nav-inner{display:flex;align-items:center;justify-content:space-between;height:68px}
.logo{
  font-size:26px;font-weight:900;
  background:linear-gradient(135deg,var(--brand),var(--brand2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  letter-spacing:-1px;cursor:pointer;transition:.2s;
}
.logo span{-webkit-text-fill-color:var(--text2);font-weight:400;font-size:12px;display:block;margin-top:-4px;letter-spacing:3px}
.logo:hover{transform:scale(1.04)}
.nav-links{display:flex;gap:30px;align-items:center}
.nav-links a{
  font-size:14px;color:var(--text2);font-weight:500;
  transition:color .2s;position:relative;
}
.nav-links a::after{content:'';position:absolute;bottom:-2px;left:0;right:0;height:2px;background:var(--brand);border-radius:2px;transform:scaleX(0);transition:.2s}
.nav-links a:hover{color:var(--text1)}
.nav-links a:hover::after{transform:scaleX(1)}
.btn-nav{
  background:linear-gradient(135deg,var(--brand),var(--brand2));
  color:#fff;padding:9px 22px;border-radius:50px;
  font-size:14px;font-weight:700;transition:all .25s;cursor:pointer;
  box-shadow:0 4px 16px rgba(255,85,0,.3);position:relative;overflow:hidden;
}
.btn-nav::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.15);transform:translateX(-100%) skewX(-15deg);transition:.4s}
.btn-nav:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(255,85,0,.45)}
.btn-nav:hover::after{transform:translateX(200%) skewX(-15deg)}

/* ═══════════════════════ ORBS ═══════════════════════ */
.orb{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0}
.orb-1{width:600px;height:600px;background:rgba(255,85,0,.08);top:-200px;right:-100px;animation:orbFloat 12s ease-in-out infinite}
.orb-2{width:400px;height:400px;background:rgba(168,85,247,.06);bottom:0;left:-100px;animation:orbFloat2 16s ease-in-out infinite}
.orb-3{width:280px;height:280px;background:rgba(56,189,248,.05);top:40%;left:50%;animation:orbFloat 10s 4s ease-in-out infinite}

/* ═══════════════════════ HERO ═══════════════════════ */
.hero{padding:160px 5% 100px;position:relative;overflow:hidden;min-height:100vh;display:flex;align-items:center}
#heroMap{position:absolute;inset:0;opacity:.18;z-index:0}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,10,15,.97) 0%,rgba(10,10,15,.5) 100%);z-index:1}
.hero-car{position:absolute;font-size:28px;z-index:2;pointer-events:none;filter:drop-shadow(0 4px 12px rgba(255,85,0,.4))}
.hero-car-1{top:35%;animation:carMove 18s 2s linear infinite}
.hero-car-2{top:55%;font-size:20px;animation:carMove2 22s 7s linear infinite;opacity:.5}
.hero-car-3{top:72%;font-size:15px;animation:carMove 30s 14s linear infinite;opacity:.3}
.hero-content{position:relative;z-index:3;max-width:700px}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(255,85,0,.1);border:1px solid rgba(255,85,0,.25);
  border-radius:50px;padding:8px 20px;
  font-size:12px;font-weight:700;color:var(--brand);letter-spacing:1.5px;
  margin-bottom:28px;animation:fadeUp .6s .8s both;
}
.hero-badge-dot{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 1.5s ease-in-out infinite;flex-shrink:0}
.hero-title{font-size:clamp(44px,6.5vw,82px);font-weight:900;line-height:1.03;margin-bottom:24px;letter-spacing:-2.5px;animation:fadeUp .7s 1s both}
.hero-title .line1{display:block}
.hero-title .line2{
  background:linear-gradient(135deg,var(--brand) 0%,#FFB347 50%,var(--brand2) 100%);
  background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;
  animation:fadeUp .7s 1.1s both,gradMove 4s 2s linear infinite;
}
.hero-p{font-size:18px;color:var(--text2);line-height:1.75;margin-bottom:40px;max-width:540px;animation:fadeUp .7s 1.2s both}
.hero-btns{display:flex;gap:16px;flex-wrap:wrap;animation:fadeUp .7s 1.3s both}
.btn-hero{
  padding:16px 34px;border-radius:50px;font-size:16px;font-weight:800;cursor:pointer;
  transition:all .3s;border:none;display:inline-flex;align-items:center;gap:10px;
  position:relative;overflow:hidden;
}
.btn-hero-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 8px 32px rgba(255,85,0,.4)}
.btn-hero-primary::after{content:'';position:absolute;inset:0;background:rgba(255,255,255,.12);transform:translateX(-100%) skewX(-20deg);transition:.4s}
.btn-hero-primary:hover{transform:translateY(-4px);box-shadow:0 16px 44px rgba(255,85,0,.55)}
.btn-hero-primary:hover::after{transform:translateX(200%) skewX(-20deg)}
.btn-hero-outline{background:rgba(255,255,255,.04);color:var(--text1);border:1.5px solid rgba(255,255,255,.12);backdrop-filter:blur(10px)}
.btn-hero-outline:hover{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.25);transform:translateY(-3px)}
.btn-ripple{position:relative;overflow:hidden}
.btn-ripple .ripple-effect{position:absolute;border-radius:50%;background:rgba(255,255,255,.3);transform:scale(0);animation:ripple .6s linear;pointer-events:none}
.hero-stats{display:flex;gap:40px;margin-top:64px;flex-wrap:wrap;animation:fadeUp .7s 1.5s both}
.hero-stat{text-align:center;position:relative}
.hero-stat::after{content:'';position:absolute;right:-20px;top:50%;transform:translateY(-50%);width:1px;height:36px;background:var(--border)}
.hero-stat:last-child::after{display:none}
.hero-stat-val{font-size:36px;font-weight:900;color:var(--text1);line-height:1}
.hero-stat-label{font-size:11px;color:var(--text3);letter-spacing:1.5px;font-weight:600;text-transform:uppercase;margin-top:4px}
.scroll-hint{position:absolute;bottom:36px;left:50%;transform:translateX(-50%);z-index:4;display:flex;flex-direction:column;align-items:center;gap:6px;animation:fadeUp .8s 2s both}
.scroll-hint-text{font-size:11px;color:var(--text3);letter-spacing:2px;text-transform:uppercase}
.scroll-hint-arrow{width:28px;height:28px;border:1.5px solid rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;animation:float 2s ease-in-out infinite}

/* ═══════════════════════ COVERAGE BANNER ═══════════════════════ */
.coverage-banner{
  background:linear-gradient(90deg,rgba(255,85,0,.08) 0%,rgba(168,85,247,.06) 100%);
  border-top:1px solid rgba(255,85,0,.12);border-bottom:1px solid rgba(255,85,0,.12);
  padding:18px 5%;overflow:hidden;
}
.coverage-inner{display:flex;align-items:center;gap:24px;white-space:nowrap;overflow-x:auto}
.coverage-inner::-webkit-scrollbar{display:none}
.coverage-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);
  color:var(--green);font-size:12px;font-weight:700;letter-spacing:1px;
  padding:6px 14px;border-radius:50px;flex-shrink:0;
}
.coverage-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:pulse 1.6s ease-in-out infinite;position:relative}
.coverage-dot::after{content:'';position:absolute;inset:-3px;border-radius:50%;border:2px solid var(--green);opacity:.5;animation:ping 1.8s ease-in-out infinite}
.coverage-city{font-size:13px;color:var(--text2);font-weight:600;flex-shrink:0}
.coverage-sep{color:var(--text3);flex-shrink:0}
.coverage-soon{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);
  color:var(--amber);font-size:11px;font-weight:700;letter-spacing:1px;
  padding:5px 12px;border-radius:50px;flex-shrink:0;
}

/* ═══════════════════════ SECTION COMMONS ═══════════════════════ */
section{padding:100px 5%;position:relative;overflow:hidden}
.section-badge{display:inline-block;background:rgba(255,85,0,.08);border:1px solid rgba(255,85,0,.15);color:var(--brand);font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase;padding:6px 16px;border-radius:50px;margin-bottom:20px}
.section-title{font-size:clamp(32px,4vw,52px);font-weight:900;line-height:1.1;letter-spacing:-1.2px}
.section-desc{font-size:17px;color:var(--text2);line-height:1.7;margin-top:16px;max-width:560px}

/* ═══════════════════════ HOW IT WORKS ═══════════════════════ */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0;margin-top:60px;position:relative}
.steps-connector{position:absolute;top:36px;left:12%;right:12%;height:2px;z-index:0}
.steps-connector-fill{height:100%;background:linear-gradient(90deg,var(--brand),var(--brand2));opacity:.25;border-radius:2px}
.step{text-align:center;padding:24px 16px;position:relative;z-index:1}
.step-num{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--brand2));display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 20px;box-shadow:0 8px 32px rgba(255,85,0,.3);transition:transform .35s,box-shadow .35s;position:relative}
.step-num::after{content:'';position:absolute;inset:-5px;border-radius:50%;border:2px dashed rgba(255,85,0,.2);animation:spinSlow 12s linear infinite}
.step:hover .step-num{transform:scale(1.12) rotate(-5deg);box-shadow:0 16px 48px rgba(255,85,0,.45)}
.step-title{font-size:15px;font-weight:800;margin-bottom:8px}
.step-desc{font-size:13px;color:var(--text3);line-height:1.55}

/* ═══════════════════════ VEHICLE CARDS ═══════════════════════ */
.vehicles-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(185px,1fr));gap:16px;margin-top:50px}
.vehicle-card{
  background:var(--card);border:2px solid var(--border);border-radius:20px;
  padding:28px 18px;text-align:center;transition:all .3s;cursor:pointer;position:relative;overflow:hidden;
}
.vehicle-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 110%,rgba(255,85,0,.12) 0%,transparent 65%);opacity:0;transition:.4s}
.vehicle-card:hover{border-color:rgba(255,85,0,.4);transform:translateY(-8px);box-shadow:0 20px 50px rgba(255,85,0,.15)}
.vehicle-card:hover::before{opacity:1}
.vehicle-emoji{font-size:52px;margin-bottom:14px;display:block;transition:transform .35s;filter:drop-shadow(0 4px 12px rgba(0,0,0,.5))}
.vehicle-card:hover .vehicle-emoji{transform:scale(1.2) translateY(-6px)}
.vehicle-name{font-size:16px;font-weight:800;margin-bottom:4px}
.vehicle-fare{font-size:22px;font-weight:900;background:linear-gradient(135deg,var(--brand),#FFB347);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:2px}
.vehicle-perkm{font-size:11px;color:var(--text3);margin-bottom:6px}
.vehicle-desc{font-size:11px;color:var(--text3)}
.vehicle-tag{display:inline-block;margin-top:10px;background:rgba(255,85,0,.1);color:var(--brand);font-size:9.5px;font-weight:700;letter-spacing:1px;padding:3px 10px;border-radius:50px}

/* ═══════════════════════ FARE TABLE ═══════════════════════ */
.fare-table{width:100%;border-collapse:collapse;margin-top:24px}
.fare-table th{text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:var(--text3);padding:10px 16px;border-bottom:1px solid var(--border)}
.fare-table td{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.04);font-size:14px;color:var(--text2)}
.fare-table tr:hover td{background:rgba(255,255,255,.02)}
.fare-table td:first-child{font-weight:700;color:var(--text1);font-size:15px}
.fare-table td:last-child{color:var(--brand);font-weight:700}

/* ═══════════════════════ FEATURE CARDS ═══════════════════════ */
.features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:60px}
.feature-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:30px;transition:transform .3s,border-color .3s,box-shadow .3s;position:relative;overflow:hidden;cursor:default}
.feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--accent-color,var(--brand));opacity:0;transition:.3s}
.feature-card::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at var(--mx,50%) var(--my,50%),rgba(255,85,0,.06) 0%,transparent 65%);opacity:0;transition:.3s}
.feature-card:hover{transform:translateY(-8px);border-color:rgba(255,85,0,.18);box-shadow:0 24px 60px rgba(0,0,0,.5)}
.feature-card:hover::before{opacity:1}
.feature-card:hover::after{opacity:1}
.feature-icon{width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:18px;transition:transform .3s}
.feature-card:hover .feature-icon{transform:scale(1.15) rotate(-4deg)}
.feature-title{font-size:17px;font-weight:800;margin-bottom:9px}
.feature-desc{font-size:13.5px;color:var(--text2);line-height:1.65}

/* ═══════════════════════ MAP SECTION ═══════════════════════ */
.map-section-inner{display:grid;grid-template-columns:1fr 1.2fr;gap:60px;align-items:center}
.map-preview{position:relative;border-radius:24px;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.6)}
.map-preview::after{content:'';position:absolute;inset:0;background:linear-gradient(to right,transparent 70%,var(--card) 100%),linear-gradient(to bottom,transparent 70%,var(--card) 100%);pointer-events:none}
#liveMapPreview{height:380px;border-radius:24px}
.map-badge{position:absolute;top:16px;left:16px;z-index:10;background:rgba(10,10,15,.85);backdrop-filter:blur(12px);border:1px solid rgba(34,197,94,.3);color:var(--green);font-size:12px;font-weight:700;padding:6px 14px;border-radius:50px;display:flex;align-items:center;gap:6px}
.map-badge-dot{width:8px;height:8px;border-radius:50%;background:var(--green);animation:pulse 1.5s ease-in-out infinite}

/* ═══════════════════════ DELIVERY ═══════════════════════ */
.delivery-grid{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:center;margin-top:50px}
.delivery-tiers{display:flex;flex-direction:column;gap:14px}
.delivery-tier{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;transition:all .3s;position:relative;overflow:hidden}
.delivery-tier::after{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--brand);transform:scaleY(0);transition:.3s;transform-origin:bottom;border-radius:0 2px 2px 0}
.delivery-tier:hover{border-color:rgba(255,85,0,.2);transform:translateX(8px)}
.delivery-tier:hover::after{transform:scaleY(1)}
.tier-icon{font-size:34px;width:52px;text-align:center;flex-shrink:0;transition:transform .3s}
.delivery-tier:hover .tier-icon{transform:scale(1.15) rotate(-8deg)}
.tier-name{font-size:15px;font-weight:800}
.tier-desc{font-size:12px;color:var(--text3);margin-top:2px}
.tier-price{margin-left:auto;font-size:17px;font-weight:900;color:var(--brand);flex-shrink:0}

/* ═══════════════════════ SAFETY ═══════════════════════ */
.safety-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;margin-top:50px}
.safety-card{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:26px;display:flex;gap:16px;align-items:flex-start;transition:all .3s}
.safety-card:hover{border-color:rgba(34,197,94,.2);transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.4)}
.safety-icon{font-size:30px;flex-shrink:0;margin-top:2px;transition:transform .3s}
.safety-card:hover .safety-icon{transform:scale(1.2) rotate(-5deg)}

/* ═══════════════════════ TESTIMONIALS ═══════════════════════ */
.testimonials{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:50px}
.testimonial{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:30px;transition:all .35s;position:relative;overflow:hidden}
.testimonial::before{content:'"';position:absolute;top:-10px;right:20px;font-size:120px;color:rgba(255,85,0,.05);font-family:Georgia,serif;line-height:1;pointer-events:none}
.testimonial:hover{transform:translateY(-6px);border-color:rgba(255,85,0,.18);box-shadow:0 20px 50px rgba(0,0,0,.4)}
.t-stars{color:#F59E0B;font-size:17px;margin-bottom:14px;letter-spacing:2px}
.t-text{font-size:14px;color:var(--text2);line-height:1.75;margin-bottom:20px}
.t-author{display:flex;align-items:center;gap:12px}
.t-avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0}
.t-name{font-size:13px;font-weight:700}
.t-role{font-size:11px;color:var(--text3);margin-top:2px}

/* ═══════════════════════ DRIVER CTA ═══════════════════════ */
.driver-cta{background:linear-gradient(135deg,rgba(255,85,0,.1) 0%,rgba(168,85,247,.07) 100%);border:1px solid rgba(255,85,0,.15);border-radius:28px;padding:70px;text-align:center;margin-top:60px;position:relative;overflow:hidden;animation:borderGlow 4s ease-in-out infinite}
.driver-cta::before{content:'';position:absolute;top:-100px;right:-100px;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(255,85,0,.08) 0%,transparent 70%);pointer-events:none}
.cta-stat-val{font-size:30px;font-weight:900;color:var(--brand)}
.cta-stat-label{font-size:11px;color:var(--text3);margin-top:4px;letter-spacing:1px;text-transform:uppercase}

/* ═══════════════════════ WALLET SECTION ═══════════════════════ */
.payment-methods{display:flex;gap:12px;flex-wrap:wrap;margin-top:20px}
.payment-chip{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--card);border:1px solid var(--border);
  border-radius:10px;padding:10px 16px;font-size:13px;font-weight:700;
  transition:all .25s;
}
.payment-chip:hover{border-color:rgba(255,85,0,.3);background:rgba(255,85,0,.06);transform:translateY(-2px)}

/* ═══════════════════════ FOOTER ═══════════════════════ */
footer{background:var(--card);border-top:1px solid var(--border);padding:70px 5% 36px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;margin-bottom:60px}
.footer-brand{font-size:30px;font-weight:900;background:linear-gradient(135deg,var(--brand),var(--brand2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.footer-desc{font-size:13px;color:var(--text3);line-height:1.75;margin-top:12px}
.footer-col h4{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--text3);margin-bottom:18px}
.footer-col a{display:block;font-size:13px;color:var(--text2);margin-bottom:10px;transition:.2s}
.footer-col a:hover{color:var(--brand);transform:translateX(4px)}
.footer-social{display:flex;gap:10px;margin-top:22px}
.footer-social-btn{width:36px;height:36px;border-radius:9px;background:rgba(255,255,255,.05);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:15px;transition:all .25s;cursor:pointer}
.footer-social-btn:hover{background:rgba(255,85,0,.15);border-color:rgba(255,85,0,.3);transform:translateY(-3px)}
.footer-bottom{border-top:1px solid var(--border);padding-top:28px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.footer-bottom p{font-size:12px;color:var(--text3)}
@media(max-width:768px){
  .nav-links{display:none}
  .hero{padding:120px 5% 80px}
  .hero-stats{gap:20px}
  .hero-stat::after{display:none}
  .footer-grid{grid-template-columns:1fr 1fr}
  .delivery-grid{grid-template-columns:1fr}
  .map-section-inner{grid-template-columns:1fr}
  .driver-cta{padding:40px 24px}
  .coverage-inner{padding-bottom:4px}
}
</style>
</head>
<body>

<!-- ══════════════ PAGE LOADER ══════════════ -->
<div id="pageLoader">
  <div class="loader-logo-wrap">
    <div class="loader-logo">BROCAR</div>
    <div class="loader-car">🚗</div>
  </div>
  <div class="loader-sub">Jhapa · Nepal</div>
  <div class="loader-bar"><div class="loader-fill"></div></div>
</div>

<!-- ══════════════ NAVBAR ══════════════ -->
<nav class="nav" id="mainNav">
  <div class="nav-inner">
    <a href="/" class="logo" onclick="event.preventDefault();window.location.reload()">
      BROCAR<span>RIDE JHAPA</span>
    </a>
    <div class="nav-links">
      <a href="#how-it-works">How It Works</a>
      <a href="#vehicles">Vehicles & Fares</a>
      <a href="#features">Features</a>
      <a href="#delivery">Delivery</a>
      <a href="#safety">Safety</a>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      @auth
        <a href="{{ url('/dashboard') }}" class="btn-nav btn-ripple" style="background:transparent;border:1.5px solid rgba(255,85,0,.5);color:var(--brand);box-shadow:none">Dashboard →</a>
      @else
        <a href="javascript:void(0)" onclick="openSignInModal()" class="btn-nav-outline btn-ripple" style="padding:9px 20px;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;border:1.5px solid rgba(255,255,255,.15);color:var(--text1);background:rgba(255,255,255,.04);transition:all .25s;backdrop-filter:blur(10px)">Sign In</a>
        <a href="{{ route('auth.phone') }}?mode=register" class="btn-nav btn-ripple">Get Started →</a>
      @endauth
    </div>
  </div>
</nav>

<!-- ══════════════ HERO ══════════════ -->
<section class="hero" id="home">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div id="heroMap"></div>
  <div class="hero-overlay"></div>
  <div class="hero-car hero-car-1">🏍️</div>
  <div class="hero-car hero-car-2">🚗</div>
  <div class="hero-car hero-car-3">🛺</div>

  <div class="hero-content">
    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      NOW LIVE IN JHAPA DISTRICT, NEPAL
    </div>
    <h1 class="hero-title">
      <span class="line1">Jhapa's #1</span>
      <span class="line2">Ride-Hailing App</span>
    </h1>
    <p class="hero-p">
      Enter your phone, pick a driver's bid, and go — no surge pricing, ever.
      Covering Birtamod, Mechinagar, Damak, Bhadrapur &amp; all of Jhapa district.
    </p>
    <div class="hero-btns">
      <a href="{{ route('auth.phone') }}?role=rider" class="btn-hero btn-hero-primary btn-ripple">
        🚗 Book a Ride
      </a>
      <a href="{{ route('auth.phone') }}?role=driver" class="btn-hero btn-hero-outline btn-ripple">
        🏍️ Drive & Earn
      </a>
    </div>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-val"><span class="counter" data-target="5000" data-suffix="K+" data-div="1000">0</span></div>
        <div class="hero-stat-label">Happy Riders</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val"><span class="counter" data-target="500" data-suffix="+">0</span></div>
        <div class="hero-stat-label">Verified Drivers</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val"><span class="counter" data-target="20000" data-suffix="K+" data-div="1000">0</span></div>
        <div class="hero-stat-label">Trips Completed</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-val">4.8<span style="color:var(--brand)">★</span></div>
        <div class="hero-stat-label">Average Rating</div>
      </div>
    </div>
  </div>

  <div class="scroll-hint">
    <div class="scroll-hint-text">Scroll</div>
    <div class="scroll-hint-arrow">↓</div>
  </div>
</section>

<!-- ══════════════ COVERAGE BANNER ══════════════ -->
<div class="coverage-banner">
  <div class="coverage-inner">
    <div class="coverage-badge">
      <div class="coverage-dot"></div>
      ACTIVE COVERAGE
    </div>
    @foreach(['Birtamod','Mechinagar','Damak','Bhadrapur','Shivasataxi','Charali','Gaurigunj','Kankai'] as $city)
    <div class="coverage-city">📍 {{ $city }}</div>
    @if(!$loop->last)<div class="coverage-sep">·</div>@endif
    @endforeach
    <div class="coverage-sep" style="margin-left:12px">·</div>
    <div class="coverage-soon">🕐 Outside Jhapa? Coming Soon!</div>
  </div>
</div>

<!-- ══════════════ HOW IT WORKS ══════════════ -->
<section id="how-it-works">
  <div style="text-align:center" class="reveal">
    <div class="section-badge">HOW IT WORKS</div>
    <div class="section-title">Book a Ride in 4 Steps</div>
    <div class="section-desc" style="margin:16px auto 0">No app download needed. Works in any browser on your phone.</div>
  </div>
  <div class="steps stagger" style="margin-top:60px">
    <div class="steps-connector"><div class="steps-connector-fill" style="width:100%"></div></div>
   @foreach([
  ['📱','Sign Up Instantly','Enter your phone, set a password, and log in instantly.'],
  ['📍','Pin Your Location','Drop your pickup and destination on the Jhapa map.'],
  ['💬','Pick the Best Bid','Drivers compete and send bids. You choose the fare you like.'],
  ['🏁','Ride & Rate','Track your driver live, chat, and rate after you arrive.'],
] as [$icon,$title,$desc])
    <div class="step">
      <div class="step-num">{{ $icon }}</div>
      <div class="step-title">{{ $title }}</div>
      <div class="step-desc">{{ $desc }}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- ══════════════ VEHICLES & FARES ══════════════ -->
<section id="vehicles" style="background:var(--card)">
  <div style="text-align:center" class="reveal">
    <div class="section-badge">VEHICLES & FARES</div>
    <div class="section-title">Transparent Fares,<br>No Surprises</div>
    <div class="section-desc" style="margin:16px auto 0">
      All fares in Nepali Rupees (NPR). No surge pricing — ever.
      You offer the fare, drivers bid, you decide.
    </div>
  </div>

  <div class="vehicles-grid stagger">
    @foreach([
      ['🏍️','Bike',  80, 15, 200, '1 passenger · Fastest','POPULAR'],
      ['🛺','Auto', 100, 20, 300, '3 passengers · Economical','BUDGET'],
      ['🚗','Car',  150, 30, 500, '4 passengers · Comfortable','COMFORT'],
      ['🚙','SUV',  200, 40, 700, '6 passengers · Premium','PREMIUM'],
      ['🚚','Truck',500, 20,null, 'Freight & heavy loads','FREIGHT'],
    ] as [$emoji,$name,$base,$km,$max,$desc,$tag])
    <div class="vehicle-card btn-ripple">
      <span class="vehicle-emoji">{{ $emoji }}</span>
      <div class="vehicle-name">{{ $name }}</div>
      <div class="vehicle-fare">NPR {{ $base }}+</div>
      <div class="vehicle-perkm">NPR {{ $km }}/km{{ $max?' · max NPR '.$max:'' }}</div>
      <div class="vehicle-desc">{{ $desc }}</div>
      <div class="vehicle-tag">{{ $tag }}</div>
    </div>
    @endforeach
  </div>

  {{-- Fare breakdown table --}}
  <div class="reveal" style="margin-top:48px;background:var(--card2);border:1px solid var(--border);border-radius:20px;overflow:hidden;max-width:680px;margin-left:auto;margin-right:auto">
    <div style="padding:20px 24px 0;font-size:14px;font-weight:800;color:var(--text1)">📊 Fare Breakdown</div>
    <table class="fare-table">
      <thead>
        <tr>
          <th>Vehicle</th>
          <th>Base Fare</th>
          <th>Per KM</th>
          <th>Max Cap</th>
        </tr>
      </thead>
      <tbody>
        @foreach([
          ['🏍️ Bike', 'NPR 80', 'NPR 15', 'NPR 200'],
          ['🛺 Auto', 'NPR 100', 'NPR 20', 'NPR 300'],
          ['🚗 Car',  'NPR 150', 'NPR 30', 'NPR 500'],
          ['🚙 SUV',  'NPR 200', 'NPR 40', 'NPR 700'],
          ['🚚 Truck','NPR 500', 'NPR 20', '—'],
        ] as [$v,$b,$k,$m])
        <tr>
          <td>{{ $v }}</td>
          <td>{{ $b }}</td>
          <td>{{ $k }}</td>
          <td>{{ $m }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div style="padding:14px 24px;font-size:12px;color:var(--text3);border-top:1px solid var(--border)">
      💡 Minimum fare NPR 50. You can apply a promo code at checkout for additional discounts.
    </div>
  </div>
</section>

<!-- ══════════════ LIVE MAP PREVIEW ══════════════ -->
<section style="padding:80px 5%;background:var(--dark)">
  <div class="map-section-inner">
    <div class="reveal-left">
      <div class="section-badge">REAL-TIME GPS</div>
      <div class="section-title">Watch Your Driver<br>Coming to You</div>
      <div class="section-desc">Live GPS updates every 5 seconds. Routes follow actual Jhapa roads — powered by OpenStreetMap, no external API key needed.</div>
      <ul style="margin-top:28px;display:flex;flex-direction:column;gap:12px;list-style:none">
        @foreach([
          ['🟢','Driver location updates every 5 seconds'],
          ['🗺️','Road route drawn via Leaflet Routing Machine + OSRM'],
          ['📍','Pickup & drop pins clearly visible on dark map'],
          ['💬','Chat & call driver from the same screen'],
          ['🆘','SOS button always visible during active ride'],
        ] as [$icon,$text])
        <li style="display:flex;align-items:center;gap:12px;font-size:14px;color:var(--text2)">
          <span>{{ $icon }}</span> {{ $text }}
        </li>
        @endforeach
      </ul>
    </div>
    <div class="reveal-right" style="position:relative">
      <div class="map-preview">
        <div class="map-badge">
          <div class="map-badge-dot"></div>
          LIVE JHAPA DRIVERS
        </div>
        <div id="liveMapPreview"></div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ FEATURES ══════════════ -->
<section id="features" style="background:var(--card)">
  <div style="text-align:center" class="reveal">
    <div class="section-badge">PLATFORM FEATURES</div>
    <div class="section-title">Everything Built Into<br>the App</div>
    <div class="section-desc" style="margin:16px auto 0">No third-party app installs. Everything works from your browser with just a phone number.</div>
  </div>
  <div class="features-grid stagger">
    @foreach([
      ['🗺️','var(--blue)','Live GPS Tracking','Leaflet dark map with real-time driver pin updated every 5 seconds. Route drawn on actual roads via OSRM.'],
      ['💰','var(--green)','Competitive Bidding','No fixed price. Drivers see your request and bid. You pick the best fare — saving you money every trip.'],
      ['💬','var(--purple)','In-App Chat','WhatsApp-style messaging with your driver. No phone number sharing needed. Works during the ride.'],
      ['📞','var(--amber)','One-Tap Calling','Tap to call your driver or rider directly from the app through BROCAR\'s secure relay.'],
      ['🆘','var(--red)','SOS Emergency Button','One tap sends your live GPS location to your trusted contacts and the BROCAR safety team.'],
      ['📦','var(--brand)','Parcel Delivery','Send documents, parcels, or freight across Jhapa. Live tracking from pickup to drop.'],
      ['👛','var(--green)','Digital Wallet','Top up via eSewa, Khalti, Connect IPS, or bank transfer. Pay rides directly from wallet balance.'],
      ['⭐','var(--amber)','Dual Rating System','Riders rate drivers, drivers rate riders. Low-rated accounts get suspended automatically.'],
      ['🔗','var(--blue)','Trip Sharing','Share your live trip link with family so they can track your journey in real time.'],
      ['🎁','var(--purple)','Promo Codes','Apply promo codes at checkout — flat NPR discount or percentage off. First-ride deals available.'],
      ['📄','var(--text3)','Driver Verification','All drivers submit ID, license, and vehicle documents. Manually reviewed before first trip.'],
      ['🔒','var(--green)','Privacy Shield','Phone numbers never shared. All calls and chats go through BROCAR\'s relay system.'],
    ] as [$icon,$color,$title,$desc])
    <div class="feature-card" style="--accent-color:{{ $color }}">
      <div class="feature-icon" style="background:{{ $color }}18">{{ $icon }}</div>
      <div class="feature-title">{{ $title }}</div>
      <div class="feature-desc">{{ $desc }}</div>
    </div>
    @endforeach
  </div>
</section>

<!-- ══════════════ DELIVERY ══════════════ -->
<section id="delivery">
  <div class="delivery-grid">
    <div class="reveal-left">
      <div class="section-badge">BROCAR DELIVERY</div>
      <div class="section-title">Send Anything<br>Across Jhapa</div>
      <div class="section-desc">Real-time parcel tracking. As fast as a ride. Pay from your wallet — no cash needed.</div>
      <div style="margin-top:32px">
        <a href="{{ route('auth.phone') }}" class="btn-hero btn-hero-primary btn-ripple" style="padding:14px 30px;font-size:15px">📦 Send a Parcel</a>
      </div>
    </div>
    <div class="delivery-tiers stagger reveal-right">
      @foreach([
        ['📄','Documents','A4 envelopes, contracts, legal documents','NPR 60 flat'],
        ['📦','Parcel','Packages up to 20 kg — door-to-door delivery','NPR 100 + NPR 15/kg'],
        ['🚚','Freight','Heavy equipment, bulk goods, machinery','NPR 500 + NPR 20/kg'],
      ] as [$icon,$name,$desc,$price])
      <div class="delivery-tier">
        <span class="tier-icon">{{ $icon }}</span>
        <div>
          <div class="tier-name">{{ $name }}</div>
          <div class="tier-desc">{{ $desc }}</div>
        </div>
        <div class="tier-price">{{ $price }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ══════════════ WALLET ══════════════ -->
<section style="background:var(--card)">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center">
    <div class="reveal-left">
      <div class="section-badge">DIGITAL WALLET</div>
      <div class="section-title">Pay Rides &<br>Parcels Digitally</div>
      <div class="section-desc">Top up your BROCAR wallet with Nepal's most popular payment methods. Instant balance — no cash required.</div>
      <div class="payment-methods">
        @foreach([['🟢','eSewa'],['🟣','Khalti'],['🏦','Bank Transfer'],['🔗','Connect IPS']] as [$icon,$name])
        <div class="payment-chip">{{ $icon }} {{ $name }}</div>
        @endforeach
      </div>
      <p style="font-size:12px;color:var(--text3);margin-top:16px">Min top-up NPR 100 · Max NPR 50,000</p>
    </div>
    <div class="reveal-right">
      <div style="background:var(--card2);border:1px solid var(--border);border-radius:22px;padding:32px">
        <div style="font-size:12px;color:var(--text3);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:8px">WALLET BALANCE</div>
        <div style="font-size:42px;font-weight:900;color:var(--green);margin-bottom:24px">NPR 0.00</div>
        <div style="display:flex;flex-direction:column;gap:10px">
          @foreach([['➕','Top Up Wallet','Add money from eSewa / Khalti / Bank'],['➖','Withdraw','Transfer to your bank account'],['📋','Transaction History','View all rides, deliveries & top-ups']] as [$icon,$action,$desc])
          <div style="background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:14px">
            <span style="font-size:20px">{{ $icon }}</span>
            <div>
              <div style="font-size:13px;font-weight:700">{{ $action }}</div>
              <div style="font-size:11px;color:var(--text3);margin-top:2px">{{ $desc }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════ SAFETY ══════════════ -->
<section id="safety">
  <div style="text-align:center" class="reveal">
    <div class="section-badge">SAFETY FIRST</div>
    <div class="section-title">Built Safe from<br>the Ground Up</div>
  </div>
  <div class="safety-grid stagger">
    @foreach([
      ['🆘','SOS Emergency Alert','One tap sends your live GPS to up to 3 trusted contacts and BROCAR\'s 24/7 safety team.'],
      ['✅','Verified Drivers Only','Every driver submits ID, license, and vehicle documents. All manually reviewed before going live.'],
      ['👁️','Trip Monitoring','All active trips are monitored. Unusual stops or route deviations are flagged automatically.'],
      ['🔒','Privacy Relay','Your personal phone number is never shared. All calls & chats route through BROCAR\'s secure relay.'],
      ['💛','Trusted Contacts','Add up to 3 contacts who receive automatic alerts when your trip starts and ends.'],
      ['⭐','Rating Accountability','Riders and drivers rate each other. Low-rated accounts are auto-suspended pending review.'],
    ] as [$icon,$title,$desc])
    <div class="safety-card">
      <span class="safety-icon">{{ $icon }}</span>
      <div>
        <div style="font-size:15px;font-weight:800;margin-bottom:6px">{{ $title }}</div>
        <div style="font-size:13px;color:var(--text2);line-height:1.6">{{ $desc }}</div>
      </div>
    </div>
    @endforeach
  </div>
</section>

<!-- ══════════════ TESTIMONIALS ══════════════ -->
<section style="background:var(--card)">
  <div style="text-align:center;margin-bottom:50px" class="reveal">
    <div class="section-badge">USER REVIEWS</div>
    <div class="section-title">What Jhapa Says</div>
  </div>
  <div class="testimonials stagger">
    @foreach([
      ['"The bidding system saved me NPR 40 on my trip to Bhadrapur. I\'ll never go back to fixed-rate rides."','Priya Sharma','Rider · Birtamod','PS','#FF5500'],
      ['"Got verified and started earning in 2 days. The driver dashboard makes it easy to manage my rides."','Ramesh Thapa','Bike Driver · Mechinagar','RT','#38BDF8'],
      ['"Sent a parcel from Damak to Birtamod for NPR 60. Faster and cheaper than any courier."','Anika Joshi','Delivery User · Damak','AJ','#A855F7'],
      ['"SOS button gives my parents peace of mind on my late-night rides. The chat is very smooth too."','Sunita KC','Rider · Bhadrapur','SK','#22C55E'],
    ] as [$text,$name,$role,$init,$color])
    <div class="testimonial">
      <div class="t-stars">★★★★★</div>
      <div class="t-text">{{ $text }}</div>
      <div class="t-author">
        <div class="t-avatar" style="background:{{ $color }}22;color:{{ $color }};border:1.5px solid {{ $color }}44">{{ $init }}</div>
        <div>
          <div class="t-name">{{ $name }}</div>
          <div class="t-role">{{ $role }}</div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
</section>

<!-- ══════════════ DRIVER CTA ══════════════ -->
<section style="padding:60px 5%">
  <div class="driver-cta reveal-scale">
    <div class="section-badge">FOR DRIVERS IN JHAPA</div>
    <div class="section-title" style="margin-top:16px">Start Earning With<br>Your Vehicle Today</div>
    <p style="font-size:17px;color:var(--text2);margin:20px auto 36px;max-width:520px;line-height:1.7">
      Register your Bike, Auto, Car, or SUV. Set your own hours, bid on rides nearby,
      and get paid directly to your BROCAR wallet.
    </p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      <a href="{{ route('auth.phone') }}?role=driver" class="btn-hero btn-hero-primary btn-ripple">🧑‍✈️ Register as Driver</a>
      <a href="#how-it-works" class="btn-hero btn-hero-outline btn-ripple">See How It Works</a>
    </div>
    <div style="display:flex;gap:50px;justify-content:center;margin-top:56px;flex-wrap:wrap" class="stagger">
      @foreach([['NPR 800+','Est. Daily Earnings'],['0%','Commission — First 30 Days'],['Instant','Wallet Withdrawals']] as [$v,$l])
      <div style="text-align:center">
        <div class="cta-stat-val">{{ $v }}</div>
        <div class="cta-stat-label">{{ $l }}</div>
      </div>
      @endforeach
    </div>
    <div style="margin-top:40px;display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      @foreach(['🏍️ Bike','🛺 Auto','🚗 Car','🚙 SUV','🚚 Truck'] as $v)
      <div style="background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:50px;padding:7px 18px;font-size:13px;font-weight:700">{{ $v }}</div>
      @endforeach
    </div>
  </div>
</section>

<!-- ══════════════ FOOTER ══════════════ -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-brand">BROCAR</div>
      <div class="footer-desc">Jhapa's trusted ride-hailing platform. Competitive bidding, verified drivers, NPR fares, and real-time GPS — serving all of Jhapa district.</div>
      <div class="footer-social">
        <div class="footer-social-btn">📘</div>
        <div class="footer-social-btn">🐦</div>
        <div class="footer-social-btn">📸</div>
        <div class="footer-social-btn">▶️</div>
      </div>
    </div>
    <div class="footer-col">
      <h4>Coverage</h4>
      @foreach(['Birtamod','Mechinagar','Damak','Bhadrapur','Shivasataxi','Charali','All of Jhapa'] as $c)
      <a href="#">{{ $c }}</a>
      @endforeach
    </div>
    <div class="footer-col">
      <h4>Drivers</h4>
      <a href="{{ route('auth.phone') }}?role=driver">Register Now</a>
      <a href="#vehicles">Vehicle Types</a>
      <a href="#how-it-works">Earnings Guide</a>
      <a href="#">Requirements</a>
    </div>
    <div class="footer-col">
      <h4>Contact Us</h4>
      <a href="tel:+9779842100471">📞 9842100471</a>
      <a href="mailto:nishubbhattarai123@gmail.com">✉️ Email Us</a>
      <a href="#">📍 Birtamod, Jhapa</a>
      <a href="#">💬 WhatsApp</a>
    </div>
  </div>
  {{-- Contact / Office Info Bar --}}
  <div style="background:rgba(255,85,0,.04);border:1px solid rgba(255,85,0,.12);border-radius:16px;padding:28px 32px;margin-bottom:32px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px">
    <div style="display:flex;flex-direction:column;gap:4px">
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--text3);margin-bottom:6px">📍 Office</div>
      <div style="font-size:14px;font-weight:700;color:var(--text1)">Birtamod, Jhapa</div>
      <div style="font-size:12px;color:var(--text3)">Koshi Province, Nepal 57200</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px">
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--text3);margin-bottom:6px">📞 Contact</div>
      <a href="tel:+9779842100471" style="font-size:14px;font-weight:700;color:var(--text1);transition:.2s" onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text1)'">+977 9842100471</a>
      <a href="mailto:nishubbhattarai123@gmail.com" style="font-size:12px;color:var(--text3);word-break:break-all;transition:.2s" onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--text3)'">nishubbhattarai123@gmail.com</a>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px">
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--text3);margin-bottom:6px">⏰ Support Hours</div>
      <div style="font-size:14px;font-weight:700;color:var(--text1)">EVERYDAY AND EVERYTIME</div>
      <div style="font-size:12px;color:var(--text3)">ANYTIME ON</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px">
      <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--text3);margin-bottom:6px">🚦 Service Area</div>
      <div style="font-size:14px;font-weight:700;color:var(--text1)">Jhapa District Only</div>
      <div style="font-size:12px;color:var(--text3);color:var(--amber)">Outside Jhapa — Coming Soon</div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© {{ date('Y') }} BROCAR Nepal · Birtamod, Jhapa</p>
    <p>Made with ❤️ in Jhapa 🇳🇵</p>
  </div>

  {{-- Designed & Developed credit --}}
<div style="text-align:center;padding-top:20px;border-top:1px solid var(--border);margin-top:8px">
  <p style="font-size:12px;color:var(--text3)">
    Designed &amp; Developed by
    <a href="mailto:nishubbhattarai123@gmail.com"
       style="color:var(--brand);font-weight:700;text-decoration:none;transition:.2s"
       onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
      NISHUB BHATTARAI
    </a>
  </p>
  <p style="font-size:11px;color:var(--text3);margin-top:8px;letter-spacing:.5px">
    HELPERS
    <span style="color:#fff;font-weight:700">RAJ CHAPAGAIN</span> ·
    <span style="color:#fff;font-weight:700">AARUSHA RAI</span> ·
    <span style="color:#fff;font-weight:700">GITA BHATTARAI</span> ·
    <span style="color:#fff;font-weight:700">ASMITA ACHARYA</span>
  </p>
</div>
  </div>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* ── PAGE LOADER ── */
window.addEventListener('load', () => {
  setTimeout(() => {
    const l = document.getElementById('pageLoader');
    if (l) { l.classList.add('hide'); setTimeout(() => { l.style.display='none'; }, 700); }
  }, 1500);
});

/* ── NAVBAR SCROLL ── */
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 40), {passive:true});

/* ── HERO MAP (Jhapa center) ── */
const heroMap = L.map('heroMap', {attributionControl:false, zoomControl:false, dragging:false, scrollWheelZoom:false, doubleClickZoom:false, keyboard:false});
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {maxZoom:14}).addTo(heroMap);
heroMap.setView([26.6331, 87.9769], 12);
const dotIcon = c => L.divIcon({className:'',html:`<div style="width:10px;height:10px;border-radius:50%;background:${c};border:2px solid rgba(255,255,255,.5);box-shadow:0 0 8px ${c}"></div>`,iconSize:[10,10]});
// Orange = active riders, green = online drivers — positions around Jhapa district
[[26.645,87.963],[26.623,87.988],[26.640,87.952],[26.618,87.974],[26.652,87.947],[26.631,87.996],[26.609,87.963]].forEach(p => L.marker(p,{icon:dotIcon('#FF5500')}).addTo(heroMap));
[[26.634,87.978],[26.644,87.958],[26.613,87.984],[26.627,87.944],[26.656,87.972]].forEach(p => L.marker(p,{icon:dotIcon('#22C55E')}).addTo(heroMap));

/* ── LIVE MAP SECTION (initialised on scroll) ── */
let liveMapInit = false;
const initLiveMap = () => {
  if (liveMapInit) return; liveMapInit = true;
  const lm = L.map('liveMapPreview', {attributionControl:false, zoomControl:false});
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {maxZoom:16}).addTo(lm);
  lm.setView([26.6331, 87.9769], 13);
  const pulseIcon = (color, label) => L.divIcon({
    className:'',
    html:`<div style="position:relative;text-align:center">
      <div style="width:22px;height:22px;border-radius:50%;background:${color};border:3px solid rgba(255,255,255,.75);box-shadow:0 0 12px ${color};animation:lpulse 2s ease-in-out infinite"></div>
    </div>
    <style>@keyframes lpulse{0%,100%{box-shadow:0 0 0 0 ${color}88}50%{box-shadow:0 0 0 10px ${color}00}}</style>`,
    iconSize:[22,22]
  });
  [
    [[26.640,87.970],'#FF5500','Bike Driver'],
    [[26.628,87.988],'#22C55E','Car Driver'],
    [[26.638,87.956],'#38BDF8','Auto Driver'],
    [[26.650,87.975],'#A855F7','SUV Driver'],
    [[26.622,87.965],'#F59E0B','Bike Driver'],
  ].forEach(([pos,color,lbl]) => L.marker(pos,{icon:pulseIcon(color,lbl)}).addTo(lm).bindPopup(lbl));
};

/* ── SCROLL REVEAL (IntersectionObserver) ── */
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    e.target.classList.add('visible');
    if (e.target.closest('section') && e.target.id === 'liveMapPreview') initLiveMap();
    io.unobserve(e.target);
  });
}, {threshold: 0.1});
document.querySelectorAll('.reveal,.reveal-left,.reveal-right,.reveal-scale,.stagger').forEach(el => io.observe(el));

// Init live map when its section becomes visible
const mapSection = document.querySelector('#liveMapPreview');
if (mapSection) {
  const mapIO = new IntersectionObserver(([e]) => { if (e.isIntersecting) { initLiveMap(); mapIO.disconnect(); } }, {threshold:0.1});
  mapIO.observe(mapSection);
}

/* ── ANIMATED COUNTERS ── */
const counterIO = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    const el = e.target;
    const target = parseInt(el.dataset.target);
    const suffix = el.dataset.suffix || '';
    const div = parseInt(el.dataset.div) || 1;
    const displayTarget = target / div;
    let cur = 0;
    const step = Math.max(displayTarget / 55, 1);
    const t = setInterval(() => {
      cur = Math.min(cur + step, displayTarget);
      el.textContent = Math.floor(cur) + suffix;
      if (cur >= displayTarget) clearInterval(t);
    }, 28);
    counterIO.unobserve(el);
  });
}, {threshold:0.5});
document.querySelectorAll('.counter').forEach(c => counterIO.observe(c));

/* ── RIPPLE EFFECT ── */
document.querySelectorAll('.btn-ripple').forEach(btn => {
  btn.addEventListener('click', function(e) {
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const r = document.createElement('span');
    r.className = 'ripple-effect';
    r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
    this.appendChild(r);
    setTimeout(() => r.remove(), 700);
  });
});

/* ── FEATURE CARD MOUSE GLOW ── */
document.querySelectorAll('.feature-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    card.style.setProperty('--mx', ((e.clientX-r.left)/r.width*100).toFixed(1)+'%');
    card.style.setProperty('--my', ((e.clientY-r.top)/r.height*100).toFixed(1)+'%');
  });
});

/* ── VEHICLE CARD CLICK → booking ── */
document.querySelectorAll('.vehicle-card').forEach(c => {
  c.addEventListener('click', () => { window.location.href='{{ route("auth.phone") }}?role=rider'; });
});

/* ── SIGN IN MODAL ── */
function openSignInModal() {
  document.getElementById('signinModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('signinModalBox').style.transform = 'translateY(0)', 10);
}
function closeSignInModal() {
  document.getElementById('signinModal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('signinModal')?.addEventListener('click', function(e) {
  if (e.target === this) closeSignInModal();
});
document.getElementById('signinForm')?.addEventListener('submit', function() {
  document.getElementById('signinBtn').disabled = true;
  document.getElementById('signinBtnText').textContent = 'Signing in…';
});
</script>

{{-- ══════════ SIGN IN MODAL ══════════ --}}
@guest
<div id="signinModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px">
  <div id="signinModalBox" style="background:#111118;border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:32px;width:100%;max-width:420px;position:relative;transform:translateY(40px);transition:transform .4s cubic-bezier(.175,.885,.32,1.275);box-shadow:0 32px 80px rgba(0,0,0,.6)">
    <button onclick="closeSignInModal()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,.08);border:none;color:var(--text2);width:32px;height:32px;border-radius:50%;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s" onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">×</button>

    <div style="text-align:center;margin-bottom:24px">
      <div style="width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 14px;box-shadow:0 8px 32px rgba(255,85,0,.3)">🚗</div>
      <h2 style="font-size:20px;font-weight:900;margin-bottom:4px">Sign In to BROCAR</h2>
      <p style="font-size:13px;color:var(--text2)">Nepal's smartest ride-hailing service</p>
    </div>

    <div style="display:flex;gap:6px;background:rgba(255,255,255,.04);border-radius:12px;padding:4px;margin-bottom:20px">
      <button type="button" id="modalRoleRider"  onclick="setModalRole('rider')"  style="flex:1;padding:9px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;background:var(--brand);color:#fff;border:none;box-shadow:0 4px 12px rgba(255,85,0,.3);transition:.2s">🧑 Rider</button>
      <button type="button" id="modalRoleDriver" onclick="setModalRole('driver')" style="flex:1;padding:9px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;background:transparent;color:var(--text2);border:none;transition:.2s">🏍️ Driver</button>
      <button type="button" id="modalRoleAdmin"  onclick="setModalRole('admin')"  style="flex:1;padding:9px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;background:transparent;color:var(--text2);border:none;transition:.2s">🛡️ Admin</button>
    </div>

    {{-- Admin notice — shown only when Admin tab is active --}}
    <div id="adminNotice" style="display:none;background:rgba(255,85,0,.07);border:1px solid rgba(255,85,0,.2);border-radius:10px;padding:10px 14px;margin-bottom:4px;font-size:12px;color:var(--brand);font-weight:600;display:none;align-items:center;gap:8px">
      🔐 Authorised personnel only. Admin credentials required.
    </div>

    <form id="signinForm" method="POST" action="{{ route('auth.send-otp') }}" style="display:flex;flex-direction:column;gap:14px">
      @csrf
      <input type="hidden" name="role" id="modalRoleField" value="rider">

      <div>
        <label style="font-size:12px;font-weight:700;color:var(--text3);letter-spacing:.5px;display:block;margin-bottom:6px">PHONE NUMBER</label>
        <div style="position:relative">
          <div style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text3);font-weight:700;display:flex;align-items:center;gap:4px">🇳🇵 <span style="color:var(--text2)">+977</span></div>
          <input type="tel" name="phone" placeholder="98XXXXXXXX" maxlength="10" required
            style="width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 14px 12px 76px;color:#fff;font-size:15px;font-weight:600;letter-spacing:1px;outline:none;transition:.2s"
            oninput="this.value=this.value.replace(/\D/g,'')"
            onfocus="this.style.borderColor='var(--brand)';this.style.boxShadow='0 0 0 3px rgba(255,85,0,.18)'"
            onblur="this.style.borderColor='rgba(255,255,255,.1)';this.style.boxShadow='none'">
        </div>
      </div>

      <div>
        <label style="font-size:12px;font-weight:700;color:var(--text3);letter-spacing:.5px;display:block;margin-bottom:6px">PASSWORD</label>
        <div style="position:relative">
          <input type="password" name="password" id="modalPwd" placeholder="Enter your password" required
            style="width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:12px 44px 12px 14px;color:#fff;font-size:14px;outline:none;transition:.2s"
            onfocus="this.style.borderColor='var(--brand)';this.style.boxShadow='0 0 0 3px rgba(255,85,0,.18)'"
            onblur="this.style.borderColor='rgba(255,255,255,.1)';this.style.boxShadow='none'">
          <button type="button" onclick="toggleModalPwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text3);font-size:15px;line-height:1">
            <span id="modalPwdEye">👁</span>
          </button>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input type="checkbox" name="remember" value="1" style="width:15px;height:15px;accent-color:var(--brand)">
          <span style="font-size:13px;color:var(--text2)">Remember me</span>
        </label>
        <a href="{{ route('auth.forgot-password') }}" style="font-size:13px;color:var(--brand);font-weight:600">Forgot Password?</a>
      </div>

      <button type="submit" id="signinBtn" style="background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:800;cursor:pointer;width:100%;transition:all .3s;box-shadow:0 6px 24px rgba(255,85,0,.35)" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <span id="signinBtnText">Sign In →</span>
      </button>

      <p style="text-align:center;font-size:13px;color:var(--text2)">
        New to BROCAR? <a href="{{ route('auth.phone') }}?mode=register" style="color:var(--brand);font-weight:700">Create account →</a>
      </p>
    </form>
  </div>
</div>

<script>
function setModalRole(r) {
  document.getElementById('modalRoleField').value = r;
  const ACTIVE = 'flex:1;padding:9px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;background:var(--brand);color:#fff;border:none;box-shadow:0 4px 12px rgba(255,85,0,.3);transition:.2s';
  const IDLE   = 'flex:1;padding:9px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;background:transparent;color:var(--text2);border:none;transition:.2s';
  document.getElementById('modalRoleRider').style.cssText  = r === 'rider'  ? ACTIVE : IDLE;
  document.getElementById('modalRoleDriver').style.cssText = r === 'driver' ? ACTIVE : IDLE;
  document.getElementById('modalRoleAdmin').style.cssText  = r === 'admin'  ? ACTIVE : IDLE;
  const notice = document.getElementById('adminNotice');
  notice.style.display = r === 'admin' ? 'flex' : 'none';
  // Update placeholder hint when admin selected
  const pwdInput = document.getElementById('modalPwd');
  if (pwdInput) pwdInput.placeholder = r === 'admin' ? 'Admin password' : 'Enter your password';
}
function toggleModalPwd() {
  const f = document.getElementById('modalPwd');
  const e = document.getElementById('modalPwdEye');
  if (f.type === 'password') { f.type = 'text'; e.textContent = '🙈'; }
  else { f.type = 'password'; e.textContent = '👁'; }
}
</script>
@endguest
</body>
</html>