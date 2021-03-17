<style>
.modal-body { overflow:hidden; }
</style>

<div id="approach_fee_detail" class="ag-theme-balham" style="width:100%;height:280px;"></div>

<script>
(function($) {
    var gridDiv = document.querySelector("#approach_fee_detail");
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
        {cellClass:'text-center', field: 'sn', headerName: '单据编号', minWidth: 140},
        {cellClass:'text-center', field: 'date', headerName: '兑现日期', width: 100},
        {cellClass:'text-right', field:'fact_verification_cost', headerName: '兑现金额', width: 100},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    new agGrid.Grid(gridDiv, grid);
    grid.remoteData();

})(jQuery);
</script>