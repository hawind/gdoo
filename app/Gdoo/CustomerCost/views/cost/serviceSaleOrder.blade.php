<style>
.modal-body { overflow:hidden; }
</style>

<div class="wrapper-xs">
    <form id="dialog-customer_cost-search-form" name="dialog_customer_cost_search_form" class="form-inline search-inline-form" method="get">
        @include('searchForm3')
    </form>
</div>

<div id="ref_customer_cost" class="ag-theme-balham" style="width:100%;height:380px;"></div>

<script>
var $ref_customer_cost = null;
var params = JSON.parse('{{json_encode($query)}}');
(function($) {
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowMultiSelectWithClick = false;
    grid.rowSelection = 'multiple';
    grid.columnDefs = [
        {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, sortable: false, width: 40},
        {cellClass:'text-center', sortable: false, field: 'sn', headerName: '单据编号', minWidth: 160},
        {cellClass:'text-center', sortable: false, field: 'fee_category_id_name', headerName: '费用类型', width: 100},
        {cellClass:'text-center', sortable: false, field: 'customer_code', headerName: '客户编码', width: 100},
        {cellClass:'text-center', sortable: false, field: 'customer_name', headerName: '客户名称', width: 100},
        {cellClass:'text-right', sortable: false, type:'number', field: 'total_money', headerName: '费用金额', width: 100},
        {cellClass:'text-right', sortable: false, type:'number', field: 'money', headerName: '可用金额', width: 100},
        {cellClass:'text-right', sortable: false, type:'number', field: 'use_money', headerName: '已用金额', width: 100},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    var gridDiv = document.querySelector("#ref_customer_cost");
    new agGrid.Grid(gridDiv, grid);
    grid.remoteData();

    $ref_customer_cost = grid;

    var data = JSON.parse('{{json_encode($search["forms"])}}');
    var search = $('#dialog-customer_cost-search-form').searchForm({
        data: data,
        init:function(e) {}
    });

    search.find('#search-submit').on('click', function() {
        var query = search.serializeArray();
        $.map(query, function(row) {
            params[row.name] = row.value;
        });
        grid.remoteData(params);
        return false;
    });
})(jQuery);
</script>