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
    grid.defaultColDef.filter = false;
    grid.singleClickEdit = true;
    grid.rowSelection = 'single';
    grid.suppressCellSelection = false;

    grid.columnDefs = [
        {cellClass:'text-center', field: 'sn', type: 'sn', headerName: '序号', width: 50},
        {cellClass:'text-center', field: 'product_code', headerName: '产品编码', width: 100},
        {cellClass:'text-left', field: 'product_name_spec', headerName: '产品名称', width: 160},
        {cellClass:'text-center', field: 'ProductBatsch', headerName: '生产批次', width: 100},
        {cellClass:'text-right', field: 'kc_num', headerName: '当前库存', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'dphz_num', headerName: '单品汇总', width: 70,type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'xqzc_num', headerName: '需求总差', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'yhk_num', headerName: '已回款数', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'kfzc_num', headerName: '打款差额', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'kfjh_num', headerName: '计划差额', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'waitin_num', headerName: '待入库', width: 60, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        {cellClass:'text-right', field: 'syfh_num', headerName: '上月发货量', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
    ];

    grid.defaultColDef.cellStyle = function(params) {
        if (params.node.rowPinned) {
            return;
        }
        var style = {};
        var value = params.value || 0;
        var field = params.colDef.field;
        if ((field.indexOf('produce_plan_num') === 0 || field == "xqzc_num" || field == "kfzc_num" || field == "kfjh_num") && value > 0) {
            style = {'color':'red'};
        }
        return style;
    };

    var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
    new agGrid.Grid(gridDiv, grid);
    gridDiv.style.height = getPanelHeight(12);

    grid.remoteData();

    var search_advanced = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
    });

    gdoo.grids[table] = {grid: grid};

    var action = new grid(table, '生产计划总表');
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
            action.export(data, '生产计划总表');
        }
    });

})(jQuery);
</script>