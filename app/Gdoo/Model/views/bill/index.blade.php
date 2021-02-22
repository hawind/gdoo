{{$header["js"]}}

<div class="panel no-border" id="{{$header['master_table']}}-controller">
    @include('headers')
    <div class='list-jqgrid'>
        <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>
<script>
    (function ($) {
        var table = '{{$header["master_table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        action.dialogType = 'dialog';

        // 自定义搜索方法
        search.searchInit = function (e) {
            var self = this;
        }

        action.view = function(data) {
            var me = this;
            var grid = config.grid;
            var url = app.url('model/template/index', {bill_id: data.master_id});
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
                action.edit(params.data);
            }
        };

        new agGrid.Grid(gridDiv, options);

        // 自定义行按钮渲染
        options.actionCellBeforeRender = function(html, act, data) {
            if (act.action == 'flow') {
                if (data.audit_type == 1) {
                    return html;
                }
                return '';
            } else {
                return html;
            }
        }

        // 读取数据
        options.remoteData({page: 1});

        // 绑定自定义事件
        var $gridDiv = $(gridDiv);
        $gridDiv.on('click', '[data-toggle="event"]', function () {
            var data = $(this).data();
            if (data.master_id > 0) {
                action[data.action](data);
            }
        });
        config.grid = options;
    })(jQuery);

</script>
@include('footers')