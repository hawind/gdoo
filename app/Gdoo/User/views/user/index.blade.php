<div class="gdoo-list-page" id="{{$header['master_table']}}-page">

    <div class="gdoo-list panel">
        <div class="gdoo-list-header">
            <gdoo-grid-header :header="header" :grid="grid" :action="action" />
        </div>

        <div class='gdoo-list-grid'>
            <div id="{{$header['master_table']}}-grid" class="ag-theme-balham"></div>
        </div>
    </div>

</div>

<script>
Vue.createApp({
    components: {
        gdooGridHeader,
    },
    setup(props, ctx) {
        var table = '{{$header["master_table"]}}';

        var config = new gdoo.grid(table);

        var grid = config.grid;
        grid.autoColumnsToFit = true;
        grid.remoteDataUrl = '{{url()}}';

        var action = config.action;
        // 详情页打开方式
        action.dialogType = 'layer';
        // 双击行执行的方法
        action.rowDoubleClick = action.show;

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
                        var loading = showLoading();
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
                        var loading = showLoading();
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

        var setup = config.setup;

        Vue.onMounted(function() {
            var gridDiv = config.div(136);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-page");
</script>