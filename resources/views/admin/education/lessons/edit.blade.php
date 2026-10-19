@extends('layouts.admin')
@section('title', 'Edit Lesson')
@section('content')
<div class="edu-lessons-page edu-lesson-editor-page"><div class="edu-lessons-heading"><div><span class="edu-lessons-eyebrow">EDUCATION MANAGEMENT</span><h1>Edit Lesson</h1><p>Update “{{ $lesson->title }}” and its media.</p></div><a class="edu-lessons-secondary" href="{{ route('admin.education.lessons.show', $lesson) }}">View Lesson ↗</a></div>
@if($errors->any())<div class="edu-lessons-alert edu-lessons-alert-error">Please check the highlighted fields. {{ $errors->first() }}</div>@endif
@if(session('error'))<div class="edu-lessons-alert edu-lessons-alert-error">{{ session('error') }}</div>@endif
<section class="edu-lessons-card edu-lesson-editor-card"><form method="POST" action="{{ route('admin.education.lessons.update', $lesson) }}" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.education.lessons.form', ['lesson' => $lesson])<div class="edu-lesson-editor-actions"><a class="edu-lessons-secondary" href="{{ route('admin.education.lessons.index') }}">Cancel</a><button class="edu-lessons-primary" type="submit">Save Changes</button></div></form></section></div>
@endsection
