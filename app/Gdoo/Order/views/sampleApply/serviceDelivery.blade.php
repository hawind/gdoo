<style>
.modal-body { overflow:hidden; }
</style>

<div class="wrapper-xs">
    <div id="dialog-sample_apply-toolbar">
        <form id="dialog-sample_apply-search-form" name="dialog_sample_apply_search_form" class="search-inline-form form-inline" method="get">
            @include('searchForm3')
        </form>
    </div>
</div>

<div id="ref_sample_apply" class="ag-theme-balham" style="width:100%;height:140px;"></div>

<div class="m-t-xs">
    <div id="ref_sample_apply_data" class="ag-theme-balham" style="width:100%;height:240px;"></div>
</div>
<script>
var $ref_sample_apply = null;
var $ref_sample_apply_data = null;
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    params['master'] = 1;
    var mGridDiv = document.querySelector("#ref_sample_apply");
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
        {cellClass:'text-center', field: 'sn', headerName: '单据编号', minWidth: 160},
        {cellClass:'text-center', field: 'created_at', headerName: '单据日期', width: 120},
        {cellClass:'text-center', field: 'region_name', headerName: '区域', width: 100},
        {cellClass:'text-center', field: 'department_name', headerName: '部门', width: 120},
        {cellClass:'text-center', field:'freight_type_name', headerName: '样品送达方式', width: 120},
        {field:'customer_name', headerName: '随货客户', width: 160},
        {field:'freight_address', headerName: '邮寄地址', width: 160},
        {cellClass:'text-center', field:'contact', headerName: '联系人', width: 100},
        {cellClass:'text-center', field:'contact_phone', headerName: '联系电话', width: 140},
        {cellClass:'text-center', field:'purpose', headerName: '样品用途', width: 180},
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
    $ref_sample_apply = mGrid;

    params['master'] = 0;
    var sGridDiv = document.querySelector("#ref_sample_apply_data");
    var sGrid = new agGridOptions();
    sGrid.remoteDataUrl = '{{url()}}';
    sGrid.remoteParams = params;
    sGrid.rowMultiSelectWithClick = true;
    sGrid.rowSelection = 'multiple';
    sGrid.autoColumnsToFit = false;
    sGrid.defaultColDef.suppressMenu = true;
    sGrid.defaultColDef.sortable = false;
    sGrid.getRowClass = function(params) {
        var data = params.data;
        params.node.setSelected(true);
        if (toNumber(data.ky_num) < toNumber(data.wc_num)) {
            return 'ag-row-warn';
        }
    };
    sGrid.columnDefs = [
        {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
        {cellClass:'text-center', field: 'product_code', headerName: '存货编码', width: 100},
        {field: 'product_name', headerName: '商品名称', minWidth: 180},
        {cellClass:'text-center', field: 'product_spec', headerName: '商品规格', width: 140},
        {cellClass:'text-center', field: 'product_unit', headerName: '计量单位', width: 80},
        {cellClass:'text-right', field: 'ky_num', type:'number', headerName: '现存量', width: 80},
        {cellClass:'text-right', field: 'quantity', type:'number', headerName: '申请数量', width: 80},
        {cellClass:'text-right', field: 'yc_num', type:'number', headerName: '已发数量', width: 80},
        {cellClass:'text-right', field: 'wc_num', type:'number', headerName: '未发数量', width: 80},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];
    new agGrid.Grid(sGridDiv, sGrid);
    
    sGrid.remoteData();
    $ref_sample_apply_data = sGrid;

    var data = JSON.parse('{{json_encode($search["forms"])}}');
    var search = $('#dialog-sample_apply-search-form').searchForm({
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