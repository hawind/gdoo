<style>
.vue-list-page .search-inline-form .form-group {
    margin-left: 4px;
}
.vue-list-page .btn-group {
    margin-left: 4px;
}
</style>

<div class="vue-list-page" id="{{$header['master_table']}}-controller">
    <div class="panel no-border">
        @include('headers3')
        <div class='list-jqgrid'>
            <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
        </div>
    </div>
</div>

{{$header["js"]}}

<script>

(function ($) {
    var header = {{json_encode($header, JSON_UNESCAPED_UNICODE)}};

    var table = '{{$header["master_table"]}}';
    var config = gdoo.grids[table];
    var action = config.action;
    var search = config.search;

    const vueData = {
        data() {
            return {
                header: {},
                tab_active: '',
            }
        },mounted() {
            abc(this);
        },
        methods: {
            url(url, query) {
                var me = this;
                let params = Vue.toRaw(me.header.search_form.params);
                for (const key in query) {
                    params[key] = query[key];
                }
                return app.url(url, params);
            }
        }
    }
    Vue.createApp(vueData).mount("#{{$header['master_table']}}-controller");

    function abc(vue) {

        action.dialogType = 'layer';

        // 自定义搜索方法
        search.searchInit = function (e) {
            var self = this;
        }
        
        var options = new agGridOptions();
        var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
        gridDiv.style.height = getPanelHeight(48);

        options.remoteDataUrl = '{{url()}}';
        options.remoteParams = search.advanced.query;
        options.columnDefs = config.cols;
        options.onRowDoubleClicked = function (params) {
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

        new agGrid.Grid(gridDiv, options);

        // 读取数据
        options.remoteData({page: 1});

        options.remoteSuccessed = function(res) {
            vue.header = res.header;
            vue.tab_active = vue.header.search_form.params['tab'] ? vue.header.search_form.params['tab'] : vue.header.tabs.items[0].value;
        };

        // 绑定自定义事件
        var $gridDiv = $(gridDiv);
        $gridDiv.on('click', '[data-toggle="event"]', function () {
            var data = $(this).data();
            if (data.master_id > 0) {
                action[data.action](data);
            }
        });
        config.grid = options;
    }

})(jQuery);
</script>

@include('footers')