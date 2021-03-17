{{$header["js"]}}
<div class="panel no-border" id="{{$header['master_table']}}-controller">
    @include('headers')
    <div class="gdoo-list-grid">
        <div id="{{$header['master_table']}}-grid" class="ag-theme-balham"></div>
    </div>
</div>
<script>
(function($) {
    var table = '{{$header["master_table"]}}';
    var config = gdoo.grids[table];
    var action = config.action;
    var search = config.search;

    var statusAction = function(type) {
        var grid = config.grid;
        var selections = grid.jqGrid('getSelections');
        var ids = [];
        $.each(selections, function(i, selection) {
            ids.push(selection.id);
        });
        if(ids.length > 0) {
            $.post('{{url("status")}}', {type: type, id: ids}, function(res) {
                if(res.status) {
                    toastrSuccess(res.data);
                    grid.trigger('reloadGrid');
                } else {
                    toastrError(res.data);
                }
            },'json');
        } else {
            toastrError('最少选择一行记录。');
        }
    }

    // 标记已读
    action.read = function() {
        status('read');
    };
    // 标记未读
    action.unread = function() {
        status('unread');
    };

    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = search.advanced.query;
    grid.columnDefs = config.cols;
    grid.onRowDoubleClicked = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        if (params.data == undefined) {
            return;
        }
        if (params.data.master_id > 0) {
            action.show(params.data);
        }
    };

    var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
    gridDiv.style.height = getPanelHeight(48);
    new agGrid.Grid(gridDiv, grid);

    grid.remoteData({page: 1});

    // 绑定自定义事件
    var $gridDiv = $(gridDiv);
    $gridDiv.on('click', '[data-toggle="event"]', function () {
        var data = $(this).data();
        if (data.master_id > 0) {
            action[data.action](data);
        }
    });

    config.grid = grid;

})(jQuery);
</script>
@include('footers')