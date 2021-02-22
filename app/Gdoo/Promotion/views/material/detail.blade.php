<style>
    .content-body {
        margin: 0;
    }
</style>

<div id="vue-app">
    <div class="form-panel">
        <div class="form-panel-header">
            <div class="pull-right"></div>
            @if(isset($access['audit']))
            <div class="btn-group">
                <button type="button" data-toggle="audit-checkall" class="btn btn-sm btn-default"><i class="fa fa-check-square"></i> 全选</button>
                <button class="btn btn-sm btn-default" data-toggle="dropdown"><i class="fa fa-navicon"></i> 批量操作 <span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a data-toggle="audit-stores" data-value="0">未审核</a></li>
                    <li><a data-toggle="audit-stores" data-value="1">已审合格</a></li>
                    <li><a data-toggle="audit-stores" data-value="2">已审不合格</a></li>
                </ul>
            </div>
            <button type="button" data-toggle="audit-download" class="btn btn-sm btn-info">
                <i class="fa fa-cloud-download"></i> 批量下载
            </button>
            @endif

            <div class="btn-group hidden-xs">
                <a class="btn btn-sm btn-default" data-toggle="closetab" data-id="promotion_material_show"><i class="fa fa-sign-out"></i> 退出</a>
            </div>
        </div>

        <div class="form-panel-body panel-form-show">

            <form class="form-horizontal form-controller" method="post" id="promotion" name="promotion">

                <div style="background-color:#fff">
                    <div class="panel-heading text-center b-b">
                        <h3 class="m-xs m-l-none">
                            {{$promotion->sn}}
                        </h3>
                        <small>
                            客户: {{$promotion->customer->name}}
                            &nbsp;&nbsp;
                            创建时间: @datetime($promotion['created_at'])
                            &nbsp;&nbsp;
                            门店数量(未审核:{{(int)$audits[0]}} / 已审合格:{{(int)$audits[1]}} / 已审不合格:{{(int)$audits[2]}})
                        </small>
                    </div>

                    <div class="padder m-t">
                        <div class="row" id="stores">
                            @foreach($rows as $i => $row)
                            <div class="col-sm-4 col-md-3 m-b">
                                <div class="panel b-a">
                                    <div class="panel-heading">
                                        <div class="m-t-xs">
                                            <span class="pull-right">
                                                <div class="btn-group">
                                                    @if(isset($access['audit']))
                                                    <button class="btn btn-xs audit-{{$row['id']}} btn-{{$changes[$row['status']]['btn']}}" data-toggle="dropdown">{{$changes[$row['status']]['text']}} <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a data-toggle="audit-store" data-id="{{$row['id']}}" data-value="0">未审核</a></li>
                                                        <li><a data-toggle="audit-store" data-id="{{$row['id']}}" data-value="1">已审合格</a></li>
                                                        <li><a data-toggle="audit-store" data-id="{{$row['id']}}" data-value="2">已审不合格</a></li>
                                                    </ul>
                                                    @else
                                                    <span class="label label-{{$changes[$row['status']]['btn']}}">{{$changes[$row['status']]['text']}}</span>
                                                    @endif
                                                </div>
                                            </span>
                                            @if(isset($access['audit']))
                                            <label class="i-checks">
                                                <input type="checkbox" name="stores[]" value="{{$row['id']}}"><i></i>
                                                {{$row['name']}}
                                            </label>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-center wrapper">
                                        <a href="javascript:;" style="height:220px;">
                                            <img style="width:100%;height:220px;" alt="店名：{{$row['name']}}, 位置：{{$row['location']}}" lay-src="{{$row['images'][0]['url']}}">
                                            <span style="display:none;">
                                                @foreach($row['images'] as $image)
                                                <img data-original="{{$image['url']}}" alt="店名：{{$row['name']}}, 位置：{{$row['location']}}" lay-src="{{$row['images'][0]['url']}}">
                                                @endforeach
                                            </span>

                                            <div class="red m-t-xs">点击查看更多照片({{count($row['images'])}})</div>
                                        </a>
                                    </div>
                                    <ul class="list-group no-radius">
                                        <li class="list-group-item">
                                            <div class="text-left">
                                                <div>店名：{{$row['name']}}</div>
                                                <div>位置：{{$row['location']}}</div>
                                                <div>提交人：{{$row['created_by']}}</div>
                                                <div>时间：@datetime($row['created_at'])</div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<link href="{{$asset_url}}/vendor/layui/css/layui.css" rel="stylesheet">
<script src="{{$asset_url}}/vendor/layui/layui.js"></script>

<script type="text/javascript">
    var changes = JSON.parse('{{json_encode($changes)}}');
    var items = JSON.parse('{{json_encode($items)}}');

    var vue = new Vue({
        el: '#vue-app',
        data: {
            viewerData: [{
                url: '',
                name: ''
            }],
        },
        methods: {},
        mounted() {
            var galley_id = "stores";
            var galley = document.getElementById(galley_id);
            var viewer = new Viewer(galley, {
                navbar: false,
                url: "data-original",
            });
            vueMounted();
        }
    });

    function vueMounted() {
        layui.use('flow', function() {
            var flow = layui.flow;
            flow.lazyimg();
        });

        // 门店审核
        $('[data-toggle="audit-store"]').on('click', function(e) {
            var data = $(this).data();
            var obj = $('.audit-' + data.id);
            obj.removeClass('btn-default btn-success btn-danger');

            var change = changes[data.value];
            obj.html(change.text + ' <span class="caret"></span>');
            obj.addClass('btn-' + change.btn);

            $.post('{{url("audit")}}', {
                id: [data.id],
                status: data.value
            }, function(res) {
                if (res.status) {
                    $.toastr('success', res.data);
                } else {
                    $.toastr('error', res.data);
                }
            });
        });

        // 批量操作
        $('[data-toggle="audit-stores"]').on('click', function(e) {
            var data = $(this).data();
            var ids = [];
            $('#stores').find("input[type='checkbox']:checked").each(function() {
                ids.push($(this).val());
            });

            if (ids.length == 0) {
                $.toastr('error', '门店没有选择。');
                return;
            }

            $.post('{{url("audit")}}', {
                id: ids,
                status: data.value
            }, function(res) {
                if (res.status) {
                    $.each(ids, function(k, v) {
                        var change = changes[data.value];
                        var obj = $('.audit-' + v);
                        obj.html(change.text + ' <span class="caret"></span>');
                        obj.removeClass('btn-default btn-success btn-danger');
                        obj.addClass('btn-' + change.btn);
                    });
                    $.toastr('success', res.data);
                } else {
                    $.toastr('error', res.data);
                }
            });
        });

        // 批量下载
        $('[data-toggle="audit-download"]').on('click', function(e) {
            var data = $(this).data();
            var ids = [];
            $('#stores').find("input[type='checkbox']:checked").each(function() {
                ids.push($(this).val());
            });

            if (ids.length == 0) {
                $.toastr('error', '门店没有选择。');
                return;
            }

            var index = layer.msg('照片压缩中...', {
                icon: 16,
                shade: 0.1,
                time: 6000 * 10
            });
            $.post('{{url("archive")}}', {
                id: ids
            }, function(res) {
                layer.close(index);
                if (res.status) {
                    $.messager.alert('照片下载', '<div class="text-center"><a href="' + app.url('promotion/material/download', {
                        sn: '{{$promotion->sn}}'
                    }) + '">点击下载核销照片</a></div>');
                } else {
                    $.toastr('error', res.data);
                }
            });
        });

        // 全选
        $('[data-toggle="audit-checkall"]').on('click', function(e) {
            $('#stores').find("input[type='checkbox']").each(function() {
                if ($(this).prop('checked')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).prop('checked', true);
                }
            });
        });
    }
</script>