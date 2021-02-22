<div class="wrapper-xs">
    <form id="dialog-{{$search['query']['id']}}-search-form" class="form-inline" method="get">
        @include('searchForm6')
    </form>
</div>

<div id="dialog-{{$search['query']['id']}}" class="ag-theme-balham" style="width:100%;height:200px;"></div>
<div class="m-t-xs">
    <div id="promotion_customer_order_data" class="ag-theme-balham" style="width:100%;height:240px;"></div>
</div>

<div class="clearfix"></div>

<script>
var $promotion_customer_order = null;
var $promotion_customer_order_data = null;
(function ($) {
    var search = JSON.parse('{{json_encode($search)}}');
    var params = search.query;

    var option = gdoo.formKey(params);
    var event = gdoo.event.get(option.key);
    event.trigger('query', params);

    var sid = params.prefix == 1 ? 'sid' : 'id';
    var multiple = params.multi == 0 ? false : true;
    var mGrid = new agGridOptions();

    params['master'] = 1;
    mGrid.remoteDataUrl = '{{url()}}';
    mGrid.remoteParams = params;
    mGrid.rowSelection = multiple ? 'multiple' : 'single';
    mGrid.suppressRowClickSelection = true;
    mGrid.columnDefs = [
        {suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
        {suppressMenu: true, cellClass:'text-center', field: 'sn', headerName: '单据编号', minWidth: 160},
        {suppressMenu: true, cellClass:'text-center', sortable: false, type: 'datetime', field: 'created_at', headerName: '单据日期', width: 140},
        {suppressMenu: true, cellClass:'text-center', field: 'customer_code', headerName: '客户编号', width: 120},
        {suppressMenu: true, cellClass:'text-left', field: 'customer_name', headerName: '客户名称', width: 220},
        {suppressMenu: true, cellClass:'text-right', field: 'total_money', headerName: '订单金额', width: 120},
        {suppressMenu: true, cellClass:'text-center', field: 'id', headerName: 'ID', width: 80}
    ];

    mGrid.onRowClicked = function(row) {
        var selected = row.node.isSelected();
        if (selected === false) {
            row.node.setSelected(true, true);
        }
        var rows = mGrid.api.getSelectedRows();
        var ids = [];
        for (let i = 0; i < rows.length; i++) {
            ids.push(rows[i].id);
        }
        params.ids = ids;
        sGrid.remoteData(params);
    };

    mGrid.onRowDoubleClicked = function (row) {
        var ret = writeSelected();
        if (ret == true) {
            $('#gdoo-dialog-' + params.dialog_index).dialog('close');
        }
    };

    /**
     * 初始化选择
     */
    function initSelected() {
        if (params.is_grid) {
        } else {
            var rows = {};
            var id = $('#'+option.id).val();
            if (id) {
                var ids = id.split(',');
                for (var i = 0; i < ids.length; i++) {
                    rows[ids[i]] = ids[i];
                }
            }
            mGrid.api.forEachNode(function(node) {
                var key = node.data[sid];
                if (rows[key] != undefined) {
                    node.setSelected(true);
                }
            });
        }
    }

    /**
     * 写入选中
     */
    function writeSelected() {
        var rows = mGrid.api.getSelectedRows();
        if (params.is_grid) {
            var list = gdoo.forms[params.form_id];
            list.api.dialogSelected(params);
        } else {
            var id = [];
            var text = [];
            $.each(rows, function(k, row) {
                id.push(row[sid]);
                text.push(row.name);
            });
            $('#'+option.id).val(id.join(','));
            $('#'+option.id+'_text').val(text.join(','));

            if (event.exist('onSelect')) {
                return event.trigger('onSelect', multiple ? rows : rows[0]);
            }
        }
        return true;
    }
    mGrid.writeSelected = writeSelected;
    gdoo.dialogs[option.id] = mGrid;

    var gridDiv = document.querySelector("#dialog-{{$search['query']['id']}}");
    new agGrid.Grid(gridDiv, mGrid);
    // 读取数据
    mGrid.remoteData();
    // 数据载入成功
    mGrid.remoteSuccessed = function() {
        initSelected();
    }

    params['master'] = 0;
    var sGridDiv = document.querySelector("#promotion_customer_order_data");
    var sGrid = new agGridOptions();
    sGrid.remoteDataUrl = '{{url()}}';
    sGrid.remoteParams = params;
    sGrid.rowMultiSelectWithClick = true;
    sGrid.rowSelection = 'multiple';
    sGrid.autoColumnsToFit = false;
    sGrid.defaultColDef.suppressMenu = true;
    sGrid.defaultColDef.sortable = false;
    sGrid.getRowClass = function(params) {
        var data = params.data;
        params.node.setSelected(true);
    };
    sGrid.columnDefs = [
        {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
        {cellClass:'text-center', field: 'product_code', headerName: '存货编码', width: 100},
        {field: 'product_name', headerName: '商品名称', minWidth: 180},
        {cellClass:'text-center', field: 'product_spec', headerName: '商品规格', width: 140},
        {cellClass:'text-center', field: 'product_barcode', headerName: '商品条码', width: 100},
        {cellClass:'text-center', field: 'product_unit', headerName: '计量单位', width: 80},
        {cellClass:'text-right', field: 'quantity', headerName: '数量', width: 80},
        {cellClass:'text-right', field: 'price', headerName: '单价(元)', width: 80},
        {cellClass:'text-right', field: 'money', headerName: '金额(元)', width: 80},
        {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
    ];
    new agGrid.Grid(sGridDiv, sGrid);
    // 读取数据
    sGrid.remoteData();
    $promotion_customer_order_data = sGrid;

    var data = search.forms;
    var search = $("#dialog-{{$search['query']['id']}}-search-form").searchForm({
        data: data,
        init: function(e) {}
    });
    search.find('#search-submit').on('click', function() {
        var query = search.serializeArray();
        $.map(query, function(row) {
            params[row.name] = row.value;
        });
        params['master'] = 1;
        mGrid.remoteData(params);

        params['master'] = 0;
        sGrid.remoteData(params);
        return false;
    });

})(jQuery);
</script>