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
        action.rowDoubleClick = action.show;

        var status = function(type) {
            var grid = config.grid;
            var selections = grid.jqGrid('getSelections');
            var ids = [];
            $.each(selections, function(i, selection) {
                ids.push(selection.id);
            });
            if (ids.length > 0) {
                $.post('{{url("status")}}', {type: type, id: ids}, function(res) {
                    if(res.status) {
                        toastrSuccess(res.data);
                        grid.remoteData({page: 1});
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