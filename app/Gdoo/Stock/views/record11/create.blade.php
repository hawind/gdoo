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

// grid初始化事件
gdoo.event.set('grid.stock_record11_data', {
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

// 子表对话框
gdoo.event.set('stock_record11_data.product_id', {
    query(query) {
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

function get_warehouse_id() {
    var customer_id = $('#stock_record11_warehouse_id').val();
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
            var rows = $ref_stock_select.api.getSelectedRows();
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
</script>