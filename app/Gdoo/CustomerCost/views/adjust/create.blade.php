<div class="form-panel">
    <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            {{$form['tpl']}}
            <input type="hidden" name="customer_cost[type_id]" value="86">
        </form>
    </div>
</div>

<script>
var table = '{{$form["table"]}}';
(function($) {
    // grid初始化事件
    gdoo.event.set('grid.customer_cost_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'customer_id';
        }
    });

    // 子表对话框
    gdoo.event.set('customer_cost_data.customer_id', {
        query(query) {
        },
        onSelect(row, selectedRow) {
            return true;
        }
    });

})(jQuery);
</script>