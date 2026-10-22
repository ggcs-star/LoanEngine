@extends('user.layouts.app')

@section('title', 'Recommended Calculators | LoanEngine')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/user-tools.css') }}">
@endpush

@section('content')
<div class="le-tools">

    <a class="le-back"
       href="{{ route('user.calculators.journeys.index') }}">
        ← Back to options
    </a>

    <section class="le-section-head">
        <div>
            <span class="le-eyebrow">RECOMMENDED TOOLS</span>
            <h1>Recommended Calculators</h1>
            <p>Choose a calculator to continue your home-loan planning.</p>
        </div>

        <span class="le-count">
            {{ $calculators->count() }} calculators
        </span>
    </section>

    <div class="le-grid">
        @forelse($calculators as $calculator)

            <article class="le-card">

                <div class="le-card-top">
                    <span class="le-icon">⌂</span>
                    <span class="le-chip">Calculator</span>
                </div>

                <div class="le-card-body">
                    <h3>{{ $calculator->name }}</h3>
                    <p>{{ $calculator->short_description }}</p>
                </div>

                
                <div class="le-card-foot">
                    @if(
                        in_array($calculator->slug, [
                            'home-affordability-calculator',
                            'home-loan-emi-calculator',
                            'home-loan-eligibility-calculator',
                            'stamp-duty-calculator',
                        ], true)
                        && \Illuminate\Support\Facades\Route::has('calculators.show')
                    )
                        <a href="{{ route('calculators.show', ['tool' => $calculator->slug]) }}">
                            Open calculator →
                        </a>

                    @elseif(
                        $calculator->route_name
                        && \Illuminate\Support\Facades\Route::has($calculator->route_name)
                    )
                        <a href="{{ route($calculator->route_name, ['tool' => $calculator->slug]) }}">
                            Open calculator →
                        </a>

                    @else
                        <span>Calculator page not connected yet</span>
                    @endif
                </div>


            </article>

        @empty

            <div class="le-empty">
                <h3>No calculators assigned yet</h3>
                <p>An administrator can assign active calculators to this journey.</p>
            </div>

        @endforelse
    </div>

</div>
@endsection