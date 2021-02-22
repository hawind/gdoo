<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}

        @if($form['action'] == 'show')
        @else
            <a href="javascript:orderDialog();" class="btn btn-sm btn-default">
                参照采购订单
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

// grid初始化事件
gdoo.event.set('grid.stock_record01_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return true;
        }
    }
});

function get_warehouse_id() {
    var warehouse_id = $('#stock_record01_warehouse_id').val();
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

// 子表对话框
gdoo.event.set('stock_record01_data.product_id', {
    query(query) {
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

// 选择客户事件
gdoo.event.set('stock_record01.supplier_id', {
    onSelect(row) {
        return true;
    }
});

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
            var master = $ref_purchase_order.api.getSelectedRows()[0];
            if (master) {
                
                grid.api.forEachNode(function (node) {
                    var data = node.data;
                    if (isEmpty(data.product_id)) {
                        grid.api.updateRowData({remove:[data]});
                    }
                });

                var supplier_ids = {};
                var rows = $ref_purchase_order_data.api.getSelectedRows();
                for (var i = 0; i < rows.length; i++) {
                    var row = rows[i];
                    supplier_ids[row.supplier_id] = row.supplier_id;
                }
                
                if (Object.keys(supplier_ids).length == 1) {

                    // 写入主表信息
                    $('#stock_record01_supplier_id').val(rows[0].supplier_id);
                    $('#stock_record01_supplier_id_text').val(rows[0].supplier_name);
                    $('#stock_record01_department_id').val(master.department_id);
                    $('#stock_record01_department_id_text').val(master.department_name);

                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        row.quantity = parseFloat(row.wr_num);
                        grid.api.memoryStore.create(row);
                    }
                    grid.generatePinnedBottomData();
                    $(this).dialog('close');
                } else {
                    toastrError('采购入库供应商必须相同');
                }

            } else {
                toastrError('采购订单主表记录没有选中');
            }
        }
    });
    $.dialog({
        title: '采购订单',
        url: '{{url("purchase/order/serviceRecord01")}}',
        dialogClass: 'modal-lg',
        buttons: buttons
    });
}

</script>