<script type="text/javascript">
var rows = {{json_encode($rows)}};
var condition = {
    // 检查公式
    checkExp:function(text)
    {
        if(text.indexOf("(") >= 0) {
            var num1 = text.split("(").length;
            var num2 = text.split(")").length;
            if(num1 != num2) {
                return false;
            }
        }
        return true;
    },
    /**
     * 增加左括号表达式，会断行
     */
    addLeft:function(id)
    {
        var oObj = getId('condition_list_'+id);
        var current = 0;
        //检查是否有条件
        if(oObj.options.length > 0)
        {
            for( var i = 0;i < oObj.options.length;i++) {
                if(oObj.options[i].selected) {
                    current = oObj.selectedIndex;
                    break;
                }
            }
            if(current == 0) {
                current = oObj.options.length-1;
            }
        } else {
            //有条件才能添加左括号表达式
            toastrError('请先添加条件，再选择括号', '步骤设计');
            return;
        }
        var sText = oObj.options[current].text;
        //已经有条件的话
        if( (sText.trim().substr(-3,3) == 'AND') || (sText.trim().substr(-2,2) == 'OR'))
        {
            toastrError('无法编辑已经存在关系的条件', '步骤设计');
            return;
        }

        var sRelation = getId('relation_'+id).value;
        if(sText.indexOf('(') >= 0)
        {
            if(!condition.CheckExp(sText))
            {
                toastrError('条件表达式书写错误,请检查括号匹配', '步骤设计');
                return;
            } else {
                sText = sText + " " + sRelation;
            }
        } else {
            sText = sText + " " + sRelation;
        }
        oObj.options[current].text = sText;
        var oMyop = document.createElement('option');
        oMyop.text = "(";
        var nPos = oObj.options.length;
        oObj.appendChild(oMyop,nPos);
    },
    /**
     * 增加右括号表达式
     */
    addRight:function(id)
    {
        var oObj = getId('condition_list_'+id);
        var current = 0;
        if(oObj.options.length > 0) {
            for (var i = 0;i < oObj.options.length;i++) {
                if(oObj.options[i].selected) {
                    current = oObj.selectedIndex;
                    break;
                }
            }
            if(current == 0) {
                current = oObj.options.length-1;
            }
        } else {
            toastrError('请先添加条件，再选择括号');
            return;
        }
        var sText = oObj.options[current].text;
        if((sText.trim().substr(-3,3)=='AND') || (sText.trim().substr(-2,2)=='OR'))
        {
            toastrError('无法编辑已经存在关系的条件');
            return;
        }
        if(sText.trim().substr(-1,1)=='(')
        {
            toastrError('请添加条件');
            return;
        }
        if(!condition.checkExp(sText))
        {
            sText = sText + ")";
        }
        oObj.options[current].text = sText;
    },
    add:function(id)
    {
        var sField = $('#field_'+id).val(),sCon = $('#condition_'+id).val(),sValue = $('#item_value_'+id).val();
        var bAdd = true;
        if(sField !=='' && sCon !=='' && sValue !=='')
        {
            var oObj = getId('condition_list_'+id);
            if( oObj.length > 0) {
                var sLength = oObj.options.length;
                var sText = oObj.options[sLength-1].text;
                if(!condition.checkExp(sText)) {
                    bAdd = false;
                }
            }

            if(sValue.indexOf("'") >= 0) {
                toastrError("值中不能含有'号");
                return;
            }

            // 字段有[]的时候不加`符号
            sField = sField.indexOf("[") == 0 ? sField : "`" + sField + "`";

            var sNewText = sField + " " + sCon + " '" + sValue + "'";
            for( var i=0;i<oObj.options.length;i++ ) {
                if( oObj.options[i].text.indexOf(sNewText)>=0 ) {
                    toastrError('条件重复');
                    return;
                }
            }
            var sRelation = $('#relation_'+id).val();
            if(bAdd)
            {
                var oMyop = document.createElement('option');
                var nPos = oObj.options.length;
                oMyop.text = sNewText;
                oObj.appendChild(oMyop,nPos);
                if(nPos > 0) {
                    oObj.options[nPos-1].text += " " + sRelation;
                }
            }
            else
            {
                if(oObj.options[sLength-1].text.trim().substr(-1,1)=='(')
                {
                    oObj.options[sLength-1].text += sNewText;
                } else {
                    oObj.options[sLength-1].text += " " + sRelation + " " + sNewText;
                }
            }
        } else {
            toastrError('请补充完整条件');
            return;
        }
    },
    deleted:function(id)
    {
        var oObj = getId('condition_list_'+id);
        for (var i = 0;i < oObj.options.length;i++ ) {
            if(oObj.options[i].selected) {
                if(typeof oObj.options[i+1] == 'undefined') {
                    if(typeof oObj.options[i-1] !== 'undefined') {
                        oObj.options[i-1].text = oObj.options[i-1].text.replace(/(AND|OR)$/,'');
                    }
                }
                oObj.removeChild(oObj.options[i]);
                i--;
            }
        }
    },
    clear:function(id) {
        $('#condition_list_'+id).empty();
    },
    //根据基本信息的下一步骤，设置《条件设置》tab的条件列表
    init:function()
    {
        var step = $('#select_next_step input:checked');
        $('#ctbody').empty();
        for(var i = 0; i < step.length; i++)
        {
            var id = step[i].value;
            var text = step[i].title;
            var node = getId('tpl').innerHTML;
            var html = node.replace(/\@id/g,id);
            html = html.replace(/\@text/g,text);
            $('#ctbody').append(html);

            var list = rows[id].condition.split("\n");
            for (var j = 0; j < list.length; j++) {
                if(list[j])
                {
                    $('#condition_list_'+id).append('<option>'+list[j]+'</option>');
                }
            }
        }
    },
    //弹窗回调保存事件
    save:function()
    {
        var formData = $('#my_form_step').serialize();
        var nextStep = $('#select_next_step input:checked');
        var condition = {};
        for (var i = 0; i < nextStep.length; i++)
        {
            var id = nextStep[i].value;
            var list = getId('condition_list_'+id).options;
            var rows = [];
            for (var j = 0; j < list.length; j++) {
                if(list[j].text) {
                    rows.push(list[j].text);
                }
            }
            // 空条件就赋值empty
            condition[id] = rows.length > 0 ? rows : 'empty';
        }
        var param = {conditions:condition};
        //console.log(formData);

        $.post("{{url()}}",formData+'&'+$.param(param),function(data) {
            if(data.status) {
                workStep.init();
                toastrSuccess('操作成功', '步骤设计');
            }
        },'json');
    }
}

function selectUser()
{
    var value = getId('select_user_type').value;
    if(value == '0') {
        $('#lock_info').html("允许修改办理人相关选项");
    }
    else {
       $('#lock_info').html("允许修改办理人相关选项及默认会签人");
    }

    $('#select_process_user').hide();
    $('#select_user').hide();
    $('#select_field_user').hide();

    if(value == 6)
        $('#select_user').show();
    if(value == 7)
        $('#select_field_user').show();
    if(value == 8)
		$('#select_process_user').show();
}

function getId(id) {
    return !id ? null : document.getElementById(id);
}

$(function()
{
    condition.init();

    selectUser();

    $("[data-toggle='tooltip']").tooltip();

    // 表单字段 checkbox 点击交互函数
    $('#write').click(function()
    {
        if(this.checked)
        {
            check('write',true);
            $('#secret').attr({'disabled':true,'checked':false});
            $('#check').attr('checked',false).removeAttr('disabled');
        } else {
            check('write',false);
            $('#secret').attr('checked',false).removeAttr('disabled');
            $('#check').attr({'disabled':true,'checked':false});
        }
    });

    $('#secret').click(function()
    {
        if(this.checked)
        {
            check('secret',true)
            $('#write').attr({'disabled':true,'checked':false});
        } else {
            check('secret',false);
            $('#write').attr('checked',false).removeAttr('disabled');
        }
    });

    $('#check').click(function()
    {
        if(this.checked) {
            check('check',true)
            check('micro',true)
        } else {
            check('check',false);
            check('micro',false);
        }
    });

    $("input[name='write[]']").click(function() {
        write_click(this);
        $('#write').removeAttr('disabled');
        if($('#write').attr('checked')==true) {
            $('#write').attr('checked',false)
        }
    });

    $("input[name='secret[]']").click(function() {
        secret_click(this);
        $('#secret').removeAttr('disabled');
        if($('#secret').attr('checked')==true){
            $('#secret').attr('checked',false)
        }
    });

    $("input[name='check[]']").click(function() {
        check_click(this);
        $('#check').removeAttr('disabled');
        if($('#check').attr('checked') == true) {
			$('#check').attr('checked',false)
        }
    });

    $("input[name='micro[]']").click(function() {
        $('#check').removeAttr('disabled');
        if($('#check').attr('checked')==true){
            $('#check').attr('checked',false)
        }
    });

    // 字段默认设置
    var process_str = '{{$field_select['write']}}';
    var parr = process_str.split(',');
    for( i=0;i<parr.length;i++ ) {
        if(parr[i]!=='') {
            $('#write_'+parr[i]).click();
            write_click(getId('write_'+parr[i]));
        }
    }
    var hidden_str = '{{$field_select['secret']}}';
    var harr = hidden_str.split(',');
    for(i=0;i<harr.length;i++) {
        if(harr[i]!=='') {
            $('#secret_'+harr[i]).click();
			secret_click(getId('secret_'+harr[i]));
        }
    }
    var micro_str = '{{$field_select['auto']}}';
    var marr = micro_str.split(',');
    for(i=0;i<marr.length;i++) {
        if(marr[i] !== '') {
            getId('micro_'+marr[i]).checked = true;
        }
    }
     @if($field_select['check'])
    var checkjson = jQuery.parseJSON('{{$field_select['check']}}');
    $.each(checkjson,function(i,n)
    {
        $('#regex_'+i).click();
        check_click(getId('regex_'+i));
        $('#regexList_'+i).val(n);
    });
     @endif
});

// 表单字段 checkbox 点击交互函数
//可写字段
function write_click(d)
{
    var id = $(d).attr('key');
    if(d.disabled == false)
    {
        if(d.checked)
        {
            $('#secret_'+id).attr('disabled',true);
            if($('#regex_'+id))
            {
                $('#regex_'+id).attr('checked',false).removeAttr('disabled');
                $('#regexList_'+id).hide();
            }
            if($('#micro_'+id))
            {
                $('#micro_'+id).attr('checked',false).removeAttr('disabled');
                $('#micro_'+id).parent().css('color','#000');
            }
        }
        else
        {
            $('#secret_'+id).attr('checked',false).removeAttr('disabled');
            if($('#regex_'+id))
            {
                $('#regex_'+id).attr({'disabled':true,'checked':false});
                $('#regexList_'+id).attr('disabled',true).hide();
            }
            if($('#micro_'+id))
            {
                $('#micro_'+id).attr({'disabled':true,'checked':false});
                $('#micro_'+id).parent().css('color','#a0a0a0');
            }
        }
    }
}
//字段验证
function check_click(d)
{
    if (d == null) {
        return;
    }
    var id = $(d).attr('key');
    if(d.disabled == false)
    {
        if(d.checked)
        {
            $('#regexList_'+id).removeAttr('disabled').show();
        }
        else
        {
            $('#regexList_'+id).attr('disabled',true).hide();
        }
    }
}
//保密字段
function secret_click(d)
{
    var id = $(d).attr('key');
    if(d.disabled == false)
    {
        if(d.checked) {
            $('#write_'+id).attr({'disabled':true,'checked':false});
        } else {
            $('#write_'+id).removeAttr('disabled').attr('checked',false);
        }
    }
}
//checkbox全选及反选操作
function check(desc, act)
{
    if(desc == 'write') {
        $("input[name='write[]']").each(function() {
            if(this.disabled == false) {
                this.checked = act;
            }
            write_click(this);
        })
    } else if(desc == 'secret') {
        $("input[name='secret[]']").each(function() {
            if(this.disabled == false) {
                this.checked = act;
            }
            secret_click(this);
        })
    } else if(desc == 'check') {
        $("input[name='check[]']").each(function() {
            if(this.disabled == false) {
                this.checked = act;
            }
            check_click(this);
        })
    } else if(desc == 'micro') {
        $("input[name='micro[]']").each(function() {
            if(this.disabled == false) {
                this.checked = act;
            }
        })
    }
}
</script>

<ul class="nav nav-tabs m-t padder" role="tablist">
    <li class="active"><a href="#tabs-1" data-toggle="tab">基本设置</a></li>
    <li><a href="#tabs-2" data-toggle="tab">办理权限</a></li>
    <li><a href="#tabs-3" data-toggle="tab">表单字段</a></li>
    <li><a href="#tabs-4" data-toggle="tab">条件设置</a></li>
</ul>

<form class="form-horizontal" action="{{url()}}" method="post" id="my_form_step" name="my_form_step">
<div class="tab-content">

    <div class="tab-pane active" id="tabs-1">

        <table class="table table-form">

            <tr>
                <td width="15%">流程名称</td>
                <td><input type="text" id="title" name="title" value="{{$row['title']}}" class="form-control input-sm"></td>
            </tr>

            <tr>
                <td>步骤序号</td>
                <td><input type="text" id="number" name="number" value="{{$row['number']}}" class="form-control input-sm"></td>
            </tr>

            <tr>
                <td>办理时限</td>
                <td><div class="input-group">
                        <span class="bg-white input-group-addon">小时</span>
                        <input type="text" id="timeout" name="timeout" value="{{$row['timeout']}}" class="form-control input-sm">
                        <span class="bg-white input-group-addon"><a href="javascript:;" class="hinted" title="允许为小数，表示接收工作后办理的时限，为空表示不限时。"><i class="icon icon-info-sign"></i></a></span>
                    </div>
                </td>
            </tr>

            <tr>
                <td>步骤类型</td>
                <td><select name="type" class="form-control input-sm" id="type">
                        <option value="1" @if($row['type']==1) selected @endif>普通节点</option>
                        <option value="2" @if($row['type']==2) selected @endif>开始节点</option>
                        <option value="3" @if($row['type']==3) selected @endif>结束节点</option>
                        <option value="4" @if($row['type']==4) selected @endif>子流程节点</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>其他选项</td>
                <td><label class="checkbox-inline"><input type="checkbox" name="last" value="1" @if($row['last']==1) checked @endif> 允许退回</label>&nbsp;
                    <label class="checkbox-inline"><input type="checkbox" name="deny" value="1" @if($row['deny']==1) checked @endif> 允许拒绝</label>&nbsp;
                    <label class="checkbox-inline"><input type="checkbox" name="print" value="1" @if($row['print']==1) checked @endif> 允许打印</label>
                </td>
            </tr>

            <tr>
                <td>提醒发起人</td>
                <td><div class="input-group">
                        <input name="notification_text[0]" type="text" class="form-control input-sm" value="{{$row['notification_text']['0']}}">
                        <span class="bg-white input-group-addon">
                            <label class="p-t-none checkbox-inline" title="短信提醒:发起人"><input type="checkbox" name="notification_type[0][]" @if(is_array($row['notification_type']['0']) && in_array(1,$row['notification_type']['0'])) checked @endif value="1"> 短信</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="邮件提醒:发起人"><input type="checkbox" name="notification_type[0][]" @if(is_array($row['notification_type']['0']) && in_array(2,$row['notification_type']['0'])) checked @endif value="2"> 邮件</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="即时通提醒:发起人"><input type="checkbox" name="notification_type[0][]" @if(is_array($row['notification_type']['0']) && in_array(3,$row['notification_type']['0'])) checked @endif value="3"> 即时通</label>
                        </span>
                    </div>
                </td>
            </tr>

            <tr>
                <td>提醒下一步经办人</td>
                <td><div class="input-group">
                        <input name="notification_text[1]" type="text" class="form-control input-sm" value="{{$row['notification_text']['1']}}">
                        <span class="bg-white input-group-addon">
                            <label class="p-t-none checkbox-inline" title="短信提醒:下一步经办人"><input type="checkbox" name="notification_type[1][]" @if(is_array($row['notification_type']['1']) && in_array(1,(array)$row['notification_type']['1'])) checked @endif value="1"> 短信</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="邮件提醒:下一步经办人"><input type="checkbox" name="notification_type[1][]" @if(is_array($row['notification_type']['1']) && in_array(2,(array)$row['notification_type']['1'])) checked @endif value="2"> 邮件</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="即时通提醒:下一步经办人"><input type="checkbox" name="notification_type[1][]" @if(is_array($row['notification_type']['1']) && in_array(3,(array)$row['notification_type']['1'])) checked @endif value="3"> 即时通</label>
                        </span>
                    </div>
                </td>
            </tr>

            <tr>
                <td>提醒下一步会签人</td>
                <td><div class="input-group">
                        <input name="notification_text[2]" type="text" class="form-control input-sm" value="{{$row['notification_text']['2']}}">
                        <span class="bg-white input-group-addon">
                            <label class="p-t-none checkbox-inline" title="短信提醒:下一步会签人"><input type="checkbox" name="notification_type[2][]" @if(is_array($row['notification_type']['2']) && in_array(1,$row['notification_type']['2'])) checked @endif value="1"> 短信</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="邮件提醒:下一步会签人"><input type="checkbox" name="notification_type[2][]" @if(is_array($row['notification_type']['2']) && in_array(2,$row['notification_type']['2'])) checked @endif value="2"> 邮件</label>&nbsp;
                            <label class="p-t-none checkbox-inline" title="即时通提醒:下一步会签人"><input type="checkbox" name="notification_type[2][]" @if(is_array($row['notification_type']['2']) && in_array(3,$row['notification_type']['2'])) checked @endif value="3"> 即时通</label>
                        </span>
                    </div>
                </td>
            </tr>

            <tr>
                <td>下一步骤</td>
                <td><div id="select_next_step">
                    @if($rows)
                    @foreach($rows as $k => $v)
                        <label class="checkbox-inline"><input type="checkbox" id="step_{{$v['id']}}" value="{{$v['id']}}" title="{{$v['title']}}"  @if(in_array($k,$row['joinArray'])) checked @endif disabled> {{$v['title']}}</label>&nbsp;&nbsp;
                    @endforeach
                    @endif
                </div>
                </td>
            </tr>

        </table>
    </div>

    <div class="tab-pane" id="tabs-2">

        <table class="table table-form">

        <tr>
            <td width="15%">经办权限</td>
            <td>{{App\Support\Dialog::search($row,'id=permission_id&name=permission_name&multi=1')}}</td>
        </tr>

        <tr>
            <td>选人过滤范围</td>
            <td><div class="input-group">
                    <select class="form-control input-sm" name="user_filter" id="user_filter">
                        <option value="0">选择指定办理人</option>
                        <option value="1">选择本部门</option>
                        <option value="3">选择上级部门</option>
                        <option value="4">选择下级部门</option>
                        <option value="2">选择本岗位</option>
                    </select>
                    <span class="bg-white input-group-addon"><a href="javascript:;" class="hinted" title="选人过滤规则在流程转交选择办理人员时生效。默认设置为只能选择指定办理人员"><i class="icon icon-info-sign"></i></a></span>
                </div>
            </td>
        </tr>

        <tr>
            <td>自动选人规则</td>
            <td><div class="input-group">
                    <select class="form-control input-sm" name="select_user_type" id="select_user_type" onchange="selectUser()">
                        <option value="0" @if($row['select_user_type']==0) selected @endif >不自动选择</option>
                        <option value="1" @if($row['select_user_type']==1) selected @endif >选择发起人</option>
                        <option value="9" @if($row['select_user_type']==9) selected @endif >选择发起人本部门主管</option>
                        <option value="10" @if($row['select_user_type']==10) selected @endif >选择发起人上级主管领导</option>
                        <option value="11" @if($row['select_user_type']==11) selected @endif >选择发起人上级分管领导</option>
                        <option value="12" @if($row['select_user_type']==12) selected @endif >选择发起人一级部门主管</option>
                        <option value="13" @if($row['select_user_type']==13) selected @endif >选择发起人直属领导</option>
                        <option value="2" @if($row['select_user_type']==2) selected @endif >选择经办人本部门主管</option>
                        <option value="3" @if($row['select_user_type']==3) selected @endif >选择经办人上级主管领导</option>
                        <option value="4" @if($row['select_user_type']==4) selected @endif >选择经办人上级分管领导</option>
                        <option value="5" @if($row['select_user_type']==5) selected @endif >选择经办人一级部门主管</option>
                        <option value="14" @if($row['select_user_type']==14) selected @endif >选择经办人直属领导</option>
                        <option value="6" @if($row['select_user_type']==6) selected @endif >选择指定人员</option>
                        <option value="7" @if($row['select_user_type']==7) selected @endif >选择表单字段</option>
                        <option value="8" @if($row['select_user_type']==8) selected @endif >选择指定步骤办理人</option>
                    </select>
                    <span class="bg-white input-group-addon">
                        <a href="javascript:;" class="hinted" title="自动选人规则,使流程办理人通过指定规则智能选择。默认设置为：不自动选择。注意，请同时设置好经办权限，自动选人规则才能生效。"> <i class="icon icon-info-sign"></i></a>
                    </span>
                </div>

                <div class="m-t" id="select_field_user" style="display:none;">
                    <label>根据表单字段选择办理人</label>
                    <select class="form-control input-sm" name="select_field_user">
                        @if($fields)
                        @foreach($fields as $k => $v)
                            <option value="{{$v['itemid']}}" @if($row['select_user_sign']==$v['itemid']) selected @endif>{{$v['title']}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div class="m-t" id="select_process_user" style="display:none;">
                    <label>选择此步骤第一次办理时的办理人</label>
                    <select class="form-control input-sm" name="select_process_user">
                        @if($rows)
                        @foreach($rows as $k => $v)
                            <option value="{{$k}}" @if($row['select_user_sign']==$k) selected @endif>{{$v['title']}}</option>
                        @endforeach 
                        @endif
                    </select>
                </div>

                <div class="m-t" id="select_user" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">主办人</label>
                        <div class="col-sm-10">
                            {{App\Support\Dialog::user('user','select_user_id',$row['select_user_id'], 0, 0)}}
                        </div>
                   </div>
                   <div class="form-group">
                        <label class="col-sm-2 control-label">会签人</label>
                        <div class="col-sm-10">
                            {{App\Support\Dialog::user('user','select_user_sign',$row['select_user_sign'], 1, 0)}}
                        </div>
                    </div>
                </div>

                <div id="lock" class="m-t">
                    <label id="lock_info"></label>
                    <select class="form-control input-sm" name="select_user_lock">
                        <option value="0" @if($row['select_user_lock'] == 0) selected @endif>允许</option>
                        <option value="1" @if($row['select_user_lock'] == 1) selected @endif>不允许</option>
                    </select>
                </div>

            </td>
        </tr>

        </table>

    </div>

    <div class="tab-pane" id="tabs-3">

		<table class="table m-b-none">
		<thead>
		    <tr>
			     <th align="left" width="140">字段名称</th>
			     <th align="left" width="140">控件类型</th>
			     <th align="left" style="width:80px;"><label class="checkbox-inline"><input type="checkbox" id="write"> 可写字段</label></th>
			     <th align="left" style="width:80px;"><label class="checkbox-inline"><input type="checkbox" id="secret"> 保密字段</label></th>
			     <th align="left" style="width:120px;"><label class="checkbox-inline"><input type="checkbox" id="check"> 字段规则</label></th>
	    	</tr>
	    </thead>
	   @if(isset($fields))
		    <tbody id="loop">
			<tr>
			    <td>[流程公共附件]</td>
			    <td><span class="label label-info">公共附件</span></td>
			    <td><label><input type="checkbox" name="write[]" key="attach" value="[attach@]" id="write_attach"></label></td>
			    <td><label><input type="checkbox" name="secret[]" value="[attach@]" key="attach" id="secret_attach"></label></td>
			    <td>无</td>
			</tr>

			@if($fields)
            @foreach($fields as $k => $v)
			<tr>
			    <td>{{$v['title']}}</td>
			    <td>{{$v['desc']}}</td>
			    <td><label><input type="checkbox" name="write[]" key="{{$v['itemid']}}" value="{{$v['title']}}" id="write_{{$v['itemid']}}"></label></td>
			    <td><label><input type="checkbox" name="secret[]" value="{{$v['title']}}" key="{{$v['itemid']}}" id="secret_{{$v['itemid']}}"></label></td>
			    <td class="form-inline">
				 @if(!isset($v['datafld']))
					<label><input type="checkbox" key="{{$v['itemid']}}" disabled="true" value="{{$v['title']}}" name="check[]" id="regex_{{$v['itemid']}}"></label>&nbsp;
					<select class="input-sm form-control" style="display:none;" disabled name="check_select[]" id="regexList_{{$v['itemid']}}">
                       @foreach(config('default.regular') as $i => $j)
					       <option value="{{$i}}">{{$j['title']}}</option>
					   @endforeach
					</select>
				@else
					<label title="锁定将不允许修改宏控件的值">
					    <input type="checkbox" key="{{$v['itemid']}}" value="{{$v['title']}}" disabled="true" name="micro[]" id="micro_{{$v['itemid']}}">&nbsp;锁定
					</label>
				@endif
			    </td>
			</tr>
			@endforeach
            @endif
	    </tbody>
	   @endif
	</table>
    </div>

    <div class="tab-pane" id="tabs-4">
   
        <div id="tpl" style="display:none;">

            <table class="table m-b-none">
                <tr>
                    <td>
                        <div class="h5">转入步骤 @text</div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="form-inline">
                            <select class="input-sm form-control" id="field_@id">
                                <option value="">字段</option>
                                @if($fields)
                                @foreach($fields as $k => $v)
                                    <option>{{$v['title']}}</option>
                                @endforeach @endif
                                <option value="[发起人姓名]">[发起人姓名]</option>
                                <option value="[发起人职位]">[发起人职位]</option>
                                <option value="[发起人岗位]">[发起人岗位]</option>
                                <option value="[发起人部门]">[发起人部门]</option>
                                <option value="[经办人姓名]">[经办人姓名]</option>
                                <option value="[经办人职位]">[经办人职位]</option>
                                <option value="[经办人岗位]">[经办人岗位]</option>
                                <option value="[经办人部门]">[经办人部门]</option>
                                <option value="[步骤号]">[步骤号]</option>
                                <option value="[流程设计步骤号]">[流程设计步骤号]</option>
                                <option value="[公共附件名称]">[公共附件名称]</option>
                                <option value="[公共附件个数]">[公共附件个数]</option>
                            </select>

                            <div class="input-group">
                                <!--
                                <span class="input-group-addon">条件</span>
                                -->
                                <select class="input-sm form-control" id="condition_@id">
                                    <option value="==">等于</option>
                                    <option value="&lt;&gt;">不等于</option>
                                    <option value="&gt;">大于</option>
                                    <option value="&lt;">小于</option>
                                    <option value="&gt;=">大于等于</option>
                                    <option value="&lt;=">小于等于</option>
                                    <option value="include">包含</option>
                                    <option value="exclude">不包含</option>
                                </select>
                            </div>
            
                            <div class="input-group">
                                <!--
                                <span class="input-group-addon">值</span>
                                -->
                                <input type="text" class="input-sm form-control" id="item_value_@id">
                            </div>

                            <div class="input-group">
                                <!--
                                <span class="input-group-addon">关系</span>
                                -->
                                <select class="input-sm form-control" id="relation_@id">
                                    <option value="AND">与</option>
                                    <option value="OR">或</option>
                                </select>
                            </div>

                            <div class="btn-group">
                                <a class="btn btn-sm btn-default" onclick="condition.addLeft('@id')">(</a>
                                <a class="btn btn-sm btn-default" onclick="condition.addRight('@id')">)</a>
                                <a class="btn btn-sm btn-default" onclick="condition.add('@id')">新增</a>
                                <a class="btn btn-sm btn-default" onclick="condition.deleted('@id')">删行</a>
                                <a class="btn btn-sm btn-default" onclick="condition.clear('@id')">清空</a>
                            </div>

                            <div class="m-t-sm">
                                <select class="form-control input-sm" id="condition_list_@id" multiple="true" style="width:100%;height:80px;"></select>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div id="ctbody"></div>
    </div>
</div>

<input type="hidden" name="id" value="{{$row['id']}}">
</form>
