<script type="text/javascript">
window.UEDITOR_HOME_URL = '{{$asset_url}}/vendor/ueditor/';
</script>
<script src="{{$asset_url}}/vendor/ueditor/ueditor.workflow.config.js"></script>
<script src="{{$asset_url}}/vendor/ueditor/ueditor.all.min.js"></script>
<script src="{{$asset_url}}/vendor/ueditor/ueditor.workflow.js"></script>

<form method="post" id="myform" name="myform">

    <div class="panel">

    <div class="panel-heading b-b b-light">
        <h4>{{$row['title']}}[{{$row['id']}}]</h4>
        <!--
        <div class="label label-info">表单智能设计器：首先将网页设计工具或Word编辑好的表格框架粘贴到表单设计区，然后创建表单控件</div>
        -->
    </div>

    <div class="panel-body">

    <div class="row">
        <div class="col-sm-10 m-b">
            <script type="text/plain" id="editor" name="template">{{$row['template']}}</script>
        </div>
        <div class="col-sm-2">

                    <div class="btn-group">
                        <a onclick="tool.control('text');" class="btn btn-default btn-block btn-md"><i class="fa fa-font"></i> 单行文本</a>
                        <a onclick="tool.control('textarea');" class="btn btn-default btn-block btn-md"><i class="fa fa-font"></i> 多行文本</a>
                        <a onclick="tool.control('listmenu');" class="btn btn-default btn-block btn-md"><i class="fa fa-bars"></i> 下拉菜单</a>
                        <a onclick="tool.control('radio');" class="btn btn-default btn-block btn-md"><i class="fa fa-check-circle"></i> 单选按钮</a>
                        <a onclick="tool.control('checkbox');" class="btn btn-default btn-block btn-md"><i class="fa fa-check-square"></i> 复选按钮</a>
                        <a onclick="tool.control('listview');" class="btn btn-default btn-block btn-md"><i class="fa fa-th"></i> 列表控件</a>
                        <a onclick="tool.control('auto');" class="btn btn-default btn-block btn-md"><i class="fa fa-gear"></i> 宏控件</a>
                        <a onclick="tool.control('calendar');" class="btn btn-default btn-block btn-md"><i class="fa fa-calendar"></i> 日历控件</a>
                        <a onclick="tool.control('calc');" class="btn btn-default btn-block btn-md"><i class="fa fa-building"></i> 计算控件</a>
                        <a onclick="tool.control('user');" class="btn btn-default btn-block btn-md"><i class="fa fa-group"></i> 部门人员控件</a>
                        <a onclick="tool.control('imgupload');" class="btn btn-default btn-block btn-md"><i class="fa fa-photo"></i> 图片上传控件</a>
                        <!--
                        <a onclick="tool.control('sign');" class="btn btn-default btn-block btn-md ">签章控件</a>
                        <a onclick="tool.control('data_select');" class="btn btn-default btn-block btn-md">数据选择控件</a>
                        <a onclick="tool.control('data_fetch');" class="btn btn-default btn-block btn-md">表单数据控件</a>
                        <a onclick="tool.control('progressbar');" class="btn btn-default btn-block btn-md">进度条控件</a>
                        <a onclick="tool.control('qrcode');" class="btn btn-default btn-block btn-md"><span class="fa fa-qrcode"></span> 二维码控件</a>
                        -->

                    </div>

                    <div class="m-t btn-group btn-group-justified">
                        <a onclick="tool.review({{$row['id']}})" class="btn btn-primary">预览</a>
                        <a onclick="tool.checkForm('ver')" class="btn btn-primary">版本</a>
                        <a onclick="tool.close();" class="btn btn-primary">关闭</a>
                    </div>

                    <div class="m-t btn-group btn-group-justified">
                        <a autocomplete="off" onclick="tool.checkForm()" class="btn btn-lg btn-success"><i class="fa fa-check-circle"></i> 保存表单</a>
                    </div>
                </div>
            </div>
    </div>

    </div>

    <input type="hidden" name="work_id" id="work_id" value="{{$row['id']}}">
    <input type="hidden" name="count_item" id="count_item" value="{{url('count')}}?work_id={{$row['id']}}">
</div>
</form>

<script type="text/javascript">
var tool = {
    checkForm:function(type) {

        // 显示loading
        if(editor.hasContents()) {

            // 同步内容
            editor.sync();

            if(typeof type !== 'undefined') {
                document.myform.type.value = type;
            }
            
            var myform = $('#myform').serialize();
            $.post('{{url()}}', myform, function(res) {
                if(res.status) {
                    toastrSuccess('保存成功。');
                }
            },'json');

        } else {
            toastrError('表单内容不能为空。');
            return false;
        }
    },
    close:function() {
        $.messager.confirm('操作警告', '关闭表单前，您是否要保存？', function(btn) {
            if (btn == true) {
                this.checkForm('close');
            }
        });
    },
    control:function(method) {
        editor.execCommand(method);
    },
    review:function(id) {
        $.dialog({
            title:'表单预览',
            dialogClass:'modal-lg',
            url:app.url('workflow/form/view', {review:true,id:id}),
            buttons:[{
                text: '确定',
                'class': 'btn-primary',
                click: function() {
                    $(this).dialog('close');
                }
            },{
                text: '取消',
                'class': 'btn-default',
                click: function() {
                    $(this).dialog('close');
                }
            }]
        });
    },
    checkClose:function() {
        if(event.clientX > document.body.clientWidth-20 && event.clientY < 0 || event.altKey) {
            window.event.returnValue = '您确定退出表单设计器吗';
        }
    }
}
var editor = UE.getEditor('editor',{'minFrameHeight':480,'initialFrameWidth':'100%'});
</script>
<div onbeforeunload="tool.checkClose();">
