@if($header['tabs'])
<div class="panel-heading tabs-box">
    <ul class="nav nav-tabs">
        @foreach($header['tabs']['items'] as $tab)
        <?php 
            $tab_count = substr_count($tab['value'], '.');
            $module = Request::module();
            $controller = Request::controller();
            $action = Request::action();

            if($action == 'edit') {
                $action = 'create';
            }

            if ($tab_count === 0) {
                $tab_uri = $action;
            }
            if ($tab_count === 1) {
                $tab_uri = $controller.'.'.$action;
            }
            if ($tab_count === 2) {
                $tab_uri = $module.'.'.$controller.'.'.$action;
            }
         ?>
        <li class="@if($tab_uri == $tab['value']) active @endif">
            <a class="text-sm" href="{{url($tab['url'])}}">{{$tab['name']}}</a>
        </li>
        @endforeach
    </ul>
</div>
@endif