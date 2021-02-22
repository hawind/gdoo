<script type="text/javascript">
$(document).ready(function() {
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
<div class="workflow" style="padding-top:10px;">
    {{$template}}
</div>
