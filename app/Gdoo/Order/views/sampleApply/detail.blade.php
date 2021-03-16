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
        grid.remoteDataUrl = '{{url()}}';
        grid.autoColumnsToFit = true;

        var action = config.action;
        // 详情页打开方式
        action.dialogType = 'layer';
        // 双击行执行的方法
        action.rowDoubleClick = action.show;

        // 关闭使用
        action.close = function(data) {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            var ids = [];
            if (rows.length > 0) {
                rows.forEach(function(row) {
                    ids.push(row.id);
                });
                $.post(app.url('order/sampleApply/close'), {ids: ids}, function(res) {
                    toastrSuccess(res.data);
                    grid.remoteData();
                });
            } else {
                toastrError('最少选择一行记录。');
                return;
            }
        }

        var setup = config.setup;

        Vue.onMounted(function() {
            var gridDiv = config.div(136);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-page");
</script>