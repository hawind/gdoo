<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            
            <div class="alert alert-info alert-dismissable m-b-sm text-sm">
                本投诉表适用于公司产品质量、售后服务及其他方面的投诉，金额超过1000元的投诉由营销中心客服部打印单据交由副总经理审批。
            </div>

            {{$form['tpl']}}

        </form>
    </div>
</div>

<script>
var form_action = '{{$form["action"]}}';
(function ($) {
    // grid初始化事件
    gdoo.event.set('grid.customer_task_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'customer_id';
        }
    });
})(jQuery);

</script>