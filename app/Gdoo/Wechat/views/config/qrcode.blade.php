<script src="{{$asset_url}}/vendor/zclip/jquery.zclip.min.js" type="text/javascript"></script>
<div class="panel">

    @include('tabs', ['tabKey' => 'mp'])

    <div class="panel-heading tabs-box">
        <ul class="nav nav-tabs">
            <li class="@if($type == 'list') active @endif">
                <a class="text-sm" href="{{url('wechat/mp/qrcode',['type'=>'list'])}}">二维码列表</a>
            </li>
            <li class="@if($type == 'statistics') active @endif">
                <a class="text-sm" href="{{url('wechat/mp/qrcode',['type'=>'statistics'])}}">二维码扫描统计</a>
            </li>
        </ul>
    </div>

    <div class="wrapper-sm">
        <a href="{{url('qrcodeadd')}}" id="addkw" class="btn btn-info btn-sm">增加二维码</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover m-b-none b-t table-hover">
            @if($type == 'list')
                <tr>
                    <th>二维码</th>
                    <th>场景名称</th>
                    <th>对应关键字</th>
                    <th>类型</th>
                    <th>到期时间</th>
                    <th>链接</th>
                    <th>操作</th>
                </tr>
                @foreach($data as $vo)
                <tr>
                    <td class="text-center">
                        <div style="padding: 1px; border: #e6e6e6 solid 1px; width:50px; float: left; ">
                            <img class="form_logo" src="{{$vo['qrcode_url']}}" width="50" height="50">
                        </div>
                    </td>
                    <td class="text-center">{{$vo['scene_name']}}</td>
                    <td class="text-center">{{$vo['keyword']}}</td>
                    <td class="text-center">@if($vo['qr_type'] == 0) 临时 @else 永久 @endif
                    </td>
                    <td class="text-center">@if($vo['qr_type'] == 0) @datetime($vo['create_time']) @else 长期有效 @endif
                    </td>
                    <td class="text-center">
                        {{$vo['url']}}
                    </td>
                    <td class="text-center">
                        <a class="option" href="javascript:;" onclick="delQrcode('{{$vo['id']}}')">删除</a>
                        <a target="_blank" href="{{$vo['qrcode_url']}}" class="option">查看</a>
                    </td>
                </tr>
                @endforeach
            @endif
            
            @if($type == 'statistics')
            <tr>
                <th>二维码</th>
                <th>场景名称</th>
                <th>触发关键字</th>
                <th>类型</th>
                <th>被扫总数</th>
                <th>粉丝增加</th>
                <th>操作</th>
            </tr>
            @foreach($data as $vo)
            <tr>
                <td>
                    <div style="padding: 1px; border: #e6e6e6 solid 1px; width:50px; float: left; ">
                        <img class="form_logo" src="{{$vo['qrcode_url']}}" width="50" height="50">
                    </div>
                </td>
                <td>{{$vo['scene_name']}}</td>
                <td>{{$vo['keyword']}}</td>
                <td>@if($vo['qr_type'] == 0) 临时 @else 永久 @endif
                </td>
                <td>{{$vo['scan_count']}}</td>
                <td>{{$vo['gz_count']}}</td>
                <td>
                    <a href="{{url('qrcode',['scene_id'=>$vo['scene_id'],'type'=>'friend'])}}" class="rha-bt-a">查看增加粉丝</a>
                </td>
            </tr>
            @endforeach

            @endif
            
            @if($type == 'friend')
            <tr>
                <th>场景 ID</th>
                <th>呢称</th>
                <th>头像</th>
                <th>扫码次数</th>
                <th>扫码时间</th>
            </tr>
                @foreach($data as $vo)
                <tr>
                    <td>{$vo['scene_id']}</td>
                    <td>{$vo['nickname']}</td>
                    <td>
                        <img height="38" width="38" style="border-radius: 3px;" src="{{$vo['headimgurl']}}">
                    </td>
                    <td>{$vo['scan_count']}</td>
                    <td>{{date('Y-m-d H:i', $vo['create_time'])}}</td>
                </tr>
                @endforeach
            @endif
        </table>
    </div>

    <footer class="panel-footer">
        <div class="row">
            <div class="col-sm-1 hidden-xs">
            </div>
            <div class="col-sm-11 text-right text-center-xs">
                {{$data->render()}}
            </div>
        </div>
    </footer>
</div>

<script>
    function delQrcode(id) {
        layui.use('layer', function () {
            var layer = layui.layer;
            layer.confirm('你确定需要删除吗？', {
                btn: ['是', '不'] //按钮
            }, function () {
                $.post("{{url('wechat/mp/delQrcode')}}", { 'id': id }, function (res) {
                    if (res.status == 1) {
                        layer.alert(res.msg, function (index) {
                            window.location.reload();
                            layer.close(index);
                        })

                    } else {
                        layer.alert(res.msg)
                    }
                })
            }, function () {

            });
        });
    }
</script>