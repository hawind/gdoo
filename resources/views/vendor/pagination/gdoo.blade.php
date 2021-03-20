<?php
$query = Request::except('page');
$limit = $query['limit'] > 0 ? $query['limit'] : 25;
$rows = [10, 25, 50, 100, 500, 1000, 5000, 10000];
$page[] = '<li class="input-group-btn dropup"><a type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="page-size">'.$limit.'</span> <span class="caret"></span></a><ul class="dropdown-menu" role="menu">';
foreach ($rows as $row) {
    $active = $limit == $row ? ' class="active"' : '';
    $query['limit'] = $row;
    $page[] = '<li'.$active.'><a href="'.url('', $query).'">'.$row.'</a></li>';
}
$page[] = '</ul></li>';
?>

<ul class="pagination pagination-sm m-n">

    {{join("\n", $page)}}

    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <li class="disabled"><span>&laquo;</span></li>
    @else
        <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
    @endif

    {{-- Pagination Elements --}}
    @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
            <li class="disabled"><span>{{ $element }}</span></li>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="active"><span>{{ $page }}</span></li>
                @else
                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
    @else
        <li class="disabled"><span>&raquo;</span></li>
    @endif

    <li><span>共 {{$paginator->total()}} 条记录</span></li>

</ul>

