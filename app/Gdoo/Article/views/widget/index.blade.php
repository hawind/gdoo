<div class="wrapper-sm p-t-none">
    <div class="gdoo-list-grid">
        <div id="widget-article-index" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    </div>
</div>
<script>
(function ($) {
    var gridDiv = document.querySelector("#widget-article-index");
    var options = new agGridOptions();
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = {};
    options.defaultColDef.suppressMenu = true;
    var columnDefs = [
        {type:'sn', cellClass:'text-center', headerName: '序号', width: 60},
        {field: "name", cellClass:'text-center', headerName: '标题', width: 240},
        {field: "created_at", cellClass:'text-center', type:'datetime', headerName: '发布时间', width: 140},
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
            top.addTab('article/article/show?id=' + params.data.id, 'article_article_show', '新闻公告');
        }
    };
    new agGrid.Grid(gridDiv, options);
    options.remoteData({page: 1});
    gdoo.widgets['article_widget_index'] = options;
})(jQuery);
</script>