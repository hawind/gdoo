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
gdoo.event.set('grid.customer_apply_brand', {
    ready(me) {
        me.dataKey = 'sale_money';
        me.suppressContextMenu = false;
    }
});

gdoo.event.set('grid.customer_apply_grid', {
    ready(me) {
        me.dataKey = 'sale_quantity';
        me.suppressContextMenu = false;
    }
});

gdoo.event.set('grid.customer_apply_category', {
    ready(me) {
        me.dataKey = 'category_id';
        me.suppressContextMenu = false;
    }
});
</script>