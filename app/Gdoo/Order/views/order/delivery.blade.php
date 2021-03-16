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

        action.logisticsPlan = function() {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length > 0) {
                var ids = [];
                for (let i = 0; i < rows.length; i++) {
                    ids.push(rows[i].master_id);
                }
                formDialog({
                    title: '修改物流信息',
                    url: app.url('order/order/logisticsPlan', {ids: ids.join(',')}),
                    storeUrl: app.url('order/order/logisticsPlan'),
                    id: 'logistics_plan',
                    dialogClass:'modal-md',
                    success: function(res) {
                        grid.remoteData();
                        toastrSuccess(res.data);
                        $(this).dialog("close");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                toastrError('至少选择一条数据。');
            }
        }

        action.deliveryEdit = function() {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                var row = rows[0];
                formDialog({
                    title: '修改运费支付方式',
                    url: app.url('order/order/deliveryEdit', {id: row.master_id}),
                    storeUrl: app.url('order/order/deliveryEdit'),
                    id: 'delivery_edit',
                    dialogClass:'modal-md',
                    success: function(res) {
                        toastrSuccess(res.data);
                        $(this).dialog("close");
                        window.open(app.url('order/order/print', {id: row.master_id, template_id: 121}), "_blank");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                toastrError('只能选择一条数据。');
            }
        }

        action.deliveryPlan = function() {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                var data = rows[0];
                var url = app.url('order/order/deliveryPlan', {id: data.master_id, date: data.master_plan_delivery_dt});
                formDialog({
                    title: '修改计划发货日期',
                    url: url,
                    storeUrl: url,
                    id: me.table,
                    dialogClass: 'modal-lg',
                    onSubmit: function() {
                        var me = this;
                        var form = $('#plan_delivery_date').serialize();
                        var loading = layer.msg('数据提交中...', {
                            icon: 16, shade: 0.1, time: 1000 * 120
                        });
                        $.post(app.url('order/order/deliveryPlanDate'), form, function(res) {
                            if (res.status) {
                                grid.remoteData();
                                $(me).dialog('close');
                                toastrSuccess(res.data);
                            } else {
                                toastrError(res.data);
                            }
                        }).complete(function() {
                            layer.close(loading);
                        });
                    }
                });
            } else {
                toastrError('只能修改一条数据。');
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