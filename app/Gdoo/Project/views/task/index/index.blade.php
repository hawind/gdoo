<div class="panel" id="{{$header['master_table']}}-controller">
    @include('task/index/header')
	<div class="gdoo-list-grid">
		<div id="jqgrid-table" class="ag-theme-balham" style="width:100%;"></div>
	</div>
</div>

<script>
var grid = null;
var project_id = "{{(int)$project['id']}}";
var params = {project_id:project_id};
var auth_id = '{{auth()->id()}}';

(function($) {
    grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowSelection = 'multiple';

    grid.columnDefs = [
        {field: "id", hide: true},
	    {field: "type", hide: true},
	    {field: "option_edit", hide: true},
	    {field: "option_delete", hide: true}
    ];

    grid.autoGroupColumnDef = {
        headerName: '任务',
        width: 250,
        cellRendererParams: {
            checkbox: false,
            suppressCount: false,
        }
    };
    grid.treeData = true;
    grid.groupDefaultExpanded = -1;
    
    grid.getDataPath = function(data) {
        return data.tree_path;
    };

    function progressRenderer(params) {
        var data = params.data;
        if (data.type == 'task' || data.type == 'subtask') {
            if (params.value == 1) {
                return '<div class="label label-success">已完成</div>';
            } else {
                return '<div class="label label-' + (auth_id == data.user_id ? 'danger' : 'info') + '">进行中</div>';
            }
        }
        return '';
    }

    function durationRenderer(params) {
        if (params.value) {
            return '<span class="hinted" title="任务持续' + params.value + '">' + params.value + '</span>';
        }
        return '';
    }

    grid.columnDefs.push(
        {cellClass:'text-center', sortable: false, field: 'user_name', headerName: '执行者', width: 140},
        {cellClass:'text-center', sortable: false, field: 'users', headerName: '参与者', minWidth: 200},
        {cellClass:'text-center', cellRenderer: progressRenderer, sortable: false, field: 'progress', headerName: '状态', width: 100},
        {cellClass:'text-center', sortable: false, field: 'start_dt', headerName: '开始时间', width: 120},
        {cellClass:'text-center', sortable: false, field: 'end_dt', headerName: '结束时间', width: 120},
        {cellClass:'text-center', cellRenderer: durationRenderer, sortable: false, field: 'duration_date', headerName: '持续时间', width: 100},
        {cellClass:'text-center', sortable: false, field: 'created_dt', headerName: '创建时间', width: 140},
        //{cellClass:'text-center', field: 'id', headerName: 'ID', width: 80}
    );

    grid.onRowDoubleClicked = function (row) {
        var data = row.data;
        if(data.type == 'item') {
            editItem(data);
        }
        if(data.type == 'task') {
            editTask(data);
        }
        if(data.type == 'subtask') {
            editSubTask(data);
        }
    };

    var gridDiv = document.querySelector("#jqgrid-table");
    gridDiv.style.height = getPanelHeight(11);

    new agGrid.Grid(gridDiv, grid);
    grid.remoteData();

})(jQuery);

function dataReload() {
    params.page = 1;
    grid.remoteData(params);
}

function getTask(id) {
	return grid.api.getRowNode(id);
}
</script>

@include('task/index/js')