<div class="form-panel">
    <div class="form-panel-header">
    <div class="pull-right">
    </div>
    {{$form['btn']}}

    @if($form['action'] == 'show')
    @else
        <a href="javascript:planDialog();" class="btn btn-sm btn-default">
            参照营销计划
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
var grid = null;
var table = '{{$form["table"]}}';
var master_id = '{{$form["row"]["id"]}}';

// grid初始化事件
gdoo.event.set('grid.produce_plan_data', {
    ready(me) {
        grid = me;
        grid.enableCellTextSelection = false;
        grid.enableRangeSelection = true;
        grid.suppressContextMenu = false;
        grid.numberEmptyDefaultValue = true;
        grid.defaultColDef.cellStyle = function(params) {
            if (params.node.rowPinned) {
                return;
            }
            var field = params.colDef.field;
            var value = params.value || 0;
            var style = {};
            if (field == "xqzc_num" && value > 0) {
                style = {'color':'red'};
            }
            if (field == "dkzc_num" && value > 0) {
                style = {'color':'red'};
            }
            if (field == "yxjh_num") {
                style = {'color':'blue'};
            }
            if (field == "yxjh_num1") {
                style = {'color':'blue'};
            }
            if (field == "yxjh_num2") {
                style = {'color':'blue'};
            }
            return style;
        };
        grid.dataKey = 'product_id';
    },
    onSaveAfter(res) {
        if (master_id > 0) {
            delete res.url;
        }
        return res;
    }
});

var planDialog = function () {
    var plan_date = $('#produce_plan_date').val().trim();
    if (plan_date == '') {
        toastrError('请选选择计划日期。');
        return;
    }
    var loading = layer.msg('数据提取中...', {
        icon: 16, shade: 0.1, time: 1000 * 120
    });
    $.get('{{url("produce/plan/orderPlan")}}', {plan_date: plan_date}, function(res) {
        layer.close(loading);
        if (res.status) {
            grid.api.setRowData([]);
            for (let i = 0; i < res.data.length; i++) {
                var row = res.data[i];
                grid.api.memoryStore.create(row);
            }
            grid.generatePinnedBottomData();
        } else {
            toastrError(res.data);
        }
    });
}

</script>