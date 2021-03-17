<style>
.modal-body { overflow:hidden; }
</style>

<div class="wrapper-xs">
    <div id="dialog-promotion-toolbar">
        <form id="dialog-promotion-search-form" name="dialog_promotion_search_form" class="search-inline-form form-inline" method="get">
            @include('searchForm3')
        </form>
    </div>
</div>

<div id="ref_promotion" class="ag-theme-balham" style="width:100%;height:140px;"></div>

<div class="m-t-xs">
    <div id="ref_promotion_data" class="ag-theme-balham" style="width:100%;height:240px;"></div>
</div>
<script>
var $ref_promotion = null;
var $ref_promotion_data = null;
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    params['master'] = 1;
    var mGridDiv = document.querySelector("#ref_promotion");
    var mGrid = new agGridOptions();
    mGrid.remoteDataUrl = '{{url()}}';
    mGrid.remoteParams = params;
    mGrid.rowMultiSelectWithClick = false;
    mGrid.rowSelection = 'multiple';
    mGrid.autoColumnsToFit = false;
    mGrid.defaultColDef.suppressMenu = true;
    mGrid.defaultColDef.sortable = false;
    mGrid.columnDefs = [
        {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
        {cellClass:'text-center', field: 'sn', headerName: '促销编号', minWidth: 160},
        {cellClass:'text-center', field: 'type_name', headerName: '促销类型', width: 80},
        {cellClass:'text-center', field: 'created_at', type: 'datetime', headerName: '单据日期', width: 120},
        {cellClass:'text-center', field: 'master_status', cellRenderer: 'htmlCellRenderer', headerName: '状态', width: 160},
        {cellClass:'text-center', field: 'customer_code', headerName: '客户编码', width: 160},
        {field:'customer_name', headerName: '客户名称', width: 160},
        {field:'warehouse_contact', headerName: '收货人', width: 160},
        {field:'warehouse_phone', headerName: '收货人电话', width: 160},
        {field:'warehouse_address', headerName: '收货地址', width: 260},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    mGrid.onSelectionChanged = function() {
        var rows = mGrid.api.getSelectedRows();
        var ids = [];
        for (let i = 0; i < rows.length; i++) {
            ids.push(rows[i].id);
        }
        params.ids = ids;
        sGrid.remoteData(params);
    };

    new agGrid.Grid(mGridDiv, mGrid);
    mGrid.remoteData();
    $ref_promotion = mGrid;

    params['master'] = 0;
    var sGridDiv = document.querySelector("#ref_promotion_data");
    var sGrid = new agGridOptions();
    sGrid.remoteDataUrl = '{{url()}}';
    sGrid.remoteParams = params;
    sGrid.rowMultiSelectWithClick = true;
    sGrid.rowSelection = 'multiple';
    sGrid.autoColumnsToFit = false;
    sGrid.defaultColDef.suppressMenu = true;
    sGrid.defaultColDef.sortable = false;
    sGrid.suppressRowClickSelection = true;
    sGrid.getRowClass = function(params) {
        params.node.setSelected(true);
    };
    sGrid.columnDefs = [
        {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
        {cellClass:'text-center', field: 'product_code', headerName: '存货编码', width: 100},
        {field: 'product_name', headerName: '商品名称', minWidth: 180},
        {cellClass:'text-center', field: 'product_spec', headerName: '商品规格', width: 140},
        {cellClass:'text-center', field: 'product_unit', headerName: '计量单位', width: 80},
        {cellClass:'text-center', field: 'ky_num', type:'number', headerName: '现存量', width: 80},
        {cellClass:'text-right', field: 'quantity', type:'number', headerName: '促销数量', width: 80},
        {cellClass:'text-right', field: 'ysy_num', type:'number', headerName: '已发数量', width: 80},
        {cellClass:'text-right', field: 'wsy_num', type:'number', headerName: '可用数量', width: 80},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    sGrid.onRowClicked = function(row) {
        var selected = row.node.isSelected();
        if (selected === false) {
            row.node.setSelected(true, true);
        }
    };

    new agGrid.Grid(sGridDiv, sGrid);
    sGrid.remoteData();
    $ref_promotion_data = sGrid;

    var data = JSON.parse('{{json_encode($search["forms"])}}');
    var search = $('#dialog-promotion-search-form').searchForm({
        data: data,
        init:function(e) {}
    });

    search.find('#search-submit').on('click', function() {
        var query = search.serializeArray();
        $.map(query, function(row) {
            params[row.name] = row.value;
        });

        params['master'] = 1;
        mGrid.remoteData(params);

        params['master'] = 0;
        sGrid.remoteData(params);
        return false;
    });

})(jQuery);
</script>