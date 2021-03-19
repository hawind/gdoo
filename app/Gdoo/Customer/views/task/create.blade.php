<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            {{$form['tpl']}}
        </form>
    </div>
</div>

<script>
var form_action = '{{$form["action"]}}';
(function ($) {

    $("#customer_task_data_tool").append('<a class="btn btn-sm btn-default" href="javascript:importExcel();">导入</a>');

    // 发货记录
    function importExcel() {
        var url = app.url('customer/task/importExcel');
        formDialog({
            title: '导入数据',
            url: url,
            id: 'import_excel',
            dialogClass:'modal-md',
            onSubmit: function() {
                var me = this;
                var form = $('#import_excel');
                var file = document.querySelector("#import_file").files[0];
                var formData = new FormData();
                formData.append('file', file);
                var loading = showLoading();
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
                            grid.api.setRowData([]);
                            for (var i = 0; i < res.data.length; i++) {
                                var row = res.data[i];
                                grid.api.memoryStore.create(row);
                            }
                            grid.generatePinnedBottomData();
                            $(me).dialog('close');
                            toastrSuccess('导入数据成功。');
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
    window.importExcel = importExcel;

    // grid初始化事件
    gdoo.event.set('grid.customer_task_data', {
        ready(me) {
            grid = me;
            grid.dataKey = 'customer_id';
        }
    });
})(jQuery);

</script>