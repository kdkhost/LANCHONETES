@if ($paginator->hasPages())
<nav class="paginacao">
    {{-- Anterior --}}
    @if ($paginator->onFirstPage())
    <span><i class="bi bi-chevron-left"></i></span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
    @endif

    {{-- Páginas --}}
    @foreach ($elements as $element)
    @if (is_string($element))
    <span>{{ $element }}</span>
    @endif
    @if (is_array($element))
    @foreach ($element as $page => $url)
    @if ($page == $paginator->currentPage())
    <span class="active"><span>{{ $page }}</span></span>
    @else
    <a href="{{ $url }}">{{ $page }}</a>
    @endif
    @endforeach
    @endif
    @endforeach

    {{-- Próximo --}}
    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
    @else
    <span><i class="bi bi-chevron-right"></i></span>
    @endif
</nav>
@endif
