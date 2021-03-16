<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline search-inline-form" method="get">
        @include('searchForm3')
    </form>
</div>

<div class="col-xs-3">
    <div class="gdoo-list-grid">
        <div id="dialog-{{$search['query']['id']}}-tree" style="width:100%;height:380px;" class="ag-theme-balham abc"></div>
    </div>
</div>

<div class="col-xs-9">
    <div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:380px;"></div>
</div>

<div class="clearfix"></div>

<style>
.ag-theme-balham {
    border-left: 1px solid #BDC3C7;
    border-right: 1px solid #BDC3C7;
}
.abc {
    border-right: 1px solid #BDC3C7;
}
.tree-box {
    border: 1px solid #dee5e7;
    overflow-y: auto;
}
.tree-box .ul {
    margin-bottom: 0;
}
.col-xs-3 {
    padding-left: 5px;
    padding-right: 0;
}
.col-xs-9 {
    padding-left: 5px;
    padding-right: 5px;
}
</style>

<script>
(function ($) {
    var treeOptions = new agGridOptions();
    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}-tree");
    
    treeOptions.autoGroupColumnDef = {
        headerName: '产品类别',
        width: 250,
        sortable: false,
        suppressMenu: true,
        cellRendererParams: {
            suppressCount: false,
        }
    };
    treeOptions.treeData = true;
    treeOptions.groupDefaultExpanded = 1;
    treeOptions.getDataPath = function(data) {
        return data.tree_path;
    };
    treeOptions.columnDefs = [];

    treeOptions.rowSelection = 'single';
    treeOptions.enableCellTextSelection = false;
    treeOptions.onRowClicked = function (params) {
        var query = {};
        query['category_id'] = params.data.id;
        query['page'] = 1;
        grid.remoteData(query);
    };
    treeOptions.remoteDataUrl = "{{url('category')}}";

    new agGrid.Grid(gridDiv, treeOptions);
    // 读取数据
    treeOptions.remoteData();

    var search = JSON.parse('{{json_encode($search)}}');
    var params = search.query;

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var sid = params.prefix == 1 ? 'sid' : 'id';
    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    var grid = new agGridOptions();
    var multiple = params.multi == 0 ? false : true;
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowSelection = multiple ? 'multiple' : 'single';
    grid.suppressRowClickSelection = true;
    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, suppressSizeToFit: true, cellRenderer: 'htmlCellRenderer', field: 'images', headerName: '图片', width: 40},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'code', headerName: '存货编码', width: 100},
        {suppressMenu: true, cellClass:'text-left', sortable: false, field: 'name', headerName: '产品名称', minWidth: 140},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'spec', headerName: '规格型号', width: 100},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'barcode', headerName: '产品条码', width: 120},
        {suppressMenu: true, cellClass:'text-center', sortable: false, field: 'unit_id_name', headerName: '计量单位', width: 80},
        {suppressMenu: true, cellClass:'text-right', field: 'price', headerName: '价格', width: 80},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
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
        var rows = grid.getSelectedRows();
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
        var values = search.serializeArray();
        $.map(values, function(row) {
            params[row.name] = row.value;
        });
        grid.remoteData(params);
        return false;
    });

})(jQuery);
</script>