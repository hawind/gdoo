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
(function ($) {
    // 选择客户事件
    gdoo.event.set('customer_tax.customer_id', {
        query(params) {
        },
        onSelect(row) {
            if (row) {
                $('#customer_tax_class_id').val(row.class_id);
                $('#customer_tax_class_id_text').val(row.class_id_name);

                $('#customer_tax_department_id').val(row.department_id);
                $('#customer_tax_department_id_text').val(row.department_id_name);
                return true;
            }
        }
    });
})(jQuery);

</script>