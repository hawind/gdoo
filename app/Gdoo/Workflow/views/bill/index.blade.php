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
        // 双击行执行的方法
        action.rowDoubleClick = action.edit;

        action.view = function(data) {
            var me = this;
            var grid = config.grid;
            var url = app.url('workflow/template/index', {bill_id: data.master_id});
            var index = layer.open({
                skin: 'layui-layer-frame',
                scrollbar: false,
                closeBtn: 2,
                title: data.master_name + '[视图]',
                type: 2,
                move: false,
                area: ['100%', '100%'],
                content: url,
            });
        }
        action.flow = function(data) {
            var me = this;
            var grid = config.grid;
            top.addTab('model/step/index2?bill_id=' + data.master_id, 'flow_step_index2', '单据流程');
        }
        action.permission = function(data) {
            var me = this;
            var grid = config.grid;
            var url = app.url('model/permission/index', {bill_id: data.master_id});
            var index = layer.open({
                skin: 'layui-layer-frame',
                scrollbar: false,
                closeBtn: 2,
                title: data.master_name + '[权限]',
                type: 2,
                move: false,
                area: ['100%', '100%'],
                content: url,
            });
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