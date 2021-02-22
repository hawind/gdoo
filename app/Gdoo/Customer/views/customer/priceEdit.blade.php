<div class="wrapper-xs">
<form id="price-edit-form" action="{{url()}}" method="post">
    <div class="form-group m-b-xs">
        <div class="select-group input-group">
            <input class="form-control input-sm" placeholder="请选择产品" data-toggle="dialog-view" readonly="readonly" data-title="产品" data-url="product/product/dialog" data-id="product_id" data-multi="0" style="min-width:153px;cursor:pointer;" id="product_id_text">
            <input type="hidden" id="product_id" name="product_id">
            <div class="input-group-btn"><a data-toggle="dialog-clear" data-id="product_id" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a></div>
        </div>
    </div>
    <div class="form-group m-b-none">
        <input name="price" autocomplete="off" placeholder="请输入销售价格" class="form-control input-sm">
    </div>
    <input name="ids" type="hidden" value="{{$gets['ids']}}">
</form>
</div>