<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline search-inline-form" method="get">
        @include('searchForm3')
    </form>
</div>

<div class="col-xs-3">
    <div class="gdoo-list-grid">
        <div id="dialog-{{$search['query']['id']}}-tree" style="width:100%;height:380px;" class="ag-theme-balham abc"></div>
    </div>
</div>

<div class="col-xs-9">
    <div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:380px;"></div>
</div>

<div class="clearfix"></div>

<style>
.ag-theme-balham {
    border-left: 1px solid #BDC3C7;
    border-right: 1px solid #BDC3C7;
}
.abc {
    border-right: 1px solid #BDC3C7;
}
.tree-box {
    border: 1px solid #dee5e7;
    overflow-y: auto;
}
.tree-box .ul {
    margin-bottom: 0;
}
.col-xs-3 {
    padding-left: 5px;
    padding-right: 0;
}
.col-xs-9 {
    padding-left: 5px;
    padding-right: 5px;
}
</style>

<script>
(function ($) {
    var treeGrid = new agGridOptions();

    treeGrid.autoGroupColumnDef = {
        headerName: '产品类别',
        width: 250,
        sortable: false,
        suppressMenu: true,
        cellRendererParams: {
            suppressCount: false,
        }
    };
    treeGrid.treeData = true;
    treeGrid.groupDefaultExpanded = 1;
    treeGrid.getDataPath = function(data) {
        return data.tree_path;
    };
    treeGrid.columnDefs = [];

    treeGrid.rowSelection = 'single';
    treeGrid.enableCellTextSelection = false;
    treeGrid.onRowClicked = function (params) {
        var query = {};
        query['category_id'] = params.data.id;
        query['page'] = 1;
        grid.remoteData(query);
    };
    treeGrid.remoteDataUrl = "{{url('category')}}";

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}-tree");
    new agGrid.Grid(gridDiv, treeGrid);
    treeGrid.remoteData();

    var search = JSON.parse('{{json_encode($search)}}');
    var params = search.query;

    var grid = new agGridOptions();
    var option = gdoo.dialogInit(params, grid);
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;

    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: grid.multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, suppressSizeToFit: true, cellRenderer: 'htmlCellRenderer', field: 'images', headerName: '图片', width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'code', headerName: '存货编码', width: 100},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'name', headerName: '产品名称', minWidth: 140},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'spec', headerName: '规格型号', width: 100},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'barcode', headerName: '产品条码', width: 120},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'unit_id_name', headerName: '计量单位', width: 80},
        {suppressMenu: true, cellClass:'text-right', field: 'price', headerName: '价格', width: 80},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    new agGrid.Grid(gridDiv, grid);
    
    grid.remoteData({page: 1});

    var data = search.forms;
    var search = $("#dialog-{{$search['query']['id']}}-search-form").searchForm({
        data: data
    });
    search.find('#search-submit').on('click', function() {
        var values = search.serializeArray();
        $.map(values, function(row) {
            params[row.name] = row.value;
        });
        grid.remoteData(params);
        return false;
    });

})(jQuery);
</script>