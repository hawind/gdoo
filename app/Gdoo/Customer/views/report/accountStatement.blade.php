<div class="panel no-border" id="material_plan-controller">

    <div class="wrapper-sm">
        @include('searchForm7')
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="filter"><i class="fa fa-search"></i> 筛选</a>
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="export"><i class="fa fa-share"></i> 导出</a>
    </div>

    <div class='list-jqgrid'>
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
            {field: "dDate", headerName: "日期", sortable: false, suppressMenu: true, cellClass: "text-center", width: 100}, 
            {field: "cdwname", headerName: "客户名称", sortable: false, suppressMenu: true, cellClass: "text-center", width: 180}, 
            {field: "tax_name", headerName: "开票单位", sortable: false, suppressMenu: true, cellClass: "text-center", width: 180}, 
            {field: "cdlcode", headerName: "单据编号", sortable: false, suppressMenu: true, cellClass: "text-center", width: 120}, 
            {field: "vtype", headerName: "单据类型", sortable: false, suppressMenu: true, cellClass: "text-center",  width: 120}, 
            {field: "dgst", headerName: "摘要", sortable: false, suppressMenu: true, cellClass: "text-left",  minWidth: 200}, 
            {field: "qtfy", headerName: "其他费用", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120}, 
            {field: "xzfy", headerName: "本次新增费用", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120}, 
            {field: "sl", headerName: "发货数量", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120}, 
            {field: "jf", headerName: "发货总金额", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120},
            {field: "bcsyfy", headerName: "使用费用金额", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120},
            {field: "df", headerName: "收款金额", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120},
            {field: "ye", headerName: "余额", type: "number", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 120}, 
        ];

        var grid = new agGridOptions();
        grid.suppressRowTransform = true;
        var gridDiv = document.querySelector("#material_plan-grid");
        gridDiv.style.height = getPanelHeight(12);

        grid.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            var data = params.data;
            if (data == undefined) {
                return;
            }
            if (data.srcMasterBID > 0) {
                var key = data.url.replace(/\//g,'_');
                top.addTab(data.url + '?id=' + data.srcMasterBID, key, data.app_name);
            }
        };

        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.query;
        grid.columnDefs = cols;
        grid.rowSelection = 'single';
        new agGrid.Grid(gridDiv, grid);

        // 读取数据
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
                LocalExport(grid, '客户对账单');
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