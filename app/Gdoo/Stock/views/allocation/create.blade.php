<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}

        @if($form['action'] == 'show')
        @else
            <a href="javascript:orderDialog();" class="btn btn-sm btn-default">
                参照客户订单
            </a>
        @endif

        @if($form['row']['id'] > 0)
        <a href="javascript:logisticsDialog();" class="btn btn-sm btn-default">
            物流信息
        </a>
        @endif

    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            {{$form['tpl']}}
        </form>
    </div>
</div>

<script>
var table = '{{$form["table"]}}';
var rowId = '{{$form["row"]["id"]}}';
var action = '{{$form["action"]}}';
var grid = null;

(function ($) {

    var in_poscode = {};

    if(action == 'show') {
    }
    else {
        $('#stock_allocation_data_tool').append('<a class="btn btn-sm btn-default" href="javascript:stockSelect();">选择库存</a>');
    }

    // 获取生产批号
    function getBatchSelect(warehouse_id, product_id, batchs, fun) {
        $.post(app.url('stock/delivery/getBatchSelectZY'), {
            warehouse_id: warehouse_id,
            product_id: product_id, 
            value: batchs
        }, function(res) {
            fun(res);
        });
    }
    
    // 选择库存
    function stockSelect() {
        if (has_out_warehouse_id() == false) {
            return;
        }
        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];
        buttons.push({
            text: '提交',
            'class': 'btn-info',
            click: function () {
                var rows = $ref_stock_select.getSelectedRows();
                stockRowsSelected(rows);
                $(this).dialog('close');
            }
        });
        var warehouse_id = get_out_warehouse_id();
        $.dialog({
            title: '选择库存',
            url: '{{url("stock/allocation/stockSelect")}}?warehouse_id=' + warehouse_id,
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }
    window.stockSelect = stockSelect;

    // 库存写入
    var stockRowsSelected = function(rows) {
        if (rows.length == 0) {
            return;
        }
        grid.api.forEachNode(function (node) {
            var data = node.data;
            if (isEmpty(data.product_id)) {
                grid.api.updateRowData({remove:[data]});
            }
        });

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            row.quantity = parseFloat(row.ky_num);
            row.out_poscode = row.poscode;
            row.out_posname = row.posname;

            row.in_poscode = in_poscode.code;
            row.in_posname = in_poscode.name;

            grid.api.memoryStore.create(row);
        }
        grid.generatePinnedBottomData();
        return true;
    };
    window.stockRowsSelected = stockRowsSelected;

    var orderDialog = function () {
        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];
        buttons.push({
            text: '提交',
            'class': 'btn-info',
            click: function () {
                var me = this;
                var orders = $ref_customer_order.api.getSelectedRows();
                if (orders.length > 0) {

                    var has = {};
                    for (var i = 0; i < orders.length; i++) {
                        has[orders[i].tax_id] = 1;
                    }
                    if (Object.keys(has).length > 1) {
                        $.messager.alert('操作警告', '参照订单开票单位必须一致。');
                        return;
                    }

                    var master = orders[0];

                    grid.api.forEachNode(function (node) {
                        var data = node.data;
                        if (isEmpty(data.product_id)) {
                            grid.api.updateRowData({remove:[data]});
                        }
                    });

                    // 合并商品
                    var products = {};
                    var batchs = {};
                    var rows = $ref_customer_order_data.api.getSelectedRows();
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        // 跳过费用
                        if (row.product_code == '99001') {
                            continue;
                        }
                        var product = parseFloat(products[row.product_id]);
                        if (isNaN(product)) {
                            products[row.product_id] = 0;
                        }

                        if (!isEmpty(row.batch_sn)) {
                            batchs[row.batch_sn] = row.batch_sn;
                        }
                        products[row.product_id] += parseFloat(row.wf_num);
                    }

                    var loading = layer.msg('数据提交中...', {
                        icon: 16, shade: 0.1, time: 1000 * 120
                    });

                    var product_ids = Object.keys(products).join(',');
                    var batch_ids = Object.keys(batchs).join(',');
                    if (batch_ids == 'null') {
                        batch_ids = '';
                    }

                    var warehouse_id = get_out_warehouse_id();
                    getBatchSelect(warehouse_id, product_ids, batch_ids, function(res) {
                        var batch_list = {};
                        for (let k = 0; k < res.data.length; k++) {
                            var row = res.data[k];
                            if (batch_list[row.product_id] == undefined) {
                                batch_list[row.product_id] = [];
                            }
                            batch_list[row.product_id].push(row);
                        }

                        for (var i = 0; i < rows.length; i++) {
                            var row = rows[i];
                            var quantity = parseFloat(row.wf_num);
                            // 折扣额使用
                            if (row.product_code == '99001') {
                                row.quantity = '';
                                grid.api.memoryStore.create(row);
                            } else {
                                // 产品库存不足
                                if (row.ky_num <= 0) {
                                    continue;
                                }
                                var ret = false;

                                var _batchs = batch_list[row.product_id];
                                if(_batchs == undefined) {
                                    continue;
                                }

                                for (var j = 0; j < _batchs.length; j++) {
                                    var batch = _batchs[j];
                                    var ky_num = parseFloat(batch.ky_num);
                                    if (ky_num <= 0) {
                                        continue;
                                    }
                                    var item = jQuery.extend({}, row);
                                    if (quantity > ky_num) {
                                        item.quantity = ky_num;
                                        quantity = quantity - ky_num;
                                    } else {
                                        ret = true;
                                        item.quantity = quantity;
                                    }
                                    // 减少批号可用量
                                    batch.ky_num = ky_num - item.quantity;

                                    item.warehouse_id = batch.warehouse_id;
                                    item.warehouse_id_name = batch.warehouse_name;
                                    item.batch_sn = batch.batch_sn;
                                    item.batch_date = batch.batch_date;

                                    item.out_poscode = batch.poscode;
                                    item.out_posname = batch.posname;

                                    row.in_poscode = in_poscode.code;
                                    row.in_posname = in_poscode.name;

                                    item.total_weight = item.quantity * item.weight;
                                    item.money = item.quantity * item.price;
                                    // 赠品
                                    if (item.type_id == 2) {
                                        item.other_money = item.money;
                                    }
                                    grid.api.memoryStore.create(item);
                                    // 单个产品写入结束
                                    if (ret == true) {
                                        break;
                                    }
                                }
                            }
                        }
                        
                        $('#stock_allocation_invoice_dt').val(master.plan_delivery_dt);
                        $('#stock_allocation_delivery_dt').val(master.plan_delivery_dt);
                        $('#stock_allocation_remark').val(master.remark);

                        layer.close(loading);
                        grid.generatePinnedBottomData();
                        $(me).dialog('close');
                    });
                    
                } else {
                    toastrError('销售订单必须选择。');
                }
            }
        });

        if (has_out_warehouse_id() == false) {
            return;
        }

        $.dialog({
            title: '客户订单',
            url: '{{url("order/order/serviceDelivery")}}?is_direct=1',
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }
    window.orderDialog = orderDialog;

    var logisticsDialog = function () {
        formDialog({
            title: '物流信息',
            url: app.url('stock/allocation/logistics', {id: rowId}),
            storeUrl: app.url('stock/allocation/logistics'),
            id: 'allocation_logistics',
            dialogClass:'modal-md',
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
    window.logisticsDialog = logisticsDialog;

    // grid初始化事件
    gdoo.event.set('grid.stock_allocation_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'product_id';
        },
        editable: {
            batch_sn(params) {
                var row = params.data;
                if (row.product_id > 0) {
                } else {
                    toastrError('请先选择产品');
                    return false;
                }
                return true;
            },
            out_poscode(params) {
                return has_out_warehouse_id();
            },
            in_poscode(params) {
                return has_in_warehouse_id();
            }
        }
    });

    // 选择生产批号
    gdoo.event.set('stock_allocation_data.batch_sn', {
        open(params) {
            params.title = '选择库存现存量';
            params.url = 'stock/delivery/getBatchSelectZY';
        },
        query(query) {
            query.warehouse_id = get_out_warehouse_id();
            var row = grid.lastEditCell.data;
            if (row.product_id > 0) {
                query.product_id = row.product_id;
            }
        },
        onSelect(row, batch) {
            var quantity = parseFloat(row.quantity);
            var ky_num = parseFloat(batch.ky_num);
            quantity = quantity - ky_num;
            if (quantity > 0) {
                row.quantity = ky_num;
            }

            row.batch_sn = batch.batch_sn;
            row.batch_date = batch.batch_date;

            row.out_poscode = batch.poscode;
            row.out_posname = batch.posname;

            row.in_poscode = in_poscode.code;
            row.in_posname = in_poscode.name;

            // 库存现存量不足写入剩余数量
            if (quantity > 0) {
                var item = jQuery.extend({}, row);
                item.quantity = quantity;

                item.batch_sn = '';
                item.batch_date = '';

                item.in_poscode = '';
                item.in_posname = '';

                item.out_poscode = '';
                item.out_posname = '';

                grid.api.memoryStore.create(item);
            }
            return true;
        }
    });

    // 选择转出货位编号
    gdoo.event.set('stock_allocation_data.out_poscode', {
        query(query) {
            var warehouse_id = get_out_warehouse_id();
            if (warehouse_id > 0) {
                query.warehouse_id = warehouse_id;
            }
        },
        onSelect(row, selectedRow) {
            row.out_poscode = selectedRow.code;
            row.out_posname = selectedRow.name;
            return true;
        }
    });

    // 选择转入货位编号
    gdoo.event.set('stock_allocation_data.in_poscode', {
        query(query) {
            var warehouse_id = get_in_warehouse_id();
            if (warehouse_id > 0) {
                query.warehouse_id = warehouse_id;
            }
        },
        onSelect(row, selectedRow) {
            row.in_poscode = selectedRow.code;
            row.in_posname = selectedRow.name;
            return true;
        }
    });

    function get_out_warehouse_id() {
        var warehouse_id = $('#stock_allocation_out_warehouse_id').val();
        return warehouse_id || 0;
    }

    function get_in_warehouse_id() {
        var warehouse_id = $('#stock_allocation_in_warehouse_id').val();
        return warehouse_id || 0;
    }

    function has_out_warehouse_id() {
        var warehouse_id = get_out_warehouse_id();
        if (warehouse_id == 0) {
            toastrError('请先选择转出仓库');
            return false;
        } else {
            return true;
        }
    }

    function has_in_warehouse_id() {
        var warehouse_id = get_in_warehouse_id();
        if (warehouse_id == 0) {
            toastrError('请先选择转入仓库');
            return false;
        } else {
            return true;
        }
    }

    function get_warehouse_in_poscode() {
        var warehouse_id = get_in_warehouse_id();
        $.post(app.url('stock/location/dialog'), {warehouse_id: warehouse_id}, function (res) {
            if (res.data.length > 0) {
                in_poscode = res.data[0];
            }
        }, 'json');
    }

    $('#stock_allocation_in_warehouse_id').on('change', function() {
        get_warehouse_in_poscode();
    });

    get_warehouse_in_poscode();

})(jQuery);

</script>