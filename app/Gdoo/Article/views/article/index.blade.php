<div class="vue-list-page" id="{{$header['master_table']}}-controller">
    <div class="panel no-border">
        <div class="panel-header">
            @include('headers2')
        </div>
        <div class='list-jqgrid'>
            <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
        </div>
    </div>
</div>

<script>
Vue.createApp({
    setup(props, ctx) {
        var table = '{{$header["master_table"]}}';

        var config = new gdoo.grid(table);

        var grid = config.grid;
        grid.remoteDataUrl = '{{url()}}';
        grid.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            if (params.data == undefined) {
                return;
            }
            if (params.data.master_id > 0) {
                action.show(params.data);
            }
        };

        var action = config.action;
        action.dialogType = 'layer';

        var setup = config.setup;

        Vue.onMounted(function() {
            var gridDiv = document.querySelector("#" + table + "-grid");
            gridDiv.style.height = getPanelHeight(136);
            new agGrid.Grid(gridDiv, grid);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-controller");
</script>