;(function($) {

    var inputLock; // 用于中文输入法输入时锁定搜索
    var grid = null;

    /**
     * 设置或获取输入框的 alt 值
     */
    function setOrGetAlt($input, val) {
        return val !== undefined ? $input.attr('alt', val) : $input.attr('alt');
    }

    /**
     * 判断字段名是否在 options.effectiveFields 配置项中
     * @param  {String} field   要判断的字段名
     * @param  {Object} options
     * @return {Boolean}        effectiveFields 为空时始终返回 true
     */
    function inEffectiveFields(field, options) {
        var effectiveFields = options.effectiveFields;

        return !(field === '__index' || effectiveFields.length && !~$.inArray(field, effectiveFields));
    }

    /**
     * 判断字段名是否在 options.searchFields 搜索字段配置中
     */
    function inSearchFields(field, options) {
        return ~$.inArray(field, options.searchFields);
    }

    /**
     * 显示下拉列表
     */
    function showDropMenu($input, options) {
        var $dropdownMenu = $('#gdoo-gird-suggest');
        if (!$dropdownMenu.is(':visible')) {
            $dropdownMenu.show();
            $input.trigger('onShowDropdown', [options ? options.data : []]);
        }
    }

    /**
     * 隐藏下拉列表
     */
    function hideDropMenu($input, options) {
        var $dropdownMenu = $('#gdoo-gird-suggest');
        if ($dropdownMenu.is(':visible')) {
            $dropdownMenu.hide();
            $input.trigger('onHideDropdown', [options ? options.data : []]);
        }
    }

    /**
     * 下拉列表刷新
     * 作为 fnGetData 的 callback 函数调用
     */
    function refreshDropMenu($input, data, options) {
        showDropMenu($input, options);
        grid.remoteParams.q = $input.val();
        grid.remoteData();
        return $input;
    }

    /**
     * 下拉列表刷新
     * 作为 fnGetData 的 callback 函数调用
     */
    function refreshDropMenu2($input, options) {

        var params = options.query;
        params.suggest = true;

        var $dropdownMenu = $('#gdoo-gird-suggest');
        $dropdownMenu.html('<div style="height:180px;overflow:auto;width:auto;"><div id="suggest-'+ params.id +'" class="ag-theme-balham" style="width:100%;height:180px;border-left:1px solid #BDC3C7;border-right:1px solid #BDC3C7;"></div></div>');

        var option = gdoo.formKey(params);
        var event = gdoo.event.get(option.key);
        event.trigger('query', params);
        event.trigger('open', params);

        var sid = params.prefix == 1 ? 'sid' : 'id';
        var gridDiv = document.querySelector("#suggest-" + params.id);
        grid = new agGridOptions();
        var multiple = params.multi == 0 ? false : true;
        grid.remoteDataUrl = app.url(params.url);
        grid.remoteParams = params;
        grid.rowSelection = multiple ? 'multiple' : 'single';
 
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
            var ret = grid.dialogSelected(row.data);
            if (ret) {
                hideDropMenu($input, options);
            }
        };

        /**
        * 写入选中
        */
        grid.dialogSelected = function(selectedRow) {
            var ret = true;
            var list = gdoo.forms[params.form_id];
            var links = list.links[params.id];
            var item = options.item;
            // 如果传入的行id为0
            if (query.grid_id == 0) {
                query.grid_id = list.lastEditCell.data.id;
            }
            for (key in links) {
                item[key] = selectedRow[links[key]];
            }

            if (event.exist('onSelect')) {
                ret = event.trigger('onSelect', item, selectedRow);
            }
            
            list.lastEditCell.data = item;
            list.api.memoryStore.update(item);
            $input.trigger('onSelect', [item]);
            list.generatePinnedBottomData();
            return ret;
        }

        new agGrid.Grid(gridDiv, grid);
        return $input;
    }

    /**
     * 检测 keyword 与 value 是否存在互相包含
     * @param  {String}  keyword 用户输入的关键字
     * @param  {String}  key     匹配字段的 key
     * @param  {String}  value   key 字段对应的值
     * @param  {Object}  options
     * @return {Boolean}         包含/不包含
     */
    function isInWord(keyword, key, value, options) {
        value = $.trim(value);

        if (options.ignorecase) {
            keyword = keyword.toLocaleLowerCase();
            value = value.toLocaleLowerCase();
        }

        return value &&
            (inEffectiveFields(key, options) || inSearchFields(key, options)) && // 必须在有效的搜索字段中
            (
                ~value.indexOf(keyword) || // 匹配值包含关键字
                options.twoWayMatch && ~keyword.indexOf(value) // 关键字包含匹配值
            );
    }

    /**
     * 通过 ajax 或 json 参数获取数据
     */
    function getData(keyword, $input, callback, options) {
        var data, validData, filterData = [], i, key, len;

        keyword = keyword || '';

        // 给了url参数，则从服务器 ajax 请求
        if (options.url) {
            callback($input, options.data, options);
        } else {
            data = options.data;
            validData = data;
            // 本地的 data 数据，则在本地过滤
            if (validData) {
                if (keyword) {
                    // 输入不为空时则进行匹配
                    len = data.length;
                    for (i = 0; i < len; i++) {
                        for (key in data[i]) {
                            if (data[i][key] && isInWord(keyword, key, data[i][key] + '', options)) {
                                filterData.push(data[i]);
                                filterData[filterData.length - 1].__index = i;
                                break;
                            }
                        }
                    }
                } else {
                    filterData = data;
                }
            }
            callback($input, filterData, options);
        }
    }

    /**
     * 取得 clearable 清除按钮
     */
    function getIClear($input, options) {
        var $iClear = $input.prev('i.clearable');

        // 是否可清除已输入的内容(添加清除按钮)
        if (options.clearable && !$iClear.length) {
                $iClear = $('<i class="clearable glyphicon glyphicon-remove"></i>')
                .prependTo($input.parent());
        }

        return $iClear.css({
            position: 'absolute',
            top: 12,
            // right: options.showBtn ? Math.max($input.next('.input-group-btn').width(), 33) + 2 : 12,
            zIndex: 4,
            cursor: 'pointer',
            fontSize: 12
        }).hide();
    }

    /**
     * 默认的配置选项
     * @type {Object}
     */
    var defaultOptions = {
        query: {},
        item: {},
        data: [],
        allowNoKeyword: true,
        ignorecase: false,
        searchFields: [],
        twoWayMatch: true,
        delay: 300,
        showBtn: true,
        clearable: false,
        /* key */
        keyLeft: 37,
        keyUp: 38,
        keyRight: 39,
        keyDown: 40,
        keyEnter: 13,
        fnGetData: getData
    };

    var methods = {
        init: function(options) {
            // 参数设置
            var self = this;
            options = options || {};

            options = $.extend(true, {}, defaultOptions, options);

            return self.each(function() {
                var $input = $(this),
                $parent = $input.parent(),
                $iClear = getIClear($input, options),
                isMouseenterMenu,
                keyupTimer; // keyup 与 input 事件延时定时器

                var $dropdownMenu = $('#gdoo-gird-suggest');
                if ($dropdownMenu.length === 0) {
                    $dropdownMenu = $('<div class="gdoo-gird-suggest" id="gdoo-gird-suggest" style="position:absolute;display:none;box-shadow:0 2px 5px 0 rgb(0 0 0 / 26%);"></div>');
                    $('body').append($dropdownMenu);
                }
                refreshDropMenu2($input, options);

                /*
                var offset = $input.offset();
                var height = $input.outerHeight();
                $dropdownMenu.css({left: offset.left - 1, top: offset.top + height - 1});
                */

                $input.off();
     
                // 是否显示 button 按钮
                if (!options.showBtn) {
                    $input.css('borderRadius', 4);
                    $parent.css('width', '100%').find('.btn:eq(0)').hide();
                }

                // 移除 disabled 类，并禁用自动完成
                $input.removeClass('disabled').prop('disabled', false).attr('autocomplete', 'off');
    
                // 开始事件处理
                $input.on('keydown', function(event) {
                    // 当提示层显示时才对键盘事件处理
                    if (!$dropdownMenu.is(':visible')) {
                        return;
                    }
                    if (event.keyCode === options.keyEnter) {
                        hideDropMenu($input, options);
                    }

                }).on('compositionstart', function(event) {
                    // 中文输入开始，锁定
                    inputLock = true;
                }).on('compositionend', function(event) {
                    // 中文输入结束，解除锁定
                    inputLock = false;
                }).on('keyup input paste', function(event) {
                    var word;

                    // 如果弹起的键是回车、向上或向下方向键则返回
                    if (~$.inArray(event.keyCode, [options.keyDown, options.keyUp, options.keyEnter])) {
                        $input.val($input.val()); // 让鼠标输入跳到最后
                        return;
                    }

                    clearTimeout(keyupTimer);
                    keyupTimer = setTimeout(function() {
                        // 锁定状态，返回
                        if (inputLock) {
                            return;
                        }

                        word = $input.val();

                        // 若输入框值没有改变则返回
                        if ($.trim(word) && word === setOrGetAlt($input)) {
                            return;
                        }

                        // 是否允许空数据查询
                        if (!word.length && !options.allowNoKeyword) {
                            return;
                        }
                        
                        options.fnGetData($.trim(word), $input, refreshDropMenu, options);
                    }, options.delay || 300);

                }).on('blur', function() {
                     // 不是进入下拉列表状态，则隐藏列表
                    if (!isMouseenterMenu) {
                        hideDropMenu($input, options);
                    }
                }).on('focus', function() {

                    $dropdownMenu.off();

                    var w = $(window).width();
                    var h = $(window).height();

                    var width = $input.outerWidth();
                    var height = $input.outerHeight();
                    var offset = $input.offset();

                    var dw = $dropdownMenu.outerWidth();
                    var dh = $dropdownMenu.outerHeight();

                    var css = {top: offset.top + height};
                    // 判断是否小于768
                    if (w < 768) {
                        css.minWidth = 360;
                        css.left = 14;
                        css.right = 14;
                    } else {
                        css.left = offset.left - 1;
                        // 右边超出
                        if (w < offset.left + dw + 10) {
                            css.left = offset.left - dw + width + 1;
                        }
                        // 下边超出
                        if (h < offset.top + dh + 10) {
                            css.top = offset.top - dh;
                        }
                    }
                    $dropdownMenu.css(css);

                    // 列表中滑动时，输入框失去焦点
                    $dropdownMenu.on('mouseenter', function() {
                        isMouseenterMenu = 1;
                        $input.blur();
                    }).on('mouseleave', function() {
                        isMouseenterMenu = 0;
                        $input.focus();
                    }).on('click', function() {
                        // 阻止冒泡
                        return false;
                    });

                });

                // 存在清空按钮
                if ($iClear.length) {
                    $iClear.click(function () {
                    });

                    $parent.mouseenter(function() {
                        if (!$input.prop('disabled')) {
                            $iClear.css('right', options.showBtn ? Math.max($input.next('.input-group-btn').width(), 33) + 2 : 12).show();
                        }
                    }).mouseleave(function() {
                        $iClear.hide();
                    });
                }

            });
        },
        show: function() {
            return this.each(function() {
                $(this).click();
            });
        },
        hide: function() {
            return this.each(function() {
                hideDropMenu($(this));
            });
        },
        disable: function() {
            return this.each(function() {
                $(this).attr('disabled', true).parent().find('.btn:eq(0)').prop('disabled', true);
            });
        },
        enable: function() {
            return this.each(function() {
                $(this).attr('disabled', false).parent().find('.btn:eq(0)').prop('disabled', false);
            });
        },
        destroy: function() {
            return this.each(function() {
                $(this).off().removeData('gdooSuggest').removeAttr('style')
                .parent().find('.btn:eq(0)').off().show().attr('data-toggle', 'dropdown').prop('disabled', false) // .addClass(disabled);
                .next().css('display', '').off();
            });
        }
    };

    $.fn['gdooSuggest'] = function(options) {
        // 方法判断
        if (typeof options === 'string' && methods[options]) {
            var inited = true;
            this.each(function() {
                if (!$(this).data('gdooSuggest')) {
                    return inited = false;
                }
            });
            // 只要有一个未初始化，则全部都不执行方法，除非是 init 或 version
            if (!inited && 'init' !== options && 'version' !== options) {
                return this;
            }

            // 如果是方法，则参数第一个为函数名，从第二个开始为函数参数
            return methods[options].apply(this, [].slice.call(arguments, 1));
        } else {
            // 调用初始化方法
            return methods.init.apply(this, arguments);
        }
    }
})(jQuery);
