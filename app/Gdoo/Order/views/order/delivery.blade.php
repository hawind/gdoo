{{$header["js"]}}

<div class="panel no-border" id="{{$header['master_table']}}-controller">
    @include('headers')
    <div class='list-jqgrid'>
        <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>

<script>
    (function ($) {
        var table = '{{$header["master_table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        action.dialogType = 'layer';

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

        // 自定义搜索方法
        search.searchInit = function (e) {
            var self = this;
        }
        
        var options = new agGridOptions();
        var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
        gridDiv.style.height = getPanelHeight(48);

        options.remoteDataUrl = '{{url()}}';
        options.autoColumnsToFit = false;
        options.remoteParams = search.advanced.query;
        options.columnDefs = config.cols;
        options.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            if (params.data == undefined) {
                return;
            }
            if (params.data.master_id > 0) {
                action.show(params.data, 'order_order_show_delivery', '销售订单(发货)');
            }
        };

        new agGrid.Grid(gridDiv, options);

        // 读取数据
        options.remoteData({page: 1});

        // 绑定自定义事件
        var $gridDiv = $(gridDiv);
        $gridDiv.on('click', '[data-toggle="event"]', function () {
            var data = $(this).data();
            if (data.master_id > 0) {
                action[data.action](data);
            }
        });
        config.grid = options;
    })(jQuery);

</script>
@include('footers')