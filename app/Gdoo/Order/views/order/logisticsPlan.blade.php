<div class="wrapper-xs">
    <form class="form-horizontal" id="logistics_plan" method="post">
    
        <div class="form-group">
          <div class="col-sm-3 control-label">预计发货日期</div>
          <div class="col-sm-9 control-text"><input id="plan_delivery_dt" name="delivery[plan_delivery_dt]" type="text" autocomplete="off" data-toggle="date" class="form-control input-sm"></div>
        </div>

        <div class="form-group">
          <div class="col-sm-3 control-label">短途承运人</div>
          <div class="col-sm-9 control-text">
                <select type="text" class="input-required form-control input-sm" autocomplete="off" id="freight_short_logistics_id" name="delivery[freight_short_logistics_id]"></select>
            </div>
        </div>
        
        <div class="form-group">
          <div class="col-sm-3 control-label">短途车牌号</div>
          <div class="col-sm-9 control-text"><input type="text" class="form-control input-sm" autocomplete="off" id="freight_short_car" name="delivery[freight_short_car]"></div>
        </div>

        <div class="form-group">
          <div class="col-sm-3 control-label">物流公司</div>
          <div class="col-sm-9 control-text">
                <select type="text" class="input-required form-control input-sm" autocomplete="off" id="freight_logistics_id" name="delivery[freight_logistics_id]"></select>
            </div>
        </div>
        
        <div class="form-group m-b-none">
          <div class="col-sm-3 control-label">物流联系电话</div>
          <div class="col-sm-9 control-text"><input type="text" class="form-control input-sm" autocomplete="off" id="freight_logistics_phone" name="delivery[freight_logistics_phone]"></div>
        </div>

        <input name="ids" value="{{$gets['ids']}}" type="hidden">
    
    </form>
</div>

<script>
$(function() {
    // 选择短途运输人
    var short_logistics_id = $("#freight_short_logistics_id").select2Field({
        placeholder:"请选择短途承运人",width:"100%",allowClear:true,search_key:'logistics',multiple:0,ajaxParams:{select2:"true"},ajax:{url:"/order/logistics/dialog"}
    });
    short_logistics_id.on('select2:select', function(e) {
        var row = e.params.data;
        $('#freight_short_car').val(row.short_car_sn);
    });

    // 选择物流公司
    var logistics_id = $("#freight_logistics_id").select2Field({
        placeholder:"物流公司",width:"100%",allowClear:true,search_key:'logistics',multiple:0,ajaxParams:{select2:"true"},ajax:{url:"/order/logistics/dialog"}
    });
    logistics_id.on('select2:select', function(e) {
        var row = e.params.data;
        console.log(row);
        $('#freight_logistics_phone').val(row.short_car_sn);
    });
});
</script>