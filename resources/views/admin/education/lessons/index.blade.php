@extends('layouts.admin')
@section('title', 'Lessons & Media')
@section('content')
<div class="edu-lessons-page">
    <div class="edu-lessons-heading">
        <div><span class="edu-lessons-eyebrow">EDUCATION MANAGEMENT</span><h1>Lessons &amp; Media</h1><p>Manage learning content, video lessons, audio and articles.</p></div>
        <a href="{{ route('admin.education.lessons.create') }}" class="edu-lessons-primary"><span>＋</span> Add Lesson</a>
    </div>

    @foreach (['success','error','warning'] as $flash)
        @if(session($flash))<div class="edu-lessons-alert edu-lessons-alert-{{ $flash }}">{{ session($flash) }}</div>@endif
    @endforeach
    @if($errors->any())<div class="edu-lessons-alert edu-lessons-alert-error">{{ $errors->first() }}</div>@endif

    <section class="edu-lessons-card">
        <form method="GET" action="{{ route('admin.education.lessons.index') }}" class="edu-lessons-filters">
            <label class="edu-lessons-search"><span>⌕</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Search by lesson name or slug..." aria-label="Search lessons"></label>
            <label class="edu-lessons-filter"><span>Module</span><select name="module"><option value="">All Modules</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected((string)request('module') === (string)$module->id)>{{ $module->title }}</option>@endforeach</select></label>
            <label class="edu-lessons-filter"><span>Content Type</span><select name="type"><option value="">All Types</option>@foreach(\App\Models\EducationLesson::CONTENT_TYPES as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>@endforeach</select></label>
            <label class="edu-lessons-filter"><span>Status</span><select name="status"><option value="">All Statuses</option><option value="published" @selected(request('status')==='published')>Published</option><option value="draft" @selected(request('status')==='draft')>Draft</option></select></label>
            <label class="edu-lessons-filter"><span>Sort By</span><select name="sort"><option value="default" @selected(request('sort','default')==='default')>Default Order</option><option value="latest" @selected(request('sort')==='latest')>Latest</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest</option><option value="title" @selected(request('sort')==='title')>Lesson Name</option></select></label>
            <button class="edu-lessons-filter-button" type="submit">⌕ Filter</button>
            @if(request()->hasAny(['search','module','type','status','sort']))<a class="edu-lessons-reset" href="{{ route('admin.education.lessons.index') }}">Reset</a>@endif
        </form>

        <div class="edu-lessons-bulkbar"><div><strong id="eduLessonSelectedCount">0</strong><span id="eduLessonSelectedText">lessons selected</span></div>
            <form id="eduLessonBulkForm" method="POST" action="{{ route('admin.education.lessons.bulk-destroy') }}">@csrf @method('DELETE')<div id="eduLessonBulkIds"></div><button id="eduLessonBulkButton" type="submit" disabled>▤ Delete Selected</button></form>
        </div>

        <div class="edu-lessons-table-wrap"><table class="edu-lessons-table"><thead><tr><th class="edu-lessons-check"><input id="eduLessonSelectAll" type="checkbox" aria-label="Select all lessons"></th><th>#</th><th>LESSON</th><th>MODULE</th><th>TYPE</th><th>DURATION</th><th>STATUS</th><th>ORDER</th><th>ACTIONS</th></tr></thead><tbody>
        @forelse($lessons as $lesson)
            @php
                $mediaUrl = $lesson->media_path ? asset('storage/' . ltrim($lesson->media_path, '/')) : $lesson->external_media_url;
                $thumbnailUrl = $lesson->thumbnail_path ? asset('storage/' . ltrim($lesson->thumbnail_path, '/')) : null;
            @endphp
            <tr class="edu-lesson-row" data-view-url="{{ route('admin.education.lessons.show', $lesson) }}" tabindex="0" aria-label="View {{ $lesson->title }}">
                <td class="edu-lessons-check"><input type="checkbox" class="edu-lesson-checkbox" value="{{ $lesson->id }}" aria-label="Select {{ $lesson->title }}"></td>
                <td class="edu-lessons-number">{{ $lessons->firstItem() + $loop->index }}</td>
                <td><div class="edu-lesson-identity">
                    <button type="button" class="edu-lesson-thumb-button" data-preview-url="{{ $mediaUrl }}" data-preview-type="{{ $lesson->content_type }}" data-preview-title="{{ $lesson->title }}" data-preview-poster="{{ $thumbnailUrl }}" aria-label="Preview {{ $lesson->title }}">
                        @if($thumbnailUrl)<img src="{{ $thumbnailUrl }}" alt="{{ $lesson->title }}" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false;"> <span class="edu-lesson-thumb-fallback" hidden>{{ strtoupper(substr($lesson->content_type,0,1)) }}</span>
                        @else <span class="edu-lesson-thumb-fallback">{{ $lesson->content_type === 'audio' ? '♫' : ($lesson->content_type === 'article' ? 'Aa' : '▶') }}</span>@endif
                    </button>
                    <div class="edu-lesson-title-wrap"><a class="edu-lesson-title" href="{{ route('admin.education.lessons.show', $lesson) }}">{{ $lesson->title }}</a><span class="edu-lesson-slug">{{ $lesson->slug }}</span></div>
                </div></td>
                <td><span class="edu-lesson-module">{{ $lesson->module?->title ?? 'Unassigned' }}</span><small class="edu-lesson-category">{{ $lesson->module?->category?->name ?? 'No category' }}</small></td>
                <td><span class="edu-lesson-type edu-lesson-type-{{ $lesson->content_type }}">{{ ucfirst($lesson->content_type) }}</span></td>
                <td><span class="edu-lesson-duration">{{ $lesson->formatted_duration }}</span></td>
                <td><span class="edu-lesson-status edu-lesson-status-{{ $lesson->status }}"><i></i>{{ ucfirst($lesson->status) }}</span></td>
                <td>{{ $lesson->sort_order }}</td>
                <td><div class="edu-lesson-actions"><a href="{{ route('admin.education.lessons.show', $lesson) }}" class="edu-lesson-action" title="View lesson" aria-label="View {{ $lesson->title }}"><svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a><a href="{{ route('admin.education.lessons.edit', $lesson) }}" class="edu-lesson-action" title="Edit lesson" aria-label="Edit {{ $lesson->title }}"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L8 20l-5 1 1-5Z"/></svg></a><form method="POST" action="{{ route('admin.education.lessons.destroy', $lesson) }}" class="edu-lesson-single-form" data-lesson-title="{{ $lesson->title }}">@csrf @method('DELETE')<button type="submit" class="edu-lesson-action edu-lesson-delete" title="Delete lesson" aria-label="Delete {{ $lesson->title }}"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m5 5v5m4-5v5"/></svg></button></form></div></td>
            </tr>
        @empty
            <tr><td colspan="9"><div class="edu-lessons-empty"><span>♫</span><h3>No lessons found</h3><p>Try another search or create your first lesson.</p><a href="{{ route('admin.education.lessons.create') }}" class="edu-lessons-primary">＋ Add Lesson</a></div></td></tr>
        @endforelse
        </tbody></table></div>
        <div class="edu-lessons-footer"><span>@if($lessons->total()) Showing <strong>{{ $lessons->firstItem() }}–{{ $lessons->lastItem() }}</strong> of <strong>{{ $lessons->total() }}</strong> lessons @else Showing 0 lessons @endif</span>@if($lessons->hasPages())<div class="edu-lessons-pagination">{{ $lessons->onEachSide(1)->links() }}</div>@endif</div>
    </section>
</div>

<div class="edu-lesson-modal" id="eduLessonConfirmModal" aria-hidden="true"><div class="edu-lesson-modal-backdrop" data-close-lesson-modal></div><div class="edu-lesson-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="eduLessonConfirmTitle"><div class="edu-lesson-modal-icon">!</div><h2 id="eduLessonConfirmTitle">Delete lesson?</h2><p id="eduLessonConfirmMessage">This action cannot be undone.</p><div class="edu-lesson-modal-actions"><button type="button" id="eduLessonCancelDelete">Cancel</button><button type="button" id="eduLessonConfirmDelete">Yes, Delete</button></div></div></div>
<div class="edu-lesson-preview-modal" id="eduLessonPreviewModal" aria-hidden="true"><div class="edu-lesson-preview-backdrop" data-close-preview></div><div class="edu-lesson-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="eduLessonPreviewTitle"><button type="button" class="edu-lesson-preview-close" data-close-preview aria-label="Close preview">×</button><span class="edu-lesson-preview-label">MEDIA PREVIEW</span><h2 id="eduLessonPreviewTitle">Lesson preview</h2><div id="eduLessonPreviewPlayer" class="edu-lesson-preview-player"></div><p class="edu-lesson-preview-help">Use the player controls to play, pause and adjust volume.</p></div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('eduLessonSelectAll');
    const checks = Array.from(document.querySelectorAll('.edu-lesson-checkbox'));
    const count = document.getElementById('eduLessonSelectedCount');
    const countText = document.getElementById('eduLessonSelectedText');
    const bulkButton = document.getElementById('eduLessonBulkButton');
    const bulkForm = document.getElementById('eduLessonBulkForm');
    const ids = document.getElementById('eduLessonBulkIds');
    const confirmModal = document.getElementById('eduLessonConfirmModal');
    const confirmTitle = document.getElementById('eduLessonConfirmTitle');
    const confirmMessage = document.getElementById('eduLessonConfirmMessage');
    const confirmButton = document.getElementById('eduLessonConfirmDelete');
    let pendingForm = null;

    function selected() { return checks.filter(item => item.checked); }
    function updateSelection() {
        const amount = selected().length;
        count.textContent = amount;
        countText.textContent = amount === 1 ? 'lesson selected' : 'lessons selected';
        bulkButton.disabled = amount === 0;
        if (selectAll) { selectAll.checked = checks.length > 0 && amount === checks.length; selectAll.indeterminate = amount > 0 && amount < checks.length; }
    }
    selectAll?.addEventListener('change', () => { checks.forEach(item => item.checked = selectAll.checked); updateSelection(); });
    checks.forEach(item => item.addEventListener('change', updateSelection));
    function openConfirm(title, message, form) { pendingForm = form; confirmTitle.textContent = title; confirmMessage.textContent = message; confirmModal.classList.add('is-open'); confirmModal.setAttribute('aria-hidden','false'); confirmButton.focus(); }
    function closeConfirm() { confirmModal.classList.remove('is-open'); confirmModal.setAttribute('aria-hidden','true'); pendingForm = null; }
    bulkForm?.addEventListener('submit', event => { event.preventDefault(); const chosen = selected(); if (!chosen.length) return; ids.replaceChildren(); chosen.forEach(item => { const input = document.createElement('input'); input.type='hidden'; input.name='lesson_ids[]'; input.value=item.value; ids.appendChild(input); }); openConfirm('Delete ' + chosen.length + ' lesson' + (chosen.length === 1 ? '' : 's') + '?', 'Selected lessons and their uploaded media will be deleted. This cannot be undone.', bulkForm); });
    document.querySelectorAll('.edu-lesson-single-form').forEach(form => form.addEventListener('submit', event => { event.preventDefault(); openConfirm('Delete this lesson?', 'Delete “' + (form.dataset.lessonTitle || 'this lesson') + '” and its media? This cannot be undone.', form); }));
    document.getElementById('eduLessonCancelDelete')?.addEventListener('click', closeConfirm);
    confirmModal?.querySelectorAll('[data-close-lesson-modal]').forEach(el => el.addEventListener('click', closeConfirm));
    confirmButton?.addEventListener('click', () => { if (pendingForm) pendingForm.submit(); });

    const previewModal = document.getElementById('eduLessonPreviewModal');
    const playerBox = document.getElementById('eduLessonPreviewPlayer');
    function closePreview() { playerBox.querySelectorAll('video,audio').forEach(player => { player.pause(); player.removeAttribute('src'); player.load(); }); playerBox.replaceChildren(); previewModal.classList.remove('is-open'); previewModal.setAttribute('aria-hidden','true'); document.body.classList.remove('edu-lesson-modal-open'); }
    document.querySelectorAll('.edu-lesson-thumb-button').forEach(button => button.addEventListener('click', () => {
        const url = button.dataset.previewUrl;
        const type = button.dataset.previewType;
        document.getElementById('eduLessonPreviewTitle').textContent = button.dataset.previewTitle || 'Lesson preview';
        playerBox.replaceChildren();
        if (!url) { const msg = document.createElement('p'); msg.className = 'edu-lesson-no-media'; msg.textContent = 'No uploaded media or playable media URL is available for this lesson.'; playerBox.appendChild(msg); }
        else if (type === 'video' || type === 'reel') { const video = document.createElement('video'); video.controls = true; video.autoplay = false; video.playsInline = true; video.preload = 'metadata'; if (button.dataset.previewPoster) video.poster = button.dataset.previewPoster; video.src = url; playerBox.appendChild(video); }
        else if (type === 'audio') { const audio = document.createElement('audio'); audio.controls = true; audio.autoplay = false; audio.preload = 'metadata'; audio.src = url; playerBox.appendChild(audio); }
        else { const msg = document.createElement('p'); msg.className = 'edu-lesson-no-media'; msg.textContent = 'This lesson is text content. Open View to read the article.'; playerBox.appendChild(msg); }
        previewModal.classList.add('is-open'); previewModal.setAttribute('aria-hidden','false'); document.body.classList.add('edu-lesson-modal-open');
    }));
    previewModal?.querySelectorAll('[data-close-preview]').forEach(el => el.addEventListener('click', closePreview));
    document.querySelectorAll('.edu-lesson-row').forEach(row => { row.addEventListener('click', event => { if (event.target.closest('a,button,input,form,label,select')) return; window.location.href = row.dataset.viewUrl; }); row.addEventListener('keydown', event => { if ((event.key === 'Enter' || event.key === ' ') && event.target === row) { event.preventDefault(); window.location.href = row.dataset.viewUrl; } }); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeConfirm(); if (previewModal.classList.contains('is-open')) closePreview(); } });
    updateSelection();
});
</script>
@endpush
