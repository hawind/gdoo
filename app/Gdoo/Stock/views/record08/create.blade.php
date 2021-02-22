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
var table = '{{$form["table"]}}';

$(function() {
    $('#stock_record08_data_tool').append('<a class="btn btn-sm btn-default" href="javascript:importExcel();">导入</a>');
});

// 发货记录
function importExcel() {
    var url = app.url('stock/record08/importExcel');
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
                        grid.api.setRowData([]);
                        for (var i = 0; i < res.data.length; i++) {
                            var row = res.data[i];
                            grid.api.memoryStore.create(row);
                        }
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
gdoo.event.set('grid.stock_record08_data',{
    ready(me) {
        grid = me;
        grid.dataKey = 'product_id';
    },
    editable: {
        product_name(params) {
            return has_warehouse_id();
        },
        poscode(params) {
            return has_warehouse_id();
        }
    }
});

function get_warehouse_id() {
    var warehouse_id = $('#stock_record08_warehouse_id').val();
    return warehouse_id || 0;
}

function has_warehouse_id() {
    var warehouse_id = get_warehouse_id();
    if (warehouse_id == 0) {
        toastrError('请先选择仓库');
        return false;
    } else {
        return true;
    }
}

// 子表对话框
gdoo.event.set('stock_record08_data.product_id', {
    query(query) {
    },
    onSelect(row, selectedRow) {
        return true;
    }
});

// 选择货位编号
gdoo.event.set('stock_record08_data.poscode', {
    query(query) {
        var warehouse_id = get_warehouse_id();
        if (warehouse_id > 0) {
            query.warehouse_id = warehouse_id;
        }
    },
    onSelect(row, selectedRow) {
        row.poscode = selectedRow.code;
        row.posname = selectedRow.name;
        return true;
    }
});
</script>