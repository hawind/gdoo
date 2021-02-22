<div class="panel-heading tabs-box">
    <ul class="nav nav-tabs">
        @foreach($tabs['items'] as $i => $tab)
        <li id="tab-{{$tab['id']}}" @if($i == 0)class="active"@endif>
            <a class="text-sm" href="javascript:tabs({{$tab['id']}});">{{$tab['name']}}</a>
        </li>
        @endforeach
    </ul>
</div>