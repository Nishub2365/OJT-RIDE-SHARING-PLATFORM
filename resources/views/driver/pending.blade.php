@extends('layouts.app')
@section('title','Account Pending')
@section('content')
@php $p = auth()->user()->driverProfile; @endphp
<div style="max-width:580px;margin:0 auto;text-align:center">

  @if($p?->status === 'rejected')
  <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:16px;padding:28px;margin-bottom:20px">
    <div style="font-size:48px;margin-bottom:12px">❌</div>
    <h2 style="font-size:20px;font-weight:900;margin-bottom:8px;color:var(--red)">Account Rejected</h2>
    <p style="font-size:13px;color:var(--text2);margin-bottom:16px">{{ $p->rejection_reason ?? 'Please re-upload your documents and resubmit.' }}</p>
    <a href="{{ route('driver.documents') }}" class="btn btn-brand">Re-upload Documents</a>
  </div>
  @else
  <div style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);border-radius:16px;padding:36px;margin-bottom:20px">
    <div style="font-size:56px;margin-bottom:16px">⏳</div>
    <h2 style="font-size:22px;font-weight:900;margin-bottom:10px">Under Review</h2>
    <p style="font-size:14px;color:var(--text2);line-height:1.7;margin-bottom:24px">
      Your driver account is being reviewed by our team. This typically takes <strong>1–3 business days</strong>. You'll be notified once approved.
    </p>
    <span class="badge badge-amber" style="font-size:13px;padding:8px 20px">Pending Verification</span>
  </div>
  @endif

  <div class="card" style="text-align:left">
    <div class="card-title" style="margin-bottom:14px">📄 Document Checklist</div>
    @foreach([
      'citizenship'         => 'Citizenship / National ID',
      'driving_license'     => 'Driving License',
      'vehicle_registration'=> 'Vehicle Registration',
      'vehicle_photo'       => 'Vehicle Photo',
    ] as $type => $label)
    @php $doc = auth()->user()->documents->where('doc_type',$type)->first(); @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:20px">
          {{ $doc ? ($doc->status==='verified'?'✅':($doc->status==='rejected'?'❌':'⏳')) : '📭' }}
        </span>
        <span style="font-size:13px;font-weight:600">{{ $label }}</span>
      </div>
      <span class="badge {{ $doc ? ($doc->status==='verified'?'badge-green':($doc->status==='rejected'?'badge-red':'badge-amber')) : 'badge-muted' }}">
        {{ $doc ? ucfirst($doc->status) : 'Not Uploaded' }}
      </span>
    </div>
    @endforeach
    <div style="margin-top:14px">
      <a href="{{ route('driver.documents') }}" class="btn btn-brand btn-sm">
        {{ auth()->user()->documents->count() < 4 ? 'Upload Documents →' : 'View Documents →' }}
      </a>
    </div>
  </div>
</div>
@endsection
