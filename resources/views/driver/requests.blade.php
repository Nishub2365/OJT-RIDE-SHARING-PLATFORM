@extends('layouts.app')
@section('title','Bid History')
@php
use Illuminate\Support\Str;
@endphp
@section('content')
<div class="page-title" style="margin-bottom:20px">📨 My Bids</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Ride #</th>
          <th>Route</th>
          <th>My Bid</th>
          <th>Offered</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($bids as $bid)
        <tr>
          <td style="font-weight:700;color:var(--text3)">#{{ $bid->ride->id }}</td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ Str::limit($bid->ride->pickup_address,28) }}</p>
            <p style="font-size:11px;color:var(--text3)">→ {{ Str::limit($bid->ride->destination_address,28) }}</p>
          </td>
          <td style="font-weight:800;color:var(--brand)">NPR {{ number_format($bid->bid_amount) }}</td>
          <td style="font-size:12px;color:var(--text3)">NPR {{ number_format($bid->ride->offered_fare) }}</td>
          <td>
            <span class="badge {{ match($bid->status) {
              'accepted' => 'badge-green', 'rejected' => 'badge-red', default => 'badge-amber'
            } }}">
              {{ ucfirst($bid->status) }}
            </span>
          </td>
          <td class="text-xs text-muted">{{ $bid->created_at->format('d M Y') }}</td>
          <td>
            @if($bid->status === 'accepted')
            <a href="{{ route('driver.active-ride',$bid->ride->id) }}" class="btn btn-blue btn-xs">View</a>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" style="text-align:center;color:var(--text3);padding:36px">No bids placed yet</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $bids->links() }}</div>
</div>
@endsection