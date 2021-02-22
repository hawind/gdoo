<script type="text/javascript">
$(document).ready(function()
{
	$('tbody').on('change',function(i)
    {
		listView.rowUpdate(i);
	});
	{{$jsonload}}
});

// 工作流全局对象
var workFlow = {{$work['js']}};

// 工作流js定义区域
{{$js}}

</script>

<div class="panel">

    <div class="panel-heading text-base">
        <i class="fa fa-list-alt"></i> 工作表单
    </div>

    <div class="table-responsive">
        <table class="table no-border">
            <tr>
                <td style="border:0;background:url('{{$asset_url}}/images/form_sheetbg.png');">
                    <div style="width:960px;margin:0 auto;padding:15px;">
                        <div class="shadow">
                            <span class="z corner_41"></span>
                            <span class="y corner_12"></span>
                                <div class="workflow" style="margin:10px;">
                                   {{$template}}
                                </div>
                            <span class="z corner_34"></span>
                            <span class="y corner_23"></span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
