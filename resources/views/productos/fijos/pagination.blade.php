@if($productosFijos->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Mostrando {{ $productosFijos->firstItem() }} a {{ $productosFijos->lastItem() }} de {{ $productosFijos->total() }} registros
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            @if($productosFijos->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-left"></i></span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link ajax-page" href="{{ $productosFijos->previousPageUrl() }}" data-page="{{ $productosFijos->currentPage() - 1 }}"><i class="fas fa-angle-left"></i></a>
            </li>
            @endif

            @foreach($productosFijos->getUrlRange(1, $productosFijos->lastPage()) as $page => $url)
            @if($page == $productosFijos->currentPage())
            <li class="page-item active">
                <span class="page-link">{{ $page }}</span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link ajax-page" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
            </li>
            @endif
            @endforeach

            @if($productosFijos->hasMorePages())
            <li class="page-item">
                <a class="page-link ajax-page" href="{{ $productosFijos->nextPageUrl() }}" data-page="{{ $productosFijos->currentPage() + 1 }}"><i class="fas fa-angle-right"></i></a>
            </li>
            @else
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-angle-right"></i></span>
            </li>
            @endif
        </ul>
    </nav>
</div>
@endif
