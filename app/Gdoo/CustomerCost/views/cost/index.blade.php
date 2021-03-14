{{$header["js"]}}
<div class="panel no-border" id="{{$header['master_table']}}-controller">
    @include('headers')
    <div class="list-jqgrid">
        <div id="{{$header['master_table']}}-grid" class="ag-theme-balham"></div>
    </div>
</div>
<script>
    (function ($) {
        var table = '{{$header["master_table"]}}';
        var config = gdoo.grids[table];
        var action = config.action;
        var search = config.search;

        action.dialogType = 'layer';

        // 关闭费用
        action.close = function(data) {
            var me = this;
            var grid = config.grid;
            var selections = grid.api.getSelectedRows();
            if (selections.length != 1) {
                toastrError('只能选择一行记录');
                return;
            }
            formDialog({
                title: '关闭费用',
                url: app.url('customerCost/cost/close', {id: selections[0].id}),
                storeUrl: app.url('customerCost/cost/close'),
                id: 'customer_cost_data_form-close',
                dialogClass:'modal-sm',
                success: function(res) {
                    toastrSuccess(res.data);
                    grid.remoteData();
                    $(this).dialog("close");
                },
                error: function(res) {
                    toastrError(res.data);
                }
            });
        }

        var options = new agGridOptions();
        var gridDiv = document.querySelector("#{{$header['master_table']}}-grid");
        gridDiv.style.height = getPanelHeight(48);

        options.remoteDataUrl = '{{url()}}';
        options.remoteParams = search.advanced.query;
        options.columnDefs = config.cols;
        options.onRowDoubleClicked = function (params) {
            var data = params.data;
            if (params.node.rowPinned) {
                return;
            }
            if (data == undefined) {
                return;
            }
            if (data.master_id > 0) {
                
                var index = data.master_sn.indexOf('QTFY');
                if (index >= 0) {
                    data.src_type_id = 46;
                }

                if (data.src_type_id == 46) {
                    top.addTab('customerCost/cost/show?id=' + data.master_id, 'customerCost_cost_show', '其他费用');
                }
                if (data.src_type_id == 55) {
                    top.addTab('promotion/review/show?id=' + data.src_id, 'promotion_review_show', '促销核销');
                }
                if (data.src_type_id == 57) {
                    top.addTab('approach/review/show?id=' + data.src_id, 'approach_review_show', '进店核销');
                }
                if (data.src_type_id == 87) {
                    top.addTab('customerCost/compen/show?id=' + data.master_id, 'customerCost_compen_show', '合同补损');
                }
                if (data.src_type_id == 88) {
                    top.addTab('customerCost/rebate/show?id=' + data.master_id, 'customerCost_rebate_show', '合同返利');
                }
                if (data.src_type_id == 86) {
                    top.addTab('customerCost/adjust/show?id=' + data.master_id, 'customerCost_adjust_show', '费用调整');
                }
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