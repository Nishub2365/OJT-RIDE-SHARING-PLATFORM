@extends('layouts.app')
@section('title','Users')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">👥 Users</div>
  <form method="GET" style="display:flex;gap:8px">
    <input type="text" name="search" class="input" placeholder="Search name / phone…" value="{{ request('search') }}" style="width:220px">
    <button type="submit" class="btn btn-ghost btn-sm">Search</button>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>User</th><th>Phone</th><th>Role</th><th>Wallet</th><th>Rides</th><th>Rating</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($users as $u)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <img src="{{ $u->avatar_url ? asset('storage/'.$u->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($u->full_name).'&background=1F1F3E&color=666&size=40&bold=true' }}"
                   style="width:30px;height:30px;border-radius:7px;object-fit:cover">
              <div>
                <p style="font-size:13px;font-weight:700">{{ $u->full_name }}</p>
                <p style="font-size:11px;color:var(--text3)">{{ $u->email }}</p>
              </div>
            </div>
          </td>
          <td class="text-sm text-muted">{{ $u->phone }}</td>
          <td><span class="badge {{ $u->role==='admin'?'badge-brand':($u->role==='driver'?'badge-blue':'badge-muted') }}">{{ ucfirst($u->role) }}</span></td>
          <td style="font-size:13px;font-weight:700;color:var(--green)">NPR {{ number_format($u->wallet_balance) }}</td>
          <td class="text-sm">{{ $u->ridesAsRider->count() + ($u->ridesAsDriver?->count()??0) }}</td>
          <td class="text-sm">{{ $u->avg_rating ? '⭐ '.$u->avg_rating : '—' }}</td>
          <td class="text-xs text-muted">{{ $u->created_at->format('d M Y') }}</td>
          <td>
            @if(!$u->is_banned)
            <form method="POST" action="{{ route('admin.users.ban', $u->id) }}" style="display:inline">
              @csrf <button type="submit" class="btn btn-red btn-xs" onclick="return confirm('Ban this user?')">Ban</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.unban', $u->id) }}" style="display:inline">
              @csrf <button type="submit" class="btn btn-green btn-xs">Unban</button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text3);padding:36px">No users found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
