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

        var action = config.action;
        // 详情页打开方式
        action.dialogType = 'layer';
        // 双击行执行的方法
        action.rowDoubleClick = action.show;

        action.priceEdit = function() {
            var me = this;
            var selections = grid.api.getSelectedRows();
            var ids = [];
            $.each(selections, function(i, selection) {
                ids.push(selection.master_id);
            });
            if (ids.length > 0) {
                formDialog({
                    title: '销售产品价格',
                    dialogClass: 'modal-sm',
                    id: 'price-edit-form',
                    url: app.url(me.bill_url + '/priceEdit', {ids: ids.join(',')}),
                    success: function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData();
                        $(this).dialog("close");
                    },
                    close: function() {
                        $(this).dialog("close");
                    }
                });
            } else {
                toastrError('最少选择一行记录。');
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