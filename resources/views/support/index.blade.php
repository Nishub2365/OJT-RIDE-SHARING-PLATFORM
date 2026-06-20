@extends('layouts.app')
@section('title','Support')
@section('content')
<div class="page-title" style="margin-bottom:20px">🎧 Support Center</div>
<div class="grid-2" style="align-items:start">
<div class="card">
  <div class="card-title" style="margin-bottom:14px">Submit a Ticket</div>
  <form method="POST" action="{{ route('support.store') }}" style="display:flex;flex-direction:column;gap:12px">
    @csrf
    <div><label class="label">Subject</label><input type="text" name="subject" class="input" placeholder="Brief issue description" required></div>
    <div>
      <label class="label">Priority</label>
      <select name="priority" class="input">
        <option value="normal">Normal</option>
        <option value="high">High</option>
        <option value="urgent">🚨 Urgent</option>
      </select>
    </div>
    <div><label class="label">Message</label><textarea name="message" class="input" rows="5" placeholder="Describe your issue in detail…" required style="resize:vertical"></textarea></div>
    <button type="submit" class="btn btn-brand btn-sm" style="justify-content:center">Submit Ticket</button>
  </form>
</div>
<div class="card">
  <div class="card-title" style="margin-bottom:14px">My Tickets</div>
  @forelse($tickets as $t)
  <div style="padding:12px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px">
      <p style="font-size:13px;font-weight:700">{{ $t->subject }}</p>
      <span class="badge {{ match($t->status) { 'open'=>'badge-red','in_progress'=>'badge-amber','resolved'=>'badge-green',default=>'badge-muted' } }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
    </div>
    <p style="font-size:12px;color:var(--text3);margin-bottom:6px">{{ Str::limit($t->message,80) }}</p>
    @if($t->admin_reply)
    <div style="background:rgba(34,197,94,.05);border:1px solid rgba(34,197,94,.12);border-radius:8px;padding:8px;margin-top:8px">
      <p style="font-size:11px;font-weight:700;color:var(--green);margin-bottom:3px">Support Reply:</p>
      <p style="font-size:12px;color:var(--text2)">{{ $t->admin_reply }}</p>
    </div>
    @endif
    <p style="font-size:11px;color:var(--text3);margin-top:6px">{{ $t->created_at->diffForHumans() }}</p>
  </div>
  @empty
  <p style="font-size:13px;color:var(--text3);text-align:center;padding:24px">No tickets yet</p>
  @endforelse
  <div class="pagination">{{ $tickets->links() }}</div>
</div>
</div>
@endsection
