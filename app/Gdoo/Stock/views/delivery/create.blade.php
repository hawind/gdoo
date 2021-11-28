<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}

        @if($form['action'] == 'show')
        @else
            @if($form['row']['id'] > 0)
            @else
            <a href="javascript:orderDialog();" class="btn btn-sm btn-default">
                参照客户订单
            </a>
            @endif
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

            @if($form['action'] == 'show')
            @else 
            <input type="hidden" id="stock_delivery_freight_short_logistics_id" name="stock_delivery[freight_short_logistics_id]">
            <input type="hidden" id="stock_delivery_freight_short_car" name="stock_delivery[freight_short_car]">
            @endif
        </form>
    </div>
</div>

<script>
var table = '{{$form["table"]}}';
var rowId = '{{$form["row"]["id"]}}';
var tax_id = '{{$form["row"]["tax_id"]}}';
var grid = null;
(function ($) {
    function get_customer_tax(customer_id, tax_id) {
        if (customer_id) {
            $.post(app.url('customer/tax/dialog'), {customer_id: customer_id}, function (res) {
                var html = '<option value=""> - </option>';
                $.each(res.data, function (i, row) {
                    var selected = tax_id == row.id ? 'selected="selected"' : '';
                    html += '<option value="' + row.id + '" ' + selected + '>' + row.name + '</option>';
                });
                $('#stock_delivery_tax_id').html(html);
            }, 'json');
        }
    }
    get_customer_tax(get_customer_id(), tax_id);

    // 获取生产批号
    function getBatchSelect(warehouse_id, product_id, batchs, customer_id, fun) {
        $.post(app.url('stock/delivery/getBatchSelect'), {
            warehouse_id: warehouse_id,
            product_id: product_id, 
            customer_id: customer_id,
            value: batchs
        }, function(res) {
            fun(res);
        });
    }

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
                        batchs[row.batch_sn] = row.batch_sn;
                        products[row.product_id] += parseFloat(row.wf_num);
                    }

                    var loading = showLoading();

                    var product_ids = Object.keys(products).join(',');
                    var batch_ids = Object.keys(batchs).join(',');
                    if (batch_ids == 'null') {
                        batch_ids = '';
                    }
                    getBatchSelect(0, product_ids, batch_ids, master.customer_id, function(res) {
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
                                    item.poscode = batch.poscode;
                                    item.posname = batch.posname;

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

                        $('#stock_delivery_customer_id').val(master.customer_id);
                        $('#stock_delivery_customer_id_text').val(master.customer_name);
                        $('#stock_delivery_invoice_dt').val(master.plan_delivery_dt);

                        $('#stock_delivery_freight_short_logistics_id').val(master.freight_short_logistics_id);
                        $('#stock_delivery_freight_short_car').val(master.freight_short_car);
                        $('#stock_delivery_freight_pay_text').val(master.freight_pay_text);

                        $('#stock_delivery_tax_type').val(master.tax_type);
                        $('#stock_delivery_tax_id').html('<option selected="selected" value="' + master.tax_id + '">' + master.tax_name + '</option>');
    
                        $('#stock_delivery_order_type_id').val(master.type_id);
                        $('#stock_delivery_order_type_id_select').val(master.type_id);

                        $('#stock_delivery_warehouse_contact').val(master.warehouse_contact);
                        $('#stock_delivery_warehouse_phone').val(master.warehouse_phone);
                        $('#stock_delivery_warehouse_tel').val(master.warehouse_tel);
                        $('#stock_delivery_warehouse_address').val(master.warehouse_address);
                        $('#stock_delivery_remark').val(master.remark);
                        
                        grid.generatePinnedBottomData();
                        $(me).dialog('close');

                        // 自动提交
                        var query = $('#' + table).serialize();

                        // 循环子表
                        var gets = gridListData(table);
                        if(gets === false) {
                            return;
                        }

                        layer.close(loading);
                        /*
                        var loading = showLoading();
                        $.post(app.url('stock/delivery/autoSave'), query + '&' + $.param(gets), function (res) {
                            if (res.status) {
                                toastrSuccess(res.data);
                                if (res.url) {
                                    location.href = res.url;
                                }
                            } else {
                                toastrError(res.data);
                            }
                        }, 'json').complete(function() {
                            layer.close(loading);
                        });
                        */
                    });
                    
                } else {
                    toastrError('销售订单必须选择。');
                }
            }
        });
        var v = $('#stock_delivery_customer_id_text').val();
        $.dialog({
            title: '客户订单',
            url: '{{url("order/order/serviceDelivery")}}?is_direct=0&field_0=customer.name&condition_0=like&search_0=' + v,
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    }

    var logisticsDialog = function () {
        formDialog({
            title: '物流信息',
            url: app.url('stock/delivery/logistics', {id: rowId}),
            storeUrl: app.url('stock/delivery/logistics'),
            id: 'delivery_logistics',
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
    gdoo.event.set('grid.stock_delivery_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'product_id';
        },
        editable: {
            product_name(params) {
                return has_customer_id();
            },
            batch_sn(params) {
                var row = params.data;
                if (row.product_id > 0) {
                } else {
                    toastrError('请先选择产品');
                    return false;
                }
                return true;
            },
            poscode(params) {
                var row = params.data;
                if (row.warehouse_id > 0) {
                } else {
                    toastrError('请先选择仓库');
                    return false;
                }
                return true;
            }
        },
        onSaveBefore(rows) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var quantity = toNumber(row.quantity);
                var price = toNumber(row.price);
                var money = toNumber(row.money).toFixed(2);
                var other_money = toNumber(row.other_money).toFixed(2);
                var new_money = (quantity * price).toFixed(2);
                if (quantity > 0 && price > 0) {
                    if (new_money != money) {
                        toastrError(row.product_name + ' 实发数量 * 单价不等于金额');
                        return false;
                    }
                    if (other_money > 0) {
                        if (other_money != money) {
                            toastrError(row.product_name + '其他金额不等于金额');
                            return false;
                        }
                    }
                }
            }
            return true;
        }
    });

    // 选择产品
    gdoo.event.set('stock_delivery_data.product_id', {
        open(params) {
            params.url = 'product/product/serviceCustomer';
        },
        query(query) {
            query.customer_id = get_customer_id();
        },
        onSelect(row, selectedRow) {
            row.type_id_name = '普通';
            row.type_id = 1;
            row.price = selectedRow.price;
            return true;
        }
    });

    function get_customer_id() {
        var customer_id = $('#stock_delivery_customer_id').val();
        return customer_id || 0;
    }

    function has_customer_id() {
        var customer_id = get_customer_id();
        if (customer_id == 0) {
            toastrError('请先选择客户');
            return false;
        } else {
            return true;
        }
    }

    // 选择生产批号
    gdoo.event.set('stock_delivery_data.batch_sn', {
        open(params) {
            params.title = '选择库存现存量';
            params.url = 'stock/delivery/getBatchSelect';
        },
        query(query) {
            var row = grid.lastEditCell.data;
            console.log(row);
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
            row.warehouse_id = batch.warehouse_id;
            row.warehouse_id_name = batch.warehouse_name;
            row.batch_sn = batch.batch_sn;
            row.batch_date = batch.batch_date;
            row.poscode = batch.poscode;
            row.posname = batch.posname;
            row.total_weight = row.quantity * row.weight;
            row.money = row.quantity * row.price;
            // 赠品
            if (row.type_id == 2) {
                row.other_money = row.money;
            }
            grid.api.memoryStore.update(row);

            // 库存现存量不足写入剩余数量
            if (quantity > 0) {
                var item = jQuery.extend({}, row);
                item.quantity = quantity;
                item.warehouse_id = 0;
                item.warehouse_id_name = '';
                item.batch_sn = '';
                item.batch_date = '';
                item.poscode = '';
                item.posname = '';
                item.total_weight = item.quantity * item.weight;
                item.money = item.quantity * item.price;
                // 赠品
                if (item.type_id == 2) {
                    item.other_money = item.money;
                }
                grid.api.memoryStore.create(item);
            }
            return true;
        }
    });

    // 选择货位编号
    gdoo.event.set('stock_delivery_data.poscode', {
        query(query) {
            var row = grid.lastEditCell.data;
            console.log(row);
            if (row.warehouse_id > 0) {
                query.warehouse_id = row.warehouse_id;
            }
        },
        onSelect(row, selectedRow) {
            row.poscode = selectedRow.code;
            row.posname = selectedRow.name;
            return true;
        }
    });
    
    // 选择客户事件
    gdoo.event.set('stock_delivery.customer_id', {
        onSelect(row) {
            if (row) {
                $('#stock_delivery_order_type_id').val(row.type_id);
                $('#stock_delivery_order_type_id_select').val(row.type_id);

                $('#stock_delivery_warehouse_contact').val(row.warehouse_contact);
                $('#stock_delivery_warehouse_phone').val(row.warehouse_phone);
                $('#stock_delivery_warehouse_tel').val(row.warehouse_tel);
                $('#stock_delivery_warehouse_address').val(row.warehouse_address);
                get_customer_tax(row.id);
                return true;
            }
        }
    });

    // 选择短途运输人
    gdoo.event.set('stock_delivery.freight_short_logistics_id', {
        query(query) {
        },
        onSelect(row) {
            if (row) {
                $('#stock_delivery_freight_short_car').val(row.short_car_sn);
                return true;
            }
        }
    });

    // 选择物流公司
    gdoo.event.set('stock_delivery.freight_logistics_id', {
        query(query) {
        },
        onSelect(row) {
            if (row) {
                $('#stock_delivery_freight_logistics_phone').val(row.business_phone);
                return true;
            }
        }
    });

    window.orderDialog = orderDialog;

})(jQuery);

</script>