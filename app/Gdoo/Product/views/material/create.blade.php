<div class="form-panel">
    <div class="form-panel-header">
    <div class="pull-right">
    </div>
    {{$form['btn']}}
</div>
<div class="form-panel-body panel-form-{{$form['action']}}">
    <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
        <div class="panel">
            {{$form['tpl']}}
        </div>
        <div id="tab-content-product_material">
            <div id="grid_product_material_data" class="ag-theme-balham ag-bordered" style="width:100%;"></div>
        </div>
    </form>
</div>
</div>

<script>
var $table = null;
var product_id = $('#product_material_product_id').val();
var params = {product_id: product_id};

(function($) {
    var options = {};
    options.columns = [
        {field:'id', hide: true},
        {field:'material_id', hide: true},
        {field:'warehouse_id', hide: true},
        {suppressSizeToFit: true, headerName:'', cellRenderer:'optionCellRenderer', width: 60, sortable: false, cellClass: 'text-center', suppressNavigable: true},
        {headerName: '仓库',editable: true, cellClass:'text-center', suppressNavigable: false, width: 100,
            cellEditorParams: {
                form_type: 'dialog',
                title: '仓库',
                type: 'warehouse',
                field: 'warehouse_name',
                url: 'stock/warehouse/dialog',
                query: {
                    form_id: "product_material_data",
                    id: "warehouse_id",
                    name: "warehouse_name"
                }
            },
            cellEditor: 'dialogCellEditor',
            field: 'warehouse_name'
        },
        {headerName: '物料编码', field:'material_code', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '物料名称',editable: true,suppressNavigable: false, width: 220,
            cellEditorParams: {
                form_type: 'dialog',
                title: '物料',
                type: 'product',
                field: 'material_name',
                url: 'product/product/dialog',
                query: {
                    form_id: "product_material_data",
                    id: "material_id",
                    name: "material_name"
                }
            },
            cellEditor: 'dialogCellEditor',
            field: 'material_name'
        },
        {headerName: '规格型号', field:'material_spec', cellClass:'text-center', suppressNavigable: false, width: 160},
        {headerName: '物料条码', field:'material_barcode', cellClass:'text-center', suppressNavigable: false, width: 120},
        {headerName: '计量单位', field:'material_unit', cellClass:'text-center', suppressNavigable: false, width: 100},
        {headerName: '用量', field:'quantity', editable: true, cellClass:'text-right', width: 100},
        {headerName: '损耗率(%)', field:'loss_rate', type:'number', editable: true, cellClass:'text-center', width: 100},
        {headerName: '备注', field:'remark', editable: true, width: 200},
    ];

    options.table = "product_material_data";
    options.title = "物料列表";
    options.heightTop = 12;

    options.links = {
        warehouse_id: {
            warehouse_id: "id",
            warehouse_name: "name",
        },
        material_id: {
            material_id: "id",
            material_name: "name",
            material_code: "code",
            material_spec: "spec",
            material_barcode: "barcode",
            material_unit: "unit_id_name"
        }
    };

    var grid = gridForms("product_material", "product_material_data", options);
    grid.dataKey = 'material_id';

    $.post(app.url('product/material/list'), params, function(res) {
        console.log(res.data);
        if (res.data.length > 0) {
            grid.api.setRowData(res.data);
        }
    });

    // 选择产品事件
    gdoo.event.set('product_material.product_id', {
        onSelect(row) {
            if (row.id) {
                params['product_id'] = row.id;
                $.post(app.url('product/material/list'), params, function(res) {
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

})(jQuery);
</script>