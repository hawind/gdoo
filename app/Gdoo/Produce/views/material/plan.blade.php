<div class="panel no-border" id="material_plan-controller">

    <div class="wrapper-sm">
        @include('searchForm7')
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="filter"><i class="fa fa-search"></i> 筛选</a>
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="export"><i class="fa fa-share"></i> 导出</a>
        <a class="btn btn-sm btn-default" data-toggle="material_plan" data-action="total"><i class="fa fa-cubes"></i> 用料总量</a>
        <span id="plan_info"></span>
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
        var cellClassRules = {
            "rowspan": function(params) {
                return params.data.rowspan > 1 ? 1 : 0;
            },
            "rowspan_end": function(params) {
                return params.data.rowspan_end == 1 ? 1 : 0;
            }
        };
        var rowSpan = function(params) {
            return params.data.rowspan > 1 ? params.data.rowspan : 0;
        }

        var searchOpen = false;
        var table = 'material_plan';
        var search = JSON.parse('{{json_encode($search)}}');
        var cols = [ 
            {field: "product_name", headerName: "成品名称", sortable: false, suppressMenu: true, width: 140, rowSpan: rowSpan, cellClassRules: cellClassRules}, 
            {field: "product_spec", headerName: "规格型号", sortable: false, suppressMenu: true, cellClass: "text-center", width: 120, rowSpan: rowSpan, cellClassRules: cellClassRules}, 
            {field: "product_unit", headerName: "计量单位", sortable: false, suppressMenu: true, cellClass: "text-center", width: 60, rowSpan: rowSpan, cellClassRules: cellClassRules}, 
            {field: "product_num", headerName: "计划数量", sortable: false, suppressMenu: true, cellClass: "text-right",  width: 80, rowSpan: rowSpan, cellClassRules: cellClassRules}, 
            {field: "category_name", headerName: "物料分类", sortable: false, suppressMenu: true, width: 140}, 
            {field: "material_name", headerName: "物料名称", sortable: false, suppressMenu: true, width: 100}, 
            {field: "material_num", headerName: "数量", sortable: false, suppressMenu: true, cellClass: "text-right", calcFooter: "sum", type: "number", width: 60}, 
            {field: "total_num", headerName: "计划用料数量", sortable: false, suppressMenu: true, cellClass: "text-right", calcFooter: "sum", type: "number", width: 70}, 
            {field: "remark", headerName: "备注", sortable: false, suppressMenu: true, cellClass: "text-right", calcFooter: "sum", type: "number", width: 70}
        ];

        var grid = new agGridOptions();
        grid.suppressRowTransform = true;
        var gridDiv = document.querySelector("#material_plan-grid");
        gridDiv.style.height = getPanelHeight(12);

        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.query;
        grid.columnDefs = cols;
        grid.rowSelection = 'single';

        var query = {};

        grid.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            if (params.data == undefined) {
                return;
            }
            if (params.data.product_id > 0) {
                query['product_id'] = params.data.product_id;
                viewDialog({
                    title: '单品用料计划',
                    dialogClass: 'modal-md',
                    url: app.url('produce/material/planProduct', query),
                    close: function(res) {
                        $(this).dialog("close");
                    }
                });
            }
        };

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
                LocalExport(grid, '用料计划');
            }
            if (data.action == 'total') {
                viewDialog({
                    title: '用料计划总量',
                    dialogClass: 'modal-md',
                    url: app.url('produce/material/planTotal', query),
                    close: function(res) {
                        $(this).dialog("close");
                    }
                });
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

                        query['date'] = params['search_0'];
                        query['department_id'] = params['search_1'];
                        var text = $('#material_plan-search-form-advanced_advanced-search-value-1_text').val();
                        $('#plan_info').text(params['search_0'] + ' ' + text);

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