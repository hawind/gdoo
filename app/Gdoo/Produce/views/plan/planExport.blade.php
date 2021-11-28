<div class="panel no-border" id="material_plan-controller">

    <div class="wrapper-sm">
        @include('searchForm7')
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="filter"><i class="fa fa-search"></i> 筛选</a>
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="export"><i class="fa fa-share"></i> 导出</a>
    </div>

    <div class='gdoo-list-grid'>
        <div id="material_plan-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>

<style>
.ag-row {
    display:table;
}
.rowspan {
    background-color: #fff;
    top: -1px;
    border-top: 1px solid #d9dcde !important;
    display: table-cell;
    vertical-align: middle;
}
.rowspan_end {
    border-bottom: 1px solid #d9dcde !important;
}
</style>

<script>
    (function ($) {
        var searchOpen = false;
        var table = 'material_plan';
        var search = JSON.parse('{{json_encode($search)}}');
        var cols = [ 
            {field: "sn", cellClass: "text-center", suppressSizeToFit: true, headerName: "", type: 'sn', width: 40}, 
            {field: "product_name", headerName: "成品名称", sortable: false, suppressMenu: true, width: 140}, 
            {field: "product_spec", headerName: "规格型号", sortable: false, suppressMenu: true, cellClass: "text-center", width: 120}, 
            {field: "product_unit", headerName: "计量单位", sortable: false, suppressMenu: true, cellClass: "text-center", width: 60}, 
            {field: "plan_num", headerName: "计划数量", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 80}, 
            // {field: "quantity", headerName: "生产数量", sortable: false, suppressMenu: true, width: 140}, 
            {field: "remark", headerName: "备注", sortable: false, suppressMenu: true, width: 100}, 
            {field: "date", headerName: "计划日期", sortable: false, suppressMenu: true, cellClass: "text-center", width: 100}, 
        ];

        var grid = new agGridOptions();
        grid.suppressRowTransform = true;
        var gridDiv = document.querySelector("#material_plan-grid");
        gridDiv.style.height = getPanelHeight(12);

        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.query;
        grid.columnDefs = cols;
        grid.rowSelection = 'single';
        new agGrid.Grid(gridDiv, grid);

        grid.remoteData({page: 1});

        // 绑定自定义事件
        var $gridDiv = $(gridDiv);
        $gridDiv.on('click', '[data-toggle="event"]', function () {
            var data = $(this).data();
            if (data.master_id > 0) {
                action[data.action](data);
            }
        });

        var data = search.forms;
        var search = $("#material_plan-search-form-advanced");
        search.searchForm({
            data: data,
            advanced: 1,
        });
        search.find('#search-submit').on('click', function() {
            var params = search.serializeArray();
            $.map(params, function(row) {
                data[row.name] = row.value;
            });
            grid.remoteData(data);
            return false;
        });

        $('#material_plan-controller').on('click', '[data-toggle="material_plan"]', function() {
            var data = $(this).data();
            if (data.action == 'filter') {
                searchBox();
            }
            if (data.action == 'export') {
                LocalExport(grid, '生产计划单导出');
            }
        });

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
                        params['page'] = 1;
                        grid.remoteData(params);
                        $(this).dialog("close");
                        return false;
                    }
                }]
            });
        }

        if (searchOpen == false) {
            searchBox();
        }

    })(jQuery);

</script>