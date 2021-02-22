<div class="padder">
<div class="m-t-sm m-b-sm">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline" method="get">
        @include('searchForm3')
    </form>
</div>
</div>

<div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:380px;"></div>

<script>
(function ($) {
var search = JSON.parse('{{json_encode($search)}}');
var params = search.query;
var sid = params.prefix == 1 ? 'sid' : 'id';
var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
var grid = new agGridOptions();
var selectedData = {};
var multiple = params.multi == 0 ? false : true;
grid.remoteDataUrl = '{{url()}}';
grid.remoteParams = {};
//grid.rowMultiSelectWithClick = multiple;
grid.rowSelection = multiple ? 'multiple' : 'single';
grid.columnDefs = [
    {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
    {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'name', headerName: '姓名', minWidth: 160},
    {suppressMenu: true, cellClass:'text-center', field: 'phone', headerName: '手机', width: 160},
    {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 80}
];

grid.onRowClicked1 = function(row) {
    var id = row.data[sid];
    if (selectedData[id]) {
        delete selectedData[id];
        row.node.setSelected(false);
    } else {
        if (multiple == false) {
            selectedData = {};
        }
        selectedData[id] = row.data.name;
    }
    writeSelected();
};

grid.onSelectionChanged = function() {
    var rows = grid.api.getSelectedRows();
    selectedData = {};
    for (let i = 0; i < rows.length; i++) {
        var row = rows[i];
        selectedData[row.id] = row.name;
    }
    writeSelected();
};

grid.onRowDoubleClicked = function (row) {
    grid.onRowClicked(row);
    $('#gdoo-dialog-' + params.dialog_index).dialog('close');
};

function initSelected() {
    selectedData = {};
    var id = $('#'+params.id).val();
    var text = $('#'+params.id+'_text').val();
    if (id && text) {
        id = id.split(',');
        text = text.split(',');
        for (var i = 0; i < id.length; i++) {
            selectedData[id[i]] = text[i];
        }
    }
}

function writeSelected() {
    var id = [];
    var text = [];
    $.each(selectedData, function(k, v) {
        id.push(k);
        text.push(v);
    });
    $('#'+params.id).val(id.join(','));
    $('#'+params.id+'_text').val(text.join(','));
}

new agGrid.Grid(gridDiv, grid);

// 读取数据
grid.remoteData({page: 1});

// 数据载入成功
grid.remoteSuccessed = function() {
    initSelected();
    grid.api.forEachNode(function(node) {
        // 默认选中
        $.each(selectedData, function(k, v) {
            if (node.data[sid] == k) {
                node.setSelected(true);
            }
        });
    });
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