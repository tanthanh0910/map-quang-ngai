<div class="card-footer bg-white mt-4">
    <div class="d-flex justify-content-end">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">

                {{-- Trang trước --}}
                @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="bi bi-chevron-left me-1"></i>
                        Trang trước
                    </span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->appends(request()->query())->previousPageUrl() }}">
                        <i class="bi bi-chevron-left me-1"></i>
                        Trang trước
                    </a>
                </li>
                @endif

                {{-- Các số trang --}}
                @foreach ($paginator->appends(request()->query())->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @elseif (
                $page == 1 ||
                $page == $paginator->lastPage() ||
                ($page >= $paginator->currentPage() - 1 && $page <= $paginator->currentPage() + 1))
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @elseif ($page == $paginator->currentPage() - 2 || $page == $paginator->currentPage() + 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    @endforeach

                    {{-- Trang sau --}}
                    @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->appends(request()->query())->nextPageUrl() }}">
                            Trang sau
                            <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                    @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            Trang sau
                            <i class="bi bi-chevron-right ms-1"></i>
                        </span>
                    </li>
                    @endif

            </ul>
        </nav>
    </div>
</div>