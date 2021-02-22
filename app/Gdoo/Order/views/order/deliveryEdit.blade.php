<div class="wrapper-xs">
    <form class="form-horizontal" id="delivery_edit" method="post">
        <div class="form-group m-b-none">
          <div class="col-sm-3 control-label">运费付款方式</div>
          <div class="col-sm-9 control-text"><input type="text" class="form-control input-sm" autocomplete="off" value="{{$gets['freight_pay_text']}}" id="freight_pay_text" name="delivery[freight_pay_text]"></div>
        </div>
        <input name="id" value="{{$gets['id']}}" type="hidden">
    </form>
</div>