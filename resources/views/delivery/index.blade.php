@extends('layouts.app')
@section('title','Delivery')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">📦 Deliveries</div>
  <a href="{{ route('delivery.create') }}" class="btn btn-brand btn-sm">+ New Delivery</a>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Type</th><th>From → To</th><th>Recipient</th><th>Fare</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
        @forelse($deliveries as $d)
        <tr>
          <td class="text-xs text-muted">#{{ $d->id }}</td>
          <td><span class="badge badge-muted">{{ ucfirst($d->item_type) }}</span></td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ Str::limit($d->pickup_address,24) }}</p>
            <p style="font-size:11px;color:var(--text3)">→ {{ Str::limit($d->delivery_address,24) }}</p>
          </td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ $d->recipient_name }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ $d->recipient_phone }}</p>
          </td>
          <td style="font-weight:700">NPR {{ number_format($d->fare) }}</td>
          <td>
            <span class="badge {{ match($d->status){ 'delivered'=>'badge-green','cancelled'=>'badge-red','picked_up'=>'badge-blue',default=>'badge-amber' } }}">
              {{ ucfirst(str_replace('_',' ',$d->status)) }}
            </span>
          </td>
          <td class="text-xs text-muted">{{ $d->created_at->format('d M Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:36px">No deliveries yet. <a href="{{ route('delivery.create') }}" style="color:var(--brand)">Send something</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $deliveries->links() }}</div>
</div>
@endsection
