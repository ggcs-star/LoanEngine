@extends('layouts.admin')
@section('title', 'Create Lesson')
@section('content')
<div class="edu-lessons-page edu-lesson-editor-page"><div class="edu-lessons-heading"><div><span class="edu-lessons-eyebrow">EDUCATION MANAGEMENT</span><h1>Create Lesson</h1><p>Add a video, audio lesson, reel or article to a module.</p></div><a class="edu-lessons-secondary" href="{{ route('admin.education.lessons.index') }}">← Back to Lessons</a></div>
@if($errors->any())<div class="edu-lessons-alert edu-lessons-alert-error">Please check the highlighted fields. {{ $errors->first() }}</div>@endif
@if(session('error'))<div class="edu-lessons-alert edu-lessons-alert-error">{{ session('error') }}</div>@endif
<section class="edu-lessons-card edu-lesson-editor-card"><form method="POST" action="{{ route('admin.education.lessons.store') }}" enctype="multipart/form-data">@csrf @include('admin.education.lessons.form', ['lesson' => null])<div class="edu-lesson-editor-actions"><a class="edu-lessons-secondary" href="{{ route('admin.education.lessons.index') }}">Cancel</a><button class="edu-lessons-primary" type="submit">Create Lesson</button></div></form></section></div>
@endsection
