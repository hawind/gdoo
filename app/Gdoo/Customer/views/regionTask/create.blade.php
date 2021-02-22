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
var form_action = '{{$form["action"]}}';
(function ($) {
    // grid初始化事件
    gdoo.event.set('grid.customer_region_task_data', {
        init(me) {
            me.enableCellTextSelection = false;
            me.enableRangeSelection = true;
            me.suppressContextMenu = false;
        },
        ready(me) {
            me.dataKey = 'region_id';
        }
    });
})(jQuery);

</script>