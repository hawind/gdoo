<div class="form-panel">
    <div class="form-panel-header">
    <div class="pull-right">
    </div>
    {{$form['btn']}}

    <a href="javascript:referCustomerDialog();" class="btn btn-sm btn-default">
        参照价格
    </a>

</div>
<div class="form-panel-body panel-form-{{$form['action']}}">
    <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
        <div class="panel">
            {{$form['tpl']}}
        </div>
        <div id="tab-content-customer_price">
            <div id="grid_customer_price_data" class="ag-theme-balham ag-bordered" style="width:100%;"></div>
        </div>
    </form>
</div>
</div>

<script>
var $table = null;
var customer_id = $('#customer_price_customer_id').val();
var params = {customer_id: customer_id};

(function($) {
    var options = {};
    options.columns = [
        {field:'id', hide: true},
        {field:'product_id', hide: true},
        {suppressSizeToFit: true, headerName:'', cellRenderer:'optionCellRenderer', width: 60, sortable: false, cellClass: 'text-center', suppressNavigable: true},
        {headerName: '存货编码', field:'product_code', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '产品名称',editable: true,suppressNavigable: false, width: 220,
            cellEditorParams: {
                form_type: 'dialog',
                title: '产品',
                type: 'product',
                field: 'product_name',
                url: 'product/product/dialog',
                query: {
                    form_id: "customer_price_data",
                    id: "product_id",
                    name: "product_name"
                }
            },
            cellEditor: 'dialogCellEditor',
            field: 'product_name'
        },
        {headerName: '规格型号', field:'product_spec', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '产品条码', field:'product_barcode', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '计量单位', field:'product_unit', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '销售价格', field:'price', editable: true, cellClass:'text-right', width: 120},
        {headerName: '备注', field:'remark', editable: true, width: 200},
    ];

    options.table = "customer_price_data";
    options.title = "订单商品";
    options.heightTop = 12;

    options.links = {
        product_id: {
            product_id: "id",
            product_name: "name",
            product_code: "code",
            product_spec: "spec",
            product_barcode: "barcode",
            product_unit: "unit_id_name"
        }
    };

    var grid = gridForms("customer_price", "customer_price_data", options);
    grid.dataKey = 'product_id';

    $.post(app.url('customer/price/list'), params, function(res) {
        if (res.data.length > 0) {
            grid.api.setRowData(res.data);
        }
    });

    // 选择客户事件
    gdoo.event.set('customer_price.customer_id', {
        onSelect(row) {
            if (row.id) {
                params['customer_id'] = row.id;
                $.post(app.url('customer/price/list'), params, function(res) {
                    if (res.data.length > 0) {
                        grid.api.setRowData(res.data);
                    } else {
                        grid.api.setRowData([]);
                        grid.api.memoryStore.create({});
                    }
                });
                return true;
            }
        }
    });

    var referCustomerDialog = function () {
        var buttons = [{
            text: "取消",
            'class': "btn-default",
            click: function () {
                $(this).dialog("close");
            }
        }];
        buttons.push({
            text: '提交',
            'class': 'btn-info',
            click: function () {
                var loading = showLoading();
                var rows = refer_customer.api.getSelectedRows();
                for (var i = 0; i < rows.length; i++) {
                    let row = rows[i];
                    grid.api.memoryStore.create(row);
                }
                layer.close(loading);
                grid.generatePinnedBottomData();
                $(this).dialog('close');
            }
        });
        $.dialog({
            title: '参照客户价格',
            url: '{{url("referCustomer")}}',
            dialogClass: 'modal-lg',
            buttons: buttons
        });
    };
    window.referCustomerDialog = referCustomerDialog;

})(jQuery);
</script>