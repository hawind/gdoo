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

        action.dialogType = 'layer';

        action.fee_detail = function(data) {
            viewDialog({
                title: '兑现明细',
                dialogClass: 'modal-md',
                url: app.url('promotion/review/feeDetail', {id: data.master_id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
        }

        action.product = function(data) {
            viewDialog({
                title: '产品明细',
                dialogClass: 'modal-md',
                url: app.url('promotion/promotion/product', {id: data.master_id}),
                close: function() {
                    $(this).dialog("close");
                }
            });
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
        // options.autoColumnsToFit = false;
        options.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            if (params.data == undefined) {
                return;
            }
            if (params.data.promotion_id > 0) {
                top.addTab('promotion/material/detail?promotion_id=' + params.data.promotion_id, 'promotion_material_show', '促销资料');
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