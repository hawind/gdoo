<div class="todo-select b-b">
    <select id="todo-select" class="form-control input-xs input-inline">
        <option value="0">全部单据</option>
        @foreach($selects as $select)
        <option value="{{$select['id']}}">{{$select['name']}}</option>
        @endforeach
    </select>
</div>

<div class="todo-grid-box">
    <div class="gdoo-list-grid" id="todo-grid-controller">
        <div id="todo-grid" class="ag-theme-balham" style="width:100%;height:200px;"></div>
    </div>
</div>

<script>
(function ($) {
    var gridDiv = document.querySelector("#todo-grid");
    var options = new agGridOptions();
    options.remoteDataUrl = '{{url()}}';
    options.remoteParams = {};
    options.rowMultiSelectWithClick = false;

    function option(params) {
        if (params.data) {
            if (params.data.option == 1) {
                return '审核';
            } else {
                return '知会';
            }
        }
    }

    options.components['link'] = function(params) {
        if (params.node.rowPinned) {
            return params.value;
        }
        var data = params.data;
        var key = data.url.replace(/\//g,'_');
        return '<a href="javascript:;" data-toggle="todo-grid" data-key="'+ key +'" data-url="' + data.url + '?id=' + data.data_id + '" data-name="'+ data.name + '" data-action="link">打开</a>';
    };

    options.columnDefs = [
        {suppressMenu: true, type:'sn', cellClass:'text-center',  headerName: '序号', width: 60},
        {suppressMenu: true, field: 'sn', cellClass:'text-center',  headerName: '单据编号', minWidth: 160},
        {suppressMenu: true, field: 'name', cellClass:'text-center',  headerName: '单据类型', width: 160},
        {suppressMenu: true, field: 'option', cellClass:'text-center',  cellRenderer: option, headerName: '通知类型', width: 160},
        {suppressMenu: true, field: 'partner_name', cellClass:'text-center',  headerName: '往来单位', width: 160},
        {suppressMenu: true, field: 'run_name', cellClass:'text-center',  headerName: '状态', width: 160},
        {suppressMenu: true, field: 'user_name', cellClass:'text-center',  headerName: '转交人', width: 160},
        {suppressMenu: true, type: 'datetime', cellClass:'text-center',  field: 'created_at', headerName: '转交时间', width: 160},
        {suppressMenu: true, field: 'link', cellClass:'text-center',  sortable: false, cellRenderer: 'link', headerName: '', width: 60},
    ];

    options.onRowDoubleClicked = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        var data = params.data;
        if (data == undefined) {
            return;
        }
        if (data.data_id > 0) {
            var key = data.url.replace(/\//g,'_');
            top.addTab(data.url + '?id=' + data.data_id, key, data.name);
        }
    };

    new agGrid.Grid(gridDiv, options);

    window.todoGrid = options;
    options.remoteData();

    gdoo.widgets['model_todo_widget'] = options;

    var panel = $('#todo-grid-controller');
    panel.on('click', '[data-toggle="todo-grid"]', function() {
        var data = $(this).data();
        if (data.action == 'link') {
            top.addTab(data.url, data.key, data.name);
        }
    });

    $('#todo-select').on('change', function() {
        var bill_id = $(this).val();
        options.remoteData({bill_id: bill_id, page: 1});
    });

})(jQuery);
</script>
<style type="text/css">
    .todo-grid-box {
        padding: 0 10px 10px 10px;
    }
    .todo-select {
        padding: 5px;
        padding-left: 10px;
    }
</style>