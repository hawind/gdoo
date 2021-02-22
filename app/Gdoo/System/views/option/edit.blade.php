<form method="post" class="form-horizontal" action="{{url('edit')}}" id="option" name="option">
    
    <table class="table table-form m-b-none">
        <tr>
            <td align="right" width="100">上级</td>
            <td align="left">
                <select class="form-control input-sm" name="parent_id" id="parent_id">
                    <option value="0">无</option>
                    @foreach($parents as $parent)
                        <option value="{{$parent['id']}}" @if($row['parent_id'] == $parent['id']) selected @endif>{{$parent['name']}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td align="right">名称</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="name" name="name" value="{{$row['name']}}">
            </td>
        </tr>
        <tr>
            <td align="right">值</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="value" name="value" value="{{$row['value']}}">
            </td>
        </tr>
        <tr>
            <td align="right">排序</td>
            <td align="left">
                <input class="form-control input-sm" type="text" id="sort" name="sort" value="{{$row['sort']}}">
            </td>
        </tr>
    </table>
    <input type="hidden" name="id" value="{{$row['id']}}">
</form>