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

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var sid = params.prefix == 1 ? 'sid' : 'id';
    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    var grid = new agGridOptions();
    var selectedData = {};
    var multiple = false;
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.suppressRowClickSelection = true;
    grid.rowSelection = multiple ? 'multiple' : 'single';
    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'sn', type:'sn', suppressSizeToFit: true, headerName: '', width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'warehouse_code', headerName: '仓库编码', width: 60},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'warehouse_name', headerName: '仓库名称', width: 100},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'product_code', headerName: '产品编码', width: 80},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'product_name', headerName: '产品名称', width: 160},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'product_spec', headerName: '规格型号', width: 100},

        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'batch_sn', headerName: '生产批号', width: 80},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'batch_date', headerName: '生产日期', width: 80},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'poscode', headerName: '货位编码', width: 80},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'posname', headerName: '货位名称', width: 80},
        {suppressMenu: true, cellClass:'text-right', sortable: false, field: 'ky_num', type:'number', headerName: '可用数量', width: 80},
    ];

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