{{$header["js"]}}
<div class="panel no-border" id="{{$header['table']}}-controller">
    @include('headers')
    <div class="gdoo-list-grid">
        <div id="{{$header['table']}}-grid" class="ag-theme-balham"></div>
    </div>
</div>
<script>
    (function ($) {
        var table = '{{$header["table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        action.dialogType = 'layer';

        action.user_warehouse = function(data) {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                var user_id = rows[0].id;
                formDialog({
                    title: '仓库权限',
                    url: app.url('stock/warehouse/permission', {user_id: user_id}),
                    storeUrl: app.url('stock/warehouse/permission'),
                    id: 'user_warehouse',
                    dialogClass:'modal-md',
                    onSubmit: function() {
                        var me = this;
                        var form = gdoo.dialogs['user_warehouse'];
                        var selectedRows = form.api.getSelectedRows();
                        var loading = layer.msg('数据提交中...', {
                            icon: 16, shade: 0.1, time: 1000 * 120
                        });
                        $.post(app.url('stock/warehouse/permission', {user_id: user_id}), {rows: selectedRows}, function(res) {
                            if (res.status) {
                                toastrSuccess(res.data);
                                $(me).dialog('close');
                            } else {
                                toastrError(res.data);
                            }
                        }).complete(function() {
                            layer.close(loading);
                        });
                    }
                });
            } else {
                toastrError('只能选择一条记录');
            }
        }

        action.user_role = function(data) {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                var user_id = rows[0].id;
                formDialog({
                    title: '角色权限',
                    url: app.url('user/role/permission', {user_id: user_id}),
                    storeUrl: app.url('user/role/permission'),
                    id: 'user_role',
                    dialogClass:'modal-md',
                    onSubmit: function() {
                        var me = this;
                        var form = gdoo.dialogs['user_role'];
                        var selectedRows = form.api.getSelectedRows();
                        var loading = layer.msg('数据提交中...', {
                            icon: 16, shade: 0.1, time: 1000 * 120
                        });
                        $.post(app.url('user/role/permission', {user_id: user_id}), {rows: selectedRows}, function(res) {
                            if (res.status) {
                                toastrSuccess(res.data);
                                $(me).dialog('close');
                            } else {
                                toastrError(res.data);
                            }
                        }).complete(function() {
                            layer.close(loading);
                        });
                    }
                });
            } else {
                toastrError('只能选择一条记录');
            }
        }

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

        var gridDiv = document.querySelector("#{{$header['table']}}-grid");
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