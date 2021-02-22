    <form method="post" id="model_bill" name="model_bill">
        <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">单据名称</th>
            <td><input type="text" id="name" name="name" value="{{$bill->name}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">单据编码</th>
            <td><input type="text" id="code" name="code" value="{{$bill->code}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">单据路径</th>
            <td><input type="text" id="uri" name="uri" value="{{$bill->uri}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">模型主表</td>
            <td>
                <select class="form-control input-sm" name="model_id" id="model_id">
                    <option value="0"> - </option>
                    @foreach($models as $_model)
                        <option value="{{$_model->id}}" @if($_model->id == $bill->model_id) selected @endif>{{$_model->name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">编号前缀</th>
            <td><input type="text" id="sn_prefix" name="sn_prefix" value="{{$bill->sn_prefix}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">编号规则</th>
            <td><input type="text" id="sn_rule" name="sn_rule" value="{{$bill->sn_rule}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">编号长度</th>
            <td><input type="text" id="sn_length" name="sn_length" value="{{$bill->sn_length}}" class="form-control input-sm"></td>
        </tr>


        <tr>
            <td align="right">审核类型</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($bill['audit_type'] == 0) checked @endif value="0" name="audit_type"> 无 </label>
                <label class="radio-inline"><input type="radio" @if($bill['audit_type'] == 1) checked @endif value="1" name="audit_type"> 固定流程 </label>
                <label class="radio-inline"><input type="radio" @if($bill['audit_type'] == 2) checked @endif value="2" name="audit_type"> 自由流程 </label>
                <label class="radio-inline"><input type="radio" @if($bill['audit_type'] == 3) checked @endif value="3" name="audit_type"> 审核 </label>
            </td>
        </tr>

        <tr>
            <td align="right">表单模式</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($bill['form_type'] == 0) checked @endif value="0" name="form_type"> ERP </label>
                <label class="radio-inline"><input type="radio" @if($bill['form_type'] == 1) checked @endif value="1" name="form_type"> OA </label>
            </td>
        </tr>

        <tr>
            <td align="right">支持回收站</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($bill['is_trash'] == 0) checked @endif value="0" name="is_trash"> 否 </label>
                <label class="radio-inline"><input type="radio" @if($bill['is_trash'] == 1) checked @endif value="1" name="is_trash"> 是 </label>
            </td>
        </tr>

        </table>
        <input type="hidden" name="id" value="{{$bill->id}}">
    </form>