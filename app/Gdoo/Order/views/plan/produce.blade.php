<div class="panel b-a" id="{{$header['master_table']}}-controller">
    @include('headers')

    <div class='list-jqgrid'>
        <div id="{{$header['master_table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
</div>

<script>
(function ($) {
    var table = '{{$header["master_table"]}}';
    var search = JSON.parse('{{json_encode($header["search_form"])}}');
    var columns = [];
    var params = search.query;
    var grid = new agGridOptions();
    grid.remoteDataUrl = '{{url()}}';
    grid.remoteParams = params;

    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.sortable = false;
    grid.defaultColDef.filter = false;
    grid.autoColumnsToFit = false;
    grid.singleClickEdit = true;
    grid.rowSelection = 'single';
    grid.suppressCellSelection = false;
    grid.columnDefs = [];

    grid.defaultColDef.cellStyle = function(params) {
        if (params.node.rowPinned) {
            return;
        }
        var style = {};
        var value = params.value || 0;
        var field = params.colDef.field;
        if ((field.indexOf('produce_plan_num') === 0 || field == "xqzc_num" || field == "kfzc_num" || field == "kfjh_num") && value > 0) {
            style = {'color':'red'};
        }
        return style;
    };

    grid.components['wfhjh'] = function(params) {
        if (params.node.rowPinned) {
            return params.value;
        }
        if (params.value) {
            let v = parseFloat(params.value);
            return '<a href="javascript:;" data-toggle="produce_data" data-id="'+ params.data.id + '" data-field="'+ params.colDef.field +'" data-product_id="'+ params.data.product_id +'" data-action="wfhjh">'+ (v > 0 ? v : '') +'</a>';
        }
        return params.value;
    };

    var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
    new agGrid.Grid(gridDiv, grid);
    gridDiv.style.height = getPanelHeight(12);

    function setColumns(res) {
        var columnDefs = [
            {cellClass:'text-center', field: 'sn', type: 'sn', headerName: '序号', width: 50, pinned:'left'},
            {cellClass:'text-left', field: 'product_name_spec', headerName: '产品名称', width: 160, pinned:'left'},
            {cellClass:'text-right', field: 'kc_num', headerName: '当前库存', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'dphz_num', headerName: '单品汇总', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'xqzc_num', headerName: '需求总差', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'yhk_num', headerName: '已回款数', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'kfzc_num', headerName: '打款差额', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'kfjh_num', headerName: '计划差额', width: 70, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
            {cellClass:'text-right', field: 'waitin_num', headerName: '待入库', width: 60, pinned:'left', type:'number', numberOptions: {places:0}, calcFooter: 'sum'},
        ];
        for(var i=0; i < res.columns.length;i++) {
            columnDefs.push(res.columns[i]);
        }
        columnDefs.push({cellClass:'text-right', field: 'syfh_num', headerName: '上月发货量', width: 70, type:'number', numberOptions: {places:0}, calcFooter: 'sum'});
        columnDefs.push({cellClass:'text-center', field: 'product_code', headerName: '产品编码', width: 100});
        grid.columnDefs = columnDefs;
        grid.api.setColumnDefs(columnDefs);
    }

    // 读取数据
    grid.remoteData(null, function(res) {
        setColumns(res);
    });

    var search_advanced = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
    });

    gdoo.grids[table] = {grid: grid};

    var action = new gridAction(table, '生产计划');
    var panel = $('#' + table + '-controller');

    var producePlan = function() {
        var me = this;
        var rows = grid.api.getSelectedRows();
        if (rows.length == 1) {
            var data = rows[0];
            var url = app.url('order/plan/producePlan', {id: data.master_id, date: data.master_plan_delivery_dt});
            viewDialog({
                title: '修改计划发货日期',
                url: url,
                storeUrl: url,
                id: me.table,
                dialogClass: 'modal-md',
                onSubmit: function() {
                    var me = this;
                    var form = $('#plan_delivery_date').serialize();
                    var loading = layer.msg('数据提交中...', {
                        icon: 16, shade: 0.1, time: 1000 * 120
                    });
                    $.post(app.url('order/order/deliveryPlanDate'), form, function(res) {
                        if (res.status) {
                            grid.remoteData();
                            $(me).dialog('close');
                            toastrSuccess(res.data);
                        } else {
                            toastrError(res.data);
                        }
                    }).complete(function() {
                        layer.close(loading);
                    });
                }
            });
        } else {
            toastrError('只能修改一条数据。');
        }
    }

    panel.on('click', '[data-toggle="' + table + '"]', function() {
        var data = $(this).data();

        if (data.action == 'wfhjh') {
            var date = data.field.split('_num_')[1];
            var url = app.url('order/plan/producePlan', {product_id: data.product_id, date: date});
            viewDialog({
                title: '发货计划',
                url: url,
                id: table,
                dialogClass: 'modal-md'
            });
            return;
        }
        
        if (data.action == 'filter') {
            // 过滤数据
            $('#' + table + '-search-form-advanced').dialog({
                title: '条件筛选',
                modalClass: 'no-padder',
                buttons: [{
                    text: "取消",
                    'class': "btn-default",
                    click: function() {
                        $(this).dialog("close");
                    }
                },{
                    text: "确定",
                    'class': "btn-info",
                    click: function() {
                        var query = search_advanced.serializeArray();
                        params = {};
                        $.map(query, function(row) {
                            params[row.name] = row.value;
                        });
                        grid.remoteData(params, function(res) {
                            setColumns(res);
                        });
                        $(this).dialog("close");
                        return false;
                    }
                }]
            });
        }

        if (data.action == 'save') {
            var rows = [];
            var date = data.id;
            var key = 'sale_plan_num_' + date;
            grid.api.stopEditing();
            grid.api.forEachLeafNode(function(node, index) {
                var row = node.data;
                if (row[key] !== null) {
                    rows.push({product_id: row.id, quantity: row[key]});
                }
            });

            if (rows.length == 0) {
                toastrError(date + '营销计划不能为空。');
                return;
            }
            $.post("{{url('produce_save')}}", {date: date, rows: rows}, function(res) {
                toastrSuccess(res.data);
                grid.remoteData(params, function(res) {
                    setColumns(res);
                });
            });
        }
        if (data.action == 'submit') {
            var rows = [];
            var date = data.id;
            grid.api.stopEditing();
            $.messager.confirm('提交' + date + '营销计划', '请确认是否提交，本操作不可逆？', function(btn) {
                if (btn) {
                    $.post("{{url('produce_submit')}}", {date: date}, function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData(params, function(res) {
                            setColumns(res);
                        });
                    });
                }
            });
        }

        if (data.action == 'export') {
            action.export(data, '生产计划(营销)');
        }

    });

})(jQuery);
</script>