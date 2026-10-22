
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
            <p>Update calculator details and journey recommendations.</p>
        </div>

        <a href="{{ route('admin.calculators.index') }}"
           class="btn-secondary-light">
            Back to Calculators
        </a>
    </div>

    @if(session('success'))
        <div class="calculator-alert calculator-alert-success">
            {{ session('success') }}
        </div>
    @endif

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
            <h2>{{ $calculator->name }}</h2>
            <p>Make your changes and save them when you're done.</p>
        </div>

        <form method="POST"
              action="{{ route('admin.calculators.update', $calculator) }}">
            @csrf
            @method('PUT')

            @include('admin.calculators._form', [
                'calculator' => $calculator
            ])

            <div class="form-actions">
                <a href="{{ route('admin.calculators.index') }}"
                   class="btn-secondary-light">
                    Cancel
                </a>

                <button type="submit" class="btn-primary-dark">
                    Update Calculator
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
