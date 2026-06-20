@extends('layouts.app')
@section('title','Earnings')
@push('styles')
<style>#earningsChart{height:200px}</style>
@endpush
@section('content')
<div class="page-title" style="margin-bottom:20px">💰 Earnings</div>

<div class="grid-4" style="margin-bottom:20px">
  @foreach([
    ['Today',       "NPR".number_format($today),   'var(--green)'],
    ['This Week',   "NPR".number_format($week),    'var(--blue)'],
    ['This Month',  "NPR".number_format($month),   'var(--brand)'],
    ['All Time',    "NPR".number_format($allTime),  'var(--amber)'],
  ] as [$l,$v,$c])
  <div class="stat-card">
    <div class="stat-label">{{ $l }}</div>
    <div class="stat-val" style="color:{{ $c }}">{{ $v }}</div>
  </div>
  @endforeach
</div>

<div class="grid-2" style="margin-bottom:20px;align-items:start">
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">📊 30-Day Breakdown</div>
    <canvas id="earningsChart"></canvas>
  </div>
  <div class="card">
    <div class="card-title" style="margin-bottom:12px">💳 Wallet</div>
    <div style="background:linear-gradient(135deg,rgba(255,85,0,.1),rgba(255,122,48,.05));border:1px solid rgba(255,85,0,.2);border-radius:12px;padding:20px;text-align:center;margin-bottom:14px">
      <p style="font-size:11px;font-weight:700;color:var(--text3);letter-spacing:1px;margin-bottom:6px">AVAILABLE BALANCE</p>
      <p style="font-size:32px;font-weight:900;color:var(--brand)">NPR {{ number_format(auth()->user()->wallet_balance) }}</p>
    </div>
    <form method="POST" action="{{ route('wallet.withdraw') }}" style="display:flex;flex-direction:column;gap:10px">
      @csrf
      <input type="number" name="amount" class="input" placeholder="Withdrawal amount (NPR)" min="100" required>
      <input type="text" name="account_name" class="input" placeholder="Account holder name" required>
      <select name="bank_name" class="input">
        <option>NIC Asia Bank</option>
        <option>Nabil Bank</option>
        <option>Nepal SBI Bank</option>
        <option>Sanima Bank</option>
        <option>Laxmi Bank</option>
        <option>Prabhu Bank</option>
        <option>eSewa Wallet</option>
        <option>Khalti Wallet</option>
      </select>
      <input type="text" name="account_number" class="input" placeholder="Account / Wallet number" required>
      <button type="submit" class="btn btn-brand btn-sm">💸 Request Withdrawal</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-title" style="margin-bottom:14px">📋 Completed Trips</div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ride #</th><th>Route</th><th>Rider</th><th>Fare</th><th>Payment</th><th>Date</th>
        </tr>
      </thead>
      <tbody>
        @forelse($completedRides as $ride)
        <tr>
          <td style="font-weight:700;color:var(--text3)">#{{ $ride->id }}</td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ Str::limit($ride->pickup_address,26) }}</p>
            <p style="font-size:11px;color:var(--text3)">→ {{ Str::limit($ride->destination_address,26) }}</p>
          </td>
          <td class="text-sm text-muted">{{ $ride->rider->full_name }}</td>
          <td style="font-weight:800;color:var(--green)">NPR {{ number_format($ride->agreed_fare ?? $ride->offered_fare) }}</td>
          <td><span class="badge badge-muted">{{ ucfirst($ride->payment_method) }}</span></td>
          <td class="text-xs text-muted">{{ $ride->completed_at?->format('d M Y') ?? $ride->updated_at->format('d M Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:var(--text3);padding:32px">No completed trips yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $completedRides->links() }}</div>
</div>
@endsection
@push('scripts')
<script>
const ctx = document.getElementById('earningsChart')?.getContext('2d');
if (ctx) new Chart(ctx, {
  type: 'line',
  data: {
    labels: @json($chartLabels ?? []),
    datasets: [{
      data: @json($chartData ?? []),
      backgroundColor: 'rgba(255,85,0,.12)',
      borderColor: '#FF5500',
      fill: true, tension: .4, borderWidth: 2,
      pointBackgroundColor: '#FF5500', pointRadius: 3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid:{ color:'rgba(255,255,255,.04)' }, ticks:{ color:'#666', font:{ size:10 }, maxRotation:45 }},
      y: { grid:{ color:'rgba(255,255,255,.04)' }, ticks:{ color:'#666', font:{ size:10 }, callback: v => "NPR"+v }}
    }
  }
});
</script>
@endpush
