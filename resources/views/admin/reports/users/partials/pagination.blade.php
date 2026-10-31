
<div class="le-pagination">
    <span>
        Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }}
        of {{ $records->total() }} records
    </span>

    @if($records->hasPages())
        <nav class="le-pagination-controls" aria-label="Pagination">
            @if($records->onFirstPage())
                <span class="le-page-control disabled">‹ Previous</span>
            @else
                <a class="le-page-control" href="{{ $records->previousPageUrl() }}">‹ Previous</a>
            @endif

            @foreach($records->getUrlRange(max(1, $records->currentPage() - 1), min($records->lastPage(), $records->currentPage() + 1)) as $page => $url)
                <a href="{{ $url }}" class="le-page-number {{ $records->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($records->hasMorePages())
                <a class="le-page-control" href="{{ $records->nextPageUrl() }}">Next ›</a>
            @else
                <span class="le-page-control disabled">Next ›</span>
            @endif
        </nav>
    @endif
</div>
