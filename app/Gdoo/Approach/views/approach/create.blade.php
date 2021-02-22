<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}
        
        <a href="javascript:costDetailDialog();" class="btn btn-sm btn-default">
            费用申请明细
        </a>

        @if($form['access']['close'])
        <a href="javascript:closeDialog();" class="btn btn-sm btn-default">
            关闭(打开)
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
var id = '{{$form["row"]["id"]}}';
var actived_dt = '{{$form["row"]["actived_dt"]}}';
var customer_id = '{{$form["row"]["customer_id"]}}';

function costDetailDialog() {
    viewDialog({
        title: '费用申请明细',
        dialogClass: 'modal-md',
        url: app.url('approach/approach/serviceCostDetail', {date: actived_dt, customer_id: customer_id}),
        close: function() {
            $(this).dialog("close");
        }
    });
}

function closeDialog() {
    $.post(app.url('approach/approach/close'), {id: id}, function(res) {
        toastrSuccess(res.data);
        location.reload();
    });
}

function get_customer_id() {
    var customer_id = $('#approach_customer_id').val();
    return customer_id;
}

function undertake_ratio() {
    var v1 = $('#approach_barcode_cast').val();
    var v2 = $('#approach_market_cast').val();
    var v3 = (toNumber(v2) / toNumber(v1)) * 100;
    $('#approach_barcode_cast_ratio').val(v3 > 100 ? 100 : v3.toFixed(2));
}

function undertake_ratio2() {
    var v1 = $('#approach_barcode_cast').val();
    var v2 = $('#approach_apply2_money').val();
    var v3 = (toNumber(v2) / toNumber(v1)) * 100;
    $('#approach_apply2_ratio').val(v3 > 100 ? 100 : v3.toFixed(2));

    var v4 = $('#approach_apply_bccount').val();
    var v5 = $('#approach_apply_market_count').val();
    var v6 = (toNumber(v2) / toNumber(v4)) / toNumber(v5);
    $('#approach_apply2_single_cast').val(toNumber(v6).toFixed(2));
}

function undertake_ratio3() {
    var v1 = $('#approach_barcode_cast').val();
    var v2 = $('#approach_apply_money').val();
    var v3 = (toNumber(v2) / toNumber(v1)) * 100;
    $('#approach_fee_support_ratio').val(v3 > 100 ? 100 : v3.toFixed(2));
}

(function($) {

    $('#approach_barcode_cast,#approach_market_cast').bind('input propertychange', function() {
        undertake_ratio();
        undertake_ratio2();
    });

    $('#approach_apply2_money,#approach_apply_bccount,#approach_apply_market_count').bind('input propertychange', function() {
        undertake_ratio();
        undertake_ratio2();
    });

    $('#approach_apply_money').bind('input propertychange', function() {
        undertake_ratio3();
    });

    $('#approach_field001').prop('checked', true);
    $('#approach_field004').prop('checked', true);

})(jQuery);

// grid初始化事件
gdoo.event.set('grid.approach_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            var customer_id = $('#approach_customer_id').val();
            if (customer_id.trim() == '') {
                toastrError('请先选择客户');
                return false;
            } else {
                return true;
            }
        }
    }
});

// 子表对话框
gdoo.event.set('approach_data.product_id', {
    open(params) {
        params.url = 'product/product/serviceCustomer';
    },
    query(query) {
        var customer_id = $('#approach_customer_id').val();
        query.customer_id = customer_id;
    },
    onSelect(row, selectedRow) {
        row.price = selectedRow.price;
        return true;
    }
});

// 选择客户事件
gdoo.event.set('approach.customer_id', {
    onSelect(row) {
        if (row.id) {
            $('#customer_region_region_id').val(row.region_id);
            $('#customer_region_region_id_text').val(row.region_id_name || '');
            $('#approach_phone').val(row.tel);
            $('#approach_fax').val(row.fax);
            return true;
        }
    }
});

// 选择超市事件
gdoo.event.set('approach.market_name', {
    init(params) {
        params.ajax.url = app.url('approach/market/dialog');
        params.resultCache = false;
    },
    query(query) {
        query.field_0 = 'approach_market.name';
        var customer_id = $('#approach_customer_id').val();
        query.customer_id = customer_id;
    },
    onSelect(row) {
        if (row.id) {
            $('#approach_market_fax').val(row.fax);
            $('#approach_market_totol').val(row.market_count);
            $('#approach_single_cast').val(row.single_cast);
            $('#approach_totol_cast').val(row.total_cast);

            $('#approach_market_size').val(row.market_area);
            $('#approach_market_address').val(row.market_address);
            $('#approach_market_contact').val(row.market_person_name);
            $('#approach_market_contact_phone').val(row.market_person_phone);
            $('#approach_market_type_id_select').val(row.type_id);
            $('#approach_market_type_id').val(row.type_id);
            return true;
        }
    }
});
</script>