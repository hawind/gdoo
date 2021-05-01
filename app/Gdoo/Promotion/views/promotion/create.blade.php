@if(is_weixin())
<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script type="text/javascript" src="https://js.cdn.aliyun.dcloud.net.cn/dev/uni-app/uni.webview.1.5.2.js"></script>
@endif

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

    @if($form["row"]["status"] == 1)
        @if(is_weixin())
        <a href="javascript:;" id="upload" class="btn btn-sm btn-default">
            核销资料
        </a>
        @endif
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
var review_id = '{{$review["id"]}}';
var id = '{{$form["row"]["id"]}}';
var actived_dt = '{{$form["row"]["actived_dt"]}}';
var customer_id = '{{$form["row"]["customer_id"]}}';
var grid = null;

document.addEventListener('UniAppJSBridgeReady', function() {
    $('#upload').on('click', function() {
        uni.navigateTo({  
            url: '/pages/app/promotionMaterial/index?promotion_id={{$form["row"]["id"]}}'
        });
    });
});

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
    $.post(app.url('promotion/promotion/close'), {id: id}, function(res) {
        toastrSuccess(res.data);
        location.reload();
    });
}

function undertake_ratio() {
    var v1 = $('#promotion_pro_total_cost').val();
    var v2 = $('#promotion_undertake_money').val();
    var v3 = (toNumber(v2) / toNumber(v1)) * 100;
    $('#promotion_undertake_ratio').val(v3 > 100 ? 100 : v3.toFixed(2));
}

function get_customer_id() {
    var customer_id = $('#promotion_customer_id').val();
    return customer_id;
}

$(function() {

    $('#promotion_pro_total_cost,#promotion_area_money').bind('input propertychange', function() {
        var area_money = $('#promotion_area_money').val();
        $('#promotion_undertake_money').val(area_money);
        undertake_ratio();
    });

    $('#promotion_undertake_money').bind('input propertychange', function() {
        undertake_ratio();
    });

    $('#approach_field001').prop('checked', true);
    $('#approach_field004').prop('checked', true);

    $('#promotion_field010').prop('checked', true);
    $(document).on('click', '[data-toggle="joint"]', function(event) {
        var data = $(this).data();
        // 关联订单
        if (data.action == 'sale_order') {
            top.addTab('order/order/show?id=' + data.id, 'order_order_show', '客户订单(联查)');
        }
        // 联查核销单
        if (data.action == 'promotion_review' && review_id > 0) {
            top.addTab('promotion/review/show?id=' + review_id, 'promotion_review_show', '促销核销(联查)');
        }
        // 联查资料
        if (data.action == 'material') {
            top.addTab('promotion/material/detail?promotion_id=' + data.id, 'promotion_material_show', '促销资料(联查)');
        }
    });
});

// grid初始化事件
gdoo.event.set('grid.promotion_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            var customer_id = $('#promotion_customer_id').val();
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
gdoo.event.set('promotion_data.product_id', {
    query(query) {
        var customer_id = $('#promotion_customer_id').val();
        query.customer_id = customer_id;
        query.query_type = 'customer_price';
    },
    open(params) {
        params.url = 'product/product/serviceCustomer';
    },
    onSelect(row, selectedRow) {
        row.price = selectedRow.price;
        return true;
    }
});

// 子表对话框
gdoo.event.set('promotion.order_id', {
    open(params) {
        params.title = '关联订单';
        params.url = 'order/order/servicePromotion';
    },
    query(query) {
        query.customer_id = $('#promotion_customer_id').val();
        query.type_id = $('#promotion_type_id').val();
        query.order_id = $('#promotion_order_id').val();
    },
    onSelect(row) {
        $('#promotion_order_id_text').val(row.sn);
        return true;
    }
});

// 选择客户事件
gdoo.event.set('promotion.customer_id', {
    onSelect(row) {
        if (row) {
            $('#customer_region_region_id').val(row.region_id);
            $('#customer_region_region_id_text').val(row.region_id_name || '');
            $('#promotion_phone').val(row.tel);
            $('#promotion_fax').val(row.fax);
            return true;
        }
    }
});
</script>