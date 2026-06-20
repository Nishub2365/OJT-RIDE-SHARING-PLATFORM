@extends('layouts.app')
@section('title','Finance')
@php
use Illuminate\Support\Str;
@endphp
@section('content')
<div class="page-title" style="margin-bottom:20px">💰 Finance</div>
<div class="grid-4" style="margin-bottom:20px">
  @foreach([
    ['💰','Total Revenue',   "NPR".number_format($finance['total_revenue']),  'var(--green)'],
    ['📊','Platform Fees',   "NPR".number_format($finance['platform_fees']),   'var(--brand)'],
    ['👛','Wallet Holdings', "NPR".number_format($finance['wallet_total']),    'var(--blue)'],
    ['🏧','Withdrawals',     "NPR".number_format($finance['withdrawals']),     'var(--amber)'],
  ] as [$icon,$label,$val,$color])
  <div class="stat-card">
    <div class="stat-label">{{ $icon }} {{ $label }}</div>
    <div class="stat-val" style="color:{{ $color }}">{{ $val }}</div>
  </div>
  @endforeach
</div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px">📋 Wallet Transactions</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>User</th><th>Role</th><th>Type</th><th>Amount</th><th>Description</th><th>Date</th></tr></thead>
      <tbody>
        @forelse($transactions as $t)
        <tr>
          <td class="text-xs text-muted">#{{ $t->id }}</td>
          <td>
            <p style="font-size:13px;font-weight:600">{{ $t->user->full_name }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ $t->user->phone }}</p>
          </td>
          <td><span class="badge badge-muted">{{ ucfirst($t->user->role) }}</span></td>
          <td>
            <span class="badge {{ $t->type==='credit'?'badge-green':'badge-red' }}">
              {{ $t->type==='credit'?'↑ Credit':'↓ Debit' }}
            </span>
          </td>
          <td style="font-weight:800;color:{{ $t->type==='credit'?'var(--green)':'var(--red)' }}">
            {{ $t->type==='credit'?'+':'-' }}NPR {{ number_format($t->amount) }}
          </td>
          <td class="text-sm text-muted">{{ Str::limit($t->description,40) }}</td>
          <td class="text-xs text-muted">{{ $t->created_at->format('d M Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:36px">No transactions</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $transactions->links() }}</div>
</div>
@endsection