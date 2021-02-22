<form method="post" id="quick-menu" name="quick_menu">
    <table class="table table-form m-b-none">
        <tr>
            <td align="right">菜单</td>
            <td>
                <select class="form-control input-sm" name="node_id" id="node_id">
                    <option value=""> - </option>
                    @foreach($menus as $menu)
                    <option value="{{$menu['id']}}" data-menu_id="{{$menu['id']}}" data-name="{{$menu['name']}}" data-color="{{$menu['color']}}" data-icon="{{$menu['icon']}}" data-url="{{$menu['url']}}">{{$menu['layer_space']}}{{$menu['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">显示名称</td>
            <td align="left">
                <input type="text" autocomplete="off" class="form-control input-sm" id="name" name="name">
            </td>
        </tr>
        <tr>
            <td align="right">图标</td>
            <td align="left">
                <div class="input-group">
                    <span class="input-group-addon" id="icon-picker"></span>
                    <input data-placement="bottomLeft" type="text" autocomplete="off" class="form-control icp icp-auto input-sm" id="icon" name="icon">
                </div>
            </td>
        </tr>
        <tr>
            <td align="right">颜色</td>
            <td align="left">
                <div class="colorpicker-controller" title="选择颜色">
                    <div id="color-picker" class="colorpicker"></div>
                </div>
                <input type="hidden" id="color" name="color">
            </td>
        </tr>
        <tr>
            <td align="right">URL</td>
            <td align="left">
                <input type="text" readonly="readonly" class="form-control input-sm" id="url" name="url">
            </td>
        </tr>
    </table>
</form>
<script>
    $(function() {
        $('#node_id').on('change', function() {
            var data = $(this).find("option:selected").data();
            $('#color-picker').css({'background-color': data.color});
            $('#color').val(data.color);
            $('#icon-picker').html('<i class="fa ' + data.icon + '"></i>');
            $('#icon').val(data.icon);
            $('#url').val(data.url);
            $('#name').val(data.name);
        });

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