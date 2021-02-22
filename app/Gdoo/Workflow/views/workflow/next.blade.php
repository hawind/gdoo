<div class="wrapper">
    <form class="form-horizontal" name="nextform" id="nextform" method="post">
    <div class="form-group">
        <label class="col-sm-2 control-label">选择下一步</label>
        <div class="col-sm-10">
            @if($rows)
            @foreach($rows as $row)
                <div class="radio">
                    <label class="i-checks i-checks-sm">
                        <input onclick="getStep();" type="radio" name="next_step_id" value="{{$row['id']}}"><i></i> @if($row['number']>0) 第 <span class="badge bg-primary">{{$row['number']}}</span>@endif {{$row['title']}}
                    </label>
                </div>
            @endforeach
            @endif
        </div>
    </div>

    <span id="next-step-box"></span>

    <input type="hidden" name="step_type" value="{{$step_type}}" />
    </form>
</div>

<script type="text/javascript">
function getStep()
{
    var myform = $('#myform,#nextform').serialize();
    $.post('{{url("step")}}', myform,function(res) {
    	$('#next-step-box').html(res.data);
    },'json');
}
</script>
