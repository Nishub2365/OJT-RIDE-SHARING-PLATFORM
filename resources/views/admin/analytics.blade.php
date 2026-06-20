@extends('layouts.app')
@section('title','Analytics')
@push('styles')
<style>#rideChart,#revenueChart,#vehicleChart{height:220px}</style>
@endpush
@section('content')
<div class="page-title" style="margin-bottom:20px">📊 Analytics</div>

<div class="grid-4" style="margin-bottom:20px">
  @foreach([
    ['💰','Total Revenue', "NPR".number_format($analytics['total_revenue']), 'var(--green)'],
    ['🏁','Completed Rides', number_format($analytics['completed_rides']), 'var(--blue)'],
    ['❌','Cancel Rate', number_format($analytics['cancel_rate'],1).'%', 'var(--red)'],
    ['📊','Avg Fare', "NPR".number_format($analytics['avg_fare'],0), 'var(--brand)'],
  ] as [$icon,$label,$val,$color])
  <div class="stat-card">
    <div class="stat-label">{{ $icon }} {{ $label }}</div>
    <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
  </div>
  @endforeach
</div>

<div class="grid-2" style="margin-bottom:20px">
  <div class="card"><div class="card-title" style="margin-bottom:12px">📈 Daily Rides (30 days)</div><canvas id="rideChart"></canvas></div>
  <div class="card"><div class="card-title" style="margin-bottom:12px">💰 Daily Revenue (30 days)</div><canvas id="revenueChart"></canvas></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">🚗 Rides by Vehicle Type</div>
    <canvas id="vehicleChart"></canvas>
  </div>
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">🏆 Top Earning Drivers</div>
    @forelse($topDrivers as $i => $d)
    <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border)">
      <div style="width:26px;height:26px;border-radius:50%;background:{{ ['var(--amber)','var(--text3)','#CD7F32'][$i] ?? 'var(--border)' }};
        display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;color:#000;flex-shrink:0">{{ $i+1 }}</div>
      <div style="flex:1;min-width:0">
        <p style="font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $d->full_name }}</p>
        <p style="font-size:11px;color:var(--text3)">{{ $d->driverProfile?->vehicle_type }} · ⭐ {{ number_format($d->avg_rating??0,1) }}</p>
      </div>
      <div style="font-size:14px;font-weight:800;color:var(--green)">NPR {{ number_format($d->total_earned??0) }}</div>
    </div>
    @empty
    <p style="font-size:13px;color:var(--text3);text-align:center;padding:20px">No data yet</p>
    @endforelse
  </div>
</div>
@endsection
@push('scripts')
<script>
const gc = 'rgba(255,255,255,.04)', tc = '#666';
new Chart(document.getElementById('rideChart').getContext('2d'), {
  type: 'bar',
  data: { labels: @json($rideLabels), datasets: [{ data: @json($rideData), backgroundColor:'rgba(56,189,248,.5)',borderColor:'#38BDF8',borderWidth:1,borderRadius:4 }]},
  options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc},ticks:{color:tc,font:{size:9},maxRotation:45}}, y:{grid:{color:gc},ticks:{color:tc,font:{size:10}}} }}
});
new Chart(document.getElementById('revenueChart').getContext('2d'), {
  type: 'line',
  data: { labels: @json($revenueLabels), datasets: [{ data: @json($revenueData), borderColor:'#FF5500',backgroundColor:'rgba(255,85,0,.1)',fill:true,tension:.4,borderWidth:2,pointRadius:2 }]},
  options: { responsive:true, plugins:{legend:{display:false}}, scales:{ x:{grid:{color:gc},ticks:{color:tc,font:{size:9},maxRotation:45}}, y:{grid:{color:gc},ticks:{color:tc,font:{size:10},callback:v=>"NPR"+v}} }}
});
new Chart(document.getElementById('vehicleChart').getContext('2d'), {
  type: 'doughnut',
  data: { labels: ['Bike','Auto','Car','SUV'], datasets: [{ data: @json($vehicleData), backgroundColor:['#FF5500','#38BDF8','#22C55E','#A855F7'], borderWidth:0, hoverOffset:6 }]},
  options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{ color:'#A0A0B0', font:{size:12}, padding:16 } } } }
});
</script>
@endpush
