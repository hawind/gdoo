<div class="gdoo-list-grid">
    <div id="customer-birthday-widget" class="ag-theme-balham" style="width:100%;height:200px;"></div>
</div>
<script>
(function ($) {
    var gridDiv = document.querySelector("#customer-birthday-widget");
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = {};
    var columnDefs = [
        {suppressMenu: true, field: "code", headerName: '客户编码', width: 120},
        {suppressMenu: true, field: "name", headerName: '客户名称', minWidth: 160},
        {suppressMenu: true, field: "head_name", headerName: '法人', width: 120},
        {suppressMenu: true, field: "head_phone", headerName: '法人手机', width: 120},
        {suppressMenu: true, field: "head_birthday", headerName: '法人生日', width: 120},
        {suppressMenu: true, field: "id", headerName: 'ID', width: 80}
    ];

    grid.onRowDoubleClicked = function(row) {
        top.addTab('customer/customer/show?id=' + row.data.id, 'customer_customer_show', '客户档案');
    }

    grid.columnDefs = columnDefs;
    new agGrid.Grid(gridDiv, grid);
    grid.remoteData({page: 1});

    gdoo.widgets['customer_widget_birthday'] = grid;

})(jQuery);
</script>