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

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var sid = params.prefix == 1 ? 'sid' : 'id';
    var grid = new agGridOptions();
    var multiple = params.multi == 0 ? false : true;
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.suppressRowClickSelection = true;
    grid.rowSelection = multiple ? 'multiple' : 'single';
    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'name', headerName: '开票名称', minWidth: 160},
        {suppressMenu: true, cellClass:'text-center', field: 'code', headerName: '开票编码', width: 80},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'customer_name', headerName: '客户名称', minWidth: 160},
        {suppressMenu: true, cellClass:'text-center', field: 'customer_code', headerName: '客户编码', width: 80},
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
        var ret = writeSelected();
        if (ret == true) {
            $('#gdoo-dialog-' + params.dialog_index).dialog('close');
        }
    };

    /**
     * 初始化选择
     */
    function initSelected() {
        if (params.is_grid) {
        } else {
            var rows = {};
            var id = $('#'+option.id).val();
            if (id) {
                var ids = id.split(',');
                for (var i = 0; i < ids.length; i++) {
                    rows[ids[i]] = ids[i];
                }
            }
            grid.api.forEachNode(function(node) {
                var key = node.data[sid];
                if (rows[key] != undefined) {
                    node.setSelected(true);
                }
            });
        }
    }

    /**
     * 写入选中
     */
    function writeSelected() {
        var rows = grid.api.getSelectedRows();
        if (params.is_grid) {
            var list = gdoo.forms[params.form_id];
            list.api.dialogSelected(params);
        } else {
            var id = [];
            var text = [];
            $.each(rows, function(k, row) {
                id.push(row[sid]);
                text.push(row.name);
            });
            console.log('#'+option.id);
            $('#'+option.id).val(id.join(','));
            $('#'+option.id+'_text').val(text.join(','));

            if (event.exist('onSelect')) {
                return event.trigger('onSelect', multiple ? rows : rows[0]);
            }
        }
        return true;
    }
    grid.writeSelected = writeSelected;
    gdoo.dialogs[option.id] = grid;

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    new agGrid.Grid(gridDiv, grid);

    // 读取数据
    grid.remoteData({page: 1});

    // 数据载入成功
    grid.remoteSuccessed = function() {
        initSelected();
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