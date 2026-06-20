@extends('layouts.app')
@section('title','Complaints')
@section('content')
<div class="page-title" style="margin-bottom:20px">😠 Complaints</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>By</th><th>Against</th><th>Ride</th><th>Complaint</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($complaints as $c)
        <tr>
          <td class="text-xs text-muted">#{{ $c->id }}</td>
          <td>
            <p style="font-size:13px;font-weight:600">{{ $c->complainant->full_name }}</p>
            <span class="badge badge-muted" style="font-size:10px">{{ ucfirst($c->complainant->role) }}</span>
          </td>
          <td>
            <p style="font-size:13px;font-weight:600">{{ $c->accused->full_name }}</p>
            <span class="badge badge-muted" style="font-size:10px">{{ ucfirst($c->accused->role) }}</span>
          </td>
          <td class="text-sm text-muted">{{ $c->ride_id ? '#'.$c->ride_id : '—' }}</td>
          <td class="text-sm text-muted">{{ Str::limit($c->description, 50) }}</td>
          <td>
            <span class="badge {{ match($c->status) {
              'open'=>'badge-red','investigating'=>'badge-amber','resolved'=>'badge-green',default=>'badge-muted'
            } }}">{{ ucfirst($c->status) }}</span>
          </td>
          <td class="text-xs text-muted">{{ $c->created_at->format('d M Y') }}</td>
          <td>
            @if($c->status !== 'resolved')
            <form method="POST" action="{{ route('admin.complaints.resolve', $c->id) }}" style="display:inline">
              @csrf <button type="submit" class="btn btn-green btn-xs">✓ Resolve</button>
            </form>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text3);padding:36px">No complaints filed</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="pagination">{{ $complaints->links() }}</div>
</div>
@endsection
