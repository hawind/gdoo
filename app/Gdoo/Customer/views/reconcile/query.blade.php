<div class="panel">
    <div class="wrapper-sm">

        <form class="form-inline" method="post" id="query-form" name="query-form">

            <div class="form-inline">
    
                <div class="row">
                    
                    <?php $m = date('Y-m-01'); ?>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="control-label">对账客户</label>
                            {{App\Support\Dialog::user('customer', 'customer', '', 0, 0, 135)}}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="control-label">开始日期</label>
                            <input class="form-control input-sm" data-toggle="date" type="text" name="start_at" id="start_at" placeholder="开始日期" value="{{$m}}">
                        </div>
                    </div>

                    <div class="form-group m-b-xs">
                        <div class="col-sm-12">
                            <label class="control-label">开始日期</label>
                            <input class="form-control input-sm" data-toggle="date" type="text" id="end_at" placeholder="开始日期" name="end_at" value="{{date('Y-m-d', strtotime("$m +1 month -1 day"))}}">
                            <div class="visible-xs-block m-t"></div>
                            <span class="hidden-xs">&nbsp;</span>
                            <a href="javascript:formQuery();" class="btn btn-sm btn-info"><i class="icon icon-search"></i> 查询</a>
                        </div>
                    </div>

                </div>
            </div>

        </form>

    </div>

    <div class="list-jqgrid">
        <table id="account-single"></table>
    </div>
</div>

<script>
var $table = null;
var params = {};

(function($) {

    $table = $("#account-single");
    var model = [
        {name: "ccusname", hidden: true, index: 'ccusname', label: '客户名称', width: 100, align: 'center'},
        {name: "date", index: 'date', label: '单据日期', width: 100, align: 'center'},
        {name: "ddh", index: 'ddh', label: '订单号', width: 120, align: 'left'},
        {name: "digest", index: 'digest', label: '摘要', width: 220, align: 'left'},
        {name: "zp", index: 'zp', label: '赠品金额', width: 180, align: 'right'},
        {name: "jmoney", index: 'jmoney', label: '本期应收金额', width: 180, align: 'right'},
        {name: "dmoney", index: 'dmoney', label: '本期收回金额', width: 180, align: 'right'},
        {name: "balance", index: 'balance', label: '余额', width: 180, align: 'right'}
    ];

    $table.jqGrid({
        caption: '',
        datatype: 'json',
        mtype: 'POST',
        url: app.url('customer/account/query'),
        colModel: model,
        rowNum: 1000,
        multiselect: false,
        viewrecords: true,
        rownumbers: true,
        height: getPanelHeight(),
        footerrow: false,
        postData: params,
        grouping:true,
        groupingView : {
            groupField : ['ccusname'],//分组属性
            groupColumnShow : [false,false],//是否显示分组列
            groupText : ['<b>{0}</b>'],//表头显示数据(每组中包含的数据量)
            groupCollapse :false,//加载数据时是否只显示分组的组信息
            groupSummary : [false,false],//是否显示汇总  如果为true需要在colModel中进行配置summaryType:'max',summaryTpl:'<b>Max: {0}</b>'
            groupDataSorted : false,//分组中的数据是否排序
            groupOrder:['desc','desc'] , //分组后组的排列顺序
            //showSummaryOnHide: true//是否在分组底部显示汇总信息并且当收起表格时是否隐藏下面的分组
        },

        gridComplete: function() {
            $(this).jqGrid('setColsWidth');
        },
        loadComplete: function(res) {
            var me = $(this);
            me.jqGrid('initPagination', res);
        }
    });

})(jQuery);


function formQuery()
{
    var query_form = $('#query-form');
    var query = query_form.serializeArray();
    for (var i = 0; i < query.length; i++) {
        params[query[i].name] = query[i].value;
    }

    $table.jqGrid('setGridParam', {
        postData: params,
        page: 1
    }).trigger('reloadGrid');
}

function getPanelHeight() {
    var list = $('.list-jqgrid').position();
    return top.iframeHeight - list.top - 45;
}

// 框架页面改变大小时会调用此方法
function iframeResize() {
    // 框架改变大小时设置Panel高度
    $table.jqGrid('setPanelHeight', getPanelHeight());
    // resize jqgrid大小
    $table.jqGrid('resizeGrid');
}

</script>