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

function get_customer_id() {
    var customer_id = $('#promotion_review_customer_id').val();
    return customer_id;
}

$(function($) {

    $(document).on('click', '[data-toggle="joint"]', function(event) {
        var data = $(this).data();
        // 联查进店申请
        if (data.action == 'apply') {
            top.addTab('promotion/promotion/show?id=' + data.id, 'promotion_promotion_show', '促销申请(联查)');
        }
        // 联查费用明细
        if (data.action == 'cash_detail') {
            viewDialog({
                title: '兑现明细',
                dialogClass: 'modal-md',
                url: app.url('promotion/review/feeDetail', {id: data.id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
        }
    });
});

// grid初始化事件
gdoo.event.set('grid.promotion_review_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            var customer_id = $('#promotion_review_apply_id').val();
            if (customer_id.trim() == '') {
                toastrError('请先选择申请编号');
                return false;
            } else {
                return true;
            }
        }
    }
});

// 子表对话框
gdoo.event.set('promotion_review_data.product_id', {
    query(query) {
        var customer_id = $('#promotion_review_customer_id').val();
        query.customer_id = customer_id;
        query.query_type = 'customer_price';
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

$(function() {
    $('#promotion_review_pay_type').on('change', function() {
        if (this.value == 1 || this.value == 3) {
            $('#promotion_review_use_order').val(1);
        } else {
            $('#promotion_review_use_order').val(0);
        }
    });
});

// 子表对话框
gdoo.event.set('promotion_review.apply_id', {
    open(params, query) {
        var customer_name = $('#promotion_review_customer_id_text').val();
        query.field_0 = 'customer_id_customer.name';
        query.condition_0 ='like';
        query.search_0 = customer_name;
    },
    query(query) {
    },
    onSelect() {
        var promotion = $ref_promotion.api.getSelectedRows()[0];
        var rows = $ref_promotion_data.api.getSelectedRows();

        if (promotion == undefined) {
            toastrError('请先选择促销申请');
            return false;
        }
        $('#promotion_review_apply_start_dt').val(promotion.start_dt);
        $('#promotion_review_apply_end_dt').val(promotion.end_dt);

        $('#promotion_review_apply_scope').val(promotion.promote_scope);
        $('#promotion_review_apply_money').val(promotion.apply_money);
        $('#promotion_review_area_money').val(promotion.undertake_money);
        $('#promotion_review_fact_verification_cost').val(promotion.verification_cost);

        $('#promotion_review_apply_id').val(promotion.id);
        $('#promotion_review_apply_id_text').val(promotion.sn);

        $('#promotion_review_customer_id').val(promotion.customer_id);
        $('#promotion_review_customer_id_text').val(promotion.customer_name);

        $('#customer_region_region_id').val(promotion.region_id);
        $('#customer_region_region_id_text').val(promotion.region_name);

        grid.api.setRowData([]);
        for (let i = 0; i < rows.length; i++) {
            var row = rows[i];
            grid.api.memoryStore.create(row);
        }
        return true;
    }
});

// 子表对话框
gdoo.event.set('promotion_review.customer_id', {
    query(query) {
    },
    onSelect(row) {
        $('#customer_region_region_id').val(row.region_id);
        $('#customer_region_region_id_text').val(row.region_id_name);
        return true;
    }
});

</script>