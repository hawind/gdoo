<style>
    .modal-body { overflow:hidden; }
    </style>
    
    <div class="wrapper-xs">
        <div id="dialog-purchase_order-toolbar">
            <form id="dialog-purchase_order-search-form" name="dialog_purchase_order_search_form" class="search-inline-form form-inline" method="get">
                @include('searchForm3')
            </form>
        </div>
    </div>
    
    <div id="ref_purchase_order" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    
    <div class="m-t-xs">
        <div id="ref_purchase_order_data" class="ag-theme-balham" style="width:100%;height:240px;"></div>
    </div>
    
    <script>
    var $ref_purchase_order = null;
    var $ref_purchase_order_data = null;
    var params = JSON.parse('{{json_encode($query)}}');
    (function($) {
        params['master'] = 1;
        var mGridDiv = document.querySelector("#ref_purchase_order");
        var mGrid = new agGridOptions();
        mGrid.remoteDataUrl = '{{url()}}';
        mGrid.remoteParams = params;
        mGrid.rowMultiSelectWithClick = false;
        mGrid.rowSelection = 'multiple';
        mGrid.autoColumnsToFit = false;
        mGrid.defaultColDef.suppressMenu = true;
        mGrid.defaultColDef.sortable = false;
        mGrid.columnDefs = [
            {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
            {cellClass:'text-center', field: 'sn', headerName: '订单编号', width: 160},
            {cellClass:'text-center', field: 'created_at', type: 'datetime', headerName: '单据日期', width: 120},
            {cellClass:'text-center', field: 'created_by', headerName: '制单人', width: 120},
            {cellClass:'text-left', field: 'remark', headerName: '备注', width: 460},
            //{cellClass:'text-center', field: 'supplier_code', headerName: '供应商编码', width: 120},
            //{field:'supplier_name', headerName: '供应商名称', width: 120},
            {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
        ];
    
        mGrid.onSelectionChanged = function() {
            var rows = mGrid.api.getSelectedRows();
            var ids = [];
            for (let i = 0; i < rows.length; i++) {
                ids.push(rows[i].id);
            }
            params.ids = ids;
            sGrid.remoteData(params);
        };
        new agGrid.Grid(mGridDiv, mGrid);
        // 读取数据
        mGrid.remoteData();
        $ref_purchase_order = mGrid;
        params['master'] = 0;
        var sGridDiv = document.querySelector("#ref_purchase_order_data");
        var sGrid = new agGridOptions();
        sGrid.remoteDataUrl = '{{url()}}';
        sGrid.remoteParams = params;
        sGrid.rowMultiSelectWithClick = true;
        sGrid.suppressRowClickSelection = true;
        sGrid.rowSelection = 'multiple';
        sGrid.autoColumnsToFit = false;
        sGrid.defaultColDef.suppressMenu = true;
        sGrid.defaultColDef.sortable = false;
        sGrid.getRowClass = function(params) {
            var data = params.data;
            params.node.setSelected(true);
            if (toNumber(data.ky_num) < toNumber(data.wf_num)) {
                return 'ag-row-warn';
            }
        };
        sGrid.columnDefs = [
            {cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: true, suppressSizeToFit: true, width: 40},
            {cellClass:'text-center', field: 'supplier_code', headerName: '供应商编码', width: 120},
            {field:'supplier_name', headerName: '供应商名称', width: 120},
            {cellClass:'text-center', field: 'product_code', headerName: '存货编码', width: 100},
            {field: 'product_name', headerName: '商品名称', minWidth: 180},
            {cellClass:'text-center', field: 'product_spec', headerName: '商品规格', width: 140},
            {cellClass:'text-center', field: 'product_unit', headerName: '计量单位', width: 80},
            {cellClass:'text-center', field: 'plan_arrive_dt', headerName: '计划到货日期', width: 100},
            //{cellClass:'text-center', field: 'ky_num', type:'number', headerName: '现存量', width: 80},
            {cellClass:'text-right', field: 'quantity', type:'number', headerName: '订单数量', width: 80},
            {cellClass:'text-right', field: 'yr_num',type:'number', headerName: '已入数量', width: 80},
            {cellClass:'text-right', field: 'wr_num', type:'number', headerName: '未入数量', width: 80},
            {cellClass:'text-center', field: 'id', headerName: 'ID', width: 60}
        ];
    
        sGrid.onRowClicked = function(row) {
            var selected = row.node.isSelected();
            if (selected === false) {
                row.node.setSelected(true, true);
            }
        };
    
        new agGrid.Grid(sGridDiv, sGrid);
        // 读取数据
        sGrid.remoteData();
        $ref_purchase_order_data = sGrid;
    
        var data = JSON.parse('{{json_encode($search["forms"])}}');
        var search = $('#dialog-purchase_order-search-form').searchForm({
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