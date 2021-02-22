<div class="panel b-a" id="{{$header['master_table']}}-controller">
    @include('headers')

    <div class='list-jqgrid'>
        <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>

<script>
(function ($) {
    var table = '{{$header["master_table"]}}';
    var search = JSON.parse('{{json_encode($header["search_form"])}}');
    var columns = [];
    var params = search.query;
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;

    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = true;
    grid.defaultColDef.filter = false;
    grid.autoColumnsToFit = false;
    grid.singleClickEdit = true;
    grid.rowSelection = 'single';
    grid.suppressCellSelection = false;

    grid.defaultColDef.cellStyle = function(params) {
        if (params.node.rowPinned) {
            return;
        }
        var style = {};
        var field = params.colDef.field;
        if (params.data.status == '0' && (field == "customer_code" || field == "customer_name")) {
            style = {'color':'red'};
        }
        return style;
    };

    grid.columnDefs = [
        {cellClass:'text-center', field: 'sn', type: 'sn', headerName: '序号', width: 60},
        //{cellClass:'text-center', field: 'region_name', headerName: '区域', width: 100},
        {cellClass:'text-center', field: 'customer_code', headerName: '客户编码', width: 80},
        {cellClass:'text-left', field: 'customer_name', headerName: '所属客户', width: 180},
        {cellClass:'text-center', field: 'ym', headerName: '日期', width: 100},
        {cellClass:'text-center', field: 'sn', headerName: '订单编号', width: 140},
        {cellClass:'text-center', field: 'category_name', headerName: '单据类型', width: 140},
        {cellClass:'text-center', field: 'fee_src_sn', headerName: '单据编号', width: 140},
        {cellClass:'text-right', field: 'money', headerName: '金额', width: 100, type:'number', numberOptions: {places:2}, calcFooter: 'sum'},
    ];

    var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
    new agGrid.Grid(gridDiv, grid);
    gridDiv.style.height = getPanelHeight(12);

    // 读取数据
    grid.remoteData();

    var search_advanced = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
    });

    gdoo.grids[table] = {grid: grid};

    var action = new gridAction(table, '销售订单费用明细表');
    var panel = $('#' + table + '-controller');

    panel.on('click', '[data-toggle="' + table + '"]', function() {
        var data = $(this).data();
        if (data.action == 'filter') {
            // 过滤数据
            $('#' + table + '-search-form-advanced').dialog({
                title: '条件筛选',
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
                        var query = search_advanced.serializeArray();
                        params = {};
                        $.map(query, function(row) {
                            params[row.name] = row.value;
                        });
                        grid.remoteData(params);
                        $(this).dialog("close");
                        return false;
                    }
                }]
            });
        }

        if (data.action == 'export') {
            action.export(data, '销售订单费用明细表');
        }

    });

})(jQuery);
</script>