<form method="post" action="{{url()}}" id="mystep" name="mystep">

<div class="panel">
    <table class="table table-form">
        <tr>
            <td align="right" width="10%">节点名称<span class="red">*</span></td>
            <td>
                <input type="text" id="name" name="name" value="{{$row->name}}" class="form-control input-sm">
            </td>
        </tr>
        
        @if($row->type != 'start')
        <tr>
            <td align="right">执行目标</td>
            <td>
                <select class="form-control input-sm" name="type" id="type">
                    <!--
                    <option value="start" @if($row->type == 'start') selected @endif>开始</option>
                    -->
                    <option value=""> - </option>
                    <option value="leader" @if($row->type == 'leader') selected @endif>直属领导</option>
                    <option value="manager" @if($row->type == 'manager') selected @endif>部门主管</option>

                    <option value="team1" @if($row->type == 'team1') selected @endif>销售组1级</option>
                    <option value="team2" @if($row->type == 'team2') selected @endif>销售组2级</option>
                    <option value="team3" @if($row->type == 'team3') selected @endif>销售组3级</option>
                    <option value="team4" @if($row->type == 'team4') selected @endif>销售组4级</option>
                    <option value="team5" @if($row->type == 'team5') selected @endif>销售组5级</option>
                    <option value="team6" @if($row->type == 'team6') selected @endif>销售组6级</option>
                    <option value="team7" @if($row->type == 'team7') selected @endif>销售组7级</option>
                    <option value="team8" @if($row->type == 'team8') selected @endif>销售组8级</option>
                    <option value="team9" @if($row->type == 'team9') selected @endif>销售组9级</option>
                    <option value="team10" @if($row->type == 'team10') selected @endif>销售组10级</option>

                    <option value="user" @if($row->type == 'user') selected @endif>指定办理人</option>
                    <option value="role" @if($row->type == 'role') selected @endif>指定角色</option>
                    <option value="created_id" @if($row->type == 'created_id') selected @endif>单据创建人ID</option>
                    <option value="field" @if($row->type == 'field') selected @endif>指定字段</option>
                    <option value="post" @if($row->type == 'post') selected @endif>职位</option>
                    <option value="custom" @if($row->type == 'custom') selected @endif>自定义</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">执行规则</td>
            <td>
                <?php $type_value = ($row->type == 'field' ? '' : $row->type_value); ?>
                <span id="type_user" class="type" style="display:none;">
                    {{App\Support\Dialog::user('user','type_value[user]', $type_value, 1, 0)}}
                </span>
                
                <span id="type_role" class="type" style="display:none;">
                    {{App\Support\Dialog::user('role','type_value[role]', $type_value, 1, 0)}}
                </span>

                <span id="type_field" class="type" style="display:none;">
                    <select class="form-control input-sm" name="type_value[field]">
                        @if($columns[$model->table]['fields'])
                        @foreach($columns[$model->table]['fields'] as $column)
                            <option value="{{$column->field}}" @if($row->type_value == $column->field) selected @endif>{{$column->name}}</option>
                        @endforeach
                        @endif
                    </select>
                </span>

                <label class="checkbox-inline m-t-xs">
                    <input name="select_org" type="checkbox" value="1" @if($row->select_org == 1) checked @endif>允许选择组织中人员
                </label>

                <div class="m-t-xs">
                    匹配不到人:
                    <select class="form-control input-sm input-inline" name="nouser" id="nouser">
                        <option value="0" @if($row->nouser == 0) selected @endif>由上节点选择执行人</option>
                        <option value="1" @if($row->nouser == 1) selected @endif>自动跳过</option>
                        <option value="2" @if($row->nouser == 2) selected @endif>转给指定人</option>
                    </select>
                    <span class="nouser" id="nouser_2" style="display:none;">
                        <span class="form-inline">
                            {{App\Support\Dialog::user('user','nouser_user_id', $row->nouser_user_id, 0, 0)}}
                        </span>
                    </span>
                </div>
            </td>
        </tr>

        @endif

        <tr>
            <td align="right">执行模式</td>
            <td>
                <select class="form-control input-sm" name="run_mode" id="run_mode">
                    <option value="1" @if($row->run_mode == 1) selected @endif>单人执行</option>
                    <option value="2" @if($row->run_mode == 2) selected @endif>多人执行</option>
                    <option value="3" @if($row->run_mode == 3) selected @endif>全体执行</option>
                    <option value="4" @if($row->run_mode == 4) selected @endif>竞争执行</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">节点类型</td>
            <td>
                <select class="form-control input-sm" name="option" id="option">
                    <option value="1" @if($row->option == 1) selected @endif>审核</option>
                    <option value="0" @if($row->option == 0) selected @endif>知会</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">表单权限</td>
            <td>
                <select class="form-control input-sm" name="permission_id" id="permission_id">
                    <option value=""> - </option>
                    @foreach($permissions as $permission)
                        <option value="{{$permission['id']}}" @if($row->permission_id == $permission['id']) selected @endif>{{$permission['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">节点提醒</td>
            <td>
                <label class="checkbox-inline">
                    <input name="notify[sms]" type="checkbox" id="notify-sms" value="1" @if($row->notify['sms'] == 1) checked @endif>短信
                </label>
            </td>
        </tr>

        @if($row->type != 'start')
        <tr>
            <td align="right">节点退回</td>
            <td>
                <select class="form-control input-sm" name="back" id="back">
                    <option value="0" @if($row->back == '0') selected @endif>无</option>
                    <option value="1" @if($row->back == '1') selected @endif>上一步</option>
                </select>
            </td>
        </tr>
        @endif
    </table>
</div>

<input type="hidden" name="id" value="{{$row->id}}">
<input type="hidden" name="bill_id" value="{{$bill->id}}">

</form>
<script type="text/javascript">
$(function() {
    userType('{{$row->type}}');
    nouserType('{{$row->nouser}}');
    $('#type').on('change', function() {
        userType(this.value);
    });
    $('#nouser').on('change', function() {
        nouserType(this.value);
    });
});

function nouserType(value) {
    value = value || '';
    $('.nouser').hide();
    $('#nouser_' + value).show();
}

function userType(type) {
    type = type || '';
    $('.type').hide().prop('disabled', true);
    $('#type_' + type).show().prop('disabled', false);
}
</script>