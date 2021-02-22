{{$header["js"]}}
<div class="panel no-border" id="{{$header['table']}}-controller">
    @include('headers')
    <div class="list-jqgrid">
        <div id="{{$header['table']}}-grid" class="ag-theme-balham"></div>
        <div class="ag-theme-balham" id="ag-pagination"></div>
        <div class="clearfix"></div>
    </div>
</div>
<script>
    (function ($) {
        var table = '{{$header["table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        // 自定义搜索方法
        search.searchInit = function (e) {
            var self = this;
        }

        var grid = new agGridOptions();

        var gridDiv = document.querySelector("#{{$header['table']}}-grid");
        gridDiv.style.height = getPanelHeight(11);

        config.cols[0]['hide'] = true;
        config.cols[1]['hide'] = true;
        grid.autoGroupColumnDef = {
            headerName: '名称',
            width: 250,
            cellRendererParams: {
                checkbox: true,
                suppressCount: true,
            }
        };

        grid.treeData = true;
        grid.groupDefaultExpanded = -1;
        
        grid.getDataPath = function(data) {
            return data.tree_path;
        };
        
        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.advanced.query;
        grid.columnDefs = config.cols;
        new agGrid.Grid(gridDiv, grid);

        // 读取数据
        grid.remoteData();

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