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
gdoo.event.set('grid.sample_apply_data', {
    ready(me) {
        me.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return true;
        }
    }
});

// 子表对话框
gdoo.event.set('sample_apply_data.product_id', {
    query(query) {
    },
    onSelect(row, selectedRow) {
        row.price = selectedRow.price1;
        return true;
    }
});
</script>