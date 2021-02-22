<style type="text/css">
.content-body {
    margin: 0;
}
@media screen and (max-width: 767px) {
    .table-responsive td, .table-responsive th {
        white-space: normal !important;
    }
}
</style>

<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        <a href="javascript:addMarketCost();" class="btn btn-sm btn-info"><i class="fa fa-check"></i> 提取费用</a>
        @if($work['print'] > 0)
        <a target="_blank" href="{{url('print', ['process_id' => $process['id']])}}" class="btn btn-sm btn-primary"><i class="icon icon-print"></i> 打印</a>
        @endif
    </div>
    <div class="form-panel-body">

<div class="panel">
    <div class="panel-heading text-base">
        <i class="fa fa-file-text"></i> 工作主题
    </div>

    <div class="table-responsive">
        <table class="table">
            <tr>
                <th align="right">工作主题</th>
                <td>{{$process['title']}}</td>

                <th align="right">工作ID</th>
                <td>{{$process['id']}}</td>

            </tr>
            <tr>
                <th width="15%" align="right">工作文号</th>
                <td width="35%">{{$process['name']}}</td>
                <th width="15%" align="right">重要等级</th>
                <td width="35%">
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

<div class="panel">

    <div class="panel-heading text-base">
        <i class="icon icon-paperclip"></i> 公共附件区
    </div>

    <div class="panel-body b-t">
    @if($attach['auth']['view'] == true)
        @include('attachment/file')
    @endif
    </div>
</div>

</div>
</div>

<script type="text/javascript">

function addCustomerCost() {
    formDialog({
        title: '新建费用记录',
        url: app.url('customerCost/cost/create', {content:'测试内容', money: '21212'}),
        id: 'customer_cost',
        table: 'customer_cost',
        dialogClass: 'modal-lg',
        success: function(res) {
            toastrSuccess(res.data);
            $(this).dialog("close");
        },
        error: function(res) {
            toastrError(res.data);
        }
    });
}

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