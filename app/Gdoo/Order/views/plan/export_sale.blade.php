<div class="panel b-a" id="{{$search['table']}}-controller">

    <div class="wrapper-sm">
        @include('searchForm7')
        <a class="btn btn-sm btn-default" data-toggle="{{$search['table']}}" data-action="filter"><i class="fa fa-search"></i> 筛选</a>
        <a class="btn btn-sm btn-default" data-toggle="{{$search['table']}}" data-action="export"><i class="fa fa-share"></i> 导出</a>
    </div>

    <div class='gdoo-list-grid'>
        <div id="{{$search['table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>

<script>
(function ($) {
    var searchOpen = false;
    var table = '{{$search["table"]}}';
    var search = JSON.parse('{{json_encode($search)}}');
    var columns = [];
    var params = search.query;
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;

    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.filter = false;
    grid.singleClickEdit = true;
    grid.rowSelection = 'single';
    grid.suppressCellSelection = false;
    grid.columnDefs = [
        {cellClass:'text-center', field: 'sn', type: 'sn', headerName: '序号', width: 50},
        {cellClass:'text-center', field: 'status', headerName: '状态', width: 100},
        {cellClass:'text-center', field: 'sn', headerName: '订单编码', width: 100},
        {cellClass:'text-center', field: 'created_dt', headerName: '订单日期', width: 80},
        {cellClass:'text-center', field: 'customer_name', headerName: '客户名称', width: 160},
        {cellClass:'text-center', field: 'product_code', headerName: '产品编码', width: 80},
        {cellClass:'text-left', field: 'product_name', headerName: '产品名称', width: 160},
        {cellClass:'text-center', field: 'product_spec', headerName: '规格型号', width: 100},
        {cellClass:'text-center', field: 'batch_sn', headerName: '生产批号', width: 100},

        {cellClass:'text-right', field: 'quantity', headerName: '订单数量', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'plan_num', headerName: '已生产数量', width: 70,type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'storage_num', headerName: '已入库数量', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
    ];

    var gridDiv = document.querySelector("#{{$search['table']}}-grid");
    new agGrid.Grid(gridDiv, grid);
    gridDiv.style.height = getPanelHeight(12);

    // 读取数据
    grid.remoteData();

    var search = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
    });

    gdoo.grids[table] = {grid: grid};

    var action = new gridAction(table, '外销生产进度表');
    var panel = $('#' + table + '-controller');

    // 过滤数据
    var searchBox = function() {
        $(search).dialog({
            title: '筛选条件',
            modalClass: 'no-padder',
            buttons: [{
                text: "取消",
                'class': "btn-default",
                click: function() {
                    $(this).dialog("close");
                }
            },{
                text: "确定",
                'class': "btn-info",
                click: function() {
                    var data = search.serializeArray();
                    var params = {};
                    search.queryType = 'advanced';
                    $.map(data, function(row) {
                        params[row.name] = row.value;
                    });
                    params['filter'] = 1;
                    grid.remoteData(params);
                    $(this).dialog("close");
                    return false;
                }
            }]
        });
    }

    panel.on('click', '[data-toggle="' + table + '"]', function() {
        var data = $(this).data();
        if (data.action == 'filter') {
            searchBox();
        }

        if (data.action == 'export') {
            action.export(data, '外销生产进度表');
        }

    });

    if (searchOpen == false) {
        searchBox();
    }

})(jQuery);
</script>