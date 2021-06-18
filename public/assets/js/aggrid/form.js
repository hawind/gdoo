var formGridList = {};

function gridForms(master, table, options) {
    options.master = master;
    var grid = gridForm(table, options);
    formGridList[master].push(grid);
    return grid;
}

function gridForm(table, options) {

    var defaults = {
        heightTop: 70,
        data: [],
        dataType: 'local',
    };
    options = $.extend(defaults, options);

    var grid = new agGridOptions();
    grid.suppressLoadingOverlay = true;
    grid.suppressNoRowsOverlay = true;
    grid.rowMultiSelectWithClick = false;
    grid.singleClickEdit = true;
    grid.rowSelection = 'single';
    grid.suppressCellSelection = false;
    grid.suppressRowClickSelection = false;
    grid.columnDefs = options.columns;
    grid.links = options.links;

    grid.tableTitle = options.title;
    grid.tableKey = options.table;
    grid.tableSaveDataNotEmpty = options.saveDataNotEmpty;

    grid.defaultColDef.sortable = false;
    grid.defaultColDef.filter = false;
    grid.defaultColDef.suppressMenu = true;
    grid.defaultColDef.suppressNavigable = true;
    grid.isEditing = false;
    grid.isEditingDialog = false;

    var event = gdoo.event.get('grid.' + table);

    var editable = event.args.editable || {};

    grid.defaultColDef.editable = function(params) {
        if (params.node.rowPinned) {
            return false;
        }
        var col = params.colDef;
        var fun = editable[col.field];
        if (typeof fun == 'function') {
            return fun.call(grid, params);
        } else {
            if (params.colDef._editable) {
                return true;
            }
            return false;
        }
    }

    grid.getRowNodeId = function(data) {
        if (data.id) {
            return data.id;
        }
    }

    grid.onRowClicked = function(row) {
    };

    grid.stopEditingWhenGridLosesFocus = false;

    grid.onRowDoubleClicked = function (row) {
    };

    grid.onCellEditingStarted = function(params) {
        grid.lastEditCell = params;
        grid.isEditingDialog = params.colDef.cellEditor == 'dialogCellEditor';
        grid.isEditing = true;
    }
    grid.onCellEditingStopped = function() {
        grid.isEditing = false;
        if (grid.isEditingDialog) {
            $('#gdoo-gird-suggest').hide();
        }
    }

    var autoHeight = false;
    var gridDiv = document.querySelector('#grid_' + table);
    if (gridDiv.style.height == '') {
        autoHeight = true;
        gridDiv.style.height = getTabContentHeight() + 'px';
    }

    event.trigger('init', grid);

    new agGrid.Grid(gridDiv, grid);

    grid.api.dialogSelected = function(query, selectedRows) {

        var dialogEvent = gdoo.event.get(query.form_id + '.' + query.id);
        var links = options.links[query.id];

        //var dialogGrid = gdoo.dialogs[query.form_id + '_' + query.id];
        //var selectedRows = dialogGrid.getSelectedRows();

        var store = grid.api.memoryStore;
        var rows = [];
        grid.api.forEachNode(function(rowNode, index) {
            if (rowNode.data[query.id] == undefined) {
                rows.push(rowNode.data);
            }
        });

        // 如果传入的行id为0
        if (query.grid_id == 0) {
            query.grid_id = grid.lastEditCell.data.id;
        }

        for (let i = 0; i < selectedRows.length; i++) {
            var row = rows[i];
            var update = true;
            if (row == undefined) {
                row = {};
                update = false;
            }
            if (selectedRows.length == 1) {
                var res = grid.api.getSelectedRows();
                if (res.length == 0) {
                    row = {};
                    if (query.grid_id) {
                        var node = grid.api.getRowNode(query.grid_id);
                        row = node.data;
                        update = true;
                    }
                } else {
                    if (query.grid_id) {
                        row = res[0];
                        update = true;
                    }
                }
            }

            var selectedRow = selectedRows[i];
            for (key in links) {
                row[key] = selectedRow[links[key]];
            }

            dialogEvent.trigger('onSelect', row, selectedRow);
            
            if (update) {
                store.update(row);
            } else {
                store.create(row);
            }

            // 数据设置
            if (selectedRows.length == 1) {
                if (query.grid_id) {
                    $('#' + query.name + '_' + query.grid_id).val(row[query.name]);
                }
            }
        }
        grid.generatePinnedBottomData();
    }

    grid.api.memoryStore = {
        lastIndex: 1,
        created: [],
        updated: [],
        deleted: [],
        create(row, index) {
            this.lastIndex++;
            row['id'] = 'draft_' + this.lastIndex;
            row = grid.calcRow(row);
            this.created.push(row);
            grid.api.updateRowData({add:[row], addIndex: index});
            
        },
        update(row) {
            row = grid.calcRow(row);
            this.updated.push(row);
            grid.api.updateRowData({update:[row]});
        },
        delete(row, draft) {
            if (draft == false) {
                this.deleted.push(row);
            }
            // 删除创建里的草稿数据
            this.created = this.created.filter(function(item) {
                return (item.id == row.id) ? false : true;
            });

            // 删除更新后的数据
            this.updated = this.updated.filter(function(item) {
                return (item.id == row.id) ? false : true;
            });
            var res = grid.api.updateRowData({remove: [row]});
            grid.generatePinnedBottomData();
        }
    };

    grid.onFirstDataRendered = function(params) {
        var api = this.api;
        // 计算合计行
        this.generatePinnedBottomData();
    };

    grid.calcRow = function(row, col) {
        for(var i = 0; i < options.columns.length; i++) {
            var column = options.columns[i];
            if (column.calcRow) {
                var fun = new Function('data', 'column', column.calcRow);
                var value = parseFloat(fun(row, col || {}));
                if (isNaN(value) || value === 0) {
                    value = '';
                }
                row[column.field] = value;
            }
        }
        return row;
    }

    grid.onCellValueChanged = function(params) {
        var me = this;
        if (params.oldValue == params.newValue) {
            return;
        }
        var data = params.data;
        data = me.calcRow(data, params.column);
        me.api.updateRowData({update:[data]});
        me.generatePinnedBottomData();
    };

    // 本地数据类型，初始化一条数据
    if (options.dataType == 'local') {
        if (options.data.length > 0) {
            if (typeof event.dataLoaded == 'function') {
                event.dataLoaded.call(grid, options.data);
            }
            grid.api.updateRowData({add:options.data});
        } else {
            grid.api.memoryStore.create({});
        }
    }

    // 删除方法
    grid.api.deleteRow = function(data) {
        var rowNode = grid.api.getRowNode(data.id);
        var row = rowNode.data;
        // 检查删除的是否是草稿
        var id = '' + row.id;
        var draft = id.indexOf('draft_');
        if (draft === 0) {
            grid.api.memoryStore.delete(row, true);
        } else {
            grid.api.memoryStore.delete(row, false);
        }
    }

    // 绑定自定义事件
    var $gridDiv = $(gridDiv);
    $gridDiv.off();

    $gridDiv.on('click', '[data-toggle="event"]', function () {
        var data = $(this).data();
        if (data.action == 'option') {
            if (data.type == 'add') {
                grid.api.memoryStore.create({});
            } else {
                // 最后一行不能删除
                var count = grid.api.getDisplayedRowCount();
                if (count > 1) {
                    grid.api.deleteRow(data);
                }
            }
        }
    });

    // 编辑器失去焦点关闭
    $gridDiv.on('blur', '.ag-input-wrapper', function() {
        if (grid.isEditingDialog) {
            return;
        }
        if (grid.isEditing) {
            grid.api.stopEditing();
        }
    });

    function getTabContentHeight() {
        var list = $('#tab-content-' + options.master).position();
        var height = $(window).height() - list.top - options.heightTop;
        return height > 320 ? height : 320;
    }

    grid.onGridReady = function (params) {

        event.trigger('ready', grid);

        if (autoHeight) {
            window.addEventListener('resize', function() {
                setTimeout(function() {
                    gridDiv.style.height = getTabContentHeight() + 'px';
                });
            });
        }
    }
    gdoo.forms[table] = grid;
    return grid;
}