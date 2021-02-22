<form class="form-horizontal" method="post" action="{{url()}}" id="myfolder" name="myfolder">
<div class="table-responsive">
    <table class="table table-form m-b-none">
        <tr>
            <td align="left">
                <input class="form-control input-sm" type="text" id="name" name="name" value="{{$row['name']}}">
            </td>
        </tr>
    </table>
</div>
<input type="hidden" name="id" value="{{$row['id']}}" />
<input type="hidden" name="parent_id" value="{{$row['parent_id']}}" />
<input type="hidden" name="folder" value="{{$row['folder']}}" />
</form>