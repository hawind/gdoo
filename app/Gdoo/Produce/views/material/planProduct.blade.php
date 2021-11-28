<style>
.modal-body { overflow:hidden; }
#planProduct .ag-root-wrapper {
    border-left-width: 1px;
    border-right-width: 1px;
}
</style>

<div class="wrapper-xs">
    <div id="planProduct" class="ag-theme-balham" style="width:100%;height:240px;"></div>
</div>

<script>
var $planProduct = null;
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    var gridDiv = document.querySelector("#planProduct");
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowMultiSelectWithClick = false;
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = false;
    grid.columnDefs = [
        {cellClass:'text-center', headerName: '', field: 'sn', type: 'sn', suppressSizeToFit: true, width: 40},
        {cellClass:'text-left', field: 'product_name', headerName: '产品名称', width: 140},
        {cellClass:'text-center', field: 'category_name', headerName: '物料分类', width: 120},
        {cellClass:'text-center', field: 'material_name', headerName: '物料名称', width: 100},
        {cellClass:'text-right', field:'material_num', headerName: '数量', width: 80, calcFooter: "sum", type: "number"},
        {cellClass:'text-right', field:'total_num', headerName: '计划用料数量', width: 100, calcFooter: "sum", type: "number"},
    ];
    new agGrid.Grid(gridDiv, grid);
    grid.remoteData();
})(jQuery);
</script>