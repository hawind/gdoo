<form method="post" id="menu" name="menu">
    
    <table class="table table-form m-b-none">

        <tr>
            <td align="right" width="100">上级</td>
            <td align="left">
                <select class="form-control input-sm" name="parent_id" id="parent_id">
                    <option value=""> - </option>
                    @foreach($parents as $parent)
                    <option value="{{$parent['id']}}" @if($row['parent_id'] == $parent['id']) selected @endif>{{$parent['layer_space']}}{{$parent['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="name" name="name" value="{{$row['name']}}">
            </td>
        </tr>

        <tr>
            <td align="right">URL</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="url" name="url" value="{{$row['url']}}">
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
            <td align="right">验证</td>
            <td align="left">
                <select class="form-control input-sm" name="access" id="access">
                    <option value="1" @if($row['access'] == '1') selected @endif>是</option>
                    <option value="0" @if($row['access'] == '0') selected @endif>否</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">备注</td>
            <td align="left">
                <textarea class="form-control" name="description" id="description">{{$row['description']}}</textarea>
            </td>
        </tr>

        <tr>
            <td align="right">排序</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="sort" name="sort" value="{{$row['sort']}}">
            </td>
        </tr>

        <tr>
            <td align="right">状态</td>
            <td align="left">
                <select class="form-control input-sm" name="status" id="status">
                    <option value="1" @if($row['status'] == '1') selected @endif>启用</option>
                    <option value="0" @if($row['status'] == '0') selected @endif>禁用</option>
                </select>
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