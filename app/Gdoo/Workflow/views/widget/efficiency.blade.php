<div class="jqgrid-wrap-no-border jqgrid-body-no-border">
    <table id="widget-workflow-efficiency"></table>
</div>

<style>
.jqgrid-wrap-no-border .ui-jqgrid-hdiv {
    border: 0 !important;
}
.jqgrid-wrap-no-border .ui-jqgrid-bdiv {
    border-left: 0;
    border-right: 0;
}
.jqgrid-wrap-no-border .ui-jqgrid-sdiv {
    border: 0 !important;
}

.jqgrid-body-no-border .ui-jqgrid-htable thead th.ui-th-column {
    border-right: 0 !important;
}
.jqgrid-body-no-border .ui-jqgrid-btable tbody tr.jqgrow td {
    border-right: 0 !important;
}
.jqgrid-body-no-border .ui-jqgrid-btable tbody tr.jqgrow td.jqgrid-rownum,
.jqgrid-body-no-border .ui-jqgrid-ftable tbody tr.footrow td.jqgrid-rownum {
    border-right: 1px solid #ddd !important;
}

.jqgrid-body-no-border .ui-jqgrid-ftable tbody tr.footrow td {
    border-right: 0 !important;
}
.jqgrid-body-no-border .ui-jqgrid-btable tbody tr.jqgrow td:last-child,
.jqgrid-body-no-border .ui-jqgrid-ftable tbody tr.footrow td:last-child {
    border-right: 1px solid #ddd !important;
}
.jqgrid-body-no-border .ui-jqgrid-htable thead th.ui-th-column.active {
    background-color: #fff !important;
}
</style>

<script>
(function($) {
    var t = $('#widget-workflow-efficiency');
    var model = [{
        name: 'user',
        index: 'user',
        label: '办理人',
        minWidth: 120,
        align: 'left'
    },{
        name: 'b',
        index: 'b',
        label: '超过3天',
        align: 'center',
        width: 90
    },{
        name: 'c',
        index: 'c',
        label: '超过30天',
        align: 'center',
        width: 90
    },{
        name: 'total', 
        index: 'total', 
        label: '合计', 
        align: 'center',
        width: 90,
        formatter: function(cellvalue, options, row) {
            return parseInt(row['b'] || 0) + parseInt(row['c'] || 0);
        }
    }];

    var footerCalculate = function() {
        var b = $(this).getCol('b', false, 'sum');
        var c = $(this).getCol('c', false, 'sum');
        $(this).footerData('set',{b: b, c: c, total: b + c});
    }

    t.jqGrid({
        caption: '',
        datatype: 'json',
        mtype: 'POST',
        url: '{{url("workflow/widget/efficiency")}}',
        colModel: model,
        rowNum: 1000,
        multiselect: false,
        viewrecords: true,
        rownumbers: true,
        height: 144,
        scrollOffset: 16,
        footerrow: true,
        loadonce: true,
        gridComplete: function() {
            footerCalculate.call(this);
        }
    });
})(jQuery);
</script>