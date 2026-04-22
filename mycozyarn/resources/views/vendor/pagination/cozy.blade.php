@if ($paginator->hasPages())
    <nav class="cozy-pager" role="navigation" aria-label="Pagination">
        <div class="cozy-pager__info">
            Trang <strong>{{ $paginator->currentPage() }}</strong> /
            <strong>{{ $paginator->lastPage() }}</strong>
            · Hiển thị {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} / {{ $paginator->total() }}
        </div>

        <ul class="cozy-pager__list">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="cozy-pager__item is-disabled"><span>‹</span></li>
            @else
                <li class="cozy-pager__item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹</a>
                </li>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" separator --}}
                @if (is_string($element))
                    <li class="cozy-pager__item is-disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="cozy-pager__item is-active"><span>{{ $page }}</span></li>
                        @else
                            <li class="cozy-pager__item">
                                <a href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="cozy-pager__item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">›</a>
                </li>
            @else
                <li class="cozy-pager__item is-disabled"><span>›</span></li>
            @endif
        </ul>
    </nav>
@endif
