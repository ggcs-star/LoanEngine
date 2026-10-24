@extends('layouts.admin')

@section('title', 'Edit Calculator')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators.css') }}?v={{ filemtime(public_path('css/calculators.css')) }}">
@endpush

@section('content')
<div class="calculator-page">
    <div class="page-heading">
        <div>
            <h1>Edit Calculator</h1>
            <p>Update calculator details, formula configuration and journey associations.</p>
        </div>
        <a href="{{ route('admin.calculators.index') }}" class="btn-secondary-light">Back to Calculators</a>
    </div>

    @if (session('success'))
        <div class="calculator-alert calculator-alert-success" role="status">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="calculator-alert calculator-alert-error" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="calculator-alert calculator-alert-error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.calculators.update', $calculator) }}">
        @csrf
        @method('PUT')
        @include('admin.calculators._form', ['calculator' => $calculator, 'journeys' => $journeys, 'formulaTypes' => $formulaTypes, 'formulaDefinitions' => $formulaDefinitions])
    </form>
</div>
@endsection
