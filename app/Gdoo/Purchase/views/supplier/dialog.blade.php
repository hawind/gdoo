<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline search-inline-form" method="get">
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
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'name', headerName: '名称', minWidth: 160},
        {suppressMenu: true, cellClass:'text-center', field: 'code', headerName: '编码', width: 60},
        {suppressMenu: true, cellClass:'text-center', cellRenderer: statusRenderer, field: 'status',  headerName: '状态', width: 60},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];

    function statusRenderer(row) {
        if (row.value == 0) {
            return '<span style="color:red">禁用</span>';
        }
        if (row.value == 1) {
            return '启用';
        }
    }

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