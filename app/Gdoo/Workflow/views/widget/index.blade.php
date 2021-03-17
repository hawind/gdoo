<div class="wrapper-sm p-t-none">
    <div class="gdoo-list-grid">
        <div id="workflow-widget" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    </div>
</div>

<script>
(function ($) {

    function datetimeFormatter(params) {
        return format_datetime(params.value);
    }
    function tabFormatter(params) {
        return '<a href="javascript:;" data-toggle="addtab" data-url="'+app.url('workflow/workflow/edit', {process_id: params.data.id})+'" data-id="workflow_workflow_edit" data-name="待办流程">' + params.value + '</a>';
    }

    var gridDiv = document.querySelector("#workflow-widget");
    var options = new agGridOptions();
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = {};
    var columnDefs = [
        {suppressMenu: true, type:'sn', cellClass:'text-center', headerName: '序号', width: 60},
        {suppressMenu: true, field: "title", headerName: '流程主题', width: 260},
        {suppressMenu: true, sortable: false, cellClass:'text-center', field: "step_title", headerName: '当前步骤', width: 160},
        {suppressMenu: true, valueFormatter: datetimeFormatter, sortable: false, field: "turn_time", headerName: '交办时间', width: 160},
        //{suppressMenu: true,field: "id", cellClass:'text-center', headerName: 'ID', width: 80}
    ];

    options.onGridReady = function(params) {
    };

    options.columnDefs = columnDefs;
    options.onRowDoubleClicked = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        if (params.data == undefined) {
            return;
        }
        if (params.data.id > 0) {
            top.addTab('workflow/workflow/edit?process_id=' + params.data.id, 'workflow_workflow_edit', '待办流程');
        }
    };
    new agGrid.Grid(gridDiv, options);

    options.remoteData({page: 1});

    gdoo.widgets['workflow_widget_index'] = options;
})(jQuery);
</script>