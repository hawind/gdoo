<form method="post" id="widget" name="widget">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}">
            </td>
        </tr>

        <tr>
            <td align="right">URL</td>
            <td align="left">
                <input class="form-control input-sm" type="text" readonly="readonly" name="url" value="{{$row['url']}}">
            </td>
        </tr>

        <tr>
            <td align="right">MoreURL</td>
            <td align="left">
                <input class="form-control input-sm" type="text" readonly="readonly" name="more_url" value="{{$row['more_url']}}">
            </td>
        </tr>

        <tr>
            <td align="right">权限</td>
            <td align="left">
                {{App\Support\Dialog::search($row, 'id=receive_id&name=receive_name&multi=1')}}
            </td>
        </tr>

        <tr>
            <td align="right">位置</td>
            <td align="left">
                <select name="grid" id="grid" class="form-control input-sm">
                    <option value="8" @if($row['grid'] == 8) selected="selected" @endif>左</option>
                    <option value="4" @if($row['grid'] == 4) selected="selected" @endif>右</option>
                </select>
            </td>
        </tr>
        
        <tr>
            <td align="right">图标</td>
            <td align="left">
                <div class="input-group">
                    <span class="input-group-addon"></span>
                    <input data-placement="bottomLeft" type="text" autocomplete="off" class="form-control icp icp-auto input-sm" id="icon" name="icon" value="{{$row['icon']}}">
                </div>
            </td>
        </tr>

        <tr>
            <td align="right">颜色</td>
            <td align="left">
                <div class="colorpicker-controller" title="选择颜色">
                    <div id="color-picker" class="colorpicker" style="background-color:{{$row['color']}};"></div>
                </div>
                <input type="hidden" id="color" name="color" value="{{$row['color']}}">
            </td>
        </tr>

        <tr>
            <td align="right">类型</td>
            <td align="left">
                <select class="form-control input-sm" disabled="disabled" name="type" id="type">
                    <option value="1" @if($row['type'] == 1) selected @endif>部件</option>
                    <option value="2" @if($row['type'] == 2) selected @endif>信息</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">状态</td>
            <td align="left">
                <select class="form-control input-sm" name="status" id="status">
                    <option value="1" @if($row['status'] == 1) selected @endif>正常</option>
                    <option value="0" @if($row['status'] == 0) selected @endif>禁用</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">全局</td>
            <td align="left">
                <select class="form-control input-sm" name="default" id="default">
                    <option value="1" @if($row['default'] == 1) selected @endif>是</option>
                    <option value="0" @if($row['default'] == 0) selected @endif>否</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">排序</td>
            <td align="left">
                <input class="form-control input-sm" type="text" name="sort" value="{{$row['sort']}}">
            </td>
        </tr>

    </table>
    <input type="hidden" name="id" value="{{$row['id']}}">
</form>

<script src="{{$asset_url}}/vendor/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>
<link href="{{$asset_url}}/vendor/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css" rel="stylesheet">

<script>
$(function() {
    $('#icon').iconpicker();
    $("#color-picker").colorpicker({
        fillcolor: true,
        target: "#color",
        change: function(obj, color) {
            $(obj).css({'background-color': color});
            $('#color').val(color);
        },
        reset: function(obj, color) {
            $(obj).css({'background-color': color});
            $('#color').val(color);
        }
    });
});
</script>