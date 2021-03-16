<div class="gdoo-list-page" id="{{$header['master_table']}}-page">
    <div class="gdoo-list panel">
        <div class="gdoo-list-header">
            <gdoo-grid-header :header="header" :grid="grid" :action="action" />
        </div>
        <div class="gdoo-list-grid">
            <div class="col-xs-4 col-sm-2">
                <div id="{{$header['master_table']}}-tree" class="tree-grid ag-theme-balham"></div>
            </div>
            <div class="col-xs-8 col-sm-10">
                <div id="{{$header['master_table']}}-grid" class="list-grid ag-theme-balham"></div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<style>
.tree-grid.ag-theme-balham {
    border-right: 1px solid #BDC3C7;
}
.tree-grid.ag-theme-balham .ag-ltr .ag-cell {
    border-width: 0 0 0 0;
    border-right-color: #d9dcde;
}
.tree-grid.ag-theme-balham .ag-header-cell, 
.tree-grid.ag-theme-balham .ag-header-group-cell {
    border-right-width: 0;
}

.ag-theme-balham {
    border-left: 1px solid #BDC3C7;
    border-right: 1px solid #BDC3C7;
}
.tree-box {
    border: 1px solid #dee5e7;
    overflow-y: auto;
}
.tree-box .ul {
    margin-bottom: 0;
}

.col-xs-4 {
    padding-right: 0;
    padding-left: 5px;
}
.col-xs-8 {
    padding-left: 5px;
    padding-right: 5px;
}

@media screen and (min-width: 768px) {
    .col-sm-2 {
        padding-right: 0;
        padding-left: 5px;
    }
    .col-sm-10 {
        padding-left: 5px;
        padding-right: 5px;
    }
}
</style>

<script>
Vue.createApp({
    components: {
        gdooGridHeader,
    },
    setup(props, ctx) {
        var table = '{{$header["master_table"]}}';

        var tree = new agGridOptions();
        tree.columnDefs = [
            {cellClass:'text-left', field: 'name', headerName: '枚举类别'},
        ];
        tree.onRowClicked = function (params) {
            var query = {};
            query['parent_id'] = params.data.id;
            query['page'] = 1;
            config.grid.remoteData(query);
        };
        tree.remoteDataUrl = "{{url('category')}}";

        var config = new gdoo.grid(table);

        var grid = config.grid;
        grid.autoColumnsToFit = true;
        grid.remoteDataUrl = '{{url()}}';

        var action = config.action;
        // 双击行执行的方法
        action.rowDoubleClick = action.edit;

        var setup = config.setup;

        Vue.onMounted(function() {
            var height = 93;
            var treeDiv = document.querySelector('#' + table + '-tree');
            treeDiv.style.height = config.getPanelHeight(height);
            new agGrid.Grid(treeDiv, tree);
            tree.remoteData();

            var gridDiv = config.div(height);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-page");
</script>