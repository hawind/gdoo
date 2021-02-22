<div class="panel">

    @include('tabs', ['tabKey' => 'mp'])
    <!--
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
    -->
    <div class="wrapper">
        <form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">
            <div class="pull-right">
                @if(isset($access['showdelete']))
                <a class="btn btn-sm btn-danger" href="javascript:optionDelete('#myform','{{url('showdelete')}}');">
                    <i class="icon icon-remove"></i> 删除</a>
                @endif
            </div>
            @include('searchForm')
        </form>
        <script type="text/javascript">
        $(function () {
            $('#search-form').searchForm({
                data: {{ json_encode($search['forms'])}},
                init: function (e) {
                    var self = this;
                }
            });
        });
        </script>
    </div>

    <div class="table-responsive">
        <table class="table table-hover m-b-none table-hover">
            <tr>
                <th align="center">
                    <input type="checkbox" class="select-all">
                </th>
                <th>场景ID</th>
                <th>类型</th>
                <th>到期时间</th>
                <th>二维码链接</th>
                <th>ID</th>
                <th></th>
            </tr>
            @foreach($rows as $vo)
            <tr>
                <td align="center">
                    <input type="checkbox" class="select-row" value="{{$vo['id']}}" name="id[]">
                </td>
                <td class="text-center">{{$vo['scene_id']}}</td>
                <td class="text-center">
                    @if($vo['qr_type'] == 0)
                        临时
                    @else 
                        永久
                    @endif
                </td>
                <td class="text-center">
                    @if($vo['qr_type'] == 0)
                        @datetime($vo['created_at'] + $vo['expire'])
                    @else
                        长期有效
                    @endif
                </td>
                <td class="text-center">
                    {{$vo['url']}}
                </td>
                <td class="text-center">
                    {{$vo['id']}}
                </td>
                <td class="text-center">
                    <a target="_blank" href="{{$vo['qrcode_url']}}" class="option">查看</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <footer class="panel-footer">
        <div class="row">
            <div class="col-sm-1 hidden-xs">
            </div>
            <div class="col-sm-11 text-right text-center-xs">
                {{$rows->render()}}
            </div>
        </div>
    </footer>
</div>