<div class="padder">
    <div class="m-t-sm m-b-sm">
        <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline" method="get">
            @include('searchForm')
        </form>
    </div>
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
        {suppressMenu: true, cellClass:'text-center', field: 'name', headerName: '名称', width: 200},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
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