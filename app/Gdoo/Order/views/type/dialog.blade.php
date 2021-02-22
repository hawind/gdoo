<div class="wrapper-sm" style="padding-bottom:0;">

    <div id="{{$query['form_id']}}-dialog-toolbar">
        <form id="{{$query['form_id']}}-dialog-search-form" name="{{$query['form_id']}}_dialog_search_form" class="form-inline" method="get">
            @include('searchForm3')
        </form>
    </div>
</div>

<div class="m-t-sm dialog-jqgrid">
    <table id="{{$query['form_id']}}-dialog"></table>
    <div id="{{$query['form_id']}}-dialog-page" class="m-r-sm"></div>
</div>

<script>
(function($) {
    window.productDialog = {};
    var params = {{json_encode($query)}};
    var $table = $("#{{$query['form_id']}}-dialog");

    var model = [
        {name: "name", index:'name', label: '名称', minWidth: 220, align: 'left'},
        {name: "type", index:'type', label: '是否收费', width: 120, align: 'center'},
        {name: "id", index:'id', label: 'ID', width: 60, align: 'center'}
    ];

    $table.jqGrid({
        caption: '',
        datatype: 'json',
        mtype: 'POST',
        url: app.url('order/type/dialog'),
        colModel: model,
        rowNum: 25,
        multiboxonly: params.multi == 0 ? true : false,
        multiselect: true,
        viewrecords: true,
        rownumbers: false,
        height: 320,
        footerrow: false,
        postData: params,
        tableType: 'dialog',
        pager: '#{{$query["form_id"]}}-dialog-page',
        gridComplete: function() {
            // 单选时禁用全选按钮
            if (params.multi == 0) {
                $("#cb_" + this.p.id).prop('disabled', true);
            }
            $(this).jqGrid('setColsWidth');
        },
        loadComplete: function(res) {
            
            var me = $(this);
            me.jqGrid('initPagination', res);

            if($.isFunction(window.productDialog.setSelecteds)) {
                window.productDialog.setSelecteds.call($table);
            } else {
                // 设置默认选中
                window.productDialog.setDefaultSelecteds();
            }

        },
        // 双击选中
        ondblClickRow: function(id) {
            if(params.multi == 1) {
                $table.jqGrid('setSelection', id);
            }
            if($.isFunction(window.productDialog.getSelecteds)) {
                window.productDialog.getSelecteds.call($table);
            } else {
                window.productDialog.getDefaultSelecteds();
            }
        },
    });

    window.productDialog.setDefaultSelecteds = function(res) {
        var ids = $('#'+params.id).val() || '';
        ids = ids.split(',');
        $.each(ids, function(k, v) {
            if(v) {
                $table.jqGrid('setSelection', v);
            }
        });
    }

    window.productDialog.getDefaultSelecteds = function(dialog) {
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
            text.push(rows[i].name);
        }

        // 会写数据
        $('#'+params.id).val(id.join(','));
        $('#'+params.id+'_text').val(text.join(','));

        // 关闭窗口
        $(dialog).dialog("close");
        return true;
    }

    var data = {{json_encode($search['forms'])}};
    var search = $('#{{$query["form_id"]}}-dialog-search-form').searchForm({
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
