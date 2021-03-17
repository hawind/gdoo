<div class="panel b-a" id="{{$header['master_table']}}-controller">
    @include('headers')

    <div class='gdoo-list-grid'>
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
        {cellClass:'text-center', field: 'sn', type: 'sn', headerName: '序号', width: 50},
        {cellClass:'text-center', field: 'region_name', headerName: '区域', width: 100},
        {cellClass:'text-center', field: 'customer_code', headerName: '客户编码', width: 80},
        {cellClass:'text-left', field: 'customer_name', headerName: '客户名称', width: 180},
        {cellClass:'text-right', field: 'total_task', headerName: '总任务', width: 80, type:'number', numberOptions: {places:2}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'total_money', headerName: '累计销售', width: 80, type:'number', numberOptions: {places:4}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'total_rate', headerName: '总进度', width: 80, type:'number', numberOptions: {places:2}, calcFooter: 'sum'},
    ];

    for(var i=1; i <= 12;i++) {
        grid.columnDefs.push({
            cellClass:'text-center', headerName: i + '月',
            children: [
                {cellClass:'text-right', headerName:'任务', width: 60, field:'month' + i, type:'number', numberOptions:{places:2, default:'0'}, calcFooter:'sum'},
                {cellClass:'text-right', headerName:'销售', width: 80, field:'month_'+i+'_money', type:'number', numberOptions:{places:4, default:'0'}, calcFooter:'sum'},
                {cellClass:'text-right', headerName:'进度', width: 60, field:'month_'+i+'_rate', type:'number', numberOptions:{places:2, default:'0'}}
            ]
        });
    }

    for(var i=1; i <= 4;i++) {
        var dx = {
            1: '一',
            2: '二',
            3: '三',
            4: '四',
        };
        grid.columnDefs.push({
            cellClass:'text-center', headerName: dx[i] + '季度',
            children: [
                {cellClass:'text-right', headerName:'任务', width: 60, field:'quarter_' + i, type:'number', numberOptions:{places:2, default:'0'}, calcFooter:'sum'},
                {cellClass:'text-right', headerName:'销售', width: 80, field:'quarter_'+i+'_money', type:'number', numberOptions:{places:4, default:'0'}, calcFooter:'sum'},
                {cellClass:'text-right', headerName:'进度', width: 60, field:'quarter_'+i+'_rate', type:'number', numberOptions:{places:2, default:'0'}}
            ]
        });
    }

    var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
    new agGrid.Grid(gridDiv, grid);
    gridDiv.style.height = getPanelHeight(12);

    grid.remoteData();

    var search_advanced = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
    });

    gdoo.grids[table] = {grid: grid};

    var action = new grid(table, '客户销售进度');
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
            action.export(data, '客户销售进度');
        }

    });

})(jQuery);
</script>