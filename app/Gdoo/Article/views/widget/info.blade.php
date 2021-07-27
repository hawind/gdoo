<div class="panel panel-shadow info-skin1">
    <div class="info-l hidden-xs" style="background-color:{{$info['color']}}">
        <i class="fa fa-2x {{$info['icon']}}"></i>
    </div>
    <div class="info-c">
        <div class="info-name">{{$info['name']}}</div>
        <a href="javascript:;" data-toggle="addtab" data-url="{{$info['more_url']}}" data-id="{{str_replace(['/', '?', '='], ['_', '_', '_'], $info['more_url'])}}" data-name="{{$info['name']}}">
            <div class="text-info info-item" data-id="{{$info['id']}}" data-more_url="{{$info['more_url']}}">{{$res['count']}}</div>
        </a>
    </div>
    <div class="info-r">
        <div>比{{$dates[$info['params']['date']]}}</div>
        <div class="rate @if($res['rate'] > 100) red @endif">{{$res['rate']}}%</div>
    </div>
</div>