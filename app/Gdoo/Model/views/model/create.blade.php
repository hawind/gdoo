    <form method="post" id="model" name="model">
        <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">模型名称</th>
            <td><input type="text" id="name" name="name" value="{{$model->name}}" onblur="app.pinyin('name','table');" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">父节模型</td>
            <td>
                <select class="form-control input-sm" name="parent_id" id="parent_id">
                    <option value="0"> - </option>
                    @foreach($models as $_model)
                        <option value="{{$_model->id}}" @if($_model->id == $model->parent_id) selected @endif>{{$_model->name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">模型类型</td>
            <td>
                <select class="form-control input-sm" name="type" id="type">
                    <option value="0" @if($model->type == 0) selected @endif> - </option>
                    <option value="1" @if($model->type == 1) selected @endif>多行子表</option>
                </select>
            </td>
        </tr>

        <tr>
            <td align="right">关联外键</td>
            <td><input type="text" id="relation" name="relation" value="{{$model->relation}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">数据表名</td>
            <td><input type="text" id="table" name="table" value="{{$model->table}}" class="form-control input-sm" @if($row->id > 0) readonly @endif></td>
        </tr>

        <!--
        <tr>
            <td align="right">审核类型</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($model['audit_type'] == 0) checked @endif value="0" name="audit_type"> 无 </label>
                <label class="radio-inline"><input type="radio" @if($model['audit_type'] == 1) checked @endif value="1" name="audit_type"> 固定流程 </label>
                <label class="radio-inline"><input type="radio" @if($model['audit_type'] == 2) checked @endif value="2" name="audit_type"> 自由流程 </label>
                <label class="radio-inline"><input type="radio" @if($model['audit_type'] == 3) checked @endif value="3" name="audit_type"> 审核 </label>
            </td>
        </tr>

        <tr>
            <td align="right">支持回收站</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($model['is_trash'] == 0) checked @endif value="0" name="is_trash"> 否 </label>
                <label class="radio-inline"><input type="radio" @if($model['is_trash'] == 1) checked @endif value="1" name="is_trash"> 是 </label>
            </td>
        </tr>

        <tr>
            <td align="right">数据排序</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($model['is_sort'] == 0) checked @endif value="1" name="is_sort"> 否 </label>
                <label class="radio-inline"><input type="radio" @if($model['is_sort'] == 1) checked @endif value="0" name="is_sort"> 是 </label>
            </td>
        </tr>
        -->

        </table>
        <input type="hidden" name="id" value="{{$model->id}}">

    </form>