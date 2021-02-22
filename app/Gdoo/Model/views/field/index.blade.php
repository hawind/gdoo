<div class="panel">

    <div class="wrapper-sm">
        <a class="btn btn-info btn-sm" data-toggle="field-edit" data-model_id="{{$model_id}}" href="javascript:;"><i class="icon icon-plus"></i> 新建</a>
    </div>

    <div class="wrapper-sm b-t">
    <form method="post" id="myform" name="myform">

        <table class="table table-bordered m-b-none table-hover b-t" id="table-sortable" url="{{url()}}">
        <thead>
            <tr>
                <th align="left">字段别名</th>
                <th align="left">字段名</th>
                <th align="center">数据绑定</th>
                <th align="center">数据关联</th>
                <th align="center">字段类型</th>
                <th align="center">字段索引</th>
                <th align="center">表单类型</th>
                <th align="center">ID</th>
                <th align="center"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($master->fields as $row)
            <tr id="{{$row['id']}}">
                <td align="left" class="move">{{$row['name']}}</td>
                <td align="left">{{$row['field']}}</td>
                <td align="center">
                    @if($row['data_type'])
                        {{$sets[$row['data_type']]['name']}}({{$row['data_field']}})
                    @endif
                </td>
                <td align="center">
                    {{$row['data_link']}}
                </td>
                <td align="center">@if($row['type']) {{$row['type']}}({{$row['length']}}) @endif</td>
                <td align="center">{{$row['index']}}</td>
                <td align="center">{{$row['form_type']}}</td>
                <td align="center">{{$row['id']}}</td>
                <td align="center">
                    <a class="option" data-toggle="field-edit" data-id="{{$row['id']}}" data-parent_id="{{$model_id}}" data-model_id="{{$row['model_id']}}" href="javascript:;">编辑</a>
                    @if($row['system'] == 0)
                        <a class="option" href="javascript:app.confirm('{{url('delete',['id'=>$row['id'],'model_id'=>$model_id])}}','确定要删除吗？');">删除</a>
                    @endif
                </td>
            </tr>
            @endforeach

            @foreach($sublist as $rows)
            @foreach($rows->fields as $row)
            <tr id="{{$row['id']}}">
                <td align="left" class="move"><span class="label label-primary">{{$rows->name}}</span> {{$row['name']}}</td>
                <td align="left">{{$row['field']}}</td>
                <td align="center">
                    @if($row['data_type'])
                        {{$sets[$row['data_type']]['name']}}({{$row['data_field']}})
                    @endif
                </td>
                <td align="center">{{$row['data_link']}}</td>
                <td align="center">@if($row['type']) {{$row['type']}}({{$row['length']}}) @endif</td>
                <td align="center">{{$row['index']}}</td>
                <td align="center">{{$row['form_type']}}</td>
                <td align="center">{{$row['id']}}</td>
                <td align="center">
                    <a class="option" data-toggle="field-edit" data-id="{{$row['id']}}" data-parent_id="{{$model_id}}" data-model_id="{{$row['model_id']}}">编辑</a>
                    @if($row['system'] == 0)
                        <a class="option" href="javascript:app.confirm('{{url('delete',['model_id'=>$model_id,'id'=>$row['id']])}}','确定要删除吗？');">删除</a>
                    @endif
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
        </table>

    </form>
    </div>
</div>

<script>
$(function() {
    $('[data-toggle="field-edit"]').on('click', function() {
        var data = $(this).data();
        formDialog({
            title: '字段管理',
            url: app.url('model/field/create', data),
            storeUrl: app.url('model/field/create'),
            id: 'flow-field',
            dialogClass:'modal-md',
            success: function(res) {
                toastrSuccess(res.data);
                $(this).dialog("close");
                location.reload();
            },
            error: function(res) {
                toastrError(res.data);
            }
        });
    });
});
</script>