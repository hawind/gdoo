<style type="text/css">
body { 
	background-color: #fff;
}
table,
table td, 
table th {
	border-color: #222 !important;
}
</style>
<style type="text/css">
@media screen and (max-width: 767px) {
    .table-responsive td, .table-responsive th {
        white-space: normal !important;
    }
}
</style>

<script type="text/javascript">
$(function() {
	$('tbody').on('change',function(i) {
		listView.rowUpdate(i);
	});
	{{$jsonload}}
});

// 工作流全局对象
var workFlow = {{$work['js']}};

// 工作流js定义区域
{{$js}}
</script>
<div class="workflow">
{{$template}}
</div>
