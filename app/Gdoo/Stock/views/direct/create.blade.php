<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            {{$form['tpl']}}
        </form>
    </div>
</div>

<script>
var table = '{{$form["table"]}}';
var grid = null;

(function ($) {

    $('#stock_direct_data_tool').append('<a class="btn btn-sm btn-default" href="javascript:batchDistribute();">批次分配</a> <a class="btn btn-sm btn-default" href="javascript:importExcel();">导入</a>');

    gdoo.event.set('stock_direct.invoice_dt', {
        onpicked() {
            var date = $('#stock_direct_invoice_dt').val();
            date = date.replace(/-/gi, '');
            $.post(app.url('index/api/billSeqNo'), {date: date, bill_id: 65}, function(res) {
                $('#stock_direct_sn').val(res.data);
            }, 'json');
        }
    });

    // 发货记录
    function importExcel() {
        if (has_customer_id() == false) {
            return;
        }
        var url = app.url('stock/direct/importExcel');
        formDialog({
            title: '导入数据',
            url: url,
            id: 'import_excel',
            dialogClass:'modal-md',
            onSubmit: function() {
                var me = this;
                var form = $('#import_excel');
                var file = document.querySelector("#import_file").files[0];
                var formData = new FormData();
                formData.append('file', file);
                formData.append('customer_id', get_customer_id());
                var loading = layer.msg('数据提交中...', {
                    icon: 16, shade: 0.1, time: 1000 * 120
                });
                $.ajax(url, {
                    method: "post",
                    data: formData,
                    processData: false,
                    contentType: false,
                    complete: function() {
                        layer.close(loading);
                    },
                    success: function (res) {
                        if (res.status) {
                            grid.api.setRowData([]);
                            for (var i = 0; i < res.data.length; i++) {
                                var row = res.data[i];
                                grid.api.memoryStore.create(row);
                            }
                            grid.generatePinnedBottomData();
                            $(me).dialog('close');
                            toastrSuccess('导入数据成功。');
                        } else {
                            toastrError(res.data);
                        }
                    },
                    error: function (res) {
                        toastrError(res.data);
                    }
                });
            }
        });
    }
    window.importExcel = importExcel;

    // 获取生产批号
    function getBatchSelect(warehouse_id, product_ids, batchs, fun) {
        $.post(app.url('stock/delivery/getBatchSelectZY'), {
            warehouse_id: warehouse_id,
            product_id: product_ids,
            value: batchs
        }, function(res) {
            fun(res);
        });
    }

    // 分配批次
    function batchDistribute() {

        if (has_warehouse_id() == false) {
            return;
        }

        var rows = [];
        var product_ids = {};

        grid.api.stopEditing();
        grid.api.forEachNode(function (node) {
            var data = node.data;
            if (data.product_id > 0) {
                if (data.product_code == '99001') {
                    return;
                }
                if (data.quantity > 0) {
                    product_ids[data.product_id] = data.product_id;
                    rows.push(data);
                } else {
                    toastrError('请先填写数量:' + data.product_name + '('+data.product_code+')');
                }
            }
        });

        var warehouse_id = $('#stock_direct_warehouse_id').val();

        var loading = layer.msg('数据提交中...', {
            icon: 16, shade: 0.1, time: 1000 * 120
        });

        var product_ids = Object.keys(product_ids).join(',');
        getBatchSelect(warehouse_id, product_ids, '', function(res) {

            var products = {};
            for (var i = 0; i < res.data.length; i++) {
                var data = res.data[i];
                var product = products['_' + data.product_id] || [];
                product.push(data);
                products['_' + data.product_id] = product;
            }

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (row.product_code == '99001') {
                    continue;
                }
                var quantity = parseFloat(row.quantity);
                var ret = false;
                var insert = false;
                // 获取批次
                var batchs = products['_' + row.product_id] || [];

                for (var j = 0; j < batchs.length; j++) {
                    var batch = batchs[j];
                    var ky_num = parseFloat(batch.ky_num);
                    if (ky_num <= 0) {
                        continue;
                    }

                    var item = jQuery.extend({}, row);
                    item.warehouse_id = batch.warehouse_id;
                    item.warehouse_id_name = batch.warehouse_name;
                    item.batch_sn = batch.batch_sn;
                    item.batch_date = batch.batch_date;
                    item.poscode = batch.poscode;
                    item.posname = batch.posname;

                    if (quantity > ky_num) {
                        item.quantity = ky_num;
                        quantity = quantity - ky_num;
                        insert = true;
                    } else {
                        ret = true;
                        item.quantity = quantity;
                    }

                    // 减少批号可用量
                    batch.ky_num = ky_num - item.quantity;

                    item.total_weight = item.quantity * item.weight;
                    item.money = item.quantity * item.price;
                    // 赠品
                    if (item.type_id == 2) {
                        item.other_money = item.money;
                    }

                    if (j > 0 && insert == true) {
                        grid.api.memoryStore.create(item);
                    } else {
                        grid.api.memoryStore.update(item);
                    }
                    
                    // 单个产品写入结束
                    if (ret == true) {
                        break;
                    }
                }
            }

            layer.close(loading); 
            grid.generatePinnedBottomData();
        });
    }
    window.batchDistribute = batchDistribute;

    // grid初始化事件
    gdoo.event.set('grid.stock_direct_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'product_id';
        },
        onSaveBefore(rows) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                var quantity = toNumber(row.quantity);
                var price = toNumber(row.price);
                var money = toNumber(row.money).toFixed(2);
                var new_money = (quantity * price).toFixed(2);
                var other_money = toNumber(row.other_money).toFixed(2);

                if (quantity > 0 && price > 0) {
                    if (new_money != money) {
                        toastrError(row.product_name + ' 数量 * 单价不等于金额');
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
                return has_warehouse_id();
            }
        }
    });

    // 选择产品
    gdoo.event.set('stock_direct_data.product_id', {
        open(params) {
            params.url = 'product/product/serviceCustomer';
        },
        query(query) {
            query.customer_id = get_customer_id();
        },
        onSelect(row, selected) {
            if (selected.code == '99001') {
                row.type_id_name = '费用';
                row.type_id = 5;
            } else {
                row.type_id_name = '普通';
                row.type_id = 1;
                row.price = selected.price;
                row.weight = selected.weight;
            }
            return true;
        }
    });

    // 选择客户事件
    gdoo.event.set('stock_direct.customer_id', {
        open(params) {
        },
        query(query) {
        },
        onSelect(row) {
            if (row.id) {
                $('#stock_direct_order_type_id').val(row.type_id);
                $('#stock_direct_order_type_id_select').val(row.type_id);

                $('#stock_direct_warehouse_contact').val(row.warehouse_contact);
                $('#stock_direct_warehouse_phone').val(row.warehouse_phone);
                $('#stock_direct_warehouse_tel').val(row.warehouse_tel);
                $('#stock_direct_warehouse_address').val(row.warehouse_address);
                get_customer_tax(row.id);
                return true;
            }
        }
    });

    // 选择生产批号
    gdoo.event.set('stock_direct_data.batch_sn', {
        open(params) {
            params.title = '选择库存现存量';
            params.url = 'stock/delivery/getBatchSelectZY';
        },
        query(query) {
            var warehouse_id = $('#stock_direct_warehouse_id').val();
            query.warehouse_id = warehouse_id;
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
    gdoo.event.set('stock_direct_data.poscode', {
        query(query) {
            var warehouse_id = get_warehouse_id();
            if (warehouse_id > 0) {
                query.warehouse_id = warehouse_id;
            }
        },
        onSelect(row, selectedRow) {
            row.poscode = selectedRow.code;
            row.posname = selectedRow.name;
            return true;
        }
    });

    function get_warehouse_id() {
        var warehouse_id = $('#stock_direct_warehouse_id').val();
        return warehouse_id || 0;
    }

    function has_warehouse_id() {
        var warehouse_id = get_warehouse_id();
        if (warehouse_id == 0) {
            toastrError('请先选择仓库');
            return false;
        } else {
            return true;
        }
    }

    function get_customer_id() {
        var customer_id = $('#stock_direct_customer_id').val();
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

    var tax_id = '{{$form["row"]["tax_id"]}}';
    function get_customer_tax(customer_id, tax_id) {
        if (customer_id) {
            $.post(app.url('customer/tax/dialog'), {customer_id: customer_id}, function (res) {
                var html = '<option value=""> - </option>';
                $.each(res.data, function (i, row) {
                    var selected = tax_id == row.id ? 'selected="selected"' : '';
                    html += '<option value="' + row.id + '" ' + selected + '>' + row.name + '</option>';
                });
                $('#stock_direct_tax_id').html(html);
            }, 'json');
        }
    }
    get_customer_tax(get_customer_id(), tax_id);
})(jQuery);

</script>