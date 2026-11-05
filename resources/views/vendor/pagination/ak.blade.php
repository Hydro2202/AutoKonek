@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">
            Showing <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span> to
            <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span> of
            <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
        </p>

        <ul class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <li><span class="btn-outline btn-sm pointer-events-none opacity-50" aria-disabled="true">Previous</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-outline btn-sm">Previous</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="px-2 text-muted">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="hidden sm:block">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="btn-navy btn-sm min-w-8">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="btn-outline btn-sm min-w-8" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-outline btn-sm">Next</a></li>
            @else
                <li><span class="btn-outline btn-sm pointer-events-none opacity-50" aria-disabled="true">Next</span></li>
            @endif
        </ul>
    </nav>
@endif
