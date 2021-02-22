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
    var customer_id = $('#approach_review_customer_id').val();
    return customer_id;
}

$(function($) {

    $(document).on('click', '[data-toggle="joint"]', function(event) {
        var data = $(this).data();
        // 联查进店申请
        if (data.action == 'apply') {
            top.addTab('approach/approach/show?id=' + data.id, 'approach_approach_show', '进店申请(联查)');
        }
        // 联查费用明细
        if (data.action == 'cash_detail') {
            viewDialog({
                title: '兑现明细',
                dialogClass: 'modal-md',
                url: app.url('approach/review/feeDetail', {id: data.id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
        }
    });
});

// grid初始化事件
gdoo.event.set('grid.approach_review_data', {
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            var approach_id = $('#approach_review_approach_id').val();
            if (approach_id.trim() == '') {
                toastrError('请先选择申请编号');
                return false;
            } else {
                return true;
            }
        }
    }
});

// 子表对话框
gdoo.event.set('approach_review_data.product_id', {
    open(params) {
        params.url = 'product/product/serviceCustomer';
    },
    query(query) {
        var customer_id = $('#approach_review_customer_id').val();
        query.customer_id = customer_id;
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

$(function() {
    $('#approach_review_pay_type').on('change', function() {
        if (this.value == 1 || this.value == 3) {
            $('#approach_review_use_order').val(1);
        } else {
            $('#approach_review_use_order').val(0);
        }
    });
});

// 进场核销申请编号
gdoo.event.set('approach_review.apply_id', {
    open(params) {
        params.url = 'approach/approach/serviceReview';
    },
    query(query) {
    },
    onSelect() {
        var approach = $ref_approach.api.getSelectedRows()[0];
        var rows = $ref_approach_data.api.getSelectedRows();
        $('#approach_review_apply_id').val(approach.id);
        $('#approach_review_apply_id_text').val(approach.sn);
        $('#approach_review_apply_dt').val(approach.created_at);
        $('#approach_review_apply_money').val(approach.apply2_money);
        $('#approach_review_verification_cost').val(approach.apply2_money);
        $('#approach_review_fact_verification_cost').val(approach.apply2_money);
        $('#approach_review_market_name').val(approach.market_name);
        $('#approach_review_customer_id').val(approach.customer_id);
        $('#approach_review_customer_id_text').val(approach.customer_name);
        $('#customer_region_region_id').val(approach.region_id);
        $('#customer_region_region_id_text').val(approach.region_name);

        grid.api.setRowData([]);
        for (let i = 0; i < rows.length; i++) {
            var row = rows[i];
            row.is_store = 1;
            grid.api.memoryStore.create(row);
        }
        return true;
    }
});

</script>