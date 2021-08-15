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

        action.config = function() {
            var me = this;
            var grid = config.grid;
            var rows = grid.api.getSelectedRows();
            if (rows.length == 1) {
                var data = rows[0];
                var url = app.url('produce/material/config', {id: data.id});
                layer.open({
                    title: false,
                    area: ['100%', '100%'],
                    skin: 'layui-layer-gdoo',
                    scrollbar: false,
                    closeBtn: false,
                    type: 2,
                    content: url,
                });
            } else {
                toastrError('只能修改一条数据。');
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