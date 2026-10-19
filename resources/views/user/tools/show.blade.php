@extends('user.layouts.app')
@section('title', $tool->name . ' | LoanEngine Tools')
@section('page_heading', 'Tools & Calculators')
@push('styles')<link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">@endpush
@section('content')
<div class="le-tools le-detail"><a class="le-back" href="{{ route('user.tools.index') }}">← Back to all tools</a>
 <section class="le-detail-hero"><div><span class="le-eyebrow">{{ $tool->category }} · {{ $tool->type }}</span><h1>{{ $tool->name }}</h1><p>{{ $tool->short_description }}</p><div class="le-detail-meta">● Available tool @if($tool->journeys->isNotEmpty()) · {{ $tool->journeys->count() }} related journeys @endif</div></div><div class="le-detail-art" aria-hidden="true">▦</div></section>
 <div class="le-detail-layout"><main><section class="le-panel"><span class="le-eyebrow">ABOUT THIS TOOL</span><h2>What you can do</h2><p>{{ $tool->short_description }}</p><hr><div class="le-note"><span>i</span><p>Review the inputs and guidance on the tool page before using an estimate to make a financial decision.</p></div></section>
 @if($relatedTools->isNotEmpty())<section class="le-related"><div class="le-section-head"><div><span class="le-eyebrow">KEEP EXPLORING</span><h2>Related tools</h2></div><a href="{{ route('user.tools.index', ['category' => $tool->category]) }}">View all →</a></div>@foreach($relatedTools as $relatedTool)<a class="le-related-card" href="{{ route('user.tools.show', $relatedTool) }}"><strong>{{ $relatedTool->name }}</strong><small>{{ $relatedTool->short_description }}</small></a>@endforeach</section>@endif</main>
 <aside class="le-detail-side"><section class="le-panel"><span class="le-eyebrow">QUICK DETAILS</span><h2>Tool information</h2><dl><div><dt>Category</dt><dd>{{ $tool->category }}</dd></div><div><dt>Type</dt><dd>{{ $tool->type }}</dd></div><div><dt>Status</dt><dd>Available</dd></div></dl></section>
 @if($tool->journeys->isNotEmpty())<section class="le-panel"><span class="le-eyebrow">PART OF YOUR PLAN</span><h2>Related journeys</h2><ul>@foreach($tool->journeys as $journey)<li>{{ $journey->name }}</li>@endforeach</ul></section>@endif
 @if(filled($tool->page_route))<section class="le-panel"><h2>Ready to get started?</h2><p>Open the tool experience to continue.</p><a class="le-btn le-btn-primary le-wide" href="{{ url($tool->page_route) }}">Open tool →</a></section>@endif</aside></div>
</div>
@endsection
