<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline" method="get">
        <!--
        <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-sm btn-default active">
                <input type="radio" name="group_id" value="1">用户
            </label>
            <label class="btn btn-sm btn-default">
                <input type="radio" name="group_id" value="2">客户
            </label>
        </div>
        -->
        @include('searchForm3')
    </form>
</div>

<div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:380px;"></div>

<script>
(function ($) {
    var search = JSON.parse('{{json_encode($search)}}');
    var params = search.query;

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    var grid = new agGridOptions();
    var multiple = params.multi == 0 ? false : true;
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.suppressRowClickSelection = true;
    grid.rowSelection = multiple ? 'multiple' : 'single';
    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'username', headerName: '用户名', width: 60},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'name', headerName: '名称', minWidth: 160},
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

    grid.onRowClicked = function(row) {
        var selected = row.node.isSelected();
        if (selected === false) {
            row.node.setSelected(true, true);
        }
    };

    grid.onRowDoubleClicked = function (row) {
        var ret = gdoo.writeSelected(event, params, option, grid);
        if (ret == true) {
            $('#gdoo-dialog-' + params.dialog_index).dialog('close');
        }
    };

    gdoo.dialogs[option.id] = grid;
    new agGrid.Grid(gridDiv, grid);

    grid.remoteData({page: 1});
    grid.remoteAfterSuccess = function() {
        gdoo.initSelected(params, option, grid);
    }

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
