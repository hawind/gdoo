<form method="post" id="model_bill_category" name="model_bill_category">
    <table class="table table-form m-b-none">
    <tr>
        <td align="right" width="10%">名称</th>
        <td><input type="text" id="name" name="name" value="{{$category->name}}" class="form-control input-sm"></td>
    </tr>

    <tr>
        <td align="right">备注</th>
        <td><textarea id="remark" name="remark" class="form-control input-sm">{{$category->remark}}</textarea></td>
    </tr>

    <tr>
        <td align="right">排序</th>
        <td><input type="text" id="sort" name="sort" value="{{$category->sort}}" class="form-control input-sm"></td>
    </tr>

    </table>
    <input type="hidden" name="id" value="{{$category->id}}">
</form>