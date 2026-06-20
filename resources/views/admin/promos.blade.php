@extends('layouts.app')
@section('title','Promo Codes')
@section('content')
<div class="page-title" style="margin-bottom:20px">🎁 Promo Codes</div>
<div class="grid-2" style="align-items:start">

<div class="card">
  <div class="card-title" style="margin-bottom:14px">Create Promo Code</div>
  <form method="POST" action="{{ route('admin.promos.store') }}" style="display:flex;flex-direction:column;gap:12px">
    @csrf
    <div class="grid-2">
      <div><label class="label">Code</label><input type="text" name="code" class="input" placeholder="BROCAR20" style="text-transform:uppercase" required></div>
      <div><label class="label">Type</label>
        <select name="discount_type" class="input">
          <option value="percentage">Percentage (%)</option>
          <option value="fixed">Fixed Amount (NPR)</option>
        </select>
      </div>
    </div>
    <div class="grid-2">
      <div><label class="label">Discount Value</label><input type="number" name="discount_value" class="input" placeholder="20" min="1" required></div>
      <div><label class="label">Max Uses</label><input type="number" name="max_uses" class="input" placeholder="100" min="1" required></div>
    </div>
    <div class="grid-2">
      <div><label class="label">Min Fare (NPR)</label><input type="number" name="min_fare" class="input" placeholder="100" value="0"></div>
      <div><label class="label">Max Discount (NPR)</label><input type="number" name="max_discount" class="input" placeholder="Optional"></div>
    </div>
    <div class="grid-2">
      <div><label class="label">Start Date</label><input type="date" name="starts_at" class="input" value="{{ now()->format('Y-m-d') }}"></div>
      <div><label class="label">End Date</label><input type="date" name="expires_at" class="input" required></div>
    </div>
    <button type="submit" class="btn btn-brand btn-sm">Create Promo Code</button>
  </form>
</div>

<div class="card">
  <div class="card-title" style="margin-bottom:12px">Active Promo Codes</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Code</th><th>Discount</th><th>Used</th><th>Expires</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($promos as $p)
        <tr>
          <td style="font-weight:800;color:var(--brand);letter-spacing:1px">{{ $p->code }}</td>
          <td style="font-size:13px;font-weight:700">
            {{ $p->discount_type==='percentage' ? $p->discount_value.'%' : "NPR".$p->discount_value }}
          </td>
          <td class="text-sm">{{ $p->uses_count }} / {{ $p->max_uses }}</td>
          <td class="text-xs text-muted">{{ $p->expires_at?->format('d M Y') ?? '—' }}</td>
          <td>
            <span class="badge {{ $p->is_active && (!$p->expires_at || $p->expires_at->isFuture()) ? 'badge-green':'badge-red' }}">
              {{ $p->is_active && (!$p->expires_at || $p->expires_at->isFuture()) ? 'Active':'Inactive' }}
            </span>
          </td>
          <td>
            <form method="POST" action="{{ route('admin.promos.toggle', $p->id) }}" style="display:inline">
              @csrf <button type="submit" class="btn btn-ghost btn-xs">{{ $p->is_active?'Disable':'Enable' }}</button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:var(--text3);padding:24px">No promo codes yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
@endsection
