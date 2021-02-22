{{$header["js"]}}
<div class="panel no-border" id="{{$header['table']}}-controller">
    @include('headers')
    <div class="list-jqgrid">
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

        // 自定义搜索方法
        search.searchInit = function (e) {
            var self = this;
        }

        action.create = function(data) {
            var me = this;
            var grid = config.grid;
            var url = app.url('file/inspectReport/create');
            formDialog({
                title: '文件上传',
                url: url,
                id: 'inspect_report_create',
                dialogClass:'modal-md',
                onSubmit: function() {
                    var me = this;
                    var form = $('#inspect_report_create');
                    var file = document.querySelector("#inspect_report_file").files[0];
                    var name = form.find('#inspect_report_name').val();
                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('name', name);

                    var loading = layer.msg('数据提交中...', {
                        icon: 16, shade: 0.1, time: 1000 * 120
                    });
                    $.ajax(url, {
                        method: "post",
                        data: formData,
                        processData: false,
                        contentType: false,
                        complete: function() {
                            layer.close(loading);
                        },
                        success: function (res) {
                            if (res.status) {
                                toastrSuccess(res.data);
                                grid.remoteData();
                                $(me).dialog('close');
                            } else {
                                toastrError(res.data);
                            }
                        },
                        error: function (res) {
                            toastrError(res.data);
                        }
                    });
                }
            });
        }

        action.download = function(data) {
            var url = app.url('file/inspectReport/download', {id: data.master_id});
            location.href = url;
        }

        var grid = new agGridOptions();
        grid.remoteDataUrl = '{{url()}}';
        grid.remoteParams = search.advanced.query;
        grid.columnDefs = config.cols;
        var gridDiv = document.querySelector("#{{$header['table']}}-grid");
        gridDiv.style.height = getPanelHeight(48);
        new agGrid.Grid(gridDiv, grid);

        // 读取数据
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