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
        grid.remoteDataUrl = '{{url()}}';
        grid.autoColumnsToFit = true;

        var action = config.action;
        // 双击行执行的方法
        action.rowDoubleClick = action.edit;

        action.test = function() {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                formDialog({
                    title: '测试邮件',
                    url: app.url('system/mail/test', {id: rows[0].id}),
                    storeUrl: app.url('system/mail/test'),
                    id: 'mail_test',
                    dialogClass:'modal-sm',
                    success: function(res) {
                        toastrSuccess(res.data);
                        $(this).dialog("close");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                toastrError('只能选择一条数据。');
            }
        }

        var setup = config.setup;

        Vue.onMounted(function() {
            var gridDiv = config.div(93);
            // 初始化数据
            grid.remoteData({page: 1}, function(res) {
                config.init(res);
            });
        });
        return setup;
    }
}).mount("#{{$header['master_table']}}-page");
</script>