<div class="wrapper-sm">
    <form class="form-horizontal" name="myrecall" id="myrecall" method="post">
        <textarea class="form-control" placeholder="撤回原因" id="remark" name="remark"></textarea>
        <input type="hidden" name="bill_id" id="bill_id" value="{{$bill_id}}">
        <input type="hidden" name="data_id" id="data_id" value="{{$data_id}}">
        <input type="hidden" name="log_id" id="log_id" value="{{$log_id}}">
    </form>
</div>