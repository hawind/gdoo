<div id="dialog-delivery_plan" class="ag-theme-balham" style="width:100%;height:380px;"></div>

<script>
(function ($) {
    var params = JSON.parse('{{json_encode($query)}}');
    var multiple = params.multi == 0 ? false : true;
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowSelection = multiple ? 'multiple' : 'single';
    grid.suppressRowClickSelection = true;
    grid.defaultColDef.sortable = false;
    grid.defaultColDef.filter = false;
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.suppressNavigable = true;
    grid.columnDefs = [
        {cellClass:'text-left', field: 'customer_name', headerName: '客户名称', minWidth: 140},
        {cellClass:'text-center', field: 'sn', headerName: '订单号', width: 140},
        {cellClass:'text-right', type: 'number', field: 'fhjh_num', headerName: '数量', width: 80},
        {cellClass:'text-center', field: 'pay_dt', headerName: '打款日期', width: 100},
        {cellClass:'text-left',  field: 'export_country', headerName: '出口国家', width: 100},
    ];

    grid.defaultColDef.cellStyle1 = function(params) {
        if (params.node.rowPinned) {
            return;
        }
        var style = {};
        var value = params.value || 0;
        var field = params.colDef.field;
        if (field == "NeedNum" && value > 0) {
            style = {'color':'red'};
        }
        return style;
    };

    var gridDiv = document.querySelector("#dialog-delivery_plan");
    new agGrid.Grid(gridDiv, grid);

    // 读取数据
    grid.remoteData();

})(jQuery);
</script>