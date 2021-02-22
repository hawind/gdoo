<form method="post" id="queryform" name="queryform" class="form-horizontal">

<div class="panel">

<div class="wrapper-sm h5 b-b b-light">
    <i class="fa fa-list"></i> 统计报表选项
</div>

<div class="panel-body">

    <div class="form-group">
        <label class="col-sm-2 control-label">流程名称</label>
        <div class="col-sm-10">
            <input class="input-sm form-control" type="text" name="name" readonly="readonly" value="{{$work['title']}}">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">工作流状态</label>
        <div class="col-sm-10">
            <select name="status" class="input-sm form-control">
                <option value="all" @if($work_type_field['status'] == 'all') selected @endif>全部</option>
                <option value="0" @if($work_type_field['status'] == '0') selected @endif>执行中</option>
                <option value="1" @if($work_type_field['status'] == '1') selected @endif>已结束</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">查询范围</label>
        <div class="col-sm-10">
            <select class="input-sm form-control" name="user_type">
                <option value="all" @if($work_type_field['user_type']=='all') selected @endif>全部</option>
                <option value="1" @if($work_type_field['user_type']=='1') selected @endif>我发起的</option>
                <option value="2" @if($work_type_field['user_type']=='2') selected @endif>我经办的</option>
                <option value="3" @if($work_type_field['user_type']=='3') selected @endif>我管理的</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">流程发起人</label>
        <div class="col-sm-10">
            {{App\Support\Dialog::user('user','start_user_id',$row->start_user_id, 0, 0)}}
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">名称/文号</label>
        <div class="col-sm-10">
            <input type="text" class="input-sm form-control" name="run_name" value="{{$work_type_field['run_name']}}" maxlenth="100">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">流程开始日期</label>
        <div class="col-sm-10 form-inline">
            <div class="input-group">
                <div class="bg-white input-group-addon">从</div>
                <input data-toggle="date" class="input-sm form-control" name="date_start_a" type="text" value="@date($row->add_time)">
            </div>
            <div class="input-group">
                <div class="bg-white input-group-addon">至</div>
                <input data-toggle="date" class="input-sm form-control" name="date_start_b" type="text" value="@date($row->expired_time)">
            </div>
        </div>
    </div>

    <!--
    <div class="form-group">
        <label class="col-sm-2 control-label">流程结束日期</label>
        <div class="col-sm-10 form-inline">
            <div class="input-group">
                <div class="bg-white input-group-addon">从</div>
                <input data-toggle="date" class="input-sm form-control" name="date_end_a" type="text" value="@date($row->add_time)">
            </div>

            <div class="input-group">
                <div class="bg-white input-group-addon">至</div>
                <input data-toggle="date" class="input-sm form-control" name="date_end_b" type="text" value="@date($row->expired_time)">
            </div>
        </div>
    </div>
    -->

    <div class="form-group">
        <label class="col-sm-2 control-label">公共附件名称</label>
        <div class="col-sm-10">
            <input type="text" class="input-sm form-control" name="attach_name" value="{{$work_type_field['attach_name']}}">
        </div>
    </div>


    <div class="form-group">
        <label class="col-sm-2 control-label"><i class="fa fa-file-text-o"></i> 表单数据条件</label>
        <div class="col-sm-10">

            <table id="condition" class="table table-condensed table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width:90px;">左括号</th>
                    <th style="width:140px;">字段</th>
                    <th style="width:130px;">条件</th>
                    <th>值</th>
                    <th style="width:90px;">右括号</th>
                    <th style="width:100px;">逻辑</th>
                    <th style="width:120px;">
                        <button type="button" data-condition="add" class="btn btn-default btn-xs"><i class="fa fa-plus"></i> 新增</button>
                    </th>
                </tr>
                <tr data-condition="tpl" style="display:none;">
                        <td>
                            <select name="conditions[@id][left]" class="input-sm form-control">
                                <option value=""></option>
                                <option value="(">(</option>
                            </select>
                        </td>
                        <td>
                            <select name="conditions[@id][field]" class="input-sm form-control">
                                <option value=""></option>
                                @foreach($fields as $field)
                                    <option value="d.{{$field['name']}}">{{$field['title']}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="conditions[@id][condition]" class="input-sm form-control">
                                <option value="=">等于</option>
                                <option value="<>">不等于</option>
                                <option value=">">大于</option>
                                <option value="<">小于</option>
                                <option value=">=">大于等于</option>
                                <option value="<=">小于等于</option>
                                <option value="like">包含</option>
                                <option value="not like">不包含</option>
                            </select>
                        </td>
                        <td>
                            <input name="conditions[@id][value]" class="input-sm form-control">
                        </td>
                        <td>
                            <select name="conditions[@id][right]" class="input-sm form-control">
                                <option value=""></option>
                                <option value=")">)</option>
                            </select>
                        </td>
                        <td>
                            <select name="conditions[@id][logic]" class="input-sm form-control">
                                <option value="and">and</option>
                                <option value="or">or</option>
                            </select>
                        </td>
                        <td align="center">
                            <div class="btn-group">
                                <button type="button" data-condition="up" class="btn btn-default btn-xs"><i class="fa fa-angle-up"></i></button>
                                <button type="button" data-condition="down" class="btn btn-default btn-xs"><i class="fa fa-angle-down"></i></button>
                                <button type="button" data-condition="delete" class="btn btn-default btn-xs"><i class="fa fa-times"></i></button>
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody data-condition="body"></tbody>
            </table>

        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label"><i class="fa fa-bar-chart-o"></i> 统计报表选项</label>
        <div class="col-sm-10">
            <table id="condition" class="table table-condensed table-bordered table-hover">
                <thead>
                <tr>
                    <th style="width:60px;">显示</th>
                    <th>字段</th>
                    <th style="width:100px;">格式</th>
                    <th style="width:100px;">合计</th>
                    <th style="width:100px;">排序</th>
                </tr>
                </thead>

                @foreach($columns as $key => $column)
                <tr>
                    <td align="center">
                        <input type="checkbox" value="{{$column['field']}}" name="columns[{{$key}}][field]" checked>
                    </td>
                    <td>
                        [{{$column['name']}}]
                    </td>
                    <td align="center">
                        {{$column['format']}}
                    </td>
                    <td align="center">
                        <input type="checkbox" name="columns[{{$key}}][total]">
                    </td>
                    <td>
                        <input name="columns[{{$key}}][sort]" class="input-sm form-control">
                        <input type="hidden" name="columns[{{$key}}][format]" value="{{$column['format']}}" class="input-sm form-control">
                        <input type="hidden" name="columns[{{$key}}][name]" value="{{$column['name']}}">
                    </td>
                </tr>
                @endforeach

                @foreach($fields as $field)
                <tr>
                    <td align="center">
                        <input type="checkbox" value="d.{{$field['name']}}" name="columns[{{$field['name']}}][field]" checked>
                    </td>
                    <td>

                        {{$field['title']}}
                        @if($field['class'] == 'listview')
                        <?php 
                            $field['lv_title'];
                            $trs = explode('`', $field['lv_title']);
                        ?>
                        <div>
                        @foreach($trs as $i => $tr)
                            @if($tr)
                                <label><input name="columns[{{$field['name']}}][field1][{{$i}}]" type="checkbox" value="1" checked="checked" />{{$tr}} </label>&nbsp;
                            @endif
                        @endforeach
                        @endif
                        </div>
                    </td>
                    <td>
                        <select name="columns[{{$field['name']}}][format]" class="input-sm form-control">
                            <option value="text">文本</option>
                            <option value="number">数字</option>
                        </select>
                    </td>
                    <td align="center">
                        <input type="checkbox" name="columns[{{$field['name']}}][total]">
                    </td>
                    <td>
                        <input name="columns[{{$field['name']}}][sort]" class="input-sm form-control">
                        <input type="hidden" name="columns[{{$field['name']}}][name]" value="{{$field['title']}}">
                        <input type="hidden" name="columns[{{$field['name']}}][class]" value="{{$field['class']}}">
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">排序字段</label>
        <div class="col-sm-10 form-inline">
            <select class="input-sm form-control" name="order_by">

                @foreach($columns as $column)
                    <option value="{{$column['field']}}">[{{$column['name']}}]</option>
                @endforeach

                @foreach($fields as $field)
                    <option value="{{$field['name']}}">{{$field['title']}}</option>
                @endforeach
            </select>
            &nbsp;
            <select class="input-sm form-control" name="sort_by">
                <option value="asc" @if($sort_by['sort'] == 'asc') selected @endif>升序</option>
                <option value="desc" @if($sort_by['sort'] == 'desc') selected @endif>降序</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">分组字段</label>
        <div class="col-sm-10 form-inline">
            <select class="input-sm form-control" name="group_by">
                @foreach($columns as $column)
                    <option value="{{$column['field']}}">[{{$column['name']}}]</option>
                @endforeach

                @foreach($fields as $field)
                    <option value="{{$field['name']}}">{{$field['title']}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <input type="hidden" name="work_id" value="{{$work_id}}">

    <div class="col-sm-10 col-sm-offset-2">
        <button type="button" onclick="querySubmit('list');" class="btn btn-info">查询列表</button>
        <button type="button" onclick="querySubmit('excel');" class="btn btn-default">导出EXCEL统计列表</button>
        <button type="button" onclick="querySubmit('html');" class="btn btn-default">导出HTML统计列表</button>
    </div>

</div>

</form>

<script type="text/javascript">

/**
 * 数据条件插件
 */
(function($) {

    $.fn.condition = function()
    {
        var self = this;

        self.index = 0;

        var tpl = self.find('[data-condition="tpl"]').html();

        var body = self.find('[data-condition="body"]');

        self.on('click', '[data-condition="add"]', function()
        {
            self.index++;
            body.append('<tr>' + tpl.replace(/\@id/g, self.index) + '</tr>');
        });

        self.on('click','[data-condition="delete"]', function()
        {
            $(this).closest('tr').remove();
        });

        self.on('click', '[data-condition="up"]', function()
        {
            var tr = $(this).closest('tr');
            var prev = tr.prev();
            if (prev.length > 0)
            {
                prev.insertAfter(tr);
            }
            else
            {
                return;
            }
        });

        self.on('click', '[data-condition="down"]', function()
        {
            var tr = $(this).closest('tr');
            var next = tr.next();
            if (next.length > 0)
            {
                next.insertBefore(tr);
            }
            else
            {
                return;
            }
        });
    }

})(jQuery);

$(function()
{
    $('#condition').condition();
});

function querySubmit(action)
{
    document.queryform.target='_blank';
    document.queryform.action = app.url('workflow/workflow/export',{action:action});
    document.queryform.submit();
}

</script>
