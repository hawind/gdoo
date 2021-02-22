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

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var sid = params.prefix == 1 ? 'sid' : 'id';
    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    var grid = new agGridOptions();
    var selectedData = {};
    var multiple = params.multi == 0 ? false : true;
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;
    grid.rowSelection = multiple ? 'multiple' : 'single';

    grid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', field: 'name', headerName: '名称', width: 200},
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
    grid.remoteData();

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