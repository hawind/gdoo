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

var tax_id = '{{$form["row"]["tax_id"]}}';
var tax_type = $('#stock_cancel_tax_type').val();

function get_customer_id() {
    var customer_id = $('#stock_cancel_customer_id').val();
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

function get_customer_tax(customer_id, tax_id) {
    if (customer_id) {
        $.post(app.url('customer/tax/dialog'), {customer_id: customer_id}, function (res) {
            var html = '<option value=""> - </option>';
            $.each(res.data, function (i, row) {
                var selected = tax_id == row.id ? 'selected="selected"' : '';
                html += '<option value="' + row.id + '" ' + selected + '>' + row.name + '</option>';
            });
            $('#stock_cancel_tax_id').html(html);
        }, 'json');
    }
}

get_customer_tax(get_customer_id(), tax_id);

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
            var row = $ref_customer_order.api.getSelectedRows()[0];
            if (row) {
                $('#stock_cancel_customer_id').val(row.customer_id);
                $('#stock_cancel_customer_id_text').val(row.customer_name);

                $('#stock_cancel_tax_type').val(row.tax_type);
                $('#stock_cancel_tax_type_select').val(row.tax_type);

                // 设置开票名称
                get_customer_tax(row.customer_id, row.tax_id);

                $('#stock_cancel_order_type_id').val(row.type_id);
                $('#stock_cancel_order_type_id_select').val(row.type_id);

                $('#stock_cancel_warehouse_contact').val(row.warehouse_contact);
                $('#stock_cancel_warehouse_phone').val(row.warehouse_phone);
                $('#stock_cancel_warehouse_tel').val(row.warehouse_tel);
                $('#stock_cancel_warehouse_address').val(row.warehouse_address);
            }
            
            var rows = $ref_customer_order_data.api.getSelectedRows();
            grid.api.setRowData([]);
            for (let i = 0; i < rows.length; i++) {
                var row = rows[i];
                row.quantity = 0 - row.quantity;
                row.money = 0 - row.money;
                grid.api.memoryStore.create(row);
            }
            grid.generatePinnedBottomData();
            $(this).dialog('close');
        }
    });
    var v = $('#stock_cancel_customer_id_text').val();
    $.dialog({
        title: '客户订单',
        url: '{{url("order/order/serviceCancelOrder")}}?field_0=customer.name&condition_0=like&search_0=' + v,
        dialogClass: 'modal-lg',
        buttons: buttons
    });
}

// grid初始化事件
gdoo.event.set('grid.stock_cancel_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return has_customer_id();
        }
    }
});


// 子表对话框
gdoo.event.set('stock_cancel_data.warehouse_id', {
    open(params) {
    },
    query(query) {
    },
    onSelect(row, selected) {
        var pos = selected.pos;
        if (pos.length > 0) {
            row.poscode = pos[0].code;
            row.posname = pos[0].name;
        }
        return true;
    }
});

// 子表对话框
gdoo.event.set('stock_cancel_data.product_id', {
    open(params) {
        params.url = 'product/product/serviceCustomer';
    },
    query(query) {
        query.customer_id = get_customer_id();
    },
    onSelect(row, selected) {
        row.type_id_name = '普通';
        row.type_id = 1;
        row.price = selected.price;
        return true;
    }
});

// 选择货位编号
gdoo.event.set('stock_cancel_data.poscode', {
    query(query) {
        var row = grid.lastEditCell.data;
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
gdoo.event.set('stock_cancel.customer_id', {
    onSelect(row) {
        if (row.id) {
            $('#stock_cancel_order_type_id').val(row.type_id);
            $('#stock_cancel_order_type_id_select').val(row.type_id);

            $('#stock_cancel_order_warehouse_contact').val(row.warehouse_contact);
            $('#stock_cancel_order_warehouse_phone').val(row.warehouse_phone);
            $('#stock_cancel_order_warehouse_tel').val(row.warehouse_tel);
            $('#stock_cancel_order_warehouse_address').val(row.warehouse_address);
            get_customer_tax(row.id);

            return true;
        }
    }
});
</script>