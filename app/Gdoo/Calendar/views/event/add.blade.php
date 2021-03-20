<ul class="nav nav-tabs m-t padder" role="tablist">
	<li class="active"><a href="#tabs-1" data-toggle="tab">事件</a></li>
	<li><a href="#tabs-2" data-toggle="tab">重复</a></li>
	<li><a href="#tabs-3" data-toggle="tab">提醒</a></li>
	<li><a href="#tabs-4" data-toggle="tab">共享</a></li>
</ul>

<form method="post" id="myform" name="myform">

	<div class="tab-content">

	    <div class="tab-pane active" id="tabs-1">

        	<div class="table-responsive">

	        <table class="table table-form m-b-none">
				<tr>
					<td width="15%" align="right">主题</td>
					<td align="left">
						<input type="text" class="form-control input-sm" id="title" name="title" value="{{$options['title']}}" size="40" />
					</td>
				</tr>

				<tr>
					<td align="right">地点</td>
						<td align="left">
						<input type="text" class="form-control input-sm" name="location" value="{{$options['location']}}" size="40" />
					</td>
				</tr>

				<tr>
					<td align="right">日历</td>
						<td align="left">
						<select class="form-control input-inline input-sm" id="calendarid" name="calendarid">
							 @if($options['calendar_options']) @foreach($options['calendar_options'] as $calendar)
	                        	<option value="{{$calendar['id']}}" @if($calendar['id']==$options['calendarid'])  selected @endif >{{$calendar['displayname']}}</option>
	                         @endforeach @endif
	          			</select>
					</td>
				</tr>

				<tr>
					<td align="right">访问规则</td>
					<td>
					  	<select class="form-control input-inline input-sm" name="accessclass">
					 		@if($options['access_class_options']) @foreach($options['access_class_options'] as $key => $value)
							<option value="{{$key}}" @if($key==$options['accessclass'])  selected="selected" @endif >{{$value}}</option>
					 		@endforeach @endif
						</select>
						&nbsp;
						<label for="allday_checkbox" class="checkbox-inline"><input type="checkbox" id="allday_checkbox" name="allday" @if($options['allday']=='true') checked @endif> 全天事件</label>
					</td>
				</tr>

				<tr>
					<td align="right">开始时间</td>
						<td align="left">
						<span id="time">
							<input type="text" onfocus="datePicker({dateFmt:'yyyy-MM-dd'})"; class="form-control input-inline input-sm" name="from" value="{{$options['startdate']}}" />
							<input type="text" onfocus="datePicker({dateFmt:'HH:mm'})"; class="form-control input-inline input-sm" id="fromtime" name="fromtime" value="{{$options['starttime']}}" />
						</span>
					</td>
				</tr>

				<tr>
					<td align="right">结束时间</td>
					<td align="left">
							<input type="text" onfocus="datePicker({dateFmt:'yyyy-MM-dd'})"; class="form-control input-inline input-sm" name="to" value="{{$options['enddate']}}" />
							<input type="text" onfocus="datePicker({dateFmt:'HH:mm'})"; class="form-control input-inline input-sm" id="totime" name="totime" value="{{$options['endtime']}}" />
					</td>
				</tr>

				<tr>
					<td align="right">附件列表</td>
					<td align="left">
						@include('attachment/create')
					</td>
				</tr>

				<tr>
					<td align="right">描述</td>
					<td align="left">
						<textarea class="form-control" rows="3" name="description">{{$options['description']}}</textarea>
					</td>
				</tr>
			</table>
			</div>

	    </div>
	    <div class="tab-pane" id="tabs-2">

	        <table class="table table-form">
			<tr>
				<td width="15%" align="right">重复</td>
				<td>
					<select id="repeat" class="form-control input-sm" name="repeat">
						 @if($options['repeat_options']) @foreach($options['repeat_options'] as $key => $value)
							<option value="{{$key}}" @if($repeats['repeat']==$key)  selected="selected" @endif >{{$value}}</option>
						 @endforeach @endif
					</select>
				</td>
			</tr>
			<tbody id="advanced_options_repeating" style="display:none;">
				<tr id="advanced_month" style="display:none;">
					<td>&nbsp;</td>
					<td>
						<select class="form-control input-inline input-sm" id="advanced_month_select" name="advanced_month_select">
							 @if($options['repeat_month_options']) @foreach($options['repeat_month_options'] as $key => $value)
								<option value="{{$key}}" @if($repeats['repeat_month']==$key)  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="advanced_year" style="display:none;">
					<td>&nbsp;</td>
					<td>
						<select class="form-control input-inline input-sm" id="advanced_year_select" name="advanced_year_select">
							 @if($options['repeat_year_options']) @foreach($options['repeat_year_options'] as $key => $value)
								<option value="{{$key}}" @if($repeats['repeat_year']==$key)  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="advanced_weekofmonth" style="display:none;">
					<td>&nbsp;</td>
					<td id="weekofmonthcheckbox">
						<select class="form-control input-inline input-sm" id="weekofmonthoptions" name="weekofmonthoptions">
							 @if($options['repeat_weekofmonth_options']) @foreach($options['repeat_weekofmonth_options'] as $key => $value)
								<option value="{{$key}}" @if($repeats['repeat_weekofmonth']==$key)  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="advanced_weekday" style="display:none;">
					<td>&nbsp;</td>
					<td id="weeklycheckbox">
						<select class="form-control input-inline input-sm" id="weeklyoptions" name="weeklyoptions[]" multiple="multiple" style="width: 150px;" title="选择星期">
							 @if($options['repeat_weekly_options']) @foreach($options['repeat_weekly_options'] as $key => $value)
								<option value="{{$key}}" @if(in_array($key,$repeats['repeat_weekdays']))  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="advanced_byyearday" style="display:none;">
					<td>&nbsp;</td>
					<td id="byyeardaycheckbox">
						<select class="form-control input-inline input-sm" id="byyearday" name="byyearday[]" multiple="multiple" title="选择日">
							 @if($options['repeat_byyearday_options']) @foreach($options['repeat_byyearday_options'] as $key => $value)
								<option value="{{$key}}" @if(is_array($repeats['repeat_byyearday']) && in_array($key, $repeats['repeat_byyearday'])) selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select> 选择每年时间发生天数
						</td>
				</tr>

				<tr id="advanced_bymonthday" style="display:none;">
					<td>&nbsp;</td>
					<td id="bymonthdaycheckbox">
						<select class="form-control input-inline input-sm" id="bymonthday" name="bymonthday[]" multiple="multiple" title="选择日">
							 @if($options['repeat_bymonthday_options']) @foreach($options['repeat_bymonthday_options'] as $key => $value)
								<option value="{{$key}}" @if(is_array($repeats['repeat_bymonthday']) && in_array($key,$repeats['repeat_bymonthday'])) selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select> 选择每个月事件发生的天
					</td>
				</tr>

				<tr id="advanced_bymonth" style="display:none;">
					<td>&nbsp;</td>
					<td id="bymonthcheckbox">
						<select class="form-control input-inline input-sm" id="bymonth" name="bymonth[]" multiple="multiple" title="选择月份">
							 @if($options['repeat_bymonth_options']) @foreach($options['repeat_bymonth_options'] as $key => $value)
								<option value="{{$key}}" @if(is_array($repeats['repeat_bymonth']) && in_array($key,$repeats['repeat_bymonth']))  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="advanced_byweekno" style="display:none;">
					<td>&nbsp;</td>
					<td id="bymonthcheckbox">
						<select class="form-control input-inline input-sm" id="byweekno" name="byweekno[]" multiple="multiple" title="选择星期">
							 @if($options['repeat_byweekno_options']) @foreach($options['repeat_byweekno_options'] as $key => $value)
								<option value="{{$key}}" @if(is_array($repeats['repeat_byweekno']) && in_array($key,$repeats['repeat_byweekno']))  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select> 每年时间发生的星期
					</td>
				</tr>

				<tr>
					<td align="right">间隔</td>
					<td>
						<input type="number" class="form-control input-inline input-sm" min="1" size="4" max="1000" value="{{$repeats['repeat_interval']}}" name="interval">
					</td>
				</tr>

				<tr>
					<td align="right">结束</td>
					<td>
						<select class="form-control input-inline input-sm" id="end" name="end">
							 @if($options['repeat_end_options']) @foreach($options['repeat_end_options'] as $key => $value)
								<option value="{{$key}}" @if($repeats['repeat_end']==$key)  selected="selected" @endif >{{$value}}</option>
							 @endforeach @endif
						</select>
					</td>
				</tr>

				<tr id="byoccurrences" style="display:none;">
					<td>&nbsp;</td>
					<td>
						<input type="number" class="form-control input-inline input-sm" min="1" max="99999" id="until_count" name="byoccurrences" value="{{$repeats['repeat_count']}}"> 发生
					</td>
				</tr>

				<tr id="bydate" style="display:none;">
					<td>&nbsp;</td>
					<td>
						<input type="text" class="form-control input-inline input-sm" data-toggle="date" value="{{$repeats['repeat_date']}}">
					</td>
				</tr>
				</tbody>
			</table>

	    </div>
	    <div class="tab-pane" id="tabs-3">

	        <table class="table table-form">
			<tr>
				<td width="15%" align="right">提醒</td>
				<td>
					 @if($options['valarm_options']) @foreach($options['valarm_options'] as $key => $valarms)
					<div id="valarm_{{$key}}">
						<select class="form-control input-sm" id="valarm" name="valarm_{{$key}}">
							<option value=""> - </option>
							 @if($valarms) @foreach($valarms as $k => $v)
								<option value="{{$k}}" @if($options['valarm']==$k)  selected="selected" @endif >{{$v}}</option>
							 @endforeach @endif
						</select>
					</div>
					 @endforeach @endif
				</td>
			</tr>
			</table>

	    </div>

	    <div class="tab-pane" id="tabs-4">

	        <table class="table table-form">
			<tr>
				<td width="15%" align="right">共享对象</td>
				<td>{{App\Support\Dialog::search($share, 'id=receive_id&name=receive_name&multi=1')}}</td>
			</tr>
			</table>
	    </div>
	</div>
	<input type="hidden" name="lastmodified" value="{{$options['lastmodified']}}" />
	<input type="hidden" name="id" value="{{$options['id']}}" />
</form>

<script type="text/javascript">

$(document).ready(function()
{
	looktime();
	@if($repeats['repeat'])
	    repeat('repeat');
	@endif
	
	@if($repeats['repeat_end'])
	    repeat('end');
	@endif
	
	$('#end').change(function() {
		repeat('end');
	});
	$('#repeat').change(function() {
		repeat('repeat');
	});
	$('#advanced_year').change(function() {
		repeat('year');
	});
	$('#advanced_month').change(function() {
		repeat('month');
	});

	$('#allday_checkbox').click(function() {
		looktime();
	});
});

function looktime() {

	$("#valarm_time").css('display', '');
	$("#valarm_day").css('display', 'none');

	if ($('#allday_checkbox').is(':checked')) {
		$("#fromtime").attr('disabled',true).addClass('disabled');
		$("#totime").attr('disabled',true).addClass('disabled');
		$("#valarm_time").css('display', 'none');
		$("#valarm_day").css('display', '');
	} else {
		$("#fromtime").attr('disabled',false).removeClass('disabled');
		$("#totime").attr('disabled',false).removeClass('disabled');
		$("#valarm_time").css('display', '');
		$("#valarm_day").css('display', 'none');
	}
}

function repeat(key) {
	if (key == 'end') {
		$('#byoccurrences').css('display', 'none');
		$('#bydate').css('display', 'none');
		if ($('#end option:selected').val() == 'count') {
			$('#byoccurrences').css('display', '');
		}
		if ($('#end option:selected').val() == 'date') {
			$('#bydate').css('display', '');
		}
	}
	if (key == 'repeat') {
		$('#advanced_month').css('display', 'none');
		$('#advanced_weekday').css('display', 'none');
		$('#advanced_weekofmonth').css('display', 'none');
		$('#advanced_byyearday').css('display', 'none');
		$('#advanced_bymonth').css('display', 'none');
		$('#advanced_byweekno').css('display', 'none');
		$('#advanced_year').css('display', 'none');
		$('#advanced_bymonthday').css('display', 'none');

		$('#advanced_options_repeating').css('display', '');

		if ($('#repeat option:selected').val() == 'monthly') {
			$('#advanced_month').css('display', '');
			repeat('month');
		}

		if ($('#repeat option:selected').val() == 'weekly') {
			$('#advanced_weekday').css('display', '');
		}

		if ($('#repeat option:selected').val() == 'yearly') {
			$('#advanced_year').css('display', '');
			repeat('year');
		}

		if ($('#repeat option:selected').val() == 'doesnotrepeat') {
			$('#advanced_options_repeating').css('display', 'none');
		}
	}

	if (key == 'month') {
		$('#advanced_weekday').css('display', 'none');
		$('#advanced_weekofmonth').css('display', 'none');
		if ($('#advanced_month_select option:selected').val() == 'weekday') {
			$('#advanced_weekday').css('display', '');
			$('#advanced_weekofmonth').css('display', '');
		}
	}

	if (key == 'year') {
		$('#advanced_weekday').css('display', 'none');
		$('#advanced_byyearday').css('display', 'none');
		$('#advanced_bymonth').css('display', 'none');
		$('#advanced_byweekno').css('display', 'none');
		$('#advanced_bymonthday').css('display', 'none');
		if ($('#advanced_year_select option:selected').val() == 'byyearday') {
			//$('#advanced_byyearday').css('display', '');
		}
		if ($('#advanced_year_select option:selected').val() == 'byweekno') {
			$('#advanced_byweekno').css('display', '');
		}
		if ($('#advanced_year_select option:selected').val() == 'bydaymonth') {
			$('#advanced_bymonth').css('display', '');
			$('#advanced_bymonthday').css('display', '');
			$('#advanced_weekday').css('display', '');
		}
	}
}
</script>
