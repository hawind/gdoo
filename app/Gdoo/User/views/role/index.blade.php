{{$header["js"]}}
<div class="panel no-border" id="{{$header['table']}}-controller">
    @include('headers')
    <div class="gdoo-list-grid">
        <div id="{{$header['table']}}-grid" class="ag-theme-balham"></div>
    </div>
</div>
<script>
    (function ($) {
        var table = '{{$header["table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        action.config = function(data) {
            top.addTab('user/role/config?role_id=' + data.master_id, 'role_config', '权限配置');
        }

        var grid = new agGridOptions();
        var gridDiv = document.querySelector("#{{$header['table']}}-grid");
        gridDiv.style.height = getPanelHeight(48);
        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.advanced.query;
        grid.columnDefs = config.cols;

        new agGrid.Grid(gridDiv, grid);

        grid.remoteData({page: 1});

        // 绑定自定义事件
        var $gridDiv = $(gridDiv);
        $gridDiv.on('click', '[data-toggle="event"]', function () {
            var data = $(this).data();
            if (data.master_id > 0) {
                action[data.action](data);
            }
        });

        config.grid = grid;

    })(jQuery);
</script>
@include('footers')