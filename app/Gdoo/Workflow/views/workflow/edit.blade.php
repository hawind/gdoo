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
        <a href="javascript:;" onclick="draft();" class="btn btn-sm btn-dark"><i class="icon icon-floppy-saved"></i> 保存草稿</a>
        @if($work['step_number'] == 1)
            <a href="javascript:;" onclick="nextStep();" class="btn btn-sm btn-info"><i class="icon icon-chevron-right"></i> 转下一步</a>
        @else
            <a href="javascript:;" onclick="nextStep();" class="btn btn-sm btn-info"><i class="fa fa-check-circle"></i> 审批</a>
        @endif
        @if($work['last'] > 0 && $work['opflag'] == 1)
            <a href="javascript:;" onclick="lastStep();" class="btn btn-sm btn-default"><i class="fa fa-chevron-left"></i> 退回</a>
        @endif
        @if($work['deny'] > 0 && $work['opflag'] == 1)
            <a href="javascript:;" class="btn btn-sm btn-danger"><i class="icon icon-ban-circle"></i> 拒绝</a>
        @endif
        @if($work['print'] > 0)
            <a target="_blank" href="{{url('print')}}?process_id={{$process['id']}}" class="btn btn-sm btn-primary"><i class="icon icon-print"></i> 打印</a>
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

<form name="myform" id="myform" method="post">

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

<div class="panel">

    <div class="panel-heading text-base">
        <i class="fa fa-comments"></i> 会签意见区
    </div>
    <!--
        <ul class="list-group list-group-lg no-bg auto">

          <li class="list-group-item clearfix">
            <span class="pull-left m-r">
                <h6>会签步骤 <span class="badge bg-dark">1</span></h6>
                
            </span>
            <span class="clear">
              <span>管理员 <span class="text-muted">2015-03-18</span> 说</span>
              <small class="text-muted clear text-ellipsis">我是会签</small>
            </span>
          </li>

        </ul>
        <div class="clearfix panel-footer">
            <span class="pull-left m-r">
                <h6>当前步骤 <span class="badge bg-info">{{$work['step_number']}}</span></h6>
            </span>
            <span class="clear">
                <textarea rows="3" name="feedback[content]" placeholder="输入会签内容" class="form-control"></textarea>
            </span>
        </div>
        -->
</div>
</div>
</div>

<input type="hidden" name="work_id" id="work_id" value="{{$work['work_id']}}">
<input type="hidden" name="step_id" id="step_id" value="{{$work['step_id']}}">
<input type="hidden" name="step_number" id="step_number" value="{{$work['step_number']}}">
<input type="hidden" name="data_id" id="data_id" value="{{$process['data_id']}}">
<input type="hidden" name="process_id" id="process_id" value="{{$process['id']}}">

</form>

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

<script type="text/javascript">
function draft()
{
    var myform = $('#myform').serialize();
    $.post('{{url("draft")}}', myform, function(res) {
        if (res.status) {
            toastrSuccess('草稿保存成功。');
        } else {
            toastrError('草稿保存失败。');
        }
    },'json');
}

function nextStep()
{
    var myform = $('#myform').serialize();
    $.post('{{url("check")}}', myform, function(res) {
        if (res.status) {
            if (res.data == null) {
                toastrError('转交步骤为空，或流程节点类型不正确。');
                return;
            }
            top.$.dialog({
                title: '工作办理',
                dialogClass:'modal-md',
                onShow:function() {
                    var self = this;
                    $.get('{{url("next")}}?'+$.param(res.data),function(html) {
                        if (html) {
                            self.html(html);
                        }
                    });
                },
                buttons:[{
                    text: '确定',
                    'class': 'btn-primary',
                    click: function() {
                        var me = this;
                        var nextform = top.$('#nextform').serialize();
                        var myform = $('#myform').serialize();
                        $.post('{{url("next")}}', nextform + '&' + myform, function(res1) {
                            if (res1.status) {
                                top.$(me).dialog('close');
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                                parent.location.reload();
                            } else {
                                toastrError(res1.data);
                            }
                        },'json');
                    }
                },{
                    text: '取消',
                    'class': 'btn-default',
                    click: function() {
                        top.$(this).dialog('close');
                    }
                }]
            });
        } else {
            toastrError(res.data);
        }
    },'json');
}

function lastStep()
{
    var data = {};
    data.work_id = $('#work_id').val();
    data.step_id = $('#step_id').val();

    top.$.dialog({
        title: '回退工作',
        dialogClass: 'modal-md',
        onShow: function() {
            var self = this;
            $.get('{{url("last")}}?'+$.param(data),function(res) {
                self.html(res);
            });
        },
        buttons:[{
            text: '确定',
            'class': 'btn-primary',
            click: function() {
                var nextform = top.$('#nextform').serialize();
                var myform = $('#myform').serialize();
                $.post('{{url("last")}}', nextform + '&' + myform, function(res1) {
                    if (res1.status) {
                        top.$(me).dialog('close');
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    } else {
                        toastrError(res1.data);
                    }
                },'json');
                /*
                var myform = $('#myform, #nextform').serialize();
                $.post('{{url("last")}}', myform, function(res1) {
                    if (res1.status) {
                        //location.href = res1.data;
                        top.$(me).dialog('close');
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                        parent.location.reload();
                    } else {
                        toastrError(res1.data);
                    }
                },'json');
                */
            }
        },{
            text: '取消',
            'class': 'btn-default',
            click: function() {
                top.$(this).dialog('close');
            }
        }]
    });
}
</script>
