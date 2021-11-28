<form method="post" id="setting-info" name="setting_info">
    <table class="table table-form m-b-none">
        <tr>
            <td align="right">名称</td>
            <td align="left">
                <input type="text" autocomplete="off" class="form-control input-sm" readonly="readonly" value="{{$info['info_name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">显示名称</td>
            <td align="left">
                <input type="text" autocomplete="off" class="form-control input-sm" id="name" name="name" value="{{$info['name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">权限</td>
            <td>
                <select class="form-control input-sm" name="permission" id="permission">
                    @foreach($permissions as $key => $permission)
                    <option value="{{$key}}" @if($info['params']['permission'] == $key) selected="selected" @endif>{{$permission}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">日期</td>
            <td>
            <select class="form-control input-sm" name="date" id="date">
                    @foreach($dates as $key => $date)
                    <option value="{{$key}}" @if($info['params']['date'] == $key) selected="selected" @endif>{{$date}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">图标</td>
            <td align="left">
                <div class="input-group">
                    <span class="input-group-addon" id="icon-picker"></span>
                    <input data-placement="bottomLeft" type="text" autocomplete="off" value="{{$info['icon']}}" class="form-control icp icp-auto input-sm" id="icon" name="icon">
                </div>
            </td>
        </tr>
        <tr>
            <td align="right">图标颜色</td>
            <td align="left">
                <div class="colorpicker-controller" title="选择颜色">
                    <div id="color-picker" class="colorpicker" style="background-color:{{$info['color']}}"></div>
                </div>
                <input type="hidden" id="color" name="color" value="{{$info['color']}}">
            </td>
        </tr>
    </table>
</form>
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