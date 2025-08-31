@if ($paginator->hasPages())
<nav class="d-flex justify-items-center justify-content-between">
    <div class="d-flex flex-column justify-content-between flex-fill d-lg-none">
        <ul class="pagination justify-content-center mb-2">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link"><i class="bi bi-chevron-left"></i></span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
            </li>
            @endif

            {{-- Smart Pagination for Mobile/Tablet --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $pages = [];
                
                if ($lastPage <= 6) {
                    // Если страниц мало, показываем все
                    for ($i = 1; $i <= $lastPage; $i++) {
                        $pages[] = $i;
                    }
                } else {
                    // Всегда показываем первую и последнюю
                    $pages[] = 1;
                    
                    if ($currentPage <= 3) {
                        // В начале: 1, 2, 3, 4, 5, ..., last
                        for ($i = 2; $i <= 5; $i++) {
                            $pages[] = $i;
                        }
                        $pages[] = '...';
                        $pages[] = $lastPage;
                    } elseif ($currentPage >= $lastPage - 2) {
                        // В конце: 1, ..., last-4, last-3, last-2, last-1, last
                        $pages[] = '...';
                        for ($i = $lastPage - 4; $i <= $lastPage; $i++) {
                            $pages[] = $i;
                        }
                    } else {
                        // В середине: 1, ..., current-1, current, current+1, ..., last
                        $pages[] = '...';
                        $pages[] = $currentPage - 1;
                        $pages[] = $currentPage;
                        $pages[] = $currentPage + 1;
                        $pages[] = '...';
                        $pages[] = $lastPage;
                    }
                }
            @endphp

            @foreach ($pages as $page)
                @if ($page === '...')
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @elseif ($page == $currentPage)
                    <li class="page-item active" aria-current="page">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
            </li>
            @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link"><i class="bi bi-chevron-right"></i></span>
            </li>
            @endif
        </ul>
        
        <div class="text-center">
            <small class="text-muted">
                Показано {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} из {{ $paginator->total() }}
            </small>
        </div>
    </div>

    <div class="d-none d-lg-flex flex-lg-fill align-items-lg-center justify-content-lg-between">
        <div>
            <p class="small text-muted mb-0">
                Показано {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} из {{ $paginator->total() }}
            </p>
        </div>

        <div>
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                        aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left"></i></a>
                </li>
                @endif

                {{-- Smart Pagination for Desktop --}}
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $pages = [];
                    
                    if ($lastPage <= 10) {
                        // Если страниц мало, показываем все
                        for ($i = 1; $i <= $lastPage; $i++) {
                            $pages[] = $i;
                        }
                    } else {
                        // Всегда показываем первую и последнюю
                        $pages[] = 1;
                        
                        if ($currentPage <= 5) {
                            // В начале: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, ..., last
                            for ($i = 2; $i <= 10; $i++) {
                                $pages[] = $i;
                            }
                            $pages[] = '...';
                            $pages[] = $lastPage;
                        } elseif ($currentPage >= $lastPage - 4) {
                            // В конце: 1, ..., last-9, last-8, last-7, last-6, last-5, last-4, last-3, last-2, last-1, last
                            $pages[] = '...';
                            for ($i = $lastPage - 9; $i <= $lastPage; $i++) {
                                $pages[] = $i;
                            }
                        } else {
                            // В середине: 1, ..., current-4, current-3, current-2, current-1, current, current+1, current+2, current+3, current+4, ..., last
                            $pages[] = '...';
                            for ($i = $currentPage - 4; $i <= $currentPage + 4; $i++) {
                                $pages[] = $i;
                            }
                            $pages[] = '...';
                            $pages[] = $lastPage;
                        }
                    }
                @endphp

                @foreach ($pages as $page)
                    @if ($page === '...')
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @elseif ($page == $currentPage)
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
                        aria-label="@lang('pagination.next')"><i class="bi bi-chevron-right"></i></a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@endif