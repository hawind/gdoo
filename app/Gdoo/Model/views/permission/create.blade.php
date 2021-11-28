<div class="panel">

    <form method="post" action="{{url()}}" id="myform" name="myform">

    <div class="wrapper-sm b-b">
        <a class="btn btn-default btn-sm" href="{{url('index',['bill_id'=>$bill_id])}}"><i class="fa fa-reply"></i> 返回</a>
        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check"></i> 提交</button>
    </div>

    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">权限名称<span class="red">*</span></td>
            <td width="20%">
                <input type="text" id="name" name="name" value="{{$permission['name']}}" class="form-control input-sm input-inline">
            </td>
            <td align="right" width="10%">权限类型 <span class="red">*</span></td>
            <td width="20%">
                <select multiple="multiple" class="input-select2 form-control input-sm input-inline" name="type[]">
                    <option value="create" @if(in_array('create', $permission['type'])) selected @endif>新增</option>
                    <option value="edit" @if(in_array('edit', $permission['type'])) selected @endif>编辑</option>
                </select>
            </td>
            <td align="right" width="10%">权限范围 <span class="red">*</span></td>
            <td width="20%">
                {{App\Support\Dialog::search($permission, 'id=receive_id&name=receive_name&multi=1')}}
            </td>
            <td align="right"></td>
            <td></td>
        </tr>

    </table>
</div>

<div class="panel">
<table class="table table-bordered table-form">
<tr>
    <td>名称</td>
    <td>字段</td>
    <td>可写</td>
    <td>保密</td>
    <td>验证</td>
</tr>

@foreach($master['fields'] as $field)
<tr>
    <td>
        {{$field->name}}
    </td>
    <td>
        {{$master->table}}.{{$field->field}}
    </td>
    <td>
        <input type="checkbox" @if($permission['data'][$master->table][$field->field]['w'] == 1) checked @endif class="field-edit" data-key="{{$master->table}}_{{$field->field}}" id="{{$master->table}}_{{$field->field}}_edit" name="data[{{$master->table}}][{{$field->field}}][w]" value="1">
    </td>
    <td>
        <input type="checkbox" @if($permission['data'][$master->table][$field->field]['s'] == 1) checked @endif class="field-secret" data-key="{{$master->table}}_{{$field->field}}" id="{{$master->table}}_{{$field->field}}_secret" name="data[{{$master->table}}][{{$field->field}}][s]" value="1">
    </td>
    <td>
        <select multiple data-placeholder="选择验证规则" class="form-control input-sm input-inline input-select2" style="width:200px;" name="data[{{$master->table}}][{{$field->field}}][v][]">
            <option value=""></option>
            @foreach($regulars as $key => $regular)
                <option @if(in_array($key, (array)$permission['data'][$master->table][$field->field]['v'])) selected @endif value="{{$key}}">{{$regular}}</option>
            @endforeach
        </select>

        @if($field['form_type'] == 'auto' || $field['form_type'] == 'sn' || $field['form_type'] == 'date')
            <label title="锁定将不允许修改字段的值">
                <input type="checkbox" value="1" @if($permission['data'][$master->table][$field->field]['m'] == 1) checked @endif name="data[{{$master->table}}][{{$field->field}}][m]"> 锁定
            </label>
        @endif
    </td>
</tr>
@endforeach

@foreach($sublist as $submodel)
<tr>
    <td>
        <span class="label label-success">{{$submodel['name']}}</span> 权限
    </td>
    <td>
        {{$submodel->table}}@option
    </td>
    <td></td>
    <td></td>
    <td>
        <select multiple data-placeholder="选择验证规则" class="form-control input-sm input-inline input-select2" name="data[{{$submodel->table}}][@option][v][]">
            <option value=""></option>
            <option @if(in_array('required', (array)$permission['data'][$submodel->table]['@option']['v'])) selected @endif value="required">必填</option>
        </select>
        <label class="inline-checkbox"><input type="checkbox" @if($permission['data'][$submodel->table]['@option']['w'] == 1) checked @endif name="data[{{$submodel->table}}][@option][w]" value="1"> 增</label>
        &nbsp;
        <label class="inline-checkbox"><input type="checkbox" @if($permission['data'][$submodel->table]['@option']['d'] == 1) checked @endif name="data[{{$submodel->table}}][@option][d]" value="1"> 删</label>
    </td>
</tr>
@foreach($submodel['fields'] as $field)
<tr>
    <td>
        <span class="label label-primary">{{$submodel['name']}}</span>
        {{$field['name']}}
    </td>
    <td>
        {{$submodel->table}}.{{$field->field}}
    </td>
    <td>
        <input type="checkbox" @if($permission['data'][$submodel->table][$field->field]['w'] == 1) checked @endif class="field-edit" data-key="{{$submodel->table}}_{{$field->field}}" id="{{$submodel->table}}_{{$field->field}}_edit" name="data[{{$submodel->table}}][{{$field->field}}][w]" value="1">
    </td>
    <td>
        <input type="checkbox" @if($permission['data'][$submodel->table][$field->field]['s'] == 1) checked @endif class="field-secret" data-key="{{$submodel->table}}_{{$field->field}}" id="{{$submodel->table}}_{{$field->field}}_secret" name="data[{{$submodel->table}}][{{$field->field}}][s]" value="1">
    </td>
    <td>
        <select multiple data-placeholder="选择验证规则" class="form-control input-sm input-inline input-select2" name="data[{{$submodel->table}}][{{$field->field}}][v][]">
            <option value=""></option>
            @foreach($regulars as $key => $regular)
                <option @if(in_array($key, (array)$permission['data'][$submodel->table][$field->field]['v'])) selected @endif value="{{$key}}">{{$regular}}</option>
            @endforeach
        </select>
        @if($field['form_type'] == 'auto' || $field['form_type'] == 'date')
            <label title="锁定将不允许修改宏控件的值">
                <input type="checkbox" value="1" @if($permission['data'][$submodel->table][$field->field]['m'] == 1) checked @endif name="data[{{$submodel->table}}][{{$field->field}}][m]"> 锁定
            </label>
        @endif
    </td>
</tr>

@endforeach
@endforeach

</table>

</div>

<div class="panel">
    <table class="table table-form m-b-none">
        <tr>
            <td>
                <input type="hidden" name="id" value="{{$permission['id']}}">
                <input type="hidden" name="bill_id" value="{{$bill_id}}">
            </td>
        </tr>
    </table>
</div>

</form>