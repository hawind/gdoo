<style>
.modal-body { overflow:hidden; }
</style>

<div id="approach_product" class="ag-theme-balham" style="width:100%;height:320px;"></div>

<script>
(function($) {
    var gridDiv = document.querySelector("#approach_product");
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';

    var params = JSON.parse('{{json_encode($query)}}');

    grid.remoteParams = params;
    grid.rowMultiSelectWithClick = false;
    grid.rowSelection = 'multiple';
    // grid.autoColumnsToFit = false;
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = false;
    grid.columnDefs = [
        {cellClass:'text-center', headerName: '', type: 'sn', width: 40},
        {cellClass:'text-left', field: 'name', headerName: '产品名称', minWidth: 140},
        {cellClass:'text-center', field: 'spec', headerName: '规格型号', width: 100},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    new agGrid.Grid(gridDiv, grid);
    // 读取数据
    grid.remoteData();

})(jQuery);
</script>