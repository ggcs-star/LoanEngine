
@extends('layouts.admin')

@section('title', 'Create Calculator')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calculators.css') }}?v={{ filemtime(public_path('css/calculators.css')) }}">
@endpush

@section('content')
<div class="calculator-page">

    <div class="page-heading">
        <div>
            <h1>Create Calculator</h1>
            <p>Add a calculator and assign it to the relevant journeys.</p>
        </div>

        <a href="{{ route('admin.calculators.index') }}"
           class="btn-secondary-light">
            Back to Calculators
        </a>
    </div>

    @if(session('error'))
        <div class="calculator-alert calculator-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="calculator-alert calculator-alert-error" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="calculator-card">
        <div class="form-card-heading">
            <h2>Calculator Details</h2>
            <p>Enter the calculator information below.</p>
        </div>

        <form method="POST"
              action="{{ route('admin.calculators.store') }}">
            @csrf

            @include('admin.calculators._form')

            <div class="form-actions">
                <a href="{{ route('admin.calculators.index') }}"
                   class="btn-secondary-light">
                    Cancel
                </a>

                <button type="submit" class="btn-primary-dark">
                    Save Calculator
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
