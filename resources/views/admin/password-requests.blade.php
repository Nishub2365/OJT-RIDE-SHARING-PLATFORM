@extends('layouts.app')
@section('title','Password Reset Requests')
@section('content')
<div style="max-width:960px;margin:0 auto;padding:24px 16px">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
      <h2 style="font-size:22px;font-weight:900;margin-bottom:4px">🔑 Password Reset Requests</h2>
      <p style="font-size:13px;color:var(--text2)">Users who requested a password reset from the app</p>
    </div>
    <div style="display:flex;gap:8px">
      <span style="background:rgba(255,85,0,.1);color:var(--brand);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:700">
        {{ $requests->where('status','pending')->count() }} Pending
      </span>
    </div>
  </div>

  @if(session('flash_success'))
  <div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:12px;margin-bottom:16px;font-size:13px;color:#16a34a">
    ✅ {{ session('flash_success') }}
  </div>
  @endif

  @forelse($requests as $req)
  <div class="card" style="padding:20px;margin-bottom:16px;border-left:3px solid {{ $req->status==='pending' ? 'var(--brand)' : ($req->status==='approved' ? 'var(--green)' : 'var(--red)') }}">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
          <span style="font-size:16px;font-weight:800">📱 +977 {{ $req->phone }}</span>
          <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;
            background:{{ $req->status==='pending' ? 'rgba(255,85,0,.12)' : ($req->status==='approved' ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)') }};
            color:{{ $req->status==='pending' ? 'var(--brand)' : ($req->status==='approved' ? '#16a34a' : 'var(--red)') }}">
            {{ strtoupper($req->status) }}
          </span>
        </div>
        @if($req->email)
        <p style="font-size:13px;color:var(--text2);margin-bottom:4px">📧 {{ $req->email }}</p>
        @endif
        <p style="font-size:12px;color:var(--text3)">Requested: {{ $req->created_at->diffForHumans() }}</p>
        @if($req->admin_note)
        <p style="font-size:12px;color:var(--text2);margin-top:6px;font-style:italic">Note: {{ $req->admin_note }}</p>
        @endif
        @if($req->resolved_at)
        <p style="font-size:12px;color:var(--text3)">Resolved: {{ $req->resolved_at->diffForHumans() }}</p>
        @endif
      </div>

      @if($req->status === 'pending')
      <div style="display:flex;gap:8px;flex-shrink:0">
        <button onclick="document.getElementById('resolve-modal-{{ $req->id }}').style.display='flex'"
          class="btn btn-brand" style="font-size:13px;padding:8px 16px">
          ✅ Resolve
        </button>
        <form method="POST" action="{{ route('admin.password-requests.resolve', $req->id) }}" onsubmit="return confirm('Reject this request?')">
          @csrf
          <input type="hidden" name="action" value="rejected">
          <button type="submit" class="btn" style="font-size:13px;padding:8px 16px;background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2)">
            ❌ Reject
          </button>
        </form>
      </div>
      @endif
    </div>

    {{-- Resolve modal (inline) --}}
    @if($req->status === 'pending')
    <div id="resolve-modal-{{ $req->id }}" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:20px">
      <div class="card" style="width:100%;max-width:440px;padding:24px">
        <h3 style="font-size:18px;font-weight:900;margin-bottom:4px">Reset Password for {{ $req->phone }}</h3>
        <p style="font-size:13px;color:var(--text2);margin-bottom:20px">Set a new password for this user</p>
        <form method="POST" action="{{ route('admin.password-requests.resolve', $req->id) }}" style="display:flex;flex-direction:column;gap:14px">
          @csrf
          <input type="hidden" name="action" value="approved">
          <div>
            <label class="label">New Password <span style="color:var(--red)">*</span></label>
            <input type="text" name="new_password" class="input" placeholder="Enter new password (min 6 chars)" required minlength="6">
          </div>
          <div>
            <label class="label">Admin Note (optional)</label>
            <input type="text" name="admin_note" class="input" placeholder="e.g. Verified via call">
          </div>
          <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-brand" style="flex:1;justify-content:center">✅ Reset Password</button>
            <button type="button" onclick="document.getElementById('resolve-modal-{{ $req->id }}').style.display='none'"
              class="btn" style="padding:10px 16px;background:var(--card2)">Cancel</button>
          </div>
        </form>
      </div>
    </div>
    @endif
  </div>
  @empty
  <div class="card" style="padding:40px;text-align:center;color:var(--text3)">
    <p style="font-size:32px;margin-bottom:12px">🔑</p>
    <p style="font-size:15px;font-weight:700;margin-bottom:6px">No password reset requests</p>
    <p style="font-size:13px">When users click "Forgot Password", their requests will appear here.</p>
  </div>
  @endforelse

  <div style="margin-top:20px">{{ $requests->links() }}</div>
</div>
@endsection