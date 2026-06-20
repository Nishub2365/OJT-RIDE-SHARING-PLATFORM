@extends('layouts.app')
@section('title','My Documents')
@section('content')
<div class="page-title" style="margin-bottom:20px">📄 Driver Documents</div>
@php
$docs = auth()->user()->documents->keyBy('doc_type');
$required = [
  'citizenship'          => ['Citizenship / National ID',   '📛'],
  'driving_license'      => ['Driving License',             '🪪'],
  'vehicle_registration' => ['Vehicle Registration',        '📋'],
  'vehicle_photo'        => ['Vehicle Photo',               '🚗'],
];
@endphp
<div class="grid-2">
  @foreach($required as $type => [$label, $icon])
  @php $doc = $docs[$type] ?? null; @endphp
  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="font-size:24px">{{ $icon }}</div>
        <div>
          <p style="font-size:14px;font-weight:800">{{ $label }}</p>
          <span class="badge {{ $doc ? ($doc->status==='verified'?'badge-green':($doc->status==='rejected'?'badge-red':'badge-amber')) : 'badge-muted' }}">
            {{ $doc ? ucfirst($doc->status) : 'Not Uploaded' }}
          </span>
        </div>
      </div>
    </div>

    @if($doc?->doc_path)
    <div style="background:rgba(255,255,255,.02);border-radius:10px;padding:10px;margin-bottom:12px;font-size:12px;color:var(--text3)">
      📁 {{ basename($doc->doc_path) }}
      @if($doc->status === 'rejected' && $doc->rejection_reason)
      <p style="color:var(--red);margin-top:4px">❌ {{ $doc->rejection_reason }}</p>
      @endif
    </div>
    @endif

    @if(!$doc || $doc->status === 'rejected')
    <form method="POST" action="{{ route('driver.documents.upload') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="doc_type" value="{{ $type }}">
      <input type="file" name="document" class="input" accept="image/*,.pdf" required style="padding:8px;font-size:12px;margin-bottom:8px">
      <button type="submit" class="btn btn-brand btn-sm" style="width:100%;justify-content:center">
        📤 Upload {{ $label }}
      </button>
    </form>
    @elseif($doc->status === 'pending')
    <div style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.15);border-radius:10px;padding:10px;text-align:center;font-size:12px;color:var(--amber)">
      ⏳ Under review — usually within 1–3 business days
    </div>
    @else
    <div style="background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.15);border-radius:10px;padding:10px;text-align:center;font-size:12px;color:var(--green)">
      ✅ Verified
    </div>
    @endif
  </div>
  @endforeach
</div>
@endsection
