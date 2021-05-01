(function($) {

    'use strict';

    var grid = null;

    /**
     * 默认的配置选项
     * @type {Object}
     */
    var defaultOptions = {
        query: {},
        item: {},
        data: [],
        delay: 300,
        showBtn: true,
        clearable: false,
        keyLeft: 37,
        keyUp: 38,
        keyRight: 39,
        keyDown: 40,
        keyEnter: 13
    };

    /**
     * 显示下拉列表
     */
    function showSuggest($input, options) {
        var $dropdownMenu = $('#gdoo-suggest');
        if (!$dropdownMenu.is(':visible')) {
            $dropdownMenu.show();
            $input.trigger('onShowSuggest', [options ? options.data : []]);
        }
    }

    /**
     * 隐藏下拉列表
     */
    function hideSuggest($input, options) {
        var $dropdownMenu = $('#gdoo-suggest');
        if ($dropdownMenu.is(':visible')) {
            $dropdownMenu.hide();
            $input.trigger('onHideSuggest', [options ? options.data : []]);
        }
    }

    /**
     * 刷新数据
     * ajax请求携带q参数
     */
    function refreshData($input, options, params) {
        showSuggest($input, options);

        var event = gdoo.event.get(params.form_id + '.' + params.id);
        event.trigger('open', params);
        event.trigger('query', params);

        params.suggest = true;
        params.q = $input.val();

        grid.rowSelection = params.multi == 1 ? 'multiple' : 'single';
        grid.remoteDataUrl = app.url(params.url);
        grid.remoteParams = params;

        grid.remoteData();
        return $input;
    }

    /**
     * 构建 Suggest的agGrid
     * 作为 fnGetData 的 callback 函数调用
     */
    function buildSuggest($input, options, params) {

        var $dropdownMenu = $('#gdoo-suggest');
        $dropdownMenu.html('<div style="height:180px;overflow:auto;width:auto;"><div id="suggest-aggrid" class="ag-theme-balham" style="width:100%;height:180px;border-left:1px solid #BDC3C7;border-right:1px solid #BDC3C7;"></div></div>');

        var gridDiv = document.querySelector("#suggest-aggrid");
        grid = new agGridOptions();
        
        grid.suppressRowClickSelection = true;
        grid.columnDefs = [
            //{suppressMenu: true, cellClass:'text-center', checkboxSelection: true, headerCheckboxSelection: multiple, suppressSizeToFit: true, sortable: false, width: 40},
            //{suppressMenu: true, cellClass:'text-center', sortable: false, suppressSizeToFit: true, cellRenderer: 'htmlCellRenderer', field: 'images', headerName: '图片', width: 40},
            {suppressMenu: true, cellClass:'text-center', sortable: true, field: 'code', headerName: '存货编码', width: 100},
            {suppressMenu: true, cellClass:'text-left', sortable: true, field: 'name', headerName: '产品名称', minWidth: 140},
            {suppressMenu: true, cellClass:'text-center', sortable: true, field: 'spec', headerName: '规格型号', width: 100},
            {suppressMenu: true, cellClass:'text-center', sortable: true, field: 'barcode', headerName: '产品条码', width: 120},
            {suppressMenu: true, cellClass:'text-center', sortable: true, field: 'unit_id_name', headerName: '计量单位', width: 80},
            {suppressMenu: true, cellClass:'text-right', field: 'price', headerName: '价格', width: 80}
        ];

        grid.onRowClicked = function (row) {
            var ret = grid.dialogSelected([row.data]);
            if (ret) {
                hideSuggest($input, options);
            }
        };

        /**
        * 写入选中
        */
        grid.dialogSelected = function(rows) {
            var params = grid.remoteParams;
            var sid = params.prefix == 1 ? 'sid' : 'id';
            var id = [];
            var text = [];
            $.each(rows, function(index, row) {
                id.push(row[sid]);
                text.push(row.name);
            });

            var input_id = params.form_id + '_' + params.id;
            $('#'+input_id).val(id.join(','));
            $('#'+input_id+'_text').val(text.join(','));

            var event = gdoo.event.get(params.form_id + '.' + params.id);
            if (event.exist('onSelect')) {
                return event.trigger('onSelect', grid.rowSelection == 'multiple' ? rows : rows[0]);
            }
            return true;
        }

        new agGrid.Grid(gridDiv, grid);
        return $input;
    }

    $.fn.gdooDialogInput = function(options) {
        var self = this;
        options = options || {};
        options = $.extend(true, {}, defaultOptions, options);

        $('body').append('<div class="gdoo-gird-suggest" id="gdoo-suggest" style="position:absolute;display:none;box-shadow:0 2px 5px 0 rgb(0 0 0 / 26%);"></div>');

        return self.each(function() {
            var $input = $(this);
            var params = $input.data();
            var keyupTimer = null;
            var isMouseenter = 0;
  
            var $dropdownMenu = $('#gdoo-suggest');
            buildSuggest($input, options, params);

            $input.off();

            // 开始事件处理
            $input.on('keydown', function(event) {
                // 当提示层显示时才对键盘事件处理
                if (!$dropdownMenu.is(':visible')) {
                    return;
                }
                if (event.keyCode === options.keyEnter) {
                    hideSuggest($input, options);
                }
            }).on('keyup input paste', function(event) {
                // 如果弹起的键是回车、向上或向下方向键则返回
                if (~ $.inArray(event.keyCode, [options.keyDown, options.keyUp, options.keyEnter])) {
                    $input.val($input.val()); // 让鼠标输入跳到最后
                    return;
                }

                clearTimeout(keyupTimer);
                keyupTimer = setTimeout(function() {
                    refreshData($input, options, params);
                }, options.delay);

            }).on('focus', function() {

                $dropdownMenu.off();

                var w = $(window).width();
                var h = $(window).height();

                var width = $input.outerWidth();
                var height = $input.outerHeight();
                var offset = $input.offset();

                var dw = $dropdownMenu.outerWidth();
                var dh = $dropdownMenu.outerHeight();

                var css = {top: offset.top + height - 1};
                // 判断是否小于768
                if (w < 768) {
                    css.minWidth = 360;
                    css.left = 14;
                    css.right = 14;
                } else {
                    css.left = offset.left;
                    // 右边超出
                    if (w < offset.left + dw + 10) {
                        css.left = offset.left - dw + width;
                    }
                    // 下边超出
                    if (h < offset.top + dh + 10) {
                        css.top = offset.top - dh + 1;
                    }
                }
                $dropdownMenu.css(css);

                // 列表中滑动时，输入框失去焦点
                $dropdownMenu.on('mouseenter', function() {
                    isMouseenter = 1;
                    $input.blur();
                }).on('mouseleave', function() {
                    isMouseenter = 0;
                    $input.focus();
                }).on('click', function() {
                    // 阻止冒泡
                    return false;
                });

            }).on('blur', function() {
                // 隐藏对话框
                if (!isMouseenter) {
                    hideSuggest($input, options);
                }
            });

        });
    }

})(jQuery);