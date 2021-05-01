<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline" method="get">
        @include('searchForm3')
    </form>
</div>

<div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:380px;"></div>

<script>
(function ($) {
    var search = JSON.parse('{{json_encode($search)}}');
    var params = search.query;

    var grid = new agGridOptions();
    var option = gdoo.dialogInit(params, grid);

    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;

    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: grid.multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', field: 'sn', headerName: '单据编号', minWidth: 160},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'created_at', headerName: '单据日期', width: 140},
        {suppressMenu: true, cellClass:'text-center', field: 'customer_code', headerName: '客户编号', width: 120},
        {suppressMenu: true, cellClass:'text-left', field: 'customer_id_name', headerName: '客户名称', width: 220},
        {suppressMenu: true, cellClass:'text-right', field: 'total_money', headerName: '订单金额', width: 120},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 80}
    ];

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    new agGrid.Grid(gridDiv, grid);

    grid.remoteData({page: 1});

    var data = search.forms;
    var search = $("#dialog-{{$search['query']['id']}}-search-form").searchForm({
        data: data
    });
    search.find('#search-submit').on('click', function() {
        var params = search.serializeArray();
        $.map(params, function(row) {
            data[row.name] = row.value;
        });
        grid.remoteData(data);
        return false;
    });

})(jQuery);
</script>