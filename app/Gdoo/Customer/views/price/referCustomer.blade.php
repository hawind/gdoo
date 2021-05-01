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
        {field:'product_id', hide: true},
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, sortable: false, width: 40},
        {headerName:'存货编码', field:'product_code', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName:'产品名称', field: 'product_name', suppressNavigable: false, width: 220},
        {headerName:'规格型号', field:'product_spec', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName:'产品条码', field:'product_barcode', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName:'计量单位', field:'product_unit', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName:'销售价格', field:'price', cellClass:'text-right', width: 120},
        {headerName:'备注', field:'remark', width: 200},
    ];

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    new agGrid.Grid(gridDiv, grid);
    
    grid.remoteData();

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