@extends('layouts.app')
@section('title','Wallet')
@section('content')
<div class="page-title" style="margin-bottom:20px">👛 My Wallet</div>
<div class="grid-2" style="align-items:start">
<div style="display:flex;flex-direction:column;gap:16px">
  <div style="background:linear-gradient(135deg,rgba(255,85,0,.15),rgba(255,122,48,.06));border:1px solid rgba(255,85,0,.2);border-radius:20px;padding:28px;text-align:center">
    <p style="font-size:11px;font-weight:800;color:var(--text3);letter-spacing:2px;text-transform:uppercase;margin-bottom:10px">AVAILABLE BALANCE</p>
    <p style="font-size:52px;font-weight:900;color:var(--brand)">NPR {{ number_format($user->wallet_balance) }}</p>
  </div>
  <div class="card">
    <div class="card-title" style="margin-bottom:14px">💳 Top Up Wallet</div>
    <form method="POST" action="{{ route('wallet.topup') }}" style="display:flex;flex-direction:column;gap:12px">
      @csrf
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        @foreach([500,1000,2000,5000] as $amt)
        <button type="button" onclick="document.getElementById('topupAmt').value={{ $amt }}"
          class="btn btn-ghost btn-sm" style="flex:1">NPR {{ $amt }}</button>
        @endforeach
      </div>
      <input type="number" id="topupAmt" name="amount" class="input" placeholder="Or enter amount…" min="100" max="50000">
      <select class="input">
        <option>eSewa</option><option>Khalti</option><option>Bank Transfer</option><option>Connect IPS</option>
      </select>
      <button type="submit" class="btn btn-brand btn-sm" style="justify-content:center">Add Money</button>
    </form>
  </div>
  <div class="card">
    <div class="card-title" style="margin-bottom:14px">🏧 Withdraw</div>
    <form method="POST" action="{{ route('wallet.withdraw') }}" style="display:flex;flex-direction:column;gap:10px">
      @csrf
      <input type="number" name="amount" class="input" placeholder="Amount (NPR)" min="100">
      <input type="text" name="account_name" class="input" placeholder="Account holder name" required>
      <select name="bank_name" class="input">
        <option>NIC Asia Bank</option><option>Nabil Bank</option><option>Sanima Bank</option>
        <option>eSewa Wallet</option><option>Khalti Wallet</option>
      </select>
      <input type="text" name="account_number" class="input" placeholder="Account / Wallet number" required>
      <button type="submit" class="btn btn-amber btn-sm" style="justify-content:center">Request Withdrawal</button>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px">📋 Transaction History</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Type</th><th>Amount</th><th>Description</th><th>Date</th></tr></thead>
      <tbody>
        @forelse($transactions as $t)
        <tr>
          <td><span class="badge {{ $t->type==='credit'?'badge-green':'badge-red' }}">{{ $t->type==='credit'?'↑ Credit':'↓ Debit' }}</span></td>
          {{-- FIXED HTML TAG CLOSURE BELOW --}}
          <td style="font-weight:800;color:{{ $t->type==='credit'?'var(--green)':'var(--red)' }}">
            {{ $t->type==='credit'?'+':'-' }}NPR {{ number_format($t->amount) }}
          </td>
          {{-- FIXED METHOD: Native fallback logic avoids any Class conflicts completely --}}
          <td class="text-sm text-muted">
            {{ mb_strlen($t->description) > 40 ? mb_substr($t->description, 0, 40) . '...' : $t->description }}
          </td>
          <td class="text-xs text-muted">{{ $t->created_at->format('d M Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" style="text-align:center;color:var(--text3);padding:28px">No transactions yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $transactions->links() }}</div>
</div>
</div>
@endsection