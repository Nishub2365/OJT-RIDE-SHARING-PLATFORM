@extends('layouts.app')
@section('title','Support Tickets')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div class="page-title">🎧 Support Tickets</div>
  <form method="GET" style="display:flex;gap:8px">
    <select name="status" class="input" style="width:140px" onchange="this.form.submit()">
      <option value="">All</option>
      @foreach(['open','in_progress','resolved','closed'] as $s)
      <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
      @endforeach
    </select>
  </form>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>User</th><th>Subject</th><th>Priority</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($tickets as $t)
        <tr>
          <td class="text-xs text-muted">#{{ $t->id }}</td>
          <td>
            <p style="font-size:13px;font-weight:600">{{ $t->user->full_name }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ ucfirst($t->user->role) }}</p>
          </td>
          <td>
            <p style="font-size:13px;font-weight:600">{{ $t->subject }}</p>
            <p style="font-size:11px;color:var(--text3)">{{ Str::limit($t->message,40) }}</p>
          </td>
          <td>
            <span class="badge {{ match($t->priority) {
              'urgent'=>'badge-red','high'=>'badge-amber','normal'=>'badge-blue',default=>'badge-muted'
            } }}">{{ ucfirst($t->priority) }}</span>
          </td>
          <td>
            <span class="badge {{ match($t->status) {
              'open'=>'badge-red','in_progress'=>'badge-amber','resolved'=>'badge-green',default=>'badge-muted'
            } }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
          </td>
          <td class="text-xs text-muted">{{ $t->created_at->format('d M Y') }}</td>
          <td>
            <div x-data="{ open:false }">
              <button class="btn btn-blue btn-xs" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none'">Reply</button>
              <div style="display:none;position:absolute;z-index:10;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px;width:280px;box-shadow:0 8px 30px rgba(0,0,0,.5)">
                <form method="POST" action="{{ route('admin.tickets.reply', $t->id) }}" style="display:flex;flex-direction:column;gap:8px">
                  @csrf
                  <textarea name="reply" class="input" rows="3" placeholder="Reply to user…" required style="font-size:13px"></textarea>
                  <select name="status" class="input" style="font-size:13px">
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                    <option value="{{ $s }}" {{ $t->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                  </select>
                  <button type="submit" class="btn btn-brand btn-sm">Send Reply</button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text3);padding:36px">No tickets found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $tickets->withQueryString()->links() }}</div>
</div>
@endsection
