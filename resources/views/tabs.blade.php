<?php
    $_params = $search['params'];
    unset($_params['referer']);
    if($_params['advanced'] == 0) {
        unset($_params['advanced']);
    }
?>

<div class="panel-heading tabs-box">
    <ul class="nav nav-tabs">
        @foreach($tabs['items'] as $tab)
        <li class="@if($search['query'][$tabs['name']] == (string)$tab['id']) active @endif">
            <?php $_params[$tabs['name']] = $tab['id']; ?>
            <a class="text-sm" href="{{url('', $_params)}}">{{$tab['name']}}</a>
        </li>
        @endforeach
    </ul>
</div>