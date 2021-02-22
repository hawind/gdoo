<div class="wrapper-xs">
    <form class="search-inline-form form-inline" id="plan_delivery_date" method="get">
      计划日期 <input name="date" id="plan_delivery_dt" value="{{$query['date']}}" type="text" autocomplete="off" data-toggle="date" class="form-control input-sm">
      <input name="id" value="{{$query['id']}}" type="hidden">
      <a id="plan_delivery_submit" class="btn btn-sm btn-default">
    <i class="fa fa-search"></i> 筛选</a>
    </form>
</div>

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
        {cellClass:'text-left', field: 'product_name', headerName: '产品名称', minWidth: 140},
        {cellClass:'text-center', field: 'product_spec', headerName: '规格型号', width: 140},
        {cellClass:'text-right', type: 'number', field: 'num', headerName: '订单数量', width: 120},
        {cellClass:'text-right', type: 'number', field: 'product_num', headerName: '订单可用量', width: 120},
        {cellClass:'text-right', type: 'number', field: 'need_num', headerName: '差额', width: 100},
        {cellClass:'text-right', type: 'number', field: 'day1', headerName: '今天生产', width: 120},
        {cellClass:'text-right', type: 'number', field: 'day2', headerName: '明天生产', width: 120},
        {cellClass:'text-right', type: 'number', field: 'day3', headerName: '后天生产', width: 120}
    ];

    grid.defaultColDef.cellStyle = function(params) {
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

    $('#plan_delivery_submit').on('click', function() {
        var v = $('#plan_delivery_dt').val();
        grid.remoteData({date: v});
    });

})(jQuery);
</script>