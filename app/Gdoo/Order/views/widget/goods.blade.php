<div class="wrapper-sm p-t-none">
    <div class="gdoo-list-grid">
        <div id="widget-order-goods" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    </div>
</div>
<script>
(function ($) {
    function datetimeFormatter(params) {
        return format_datetime(params.value);
    }
    var gridDiv = document.querySelector("#widget-order-goods");
    var options = new agGridOptions();
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = {};
    var columnDefs = [
        {suppressMenu: true, type:'sn', cellClass:'text-center', headerName: '序号', width: 60},
        {suppressMenu: true, field: "sn", cellClass:'text-center', headerName: '订单编号', width: 160},
        {suppressMenu: true, sortable: false, cellClass:'text-center', field: "name", headerName: '客户名称', width: 160},
        {suppressMenu: true, sortable: false, cellClass:'text-center', field: "freight_arrival_date", headerName: '预计到货日期', width: 140},
    ];
    options.columnDefs = columnDefs;
    options.onRowDoubleClicked = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        if (params.data == undefined) {
            return;
        }
        if (params.data.id > 0) {
            top.addTab('stock/delivery/show?id=' + params.data.id, 'stock_delivery_show', '发货单');
        }
    };
    new agGrid.Grid(gridDiv, options);
    options.remoteData({page: 1});
    gdoo.widgets['order_widget_goods'] = options;
})(jQuery);
</script>