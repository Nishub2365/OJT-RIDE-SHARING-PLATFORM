@extends('layouts.app')
@section('title','Rate Rider')
@section('content')
<div style="max-width:480px;margin:0 auto">
  <div class="card" style="text-align:center;padding:32px">
    <div style="font-size:52px;margin-bottom:12px">⭐</div>
    <h2 style="font-size:22px;font-weight:900;margin-bottom:6px">Rate Your Rider</h2>
    <p style="font-size:14px;color:var(--text2);margin-bottom:24px">{{ $ride->rider->full_name ?? 'Your Rider' }}</p>
    <form method="POST" action="{{ route('driver.rate.store',$ride->id) }}">
      @csrf
      <div id="stars" style="display:flex;justify-content:center;gap:10px;font-size:40px;margin-bottom:20px;cursor:pointer">
        @for($i=1;$i<=5;$i++)
        <span class="star-btn" data-v="{{ $i }}" style="transition:.1s;user-select:none">☆</span>
        @endfor
      </div>
      <input type="hidden" name="rating" id="ratingInput" required>
      <div style="margin-bottom:14px">
        <label class="label">Comment (optional)</label>
        <textarea name="comment" class="input" rows="3" placeholder="How was the rider?" style="text-align:left"></textarea>
      </div>
      <button type="submit" class="btn btn-brand" style="width:100%;justify-content:center;padding:14px" id="submitBtn" disabled>Submit Rating</button>
    </form>
    <a href="{{ route('driver.dashboard') }}" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:10px">Skip</a>
  </div>
</div>
@endsection
@push('scripts')
<script>
const stars = document.querySelectorAll('.star-btn');
const ri = document.getElementById('ratingInput');
stars.forEach(s => {
  s.addEventListener('click', () => {
    const v = +s.dataset.v; ri.value = v;
    document.getElementById('submitBtn').disabled = false;
    stars.forEach((st, i) => { st.textContent = i < v ? '⭐' : '☆'; });
  });
  s.addEventListener('mouseenter', () => { const v = +s.dataset.v; stars.forEach((st,i)=>{ st.textContent = i<v?'⭐':'☆'; }); });
  s.addEventListener('mouseleave', () => { const cur = +ri.value||0; stars.forEach((st,i)=>{ st.textContent = i<cur?'⭐':'☆'; }); });
});
</script>
@endpush
