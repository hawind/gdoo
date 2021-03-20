<script src="{{$asset_url}}/vendor/dhtmlxgantt/dhtmlxgantt.js" type="text/javascript"></script>
<script src="{{$asset_url}}/vendor/dhtmlxgantt/dhtmlxgantt_marker.js" type="text/javascript"></script>
<script src="{{$asset_url}}/vendor/dhtmlxgantt/dhtmlxgantt_tooltip.js" type="text/javascript"></script>
<script src="{{$asset_url}}/vendor/dhtmlxgantt/locale_cn.js" type="text/javascript"></script>  
<link rel="stylesheet" href="{{$asset_url}}/vendor/dhtmlxgantt/dhtmlxgantt.css" type="text/css">
<style type="text/css">
html, body { 
	overflow: hidden;
}
.gantt_side_content.gantt_right {
    padding-left: 10px;
}

.gantt_task_line.gantt_selected {
	box-shadow: 0 0 5px #fff;
}

.gantt_container {
	border: 1px solid #fff;
	border-top: 1px solid #cecece;
}

.project-item {
	position: absolute;
	height: 8px;
	color: #fff;
	background-color: #57b4f6;
}
.project-item div {
	position: absolute;
}
.project-left, .project-right {
	top: 8px;
	background-color: transparent;
	border-style: solid;
	width: 0px;
	height: 0px;
}

.project-left {
	left: 0px;
	border-width: 0px 0px  8px 7px;
	border-top-color: transparent;
	border-right-color: transparent !important;
	border-bottom-color: transparent !important;
	border-left-color: #3399ff !important;
}

.project-right {
	right: 0px;
	border-width: 0px 7px 8px 0px;
	border-top-color: transparent;
	border-right-color: #3399ff;
	border-bottom-color: transparent !important;
	border-left-color: transparent;
}

.gantt_task_line {
	background-color: #3399ff;
	border-width: 0;
	border-radius: 0;
}

.gantt_task_line.done {
	background-color: #66cc33;
}

.gantt_task_line .gantt_task_progress {
	background-color: #197de1;
	border-width: 0;
	opacity: 0;
}

.gantt_grid_data .gantt_cell {
	border-right: 1px solid #ECECEC;
}

.gantt_grid_data .gantt_cell.gantt_last_cell {
	border-right: none;
}

.gantt_task .gantt_task_scale .gantt_scale_cell, .gantt_grid_scale .gantt_grid_head_cell{
	color:#5C5C5C;
}

.gantt_row, .gantt_cell {
	border-color:#cecece;
}
.gantt_grid_scale .gantt_grid_head_cell {
	border-right: 1px solid #cecece !important;
}
.gantt_grid_scale .gantt_grid_head_cell.gantt_last_cell  {
	border-right: none !important;
}

.gantt_tooltip {
	background-color: #383838;
    color: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 2px rgba(56, 56, 56, 0.25);
    word-break: break-all;
    white-space: pre-line;
}

</style>

<div class="panel no-border" id="{{$header['master_table']}}-controller">
	@include('task/index/header')
	<div id="gantt-view"></div>
</div>

<script type="text/javascript">

var project_id = "{{(int)$project['id']}}";
var params = {project_id:project_id};

gantt.config.columns = [
	{name:"name", label:"任务列表", tree:true, width:'*', resize: true}
];
gantt.config.scale_unit = 'month';
gantt.config.date_scale = '%Y - %m';
gantt.config.scale_height = 50;
gantt.config.link_line_width = 1;
gantt.config.row_height = 28;
gantt.config.task_height = 16;
gantt.config.grid_resize = true;
gantt.config.drag_links = false;
gantt.config.drag_progress = false;
gantt.config.min_column_width = 60;
gantt.config.duration_unit = 'day';
gantt.config.grid_width = 220;
gantt.config.api_date = gantt.config.xml_date = '%Y-%m-%d %H:%i';
gantt.config.show_links = false;
gantt.config.order_branch = true;

// gantt.config.order_branch_free = true;
/*
var date_to_str = gantt.date.date_to_str(gantt.config.api_date);
var today = new Date();
gantt.addMarker({
	start_date: today,
	css: "today",
	text: "今天",
	title:"今天: "+ date_to_str(today)
});
*/

gantt.config.subscales = [
	{unit:"day", step:1, date:"%d %D"}
];

gantt.config.types.project = 'item';

gantt.config.type_renderers['item'] = function(task) {

	var el = document.createElement('div');
	el.setAttribute(gantt.config.task_attribute, task.id);
	var size = gantt.getTaskPosition(task);

	el.innerHTML = '<div class="project-left"></div><div class="gantt_task_content"></div><div class="project-right"></div>';
	el.className = 'project-item';
	el.style.left = size.left + 'px';
	el.style.top = size.top + 6 + 'px';
	el.style.width = size.width + 'px';
	return el;
};

gantt.templates.task_class = function(start, end, task) {
	if(task.progress == 1) {
		return 'done';
	}
};

gantt.templates.task_text = function() {
	return '';
};

gantt.templates.rightside_text = function(start, end, task) {
	return task.user_name;
};

gantt.templates.tooltip_text = function(start, end, task) {
	if(task.type == 'task' || task.type == 'subtask') {
		return '<div>任务: '+task.name+'</div><div>执行者: ' + (task.user_name || '无') + '</div><div>参与者: ' + (task.users || '无') + '</div><div>开始时间: ' + gantt.templates.tooltip_date_format(start) + '</div><div>结束时间: '+gantt.templates.tooltip_date_format(end) + '</div><div>备注: '+task.remark+'</div>';
	}
};

gantt.attachEvent('onTaskDblClick', function (task_id) {
	var task = gantt.getTask(task_id);
	if(task.type == 'item') {
		editItem(task);
	}
	if(task.type == 'task') {
		editTask(task);
	}
	if(task.type == 'subtask') {
		editSubTask(task);
	}
});

gantt.attachEvent('onBeforeRowDragEnd', function(task_id, parent, index) {

	var task = gantt.getTask(task_id);
	if(task.option_delete == 0) {
		return false;
	}

    var data = gantt.getSiblings(task_id);
	$.post('{{url("sort")}}', {id:task_id,parent_id:task.parent,sort:data}, function(res) {
		toastrSuccess('恭喜您，任务排序成功。');
	}, 'json');

	return true;

});

gantt.attachEvent('onBeforeTaskDrag', function(task_id, mode, e) {
	var task = gantt.getTask(task_id);
	if(task.option_delete == 0) {
		return false;
	}
    return true;
});

gantt.attachEvent('onAfterTaskDrag', function(task_id, mode, e) {
	var task = gantt.getTask(task_id);
	var data = {id: task.id,progress: task.progress};
	var date_to_str = gantt.date.date_to_str(gantt.config.api_date);
	data.start_date = date_to_str(task.start_date);
	data.end_date = date_to_str(task.end_date);
	$.post('{{url("drag")}}', data, function(res) {
		gantt.render();
	}, 'json');
});

gantt._do_autosize = function() {
	// 设置高度
	var height = $('#gantt-wrapper').outerHeight();
	var iframeHeight = $(window).height();
	$('#gantt-view').height(iframeHeight - height - 102 + 'px');

	var resize = this._get_resize_options();
	var boxSizes = this._get_box_styles();
	
	if(resize.y) {
		var reqHeight = this._calculate_content_height();
		if(boxSizes.borderBox) {
			reqHeight += boxSizes.vertPaddings;
		}
		this._obj.style.height = reqHeight + 'px';
	}
	if(resize.x) {
		var reqWidth = this._calculate_content_width();
		if(boxSizes.borderBox) {
			reqWidth += boxSizes.horPaddings;
		}
		this._obj.style.width = reqWidth + 'px';
	}
};

gantt.init("gantt-view");
gantt.load(app.url('project/task/index', params));

function dataReload() {
	gantt.clearAll();
	gantt.load(app.url('project/task/index', params));
}

</script>

@include('task/index/js')