
@extends('layouts.admin')

@section('title', 'Edit Education Module')

@section('content')
<div class="dashboard-page edu-modules-page">

    <div class="edu-modules-header">
        <div>
            <a href="{{ route('admin.education.modules.index') }}"
               class="edu-modules-back-link">
                <span>&larr;</span> Back to Modules
            </a>

            <span class="edu-modules-eyebrow">EDUCATION MANAGEMENT</span>
            <h1>Edit Module</h1>
            <p>Update your module information, category and cover image.</p>
        </div>

        <div class="edu-modules-edit-reference">
            <span>Module ID</span>
            <strong>#{{ $module->id }}</strong>
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
        action="{{ route('admin.education.modules.update', $module) }}"
        method="POST"
        enctype="multipart/form-data"
        class="edu-modules-editor"
    >
        @csrf
        @method('PUT')

        @include('admin.education.modules.form', [
            'module' => $module,
            'categories' => $categories,
        ])

        <div class="edu-modules-form-footer">
            <a href="{{ route('admin.education.modules.index') }}"
               class="edu-modules-secondary-btn">
                Cancel
            </a>

            <button type="submit" class="edu-modules-primary-btn">
                Save Changes
                <span>&rarr;</span>
            </button>
        </div>
    </form>

</div>
@endsection
