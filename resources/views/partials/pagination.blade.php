@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center w-100">
        <!-- Results Summary -->
        <div class="text-gray-400 small">
            عرض <span class="fw-bold text-white">{{ $paginator->firstItem() }}</span> إلى <span
                class="fw-bold text-white">{{ $paginator->lastItem() }}</span> من أصل <span
                class="fw-bold text-white">{{ $paginator->total() }}</span> سجل
        </div>

        <!-- Pagination Links -->
        <ul class="pagination mb-0 gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link glass-page-link disabled"><i class="bi bi-chevron-right"></i></span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link glass-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i
                            class="bi bi-chevron-right"></i></a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link glass-page-link disabled">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link glass-page-link active">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link glass-page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link glass-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i
                            class="bi bi-chevron-left"></i></a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link glass-page-link disabled"><i class="bi bi-chevron-left"></i></span>
                </li>
            @endif
        </ul>
    </nav>

    <style>
        .glass-page-link {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ccc;
            border-radius: 8px !important;
            padding: 0.5rem 0.8rem;
            margin: 0 2px;
            transition: all 0.3s;
        }

        .glass-page-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
            text-decoration: none;
        }

        .glass-page-link.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
        }

        .glass-page-link.disabled {
            background: rgba(255, 255, 255, 0.02);
            color: #666;
            border-color: transparent;
            cursor: not-allowed;
        }
    </style>
@endif