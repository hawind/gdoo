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
        action.rowDoubleClick = function (data) {
            if (data.master_id > 0) {
                var index = data.master_sn.indexOf('QTFY');
                if (index >= 0) {
                    data.src_type_id = 46;
                }
                if (data.src_type_id == 46) {
                    top.addTab('customerCost/cost/show?id=' + data.master_id, 'customerCost_cost_show', '其他费用');
                }
                if (data.src_type_id == 55) {
                    top.addTab('promotion/review/show?id=' + data.src_id, 'promotion_review_show', '促销核销');
                }
                if (data.src_type_id == 57) {
                    top.addTab('approach/review/show?id=' + data.src_id, 'approach_review_show', '进店核销');
                }
                if (data.src_type_id == 87) {
                    top.addTab('customerCost/compen/show?id=' + data.master_id, 'customerCost_compen_show', '合同补损');
                }
                if (data.src_type_id == 88) {
                    top.addTab('customerCost/rebate/show?id=' + data.master_id, 'customerCost_rebate_show', '合同返利');
                }
                if (data.src_type_id == 86) {
                    top.addTab('customerCost/adjust/show?id=' + data.master_id, 'customerCost_adjust_show', '费用调整');
                }
            }
        };

        // 关闭费用
        action.close = function(data) {
            var me = this;
            var grid = config.grid;
            var selections = grid.api.getSelectedRows();
            if (selections.length != 1) {
                toastrError('只能选择一行记录');
                return;
            }
            formDialog({
                title: '关闭费用',
                url: app.url('customerCost/cost/close', {id: selections[0].id}),
                storeUrl: app.url('customerCost/cost/close'),
                id: 'customer_cost_data_form-close',
                dialogClass:'modal-sm',
                success: function(res) {
                    toastrSuccess(res.data);
                    grid.remoteData();
                    $(this).dialog("close");
                },
                error: function(res) {
                    toastrError(res.data);
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