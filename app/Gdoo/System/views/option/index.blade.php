{{$header["js"]}}
<div class="panel no-border m-b-sm" id="{{$header['table']}}-controller">
    @include('headers')

    <div class="col-xs-4 col-sm-2">
        <div class="list-jqgrid">
            <div id="{{$header['table']}}-tree" style="width:100%;" class="tree-grid ag-theme-balham"></div>
        </div>
    </div>
    
    <div class="col-xs-8 col-sm-10">
        <div id="{{$header['table']}}-grid" style="width:100%;" class="ag-theme-balham"></div>
    </div>
    
    <div class="clearfix"></div>
</div>
 
<style>
.tree-grid.ag-theme-balham {
    border-right: 1px solid #BDC3C7;
}

.tree-grid.ag-theme-balham .ag-ltr .ag-cell {
    border-width: 0 0 0 0;
    border-right-color: #d9dcde;
}
.tree-grid.ag-theme-balham .ag-header-cell, 
.tree-grid.ag-theme-balham .ag-header-group-cell {
    border-right-width: 0;
}

.ag-theme-balham {
    border-left: 1px solid #BDC3C7;
    border-right: 1px solid #BDC3C7;
}
.tree-box {
    border: 1px solid #dee5e7;
    overflow-y: auto;
}
.tree-box .ul {
    margin-bottom: 0;
}
.col-xs-4 {
    padding-right: 0;
    padding-left: 5px;
}
.col-xs-8 {
    padding-left: 5px;
    padding-right: 5px;
}
@media screen and (min-width: 768px) {
    .col-sm-2 {
        padding-right: 0;
        padding-left: 5px;
    }
    .col-sm-10 {
        padding-left: 5px;
        padding-right: 5px;
    }
}
</style>
<script>
var table = '{{$header["table"]}}';
var config = gdoo.grids[table];
var action = config.action;
var search = config.search;
(function ($) {
    var height = getPanelHeight(98);
    var treeOptions = new agGridOptions();
    var gridDiv = document.querySelector("#{{$header['table']}}-tree");
    gridDiv.style.height = height;
    treeOptions.columnDefs = [
        {cellClass:'text-left', field: 'name', headerName: '枚举类别'},
    ];
    treeOptions.onRowClicked = function (params) {
        if (params.data.id >= 0) {
            var query = search.simple.query;
            query['parent_id'] = params.data.id;
            query['page'] = 1;
            config.grid.remoteData(query);
        }
    };
    treeOptions.remoteDataUrl = "{{url('category')}}";
    new agGrid.Grid(gridDiv, treeOptions);
    // 读取数据
    treeOptions.remoteData();

    var options = new agGridOptions();
    var gridDiv = document.querySelector("#{{$header['table']}}-grid");
    gridDiv.style.height = height;
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = search.advanced.query;
    options.columnDefs = config.cols;
    options.onRowDoubleClicked = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        if (params.data == undefined) {
            return;
        }
        if (params.data.id > 0) {
            action.edit(params.data);
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