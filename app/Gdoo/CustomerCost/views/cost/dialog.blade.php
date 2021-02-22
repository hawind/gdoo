<div class="wrapper" style="padding-bottom:0;">

    <div id="dialog-customer-toolbar">
        <form id="dialog-customer-search-form" name="dialog_customer_search_form" class="form-inline" method="get">
            @include('searchForm')
        </form>
    </div>

    <div class="m-t">
        <table id="dialog-customer" class="table-condensed"></table>
        <div id="dialog-customer-page"></div>
    </div>

</div>

<script>
(function($) {
    var selectBox = {};
    var params = {{json_encode($gets)}};
    var $table = $("#dialog-customer");

    var model = [
        {name: "text", index: 'user.name', label: '客户名称', width: 220, align: 'left'},
        {name: "username", index: 'user.username', label: '客户代码', minWidth: 180, align: 'center'},
        {name: "id", index: 'user.id', label: 'ID', width: 60, align: 'center'}
    ];

    $table.jqGrid({
        caption: '',
        datatype: 'json',
        mtype: 'POST',
        url: app.url('customer/customer/dialog'),
        colModel: model,
        rowNum: 25,
        multiboxonly: params.multi == 0 ? true : false,
        multiselect: true,
        viewrecords: true,
        rownumbers: false,
        height: 340,
        footerrow: false,
        postData: params,
        pager: '#dialog-customer-page',
        
        gridComplete: function() {
            // 单选时禁用全选按钮
            if(params.multi == 0) {
                $("#cb_" + this.p.id).prop('disabled', true);
            }
            $(this).jqGrid('setColsWidth');
        },
        loadComplete: function(res) {
            var me = $(this);
            me.jqGrid('initPagination', res);
            // 设置默认选中
            setSelecteds(res);
        },
        // 双击选中
        ondblClickRow: function(id) {
            if(params.multi == 1) {
                $table.jqGrid('setSelection', id);
            }
            getSelecteds();
        },
    });

    function setSelecteds(res) {
        var ids = $('#'+params.id).val();
        ids = ids.split(',');
        $.each(ids, function(k, v) {
            if(v) {
                $table.jqGrid('setSelection', v);
            }
        });
    }

    function getSelecteds(dialog) {
        var rows = $table.jqGrid('getSelections');
        if(params.multi == 0) {
            if(rows.length > 1) {
                toastrError('只能选择一项。');
                return false;
            }
        }

        var id = [], text = [];
        for (var i = 0; i < rows.length; i++) {
            id.push(rows[i].id);
            text.push(rows[i].text);
        }

        // 回写数据
        $('#'+params.id).val(id.join(','));
        $('#'+params.id+'_text').val(text.join(','));

        // 关闭窗口
        $(dialog).dialog("close");
        return true;
    }

    window.selectBox = {getSelecteds: getSelecteds};

    var data = {{json_encode($search['forms'])}};
    var search = $('#dialog-customer-search-form').searchForm({
        data: data,
        init:function(e) {}
    });

    search.find('#search-submit').on('click', function() {
        var query = search.serializeArray();
        $.map(query, function(row) {
            params[row.name] = row.value;
        });

        $table.jqGrid('setGridParam', {
            postData: params,
            page: 1
        }).trigger('reloadGrid');
        return false;
    });
})(jQuery);

</script>