    <form method="post" id="model_bill" name="model_bill">
        <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="10%">流程名称</th>
            <td><input type="text" id="name" name="name" value="{{$bill->name}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">流程编码</th>
            <td><input type="text" id="code" name="code" value="{{$bill->code}}" class="form-control input-sm"></td>
        </tr>

        <tr>
            <td align="right">流程类别</td>
            <td>
                <select class="form-control input-sm" name="category_id" id="category_id">
                    @foreach($categorys as $category)
                        <option value="{{$category->id}}" @if($category->id == $bill->category_id) selected @endif>{{$category->name}}</option>
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
            <td align="right">支持回收站</td>
            <td>
                <label class="radio-inline"><input type="radio" @if($bill['is_trash'] == 0) checked @endif value="0" name="is_trash"> 否 </label>
                <label class="radio-inline"><input type="radio" @if($bill['is_trash'] == 1) checked @endif value="1" name="is_trash"> 是 </label>
            </td>
        </tr>

        </table>
        <input type="hidden" name="id" value="{{$bill->id}}">
    </form>