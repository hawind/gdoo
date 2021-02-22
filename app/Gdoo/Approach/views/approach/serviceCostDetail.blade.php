<style>
.modal-body { overflow:hidden; }
.service-cost-detail .control-label {
    padding-top: 11px;
    text-align: right;
}
.service-cost-detail .form-group:first-child .col-xs-3:nth-child(3),
.service-cost-detail .form-group:first-child .col-xs-3:nth-child(4) {
    border-top: 0 !important;
}

.service-cost-detail .ag-theme-balham .ag-root-wrapper {
    border-left-width: 1px;
    border-right-width: 1px;
}

</style>

<div class="service-cost-detail">

    <div class="form-controller">
        <div class="form-group">
            <div class="col-xs-3 col-sm-2 control-label">费用合计</div>
            <div class="col-xs-3 col-sm-2 control-text"><div class="form-control input-sm">@number($all['apply_money'], 2)</div></div>
            <div class="col-xs-3 col-sm-2 control-label">支持合计</div>
            <div class="col-xs-3 col-sm-2 control-text"><div class="form-control input-sm">@number($all['support_money'], 2)</div></div>
            <div class="col-xs-3 col-sm-2 control-label">年度销售额</div>
            <div class="col-xs-3 col-sm-2 control-text"><div class="form-control input-sm">@number($all['money'], 2)</div></div>
        </div>
        <div class="form-group">
            <div class="col-xs-3 col-sm-2 control-label">申请产出比(%)</div>
            <div class="col-xs-3 col-sm-2 control-text"><div class="form-control input-sm">@number($all['support_percent'], 2)</div></div>
            <div class="col-xs-3 col-sm-2 control-label">兑现产出比(%)</div>
            <div class="col-xs-3 col-sm-2 control-text"><div class="form-control input-sm">@number($all['apply_percent'], 2)</div></div>
            <div class="col-xs-3 col-sm-2 control-label"></div>
            <div class="col-xs-3 col-sm-2 control-text"></div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="tabs-box">
        <ul class="nav nav-tabs">
            <li class="active">
                <a class="text-sm" href="#tab_a" data-toggle="tab">促销</a>
            </li>
            <li>
                <a class="text-sm" href="#tab_b" data-toggle="tab">进店</a>
            </li>
            <li>
                <a class="text-sm" href="#tab_c" data-toggle="tab">物资</a>
            </li>
        </ul>
        <div id="tab_ontent" class="tab-content" style="padding:5px;">
            <div class="tab-pane active" id="tab_a">
                <div id="ref_tab_a" class="ag-theme-balham" style="width:100%;height:280px;"></div>
            </div>
            <div class="tab-pane" id="tab_b">
                <div id="ref_tab_b" class="ag-theme-balham" style="width:100%;height:280px;"></div>
            </div>
            <div class="tab-pane" id="tab_c">
                <div id="ref_tab_c" class="ag-theme-balham" style="width:100%;height:280px;"></div>
            </div>
        </div>
    </div>

</div>

<script>
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    var tabDiv = document.querySelector("#ref_tab_a");
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowSelection = 'single';
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = false;
    grid.columnDefs = [
        {cellClass:'text-center', field: 'start_dt', headerName: '开始日期', width: 100},
        {cellClass:'text-center', field: 'end_dt', headerName: '结束日期', width: 100},
        {cellClass:'text-center', field: 'promote_scope', headerName: '超市', minWidth: 160},
        //{cellClass:'text-center', field: 'created_at', headerName: '促销方式', width: 100},
        {cellClass:'text-right',field:'pro_total_cost', headerName: '总费用', width: 100},
        {cellClass:'text-right',field:'undertake_money', headerName: '支持费用', width: 100}
    ];
    new agGrid.Grid(tabDiv, grid);
    // 读取数据
    grid.remoteData({type: 'promotion'});

    var tabDiv2 = document.querySelector("#ref_tab_b");
    var grid2 = new agGridOptions();
    grid2.remoteDataUrl = '{{url()}}';
    grid2.remoteParams = params;
    grid2.rowSelection = 'single';
    grid2.defaultColDef.suppressMenu = true;
    grid2.defaultColDef.sortable = false;
    grid2.columnDefs = [
        {cellClass:'text-center', field: 'created_at', type: 'date', headerName: '申请日期', width: 120},
        {cellClass:'text-center', field: 'market_name', headerName: '超市名称', minWidth: 160},
        {cellClass:'text-right',field:'barcode_cast', headerName: '总费用', width: 140},
        {cellClass:'text-right',field:'apply2_money', headerName: '支持费用', width: 140}
    ];
    new agGrid.Grid(tabDiv2, grid2);
    // 读取数据
    grid2.remoteData({type: 'approach'});

    var tabDiv3 = document.querySelector("#ref_tab_c");
    var grid3 = new agGridOptions();
    grid3.remoteDataUrl = '{{url()}}';
    grid3.remoteParams = params;
    grid3.rowSelection = 'single';
    grid3.defaultColDef.suppressMenu = true;
    grid3.defaultColDef.sortable = false;
    grid3.columnDefs = [
        {cellClass:'text-center', field: 'created_at', type: 'date', headerName: '申请日期', minWidth: 120},
        {cellClass:'text-right',field:'pro_total_cost', headerName: '总费用', width: 140},
        {cellClass:'text-right',field:'undertake_money', headerName: '支持费用', width: 140}
    ];
    new agGrid.Grid(tabDiv3, grid3);
    // 读取数据
    grid3.remoteData({type: 'material'});
})(jQuery);
</script>