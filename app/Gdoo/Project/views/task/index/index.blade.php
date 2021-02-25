<div class="panel">

	<div class="wrapper-sm b-b">
		<span class="text-md">{{$project['name']}}</span> <span class="text-muted">{{$project['description']}}</span>
	</div>

	<div class="wrapper-xs" id="index-wrapper">
		<form id="search-task-form" class="form-inline" name="mytasksearch" method="get">
		<div class="pull-right">
			<div class="btn-group">
				<a href="{{url('index', ['project_id' => $project['id'], 'tpl' => 'index'])}}" class="btn btn-sm btn-default @if($query['tpl'] == 'index') active @endif">列表</a>
				<a href="{{url('index', ['project_id' => $project['id'], 'tpl' => 'gantt'])}}" class="btn btn-sm btn-default @if($query['tpl'] == 'gantt') active @endif">甘特图</a>
			</div>
		</div>

		<a href="{{url($referer)}}" class="btn btn-sm btn-default"><i class="fa fa-reply"></i> 返回</a>

		@if(isset($access['add']))

			@if($permission['add_item'])
			<a href="javascript:addItem();" title="添加列表" class="hinted btn btn-sm btn-info"><i class="icon icon-plus"></i> 添加列表</a>
			@endif
			
			@if($permission['add_task'])
			<a href="javascript:addTask();" title="添加任务" class="hinted btn btn-sm btn-info"><i class="icon icon-plus"></i> 添加任务</a>
			@endif
			
		@endif

		@include('searchForm')
		</form>
	</div>

	<div class="list-jqgrid">
		<div id="jqgrid-table" class="ag-theme-balham" style="width:100%;"></div>
	</div>

</div>

<script>
var grid = null;
var project_id = "{{(int)$project['id']}}";
var params = {project_id:project_id};
var auth_id = '{{auth()->id()}}';

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
    gridDiv.style.height = getPanelHeight(12);

    new agGrid.Grid(gridDiv, grid);
    // 读取数据
    grid.remoteData();

    var search = $('#search-task-form').searchForm({
        data: JSON.parse('{{json_encode($search["forms"])}}'),
        init:function(e) {
            var self = this;
        }
    });
    search.find('#search-submit').on('click', function() {
        var query = search.serializeArray();
        params.page = 1;
        grid.remoteData(params);
        return false;
    });

})(jQuery);

function dataReload() {
    params.page = 1;
    grid.remoteData(params);
}

function getTask(id) {
    console.log(grid.api.getRowNode(id));
	return grid.api.getRowNode(id);
}
</script>

@include('task/index/js')