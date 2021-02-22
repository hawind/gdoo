<form class="form-horizontal" method="post" action="{{url()}}" id="myshare" name="myshare">
<div class="table-responsive">
    <table class="table table-form m-b-none">
        <tr>
            <td align="left">
                {{App\Support\Dialog::search($row, 'id=receive_id&name=receive_name&multi=1')}}
            </td>
        </tr>
    </table>
</div>
<input type="hidden" name="id" value="{{$row['id']}}" />
</form>