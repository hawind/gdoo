<style>
#order-index-widget .ag-header-cell-label { 
    justify-content: left !important;
}
</style>
<div class="wrapper-sm p-t-none">
    <div class="gdoo-list-grid">
        <div id="order-index-widget" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    </div>
</div>
<script>
(function ($) {
    var gridDiv = document.querySelector("#order-index-widget");
    var options = new agGridOptions();
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = {};
    var columnDefs = [{
        field: 'title', 
        cellClass:'text-left',
        cellRenderer: 'htmlCellRenderer',
        headerName: '统计', 
        suppressMenu: true,
        minWidth: 160
    }];
    options.columnDefs = columnDefs;
    new agGrid.Grid(gridDiv, options);
    // 读取数据
    options.remoteData({page: 1});
    gdoo.widgets['order_widget_index'] = options;
})(jQuery);
</script>