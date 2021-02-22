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

gdoo.event.set('stock_record10.invoice_dt', {
    onpicked() {
        var date = $('#stock_record10_invoice_dt').val();
        date = date.replace(/-/gi, '');
        $.post(app.url('index/api/billSeqNo'), {date: date, bill_id: 59}, function(res) {
            $('#stock_record10_sn').val(res.data);
        }, 'json');
    }
});

var poscode = {};
function get_warehouse_poscode() {
    var warehouse_id = get_warehouse_id();
    $.post(app.url('stock/location/dialog'), {warehouse_id: warehouse_id}, function (res) {
        if (res.data.length > 0) {
            poscode = res.data[0];
        } else {
            poscode = {};
        }
    }, 'json');
}

$(function() {
    $('#stock_record10_warehouse_id').on('change', function() {
        get_warehouse_poscode();
    });
    get_warehouse_poscode();
});

// grid初始化事件
gdoo.event.set('grid.stock_record10_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return has_warehouse_id();
        },
        poscode(params) {
            return has_warehouse_id();
        }
    }
});

// 子表对话框
gdoo.event.set('stock_record10_data.product_id', {
    query(query) {
        var customer_id = $('#stock_record10_warehouse_id').val();
    },
    onSelect(row, selectedRow) {
        row.poscode = poscode.code;
        row.posname = poscode.name;
        return true;
    }
});

// 选择货位编号
gdoo.event.set('stock_record10_data.poscode', {
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
    var warehouse_id = $('#stock_record10_warehouse_id').val();
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
</script>