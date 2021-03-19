<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}

        @if($form['action'] == 'show')
        @else
            <a href="javascript:sampleDialog();" class="btn btn-sm btn-default">
                参照样品申请单
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

$(function() {
    $('#stock_record09_data_tool').append('<a class="btn btn-sm btn-default" href="javascript:stockSelect();">选择库存</a>');
});

// grid初始化事件
gdoo.event.set('grid.stock_record09_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return has_warehouse_id();
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
            if (row.product_id > 0) {
            } else {
                toastrError('请先选择产品');
                return false;
            }
            return true;
        }
    }
});

// 选择生产批号
gdoo.event.set('stock_record09_data.batch_sn', {
    open(params) {
        params.title = '选择库存现存量';
        params.url = 'stock/delivery/getBatchSelectAll';
    },
    query(query) {
        query.warehouse_id = get_warehouse_id();
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

        row.poscode = batch.poscode;
        row.posname = batch.posname;
        row.total_weight = row.quantity * row.weight;
        row.money = row.quantity * row.price;
        // 库存现存量不足写入剩余数量
        if (quantity > 0) {
            var item = jQuery.extend({}, row);
            item.quantity = quantity;

            item.batch_sn = '';
            item.batch_date = '';

            item.poscode = '';
            item.posname = '';

            item.total_weight = item.quantity * item.weight;
            item.money = item.quantity * item.price;
            grid.api.memoryStore.create(item);
        }
        return true;
    }
});

// 子表对话框
gdoo.event.set('stock_record09_data.product_id', {
    query(query) {
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

// 选择货位编号
gdoo.event.set('stock_record09_data.poscode', {
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
    var customer_id = $('#stock_record09_warehouse_id').val();
    return customer_id || 0;
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

// 获取生产批号
function getBatchSelect(warehouse_id, product_id) {
    var ret = [];
    $.ajax({
        url:app.url('stock/delivery/getBatchSelectAll'),
        type: 'POST',
        data: {
            warehouse_id: warehouse_id, 
            product_id: product_id
        },
        dataType: "json",
        async: false,
        success: function (res) {
            ret = res.data;
        }
    });
    return ret;
}

// 选择库存
function stockSelect() {
    if (has_warehouse_id() == false) {
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
    var warehouse_id = get_warehouse_id();
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
        row.total_weight = row.quantity * row.weight;
        row.money = row.quantity * row.price;
        grid.api.memoryStore.create(row);
    }
    grid.generatePinnedBottomData();
    return true;
};
window.stockRowsSelected = stockRowsSelected;

var sampleDialog = function () {
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
            var row = $ref_sample_apply.api.getSelectedRows()[0];
            if (row) {
                $('#stock_record09_department_id').val(row.department_id);
                $('#stock_record09_department_id_text').val(row.department_name);
                $('#stock_record09_type_id').val(2);
            }
            grid.api.forEachNode(function (node) {
                var data = node.data;
                if (isEmpty(data.product_id)) {
                    grid.api.updateRowData({remove:[data]});
                }
            });

            var loading = showLoading();
            var warehouse_id = $('#stock_record09_warehouse_id').val();
            var rows = $ref_sample_apply_data.api.getSelectedRows();
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                // 产品库存不足
                if (row.ky_num <= 0) {
                    continue;
                }
                var quantity = parseFloat(row.wc_num);
                var batchs = getBatchSelect(warehouse_id, row.product_id);
                var ret = false;
                for (var j = 0; j < batchs.length; j++) {
                    console.log(batch);
                    var batch = batchs[j];
                    var ky_num = parseFloat(batch.ky_num);
                    var item = jQuery.extend({}, row);
                    if (quantity > ky_num) {
                        item.quantity = ky_num;
                        quantity = quantity - ky_num;
                    } else {
                        ret = true;
                        item.quantity = quantity;
                    }
                    item.batch_sn = batch.batch_sn;
                    item.batch_date = batch.batch_date;
                    item.poscode = batch.poscode;
                    item.posname = batch.posname;
                    grid.api.memoryStore.create(item);
                    // 单个产品写入结束
                    if (ret == true) {
                        break;
                    }
                }
            }
            layer.close(loading);
            grid.generatePinnedBottomData();
            $(this).dialog('close');
        }
    });
    $.dialog({
        title: '样品申请单',
        url: '{{url("order/sample-apply/serviceDelivery")}}',
        dialogClass: 'modal-lg',
        buttons: buttons
    });
}
</script>