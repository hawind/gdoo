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
        
        var action = config.action;
        // 详情页打开方式
        action.dialogType = 'layer';
        // 双击行执行的方法
        action.rowDoubleClick = action.show;

        action.fee_detail = function(data) {
            viewDialog({
                title: '兑现明细',
                dialogClass: 'modal-md',
                url: app.url('approach/review/feeDetail', {id: data.master_id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
        }

        action.product = function(data) {
            viewDialog({
                title: '产品明细',
                dialogClass: 'modal-md',
                url: app.url('approach/approach/product', {id: data.master_id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
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