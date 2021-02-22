<script type="text/javascript">
$(function() {
	$("#color-picker").colorpicker({
		fillcolor: true,
		target: "#calendarcolor",
		change: function(obj, color) {
			$(obj).css({'background-color': color});
			$('#calendarcolor').val(color);
		},
		reset: function(obj, color) {
			$(obj).css({'background-color': color});
			$('#calendarcolor').val(color);
		}
	});
});
</script>

<form method="post" role="form" class="form-horizontal" id="myform" name="myform">

	<div class="form-group">
  		<label class="col-sm-2 control-label">日程名称</label>
  		<div class="col-sm-10">
    		<input type="text" class="form-control input-sm" id="displayname" name="displayname" value="{{$calendar['displayname']}}" />
  		</div>
	</div>

	<div class="form-group">
  		<label class="col-sm-2 control-label">日程描述</label>
  		<div class="col-sm-10">
  			<textarea rows="2" class="form-control input-sm" name="description">{{$calendar['description']}}</textarea>
  		</div>
	</div>

	<div class="form-group m-b-none">
  		<label class="col-sm-2 control-label">日程颜色</label>
  		<div class="col-sm-10">
			<div class="colorpicker-controller m-t-xs" title="选择颜色">
				<div id="color-picker" class="colorpicker" style="background-color:{{$calendar['calendarcolor']}};"></div>
			</div>
			<input type="hidden" id="calendarcolor" name="calendarcolor" value="{{$calendar['calendarcolor']}}">
  		</div>
	</div>
	<input type="hidden" id="id" name="id" value="{{$calendar['id']}}" />
</form>
