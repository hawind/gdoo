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

<div class="panel">
    <div class="panel-heading text-base">
        <i class="fa fa-file-text"></i> 工作主题
    </div>

    <div class="table-responsive">
        <table class="table">
            <tr>
                <th width="15%" align="right">工作主题</th>
                <td width="35%">{{$process['title']}}</td>

                <th width="15%" align="right">工作ID</th>
                <td width="35%">{{$process['id']}}</td>

            </tr>
            <tr>
                <th align="right">工作文号</th>
                <td>{{$process['name']}}</td>
                <th align="right">重要等级</th>
                <td>
                    {{:$levels = array(1=>'普通',2=>'重要',3=>'紧急')}}
                    {{$levels[$process['level']]}}
                </td>
            </tr>
            <tr>
                <th align="right">发起人</th>
                <td>{{get_user($process['start_user_id'], 'name')}}</td>
                <th align="right">工作描述</th>
                <td>{{$process['description']}}</td>
            </tr>
        </table>
    </div>
</div>

<div class="panel">

    <div class="panel-heading text-base">
        <i class="fa fa-list-alt"></i> 工作表单
    </div>

    <div class="table-responsive no-borders">
        <table class="table">
            <tr>
                <td style="background:url('{{$asset_url}}/images/form_sheetbg.png');">
                    <div style="width:960px;margin:0 auto;padding:15px;">
                        <div class="shadow">
                        	<span class="z corner_41"></span>
                        	<span class="y corner_12"></span>
                                <div style="margin:10px;">
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

<div class="panel">

    <div class="panel-heading text-base">
        <i class="icon icon-paperclip"></i> 公共附件区
    </div>

    <div class="panel-body b-t">
    @if($attach['auth']['view'] == true)
        @include('attachment/mobile/file')
    @endif
    </div>
</div>