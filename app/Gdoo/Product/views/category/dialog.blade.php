<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline search-inline-form" method="get">
        @include('searchForm6')
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

    grid.autoGroupColumnDef = {
        headerName: '名称',
        width: 250,
        cellRendererParams: {
            checkbox: true,
            suppressCount: false,
        }
    };
    grid.treeData = true;
    grid.groupDefaultExpanded = -1;
    
    grid.getDataPath = function(data) {
        return data.tree_path;
    };
    grid.columnDefs = [];
    grid.columnDefs.push(
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    );

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