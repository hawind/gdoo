<style>
.modal-body { overflow:hidden; }
</style>
<form method="post" action="{{url()}}" id="comment-form" name="comment-form">
<div class="panel m-b-none">
    <table class="table table-form m-b-none">
        <tr>
            <td>
                <textarea class="form-control" type="text" name="content" id="content"></textarea>
            </td>
        </tr>
        <tr>
            <td align="left">
            @include('attachment/create')
            </td>
        </tr>
        </table>
    </div>
</div>
<input type="hidden" name="task_id" value="{{$task_id}}">
</form>