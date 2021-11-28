<style>
.modal-body { overflow:hidden; }
#planTotal .ag-root-wrapper {
    border-left-width: 1px;
    border-right-width: 1px;
}
</style>

<div class="wrapper-xs" id="plan_total-controller">
    <a class="btn btn-sm btn-default" data-toggle="plan_total" data-action="export"><i class="fa fa-share"></i> 导出</a>
    <div id="planTotal" class="ag-theme-balham m-t-xs" style="width:100%;height:340px;"></div>
</div>

<script>
var $planTotal = null;
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    var gridDiv = document.querySelector("#planTotal");
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowMultiSelectWithClick = false;
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = false;
    grid.columnDefs = [
        {cellClass:'text-center', headerName: '', field: 'sn', type: 'sn', suppressSizeToFit: true, width: 40},
        {cellClass:'text-left', field: 'category_name', headerName: '物料分类', width: 100},
        {cellClass:'text-left', field: 'material_name', headerName: '物料名称', width: 80},
        {cellClass:'text-right', field:'material_num', headerName: '数量', width: 80, calcFooter: "sum", type: "number"},
        {cellClass:'text-right', field:'total_num', headerName: '计划用料数量', width: 80, calcFooter: "sum", type: "number"},
    ];
    new agGrid.Grid(gridDiv, grid);
    grid.remoteData();

    $('#plan_total-controller').on('click', '[data-toggle="plan_total"]', function() {
        var data = $(this).data();
        if (data.action == 'export') {
            LocalExport(grid, '用料计划总量');
        }
    });

})(jQuery);
</script>