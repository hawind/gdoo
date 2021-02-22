<style type="text/css">
@media screen and (max-width: 767px) {
    .table-responsive td, .table-responsive th {
        white-space: normal !important;
    }
}
</style>

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

<div class="panel">
    <div class="panel-body">

        <input type="hidden" name="work_id" id="work_id" value="{{$work['work_id']}}">
        <input type="hidden" name="step_id" id="step_id" value="{{$work['step_id']}}">
        <input type="hidden" name="step_number" id="step_number" value="{{$work['step_number']}}">
        <input type="hidden" name="data_id" id="data_id" value="{{$process['data_id']}}">
        <input type="hidden" name="process_id" id="process_id" value="{{$process['id']}}">

        <a href="javascript:;" onclick="draft();" class="btn btn-dark"><i class="icon icon-floppy-saved"></i> 保存草稿</a>

        @if($work['step_number'] == 1)
            <a href="javascript:;" onclick="nextStep();" class="btn btn-info"><i class="icon icon-chevron-right"></i> 转下一步</a>
        @else
            <a href="javascript:;" onclick="nextStep();" class="btn btn-info"><i class="fa fa-check-circle"></i> 审批</a>
        @endif

        @if($work['last'] > 0 && $work['opflag'] == 1)
            <a href="javascript:;" onclick="lastStep();" class="btn btn-default"><i class="fa fa-chevron-left"></i> 退回</a>
        @endif

        @if($work['deny'] > 0 && $work['opflag'] == 1)
            <a href="javascript:;" class="btn btn-danger"><i class="icon icon-ban-circle"></i> 拒绝</a>
        @endif

    </div>
</div>

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

var donf   = null;
var doapp  = null;
var dopage = null;

window.onDeviceOneLoaded = function() {
    donf   = sm("do_Notification");
    doapp  = sm("do_App");
    dopage = sm("do_Page");
}

function draft()
{
    var myform = $('#myform').serialize();
    $.post('{{url("draft")}}', myform, function(res) {
        if (res.status) {
            donf.toast('保存成功。');
        } else {
            donf.toast('保存失败。');
        }
    },'json');
}

function nextStep()
{
    var myform = $('#myform').serialize();
    
    $.post('{{url("check")}}', myform, function(res) {
        if (res.status) {
            if (res.data == null) {
                donf.alert('转交步骤为空，或流程节点类型不正确。');
                return;
            }

            $.dialog({
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
                        var myform = $('#myform,#nextform').serialize();
                        $.post('{{url("next")}}', myform, function(res1) {
                            if (res1.status) {
                                donf.toast('审批成功。');
                                doapp.closePage('reload');
                            } else {
                                donf.alert(res1.data);
                            }
                        },'json');
                    }
                },{
                    text: '取消',
                    'class': 'btn-default',
                    click: function() {
                        $(this).dialog('close');
                    }
                }]
            });
        }
        else
        {
            donf.alert(res.data);
        }
    },'json');
}

function lastStep()
{
    var data = {};
    data.work_id = $('#work_id').val();
    data.step_id = $('#step_id').val();

    $.dialog({
        title: '回退工作',
        dialogClass:'modal-md',
        onShow:function() {
            var self = this;
            $.get('{{url("last")}}?'+$.param(data),function(res) {
                self.html(res);
            });
        },
        buttons:[{
            text: '确定',
            'class': 'btn-primary',
            click: function() {
                var myform = $('#myform,#nextform').serialize();
                $.post('{{url("last")}}', myform, function(res) {
                    if (res.status) {
                        donf.toast('退回成功。');
                        doapp.closePage('reload');
                    } else {
                        donf.alert(res.data);
                    }
                },'json');
            }
        },{
            text: '取消',
            'class': 'btn-default',
            click: function() {
                $(this).dialog('close');
            }
        }]
    });
}
</script>
