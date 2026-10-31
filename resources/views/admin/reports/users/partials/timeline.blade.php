
<section class="le-data-panel le-timeline-panel">
    <div class="le-panel-heading">
        <div>
            <h2>Activity Timeline</h2>
            <p>Complete activity history for {{ $user->name }}</p>
        </div>
        <span class="le-record-count">{{ number_format($timeline->total()) }} records</span>
    </div>

    <div class="le-timeline-list">
        @forelse($timeline as $r)
            <article class="le-timeline-item">
                <span class="le-timeline-marker"></span>

                <div class="le-timeline-content">
                    <div class="le-timeline-top">
                        <strong>{{ str($r->activity_type ?? 'Activity')->replace('_', ' ')->title() }}</strong>
                        <time>{{ isset($r->happened_at) ? \Illuminate\Support\Carbon::parse($r->happened_at)->format('d M Y, h:i A') : ($r->created_at?->format('d M Y, h:i A') ?? '—') }}</time>
                    </div>

                    <div class="le-timeline-meta">
                        <span class="le-module-pill">{{ str($r->module ?? 'Activity')->replace('_', ' ')->title() }}</span>
                        <span>{{ str($r->subject_type ?? 'General activity')->replace('_', ' ')->title() }}</span>
                        @if(!empty($r->subject_id))
                            <span>Reference #{{ $r->subject_id }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="le-empty-state">
                <strong>No activity records found</strong>
                <span>This user does not have matching timeline activity.</span>
            </div>
        @endforelse
    </div>

    @include('admin.reports.users.partials.pagination', ['records' => $timeline])
</section>
