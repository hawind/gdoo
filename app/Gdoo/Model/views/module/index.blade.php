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

        action.refresh = function() {
            var me = this;
            var loading = layer.msg('模块更新中...', {
                icon: 16, shade: 0.1, time: 1000 * 120
            });
            $.post(app.url('model/module/refresh'), function(res) {
                if (res.status) {
                    options.remoteData({page: 1});
                    toastrSuccess(res.data);
                } else {
                    toastrError(res.data);
                }
            },'json').complete(function() {
                layer.close(loading);
            });
        }

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