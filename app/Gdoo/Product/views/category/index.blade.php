<div class="gdoo-list-page" id="{{$header['master_table']}}-page">
    <div class="gdoo-list panel">
        <div class="gdoo-list-header">
            <gdoo-grid-header :header="header" :grid="grid" :action="action" />
        </div>
        <div class='gdoo-list-grid'>
            <div id="{{$header['master_table']}}-grid" class="ag-theme-balham"></div>
        </div>
    </div>
</div>

<script>
Vue.createApp({
    components: {
        gdooGridHeader,
    },
    setup(props, ctx) {
        var table = '{{$header["master_table"]}}';

        var config = new gdoo.grid(table);

        var grid = config.grid;
        grid.autoColumnsToFit = true;
        grid.remoteDataUrl = '{{url()}}';

        grid.autoGroupColumnDef = {
            headerName: '名称',
            width: 250,
            cellRendererParams: {
                checkbox: true,
                suppressCount: false,
            }
        };
        grid.treeData = true;
        grid.groupDefaultExpanded = -1;
        grid.getDataPath = function(data) {
            return data.tree_path;
        };

        var action = config.action;
        // 双击行执行的方法
        action.rowDoubleClick = action.edit;

        var setup = config.setup;

        Vue.onMounted(function() {
            var gridDiv = config.div(101);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-page");
</script>