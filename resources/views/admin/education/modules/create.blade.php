
@extends('layouts.admin')

@section('title', 'Create Education Module')

@section('content')
<div class="dashboard-page edu-modules-page">

    <div class="edu-modules-header">
        <div>
            <a href="{{ route('admin.education.modules.index') }}"
               class="edu-modules-back-link">
                <span>&larr;</span> Back to Modules
            </a>

            <span class="edu-modules-eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Create Module</h1>
            <p>Add a new learning module to your education library.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="edu-modules-alert edu-modules-alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.education.modules.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="edu-modules-editor"
    >
        @csrf

        @include('admin.education.modules.form', [
            'module' => $module ?? null,
            'categories' => $categories,
        ])

        <div class="edu-modules-form-footer">
            <a href="{{ route('admin.education.modules.index') }}"
               class="edu-modules-secondary-btn">
                Cancel
            </a>

            <button type="submit" class="edu-modules-primary-btn">
                Create Module
                <span>&rarr;</span>
            </button>
        </div>
    </form>

</div>
@endsection
