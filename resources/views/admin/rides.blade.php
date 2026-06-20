@extends('layouts.app')
@section('title','All Rides')
@php
use Illuminate\Support\Str;
@endphp
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">🚗 All Rides</div>
  <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
    <select name="status" class="input" style="width:140px" onchange="this.form.submit()">
      <option value="">All Status</option>
      @foreach(['pending','accepted','driver_arrived','in_progress','completed','cancelled'] as $s)
      <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ str_replace('_',' ',ucfirst($s)) }}</option>
      @endforeach
    </select>
    <input type="text" name="search" class="input" placeholder="Rider / Driver…" value="{{ request('search') }}" style="width:180px">
    <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Rider</th><th>Driver</th><th>Route</th><th>Fare</th><th>Vehicle</th><th>Status</th><th>Date</th></tr>
      </thead>
      <tbody>
        @forelse($rides as $r)
        <tr>
          <td style="font-weight:700;color:var(--text3)">#{{ $r->id }}</td>
          <td class="text-sm">{{ $r->rider->full_name }}</td>
          <td class="text-sm text-muted">{{ $r->driver?->full_name ?? '—' }}</td>
          <td>
            <p style="font-size:12px;font-weight:600">{{ Str::limit($r->pickup_address,24) }}</p>
            <p style="font-size:11px;color:var(--text3)">→ {{ Str::limit($r->destination_address,24) }}</p>
          </td>
          <td style="font-weight:700">NPR {{ number_format($r->agreed_fare??$r->offered_fare) }}</td>
          <td class="text-sm">{{ ucfirst($r->vehicle_category) }}</td>
          <td>
            <span class="badge {{ match($r->status) {
              'completed'=>'badge-green','cancelled'=>'badge-red',
              'in_progress'=>'badge-blue','driver_arrived'=>'badge-amber',
              default=>'badge-muted'
            } }}">{{ str_replace('_',' ',ucfirst($r->status)) }}</span>
          </td>
          <td class="text-xs text-muted">{{ $r->created_at->format('d M Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text3);padding:36px">No rides found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $rides->withQueryString()->links() }}</div>
</div>
@endsection