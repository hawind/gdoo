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
gdoo.event.set('grid.purchase_order_data', {
    ready(me) {
        me.dataKey = 'product_id';
    }
});
</script>