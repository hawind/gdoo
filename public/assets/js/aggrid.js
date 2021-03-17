function getPanelHeight(v) {
    var list = $('.gdoo-list-grid').position();
    var position = list.top + v +'px';
    return 'calc(100vh - ' + position + ')';
}

(function (window, $) {

    const localeText = {
        page: "页",
        more: "更多",
        to: "到",
        of: "至",
        next: "下一页",
        last: "上一页",
        first: "首页",
        previous: "上一页",
        loadingOoo: "加载中...",
        selectAll: "查询全部",
        searchOoo: "查询...",
        blanks: "空白",
        filterOoo: "过滤...",
        applyFilter: "daApplyFilter...",
        equals: "相等",
        notEqual: "不相等",
        lessThan: "小于",
        greaterThan: "大于",
        lessThanOrEqual: "小于等于",
        greaterThanOrEqual: "大于等于",
        inRange: "范围",
        contains: "包含",
        notContains: "不包含",
        startsWith: "开始于",
        endsWith: "结束于",
        group: "组",
        columns: "列",
        filters: "筛选",
        rowGroupColumns: "laPivot Cols",
        rowGroupColumnsEmptyMessage: "la drag cols to group",
        valueColumns: "laValue Cols",
        pivotMode: "laPivot-Mode",
        groups: "laGroups",
        values: "值",
        pivots: "laPivots",
        valueColumnsEmptyMessage: "la drag cols to aggregate",
        pivotColumnsEmptyMessage: "la drag here to pivot",
        toolPanelButton: "la tool panel",
        noRowsToShow: "数据为空",
        pinColumn: "laPin Column",
        //valueAggregation: "laValue Agg",
        //autosizeThiscolumn: "laAutosize Diz",
        //autosizeAllColumns: "laAutsoie em All",
        groupBy: "排序",
        ungroupBy: "不排序",
        resetColumns: "重置列",
        expandAll: "展开全部",
        collapseAll: "关闭",
        toolPanel: "工具面板",
        export: "导出",
        csvExport: "导出为CSV格式文件",
        excelExport: "导出到Excel",
        //pinLeft: "laPin &lt;&lt;",
        //pinRight: "laPin &gt;&gt;",
        //noPin: "laDontPin &lt;&gt;",
        sum: "总数",
        min: "最小值",
        max: "最大值",
        none: "无",
        count: "总",
        average: "平均值",
        copy: "复制",
        copyWithHeaders: "携带表头复制",
        ctrlC: "ctrl + C",
        paste: "粘贴",
        ctrlV: "ctrl + V"
    };

    function OptionCellRenderer() {}
    OptionCellRenderer.prototype.init = function (params) {
        if (params.node.rowPinned) {
            return;
        }
        this.eGui = document.createElement('div');
        this.eGui.className = 'options';
        this.eGui.innerHTML = '<a data-toggle="event" data-action="option" data-type="add" data-index="'+ params.rowIndex +'" data-id="'+ params.data.id +'" class="fa fa-plus" title="新增行"></a>  <a data-toggle="event" data-action="option" data-type="delete" data-id="'+ params.data.id +'" data-index="'+ params.rowIndex +'" class="fa fa-times" title="删除行"></a></div>';
    };
    OptionCellRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    function HtmlCellRenderer() {}
    HtmlCellRenderer.prototype.init = function (params) {
        this.eGui = document.createElement('span');
        this.eGui.innerHTML = (params.value || '');
    };
    HtmlCellRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    function ActionCellRenderer() {}
    ActionCellRenderer.prototype.init = function (params) {
        var gridOptions = params.api.gridCore.gridOptions;
        if (params.node.rowPinned) {
            return;
        }
        if (params.data == undefined) {
            return;
        }
        var data = params.data;

        var links = '';
        if (data.master_id > 0) {
            params.colDef.options.forEach(function (action) {
                if (action.display) {
                    var html = '<a data-toggle="event" class="option" data-action="' + action.action + '" data-master_name="' + data.name + '"  data-master_id="' + data.master_id + '" href="javascript:;">' + action.name + '</a>';
                    links += gridOptions.actionCellBeforeRender(html, action, data) || '';
                }
            });
        }
        this.eGui = document.createElement('span');
        this.eGui.innerHTML = links;
    };
    ActionCellRenderer.prototype.getGui = function () {
        return this.eGui;
    };

    function SelectCellEditor() {}
    SelectCellEditor.prototype.init = function(params) {
        this.grid = params;
        this.selectedKey = null;
        this.items = params.colDef.cellEditorParams.values;
        this.eInput = document.createElement('input');
        this.eInput.value = params.value || '';
        this.eInput.className = 'ag-cell-edit-input form-control';
    };
    SelectCellEditor.prototype.getGui = function(params) {
        return this.eInput;
    };

    SelectCellEditor.prototype.afterGuiAttached = function() {
        var me = this;
        var grid = me.grid;
        var oValue = me.eInput.value;
        // 初始化编辑器
        $(me.eInput).agDropdownCellEditor({
            grid: me,
            arrow: 'fa-caret-down',
            data: {
                items: me.items,
                selected: grid.data[grid.select_key],
            },
            select: function(item) {
                if (item) {
                    grid.data[grid.select_key] = item.id;
                    me.eInput.value = item.name;
                    me.selectedKey = item.id;
                } else {
                    me.eInput.value = oValue;
                }
                grid.stopEditing();
            }
        });
        me.eInput.focus();
        me.eInput.select();
    };
    SelectCellEditor.prototype.getValue = function() {
        return this.eInput.value;
    };
    SelectCellEditor.prototype.destroy = function() {
        $('body').find('.combo-select').remove();
    };

    function CheckboxCellRenderer() {}
    CheckboxCellRenderer.prototype.init = function (params) {
        var value = params.value;
        var values = params.colDef.cellEditorParams.values;
        this.eGui = document.createElement('div');
        this.eGui.innerHTML = values[value] || values[0];
    };
    CheckboxCellRenderer.prototype.getGui = function () {
        return this.eGui;
    };
    function CheckboxCellEditor() {}
    CheckboxCellEditor.prototype.init = function(params) {
        var value = params.value;
        this.eInput = document.createElement('input');
        this.eInput.type = 'checkbox'; 
        this.eInput.checked = value;
        this.eInput.value = value;
    };
    CheckboxCellEditor.prototype.getGui = function(params) {
        return this.eInput;
    };

    CheckboxCellEditor.prototype.afterGuiAttached = function() {
        var me = this;
        me.eInput.focus();
        me.eInput.select();
    };
    CheckboxCellEditor.prototype.getValue = function() {
        return this.eInput.checked ? 1 : 0;
    };
    CheckboxCellEditor.prototype.destroy = function() {
    };

    function DialogCellEditor() {}
    DialogCellEditor.prototype.init = function(params) {
        this.params = params;
        this.eInput = document.createElement('div');
        this.eInput.tabIndex = '-1';
        var key = params.colDef.field + '_' + params.data.id;
        var query = params.query;
        query.multi = 1;
        query.is_grid = 1;
        query.url = params.url;
        query.grid_id = params.data.id;
        query.title = params.title;
        var url = '';
        $.each(query, function(k, v) {
            url += ' data-' + k + '="' + v + '"';
        });
        this.query = query;
        this.eInput.innerHTML = '<input class="ag-cell-edit-input" value="' + (params.value || '') + '" id="' + key + '"><a class="combo-arrow" data-toggle="dialog-view" '+ url +'><i class="fa fa-search"></i></a>';
        this.eInput.className = 'ag-input-wrapper ag-input-dialog-wrapper';
    };
    DialogCellEditor.prototype.getGui = function(params) {
        return this.eInput;
    };

    DialogCellEditor.prototype.afterGuiAttached = function() {
        var me = this;
        $(me.eInput).find('input').gdooSuggest({item:me.params.data, query:me.query})
        .on('onSelect', function(e, item) {
            me.params.data[me.query.name] = item[me.query.name];
            me.eInput.querySelector('input').value = item[me.query.name];
        });
        me.eInput.querySelector('input').select();
    };
    DialogCellEditor.prototype.getValue = function() {
        return this.params.data[this.params.query.name];
    };
    DialogCellEditor.prototype.destroy = function() {
        var me = this;
        $(me.eInput).find('input').off();
    };

    DialogCellEditor.prototype.isPopup = function () {
        return false;
    };

    function DateCellEditor() {}
    DateCellEditor.prototype.init = function(params) {
        this.params = params.colDef.cellEditorParams;
        this.eInput = document.createElement('div');
        this.eInput.innerHTML = '<input type="text" class="ag-cell-edit-input" data-toggle="date" value="' + (params.value || '') + '">';
        this.eInput.className = 'ag-input-wrapper ag-input-date-wrapper';
    };
    DateCellEditor.prototype.getGui = function(params) {
        return this.eInput;
    };
    DateCellEditor.prototype.afterGuiAttached = function() {
        this.eInput.querySelector('input').click();
    };
    DateCellEditor.prototype.getValue = function() {
        return this.eInput.querySelector('input').value;
    };
    DateCellEditor.prototype.destroy = function() {
    };

    function agGridOptions() {

        // 定义ag-grid默认参数
        var gridOptions = {
            defaultColDef: {
                minWidth: 100,
                enableRowGroup: true,
                enablePivot: true,
                enableValue: true,
                sortable: true,
                resizable: true,
                filter: true,
                comparator: function(a, b) {
                    if (this.cellRenderer == 'htmlCellRenderer') {
                        a = delHtmlTag(a);
                        b = delHtmlTag(b);
                        return a.localeCompare(b);
                    }
                    return typeof a === 'string' ? a.localeCompare(b) : (a > b ? 1 : (a < b ? -1 : 0));
                }
            },
            pinnedBottomRowData: [],
            rowDragManaged: true,
            suppressRowClickSelection: true,
            rowMultiSelectWithClick: false,
            rowSelection: 'multiple',
            localeText: localeText,
            suppressAnimationFrame: true,
            suppressContextMenu: true,
            // 关闭参数检查
            suppressPropertyNamesCheck: true,
            suppressCellSelection: true,
            enableCellTextSelection: true,
            // 自定义后端数据地址
            remoteDataUrl: '',
            remoteParams: {},
            dialogList: {},
            editableList: {},
            autoColumnsToFit: true,
            lastEditCell: {},
            selectedRows: [],
            pager: false,
            pagerDom: null,
            pagePer: 50,
            // 格式化数字时候默认值是否强行为空
            numberEmptyDefaultValue: false,
            pageList: [50, 100, 500, 1000, 2000, 5000, 10000, 20000, 50000],

            onCellEditingStarted(params) {
                this.lastEditCell = params;
            },
            remoteBeforeSuccess() {},
            remoteAfterSuccess() {},
            onGridSizeChanged() {
            },
            onGridReady() {
            },
            onFirstDataRendered(params) {
                var me = this;
                var api = me.api;
                if (me.autoColumnsToFit) {
                    api.sizeColumnsToFit();
                }

                if (typeof me.onCustomFirstDataRendered == "function") {
                    me.onCustomFirstDataRendered.call(me, params);
                }

                // 计算合计行
                me.generatePinnedBottomData();
            },
            onCellValueChanged(params) {
                this.generatePinnedBottomData();
            },
            getRowStyle(params) {
            },
            onRowClicked(params) {
                var selected = params.node.isSelected();
                if (selected === false) {
                    params.node.setSelected(true, true);
                }
            },
            getSelectedRows() {
                return this.selectedRows;
            },
            onRowSelected(params) {
                var me = this;
                var node = params.node;
                if (node.selected) {
                    me.selectedRows.push(node.data);
                } else {
                    for (let i = 0; i < me.selectedRows.length; i++) {
                        var select = me.selectedRows[i];
                        if (node.data.id == select.id) {
                            me.selectedRows.splice(i, 1);
                        }
                    }
                }
            },
            columnTypes: {
                number: {
                    cellClass: 'ag-cell-number',
                    valueFormatter: function (params) {
                        var me = this;
                        if (params.node.rowPinned) {
                            if(params.colDef.calcFooter) {
                            } else {
                                return '';
                            }
                        }
                        var options = params.colDef.numberOptions || {};
                        var places = options.places == undefined ? 2 : options.places;
                        var separator = options.separator == undefined ? '.' : options.separator;
                        var thousands = options.thousands == undefined ? ',' : options.thousands;
                        var defaultValue = options.default == undefined ? 0 : options.default;
                        var value = parseFloat(params.value);

                        if (isNaN(value) || value == 0) {
                            return gridOptions.numberEmptyDefaultValue == false ? defaultValue : '';
                        }
                        value = number_format(value, places, separator, thousands);
                        return value;
                    },
                    valueParser: function (params) {
                        var value = parseFloat(params.newValue);
                        if (isNaN(value)) {
                            return 0;
                        }
                        return value;
                    }
                },
                sn: {
                    cellClass: 'ag-cell-sn',
                    valueFormatter: function (params) {
                        if (params.node.rowPinned) {
                            return '';
                        }
                        return parseInt(params.node.childIndex) + 1
                    },
                    valueParser: function (params) {
                        return parseFloat(params.newValue);
                    }
                },
                datetime: {
                    cellClass: 'ag-cell-datetime',
                    valueFormatter: function (params) {
                        if (params.node.rowPinned) {
                            return '';
                        }
                        return format_datetime(params.value);
                    },
                    valueParser: function (params) {
                        return parseFloat(params.newValue);
                    }
                },
                date: {
                    cellClass: 'ag-cell-date',
                    valueFormatter: function (params) {
                        if (params.node.rowPinned) {
                            return '';
                        }
                        return format_date(params.value);
                    },
                    valueParser: function (params) {
                        return parseFloat(params.newValue);
                    }
                }
            },
            components: {
                'optionCellRenderer': OptionCellRenderer,
                'actionCellRenderer': ActionCellRenderer,
                'htmlCellRenderer': HtmlCellRenderer,
                'selectCellEditor': SelectCellEditor,
                'dialogCellEditor': DialogCellEditor,
                'dateCellEditor': DateCellEditor,
                'checkboxCellEditor': CheckboxCellEditor,
                'checkboxCellRenderer': CheckboxCellRenderer,
            },
            overlayLoadingTemplate: '<span class="ag-overlay-loading-center">数据加载中...</span>',
            overlayNoRowsTemplate: '<div style="padding-top:20px;"><img alt="暂无数据" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNDEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAxKSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgIDxlbGxpcHNlIGZpbGw9IiNGNUY1RjUiIGN4PSIzMiIgY3k9IjMzIiByeD0iMzIiIHJ5PSI3Ii8+CiAgICA8ZyBmaWxsLXJ1bGU9Im5vbnplcm8iIHN0cm9rZT0iI0Q5RDlEOSI+CiAgICAgIDxwYXRoIGQ9Ik01NSAxMi43Nkw0NC44NTQgMS4yNThDNDQuMzY3LjQ3NCA0My42NTYgMCA0Mi45MDcgMEgyMS4wOTNjLS43NDkgMC0xLjQ2LjQ3NC0xLjk0NyAxLjI1N0w5IDEyLjc2MVYyMmg0NnYtOS4yNHoiLz4KICAgICAgPHBhdGggZD0iTTQxLjYxMyAxNS45MzFjMC0xLjYwNS45OTQtMi45MyAyLjIyNy0yLjkzMUg1NXYxOC4xMzdDNTUgMzMuMjYgNTMuNjggMzUgNTIuMDUgMzVoLTQwLjFDMTAuMzIgMzUgOSAzMy4yNTkgOSAzMS4xMzdWMTNoMTEuMTZjMS4yMzMgMCAyLjIyNyAxLjMyMyAyLjIyNyAyLjkyOHYuMDIyYzAgMS42MDUgMS4wMDUgMi45MDEgMi4yMzcgMi45MDFoMTQuNzUyYzEuMjMyIDAgMi4yMzctMS4zMDggMi4yMzctMi45MTN2LS4wMDd6IiBmaWxsPSIjRkFGQUZBIi8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4K"><div style="padding-top:5px;color:#999;">暂无数据</div></div>',
        };

        gridOptions.generatePinnedBottomData = function() {
            var me = this;
            var result = {};
            var renderer = false;
            var columns = gridOptions.columnApi.getAllGridColumns();
            columns.forEach(function (item) {
                if (item.colDef.calcFooter) {
                    renderer = true;
                    result[item.colId] = me.calculatePinnedBottomData(item);
                }
            });
            if (renderer) {
                me.api.setPinnedBottomRowData([result]);
            }
        }
        gridOptions.calculatePinnedBottomData = function(item) {
            var value = 0;
            gridOptions.api.forEachNode(function (row) {
                value += toNumber(row.data[item.colId]);
            });
            return value == 0 ? '' : value;
        }

        // 格式化行按钮
        gridOptions.actionCellBeforeRender = function(html, action, data) {
            return html;
        }

        gridOptions.remoteData = function (params, success) {
            var me = this;
            let remoteParams = gridOptions.remoteParams;
            for (let key in params) {
                remoteParams[key] = params[key];
            }
            gridOptions.api.showLoadingOverlay();
            $.post(gridOptions.remoteDataUrl, remoteParams, function (res) {

                gridOptions.remoteBeforeSuccess.call(gridOptions, res);

                if (typeof success === 'function') {
                    success(res);
                }

                if (res.per_page) {
                    if (me.pagerDom === null) {
                        var div = me.api.gridCore.eGridDiv;
                        var pageId = div.id + '-pager';
                        $(div).after('<div id="' + pageId + '" class="ag-pager"></div>');
                        me.pagerDom = $('#' + pageId).Paging({
                            pagesize: res.per_page, 
                            count: res.total,
                            current: res.current_page, 
                            pageSizeList: [50, 100, 500, 1000, 2000, 5000, 10000, 20000, 50000],
                            callback: function(page, size, count) {
                                me.remoteData2({page: page, limit: size});
                            }
                        });
                    } else {
                        me.pagerDom[0].render({
                            pagesize: res.per_page, 
                            count: res.total,
                            current: res.current_page
                        });
                    }
                }

                gridOptions.api.hideOverlay();
                gridOptions.api.setRowData(res.data);
                gridOptions.generatePinnedBottomData();

                gridOptions.remoteAfterSuccess.call(gridOptions, res);
            }, 'json');
        }

        gridOptions.remoteData2 = function (params, success) {
            var me = this;
            let remoteParams = gridOptions.remoteParams;
            for (let key in params) {
                remoteParams[key] = params[key];
            }
            gridOptions.api.showLoadingOverlay();
            $.post(gridOptions.remoteDataUrl, remoteParams, function (res) {

                gridOptions.remoteBeforeSuccess.call(gridOptions, res);

                if (typeof success === 'function') {
                    success(res);
                }

                gridOptions.api.hideOverlay();
                gridOptions.api.setRowData(res.data);
                gridOptions.generatePinnedBottomData();

                gridOptions.remoteAfterSuccess.call(gridOptions, res);
            
            }, 'json');
        }
        return gridOptions;
    }
    window.agGridOptions = agGridOptions;
})(window, jQuery);