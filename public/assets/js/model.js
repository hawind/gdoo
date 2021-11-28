(function (window) {

    // 循环子表
    function gridListData(table) {
        var gets = {};
        var tables = formGridList[table] || [];
        if (tables.length) {
            for (var i = 0; i < tables.length; i++) {
                var t = tables[i];
                var store = t.api.memoryStore;
                var rows = [];

                t.api.stopEditing();
                t.api.forEachNode(function(rowNode) {
                    var data = rowNode.data;
                    if (isNotEmpty(data[t.dataKey])) {
                        var id = '' + data.id;
                        var draft = id.indexOf('draft_');
                        if (draft === 0) {
                            data.id = 0;
                        }
                        rows.push(data);
                    }
                });

                var event = gdoo.event.get('grid.' + t.tableKey);

                if (event.exist('onSaveBefore')) {
                    var ret = event.trigger('onSaveBefore', rows);
                    if (ret === false) {
                        return false;
                    }
                }

                if (rows.length == 0 && t.tableSaveDataNotEmpty === true) {
                    toastrError(t.tableTitle + '不能为空。');
                    return false;
                } else {
                    gets[t.tableKey] = {rows: rows, deleteds: store.deleted};
                }
            }
        }
        return gets;
    }

    // 刷新所有相关grid
    function reloadGrid(table) {
        var iframes = top.document.getElementsByTagName("iframe");
        for (var i=0; i < iframes.length; i++) {
            var iframe = iframes[i];
            var $gdoo = iframe.contentWindow.gdoo;
            if (iframe.id == 'tab_iframe_dashboard') {
                // 刷新首页全部部件
                var widgets = Object.values($gdoo.widgets);
                widgets.forEach(function(grid) {
                    grid.remoteData();
                });
            } else {
                // 刷新全部页面的相关grid
                if ($gdoo && $gdoo.grids) {
                    var grids = $gdoo.grids;
                    if (grids[table]) {
                        grids[table].grid.remoteData();
                    }
                }
            }
        }
    }

    window.gridListData = gridListData;

    var model = {
        bill_url: '',
        audit: function (table) {
            var form = $('#' + table);
            var key = form.find('#master_key').val();
            var run_id = form.find('#master_run_id').val();
            var step_id = form.find('#master_step_id').val();
            var run_log_id = form.find('#master_run_log_id').val();
            var uri = $('#' + table).find('#master_uri').val();
            var url = app.url(uri + '/flowAudit', {key:key, run_id:run_id, step_id:step_id, run_log_id:run_log_id});
            $.dialog({
                title: '单据审批',
                url: url,
                buttons: [{
                    text: '取消',
                    'class': 'btn-default',
                    click: function () {
                        $(this).dialog('close');
                    }
                },{
                    text: '提交',
                    'class': 'btn-info',
                    click: function () {
                        var query = $('#myturn,#' + table).serialize();
                        // 循环子表
                        var gets = gridListData(table);
                        if(gets === false) {
                            return;
                        }
                        var loading = showLoading();
                        $.post(app.url(uri + '/flowAudit'), query + '&' + $.param(gets), function (res) {
                            if (res.status) {
                                reloadGrid(table);
                                toastrSuccess(res.data);
                                if (res.url) {
                                    location.href = res.url;
                                }
                            } else {
                                toastrError(res.data);
                            }
                        }, 'json').complete(function() {
                            layer.close(loading);
                        });
                    }
                }]
            });
        }, draft: function (table) {
            var uri = $('#' + table).find('#master_uri').val();
            var query = $('#myturn,#' + table).serialize();
            // 循环子表
            var gets = gridListData(table);
            if(gets === false) {
                return;
            }

            var loading = showLoading();

            var event = gdoo.event.get('grid.' + table);

            $.post(app.url(uri + '/flowDraft'), query + '&' + $.param(gets), function (res) {
                
                if (event.exist('onSaveAfter')) {
                    res = event.trigger('onSaveAfter', res);
                }

                if (res.status) {
                    reloadGrid(table);
                    toastrSuccess(res.data);
                    if (res.url) {
                        location.href = res.url;
                    }
                } else {
                    toastrError(res.data);
                }
            }, 'json').complete(function() {
                layer.close(loading);
            });

        }, remove: function (url) {
            $.messager.confirm('操作警告', '确定要删除吗？', function (btn) {
                if (btn == true) {
                    $.post(url, function (res) {
                        if (res.status) {
                            toastrSuccess(res.data);
                            location.reload();
                        } else {
                            toastrError(res.data);
                        }
                    }, 'json');
                }
            });
        }, store: function (table) {
            var uri = $('#' + table).find('#master_uri').val();
            var query = $('#' + table).serialize();
            // 循环子表
            var gets = gridListData(table);
            if(gets === false) {
                return;
            }

            var loading = showLoading();

            $.post(app.url(uri + '/store'), query + '&' + $.param(gets), function (res) {
                if (res.status) {
                    reloadGrid(table);
                    toastrSuccess(res.data);
                    if (res.url) {
                        location.href = res.url;
                    }
                } else {
                    toastrError(res.data);
                }
            }, 'json').complete(function() {
                layer.close(loading);
            });

        }, read: function (table) {
            var uri = $('#' + table).find('#master_uri').val();
            var query = $('#' + table).serialize();

            var loading = showLoading();

            $.post(app.url(uri + '/flowRead'), query, function (res) {
                if (res.status) {
                    reloadGrid(table);
                    toastrSuccess(res.data);
                    location.reload();
                } else {
                    toastrError(res.data);
                }
            }, 'json').complete(function() {
                layer.close(loading);
            });
        },
        reset: function (table) {
            $.messager.confirm('操作警告', '确定要重置流程吗', function(btn) {
                if (btn == true) {
                    var uri = $('#' + table).find('#master_uri').val();
                    var query = $('#' + table).serialize();
                    
                    var loading = showLoading();

                    $.post(app.url(uri + '/flowReset'), query, function (res) {
                        if (res.status) {
                            location.reload();
                        } else {
                            toastrError(res.data);
                        }
                    }, 'json').complete(function() {
                        layer.close(loading);
                    });
                }
            });
        },
        auditLog: function (key) {
            var url = app.url('index/workflow/flowLog', {key: key});
            $.dialog({
                title: '审批记录',
                dialogClass: 'modal-lg',
                url: url,
                buttons: [{
                    text: '取消',
                    'class': 'btn-default',
                    click: function () {
                        $(this).dialog('close');
                    }
                }]
            });
        },
        revise: function (key) {
            var url = app.url('index/workflow/flowRevise', {key: key});
            formDialog({
                title: '流程修正',
                url: url,
                dialogClass: 'modal-md',
                id: 'revise-form',
                success: function(res) {
                    toastrSuccess(res.data);
                    location.reload();
                    $(this).dialog("close");
                },
                error: function(res) {
                    toastrError(res.data);
                }
            });
        }
    }

    // 流程撤回
    model.recall = function (table) {
        var key = $('#' + table).find('#master_key').val();
        var uri = $('#' + table).find('#master_uri').val();
        var log_id = $('#' + table).find('#master_recall_log_id').val();
        var url = app.url(uri + '/recall', {key: key, log_id: log_id});
        $.dialog({
            title: '撤回单据',
            url: url,
            buttons: [{
                text: "取消",
                'class': "btn-default",
                click: function () {
                    $(this).dialog("close");
                }
            },{
                text: "提交",
                'class': "btn-info",
                click: function () {
                    var query = $('#myrecall').serialize();
                    
                    var loading = showLoading();

                    $.post(app.url(uri + '/recall'), query, function (res) {
                        if (res.status) {
                            reloadGrid(table);
                            toastrSuccess(res.data);
                            location.reload();
                        } else {
                            toastrError(res.data);
                        }
                    }, 'json').complete(function() {
                        layer.close(loading);
                    });
                }
            }]
        });
    }

    // 弃审单据
    model.abort = function (table) {
        var key = $('#' + table).find('#master_key').val();
        var uri = $('#' + table).find('#master_uri').val();
        var url = app.url(uri + '/abort', {key: key});
        $.dialog({
            title: '弃审单据',
            url: url,
            buttons: [{
                text: "取消",
                'class': "btn-default",
                click: function () {
                    $(this).dialog("close");
                }
            },{
                text: "提交",
                'class': "btn-info",
                click: function () {
                    var query = $('#myabort').serialize();

                    var loading = showLoading();

                    $.post(app.url(uri + '/abort'), query, function (res) {
                        if (res.status) {
                            reloadGrid(table);
                            toastrSuccess(res.data);
                            location.reload();
                        } else {
                            toastrError(res.data);
                        }
                    }, 'json').complete(function() {
                        layer.close(loading);
                    });
                }
            }]
        });
    }

    // 普通审核弃审
    model.audit2 = function (table) {
        var key = $('#' + table).find('#master_key').val();
        var uri = $('#' + table).find('#master_uri').val();
        $.messager.confirm('操作警告', '确定要审核单据吗', function(btn) {
            if (btn == true) {
                var loading = showLoading();
                $.post(app.url(uri + '/audit'), {key: key}, function (res) {
                    if (res.status) {
                        reloadGrid(table);
                        toastrSuccess(res.data);
                        location.reload();
                    } else {
                        toastrError(res.data);
                    }
                }, 'json').complete(function() {
                    layer.close(loading);
                });
            }
        });
    }

    // 普通审核弃审
    model.abort2 = function (table) {
        var key = $('#' + table).find('#master_key').val();
        var uri = $('#' + table).find('#master_uri').val();
        $.messager.confirm('操作警告', '确定要弃审单据吗', function(btn) {
            if (btn == true) {
                var loading = showLoading();
                $.post(app.url(uri + '/abort'), {key: key}, function (res) {
                    if (res.status) {
                        reloadGrid(table);
                        top.$.toastr('success', res.data);
                        location.reload();
                    } else {
                        top.$.toastr('error', res.data);
                    }
                }, 'json').complete(function() {
                    layer.close(loading);
                });
            }
        });
    }

    // 子表新增
    model.createRow = function (table) {
        var grid = gdoo.forms[table];
        var onCreateRow = window[table + '.onCreateRow'];
        if (typeof onCreateRow == 'function') {
            var ret = onCreateRow.call(grid, table);
            if (ret === false) {
                return false;
            }
        }
        grid.api.memoryStore.create({});
    }

    // 子表删除
    model.deleteRow = function (table) {
        var grid = gdoo.forms[table];
        var onDeleteRow = window[table + '.onDeleteRow'];
        if (typeof onDeleteRow == 'function') {
            var ret = onDeleteRow.call(grid, table);
            if (ret === false) {
                return false;
            }
        }
        let selectedNodes = grid.api.getSelectedNodes();
        if (selectedNodes && selectedNodes.length === 1) {
            let selectedNode = selectedNodes[0];
            grid.api.deleteRow(selectedNode.data);
            grid.api.forEachNode((node) => {
                if (node.childIndex === (selectedNode.childIndex)) {
                    node.setSelected(true);
                    return;
                }
            });
        }
    }

    // 快速搜索
    model.quickFilter = function (table) {
        var grid = gdoo.forms[table];
        var $div = $('#' + table + '_quick_filter_text');
        var $el = $div.dialog({
            title: '<i class="fa fa-filter"></i> 过滤' + grid.tableTitle,
            modalClass:'no-padder',
            dialogClass:'modal-sm',
            buttons: [
                {text: "确定", classed: 'btn-info', click: function() {
                    grid.api.setQuickFilter($div.find('input').val());
                    $el.dialog("close");
                }
            },{
                text: "取消", classed: 'btn-default', click: function() {
                    $el.dialog("close");
                }}
            ]
        }).on('keydown', function(e) {
            if (e.keyCode == 13) {
                grid.api.setQuickFilter($div.find('input').val());
                $el.dialog("close");
            }
        });
    }

    // 子表关闭
    model.closeRow = function (table) {
        var me = this;
        var grid = gdoo.forms[table];
        var rows = grid.api.getSelectedRows();
        if (rows.length > 0) {
            var id = rows[0].id;
            top.$.messager.confirm('操作提醒', '是否要关闭选中的行数据?', function(btn) {
                if (btn == true) {
                    var loading = showLoading();
                    $.post(app.url(me.bill_url + '/closeRow'), {table: table,id: id}, function(res) {
                        if (res.status) {
                            toastrSuccess(res.data);
                            grid.remoteData();
                        } else {
                            toastrError(res.data);
                        }
                    },'json').complete(function() {
                        layer.close(loading);
                    });
                }
            });
        } else {
            toastrError('最少选择一行记录。');
        }
    }

    // 子表关闭所有
    model.closeAllRow = function (table) {
        var me = this;
        var grid = gdoo.forms[table];
        var ids = [];
        grid.api.forEachNode(function(node) {
            ids.push(node.data.id);
        });
        if (ids.length > 0) {
            top.$.messager.confirm('操作提醒', '是否要关闭所有行数据?', function(btn) {
                if (btn == true) {
                    var loading = showLoading();
                    $.post(app.url(me.bill_url + '/closeAllRow'), {table: table,ids: ids}, function(res) {
                        if (res.status) {
                            toastrSuccess(res.data);
                            grid.remoteData();
                        } else {
                            toastrError(res.data);
                        }
                    },'json').complete(function() {
                        layer.close(loading);
                    });
                }
            });
        } else {
            toastrError('最少选择一行记录。');
        }
    }

    window.flow = model;
})(window);

(function($) {
    function gridAction(table, name) {
        this.name = name;
        this.table = table;
        this.dialogType = 'dialog';

        this.show = function(data, key, name) {
            var me = this;

            if (data.flow_form_edit == 1) {
                me.audit(data);
                return;
            }

            var url = app.url(me.bill_url + '/show', {id: data.master_id});
            if (me.dialogType == 'dialog') {
                viewDialog({
                    title: me.name,
                    dialogClass: 'modal-lg',
                    url: url,
                    close: function() {
                        $(this).dialog("close");
                    }
                });
            } else {
                if (isEmpty(key)) {
                    key = me.bill_url.replace(/\//g,'_') + '_show';
                }
                if (isEmpty(name)) {
                    name = me.name;
                }
                top.addTab(me.bill_url + '/show?id=' + data.master_id, key, name);
            }
        }

        this.import = function() {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            formDialog({
                title: '数据导入',
                url: app.url(me.bill_url + '/import'),
                dialogClass: 'modal-md',
                id: 'import-dialog',
                onSubmit: function() {
                    var fd = new FormData();
                    fd.append("file", $('#import_file')[0].files[0]);
                    var loading = showLoading();
                    $.ajax({
                        url: app.url(me.bill_url + '/import'),
                        type: "POST",
                        data: fd,
                        processData: false,
                        contentType: false,
                        complete: function() {
                            layer.close(loading);
                        },
                        success: function(res) {
                            if (res.status) {
                                $('#modal-import-dialog').dialog('close');
                                grid.remoteData();
                                toastrSuccess(res.data);
                            } else {
                                toastrError(res.data);
                            }
                        }
                    });
                }
            });
        }

        this.delete = function() {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            var rows = grid.api.getSelectedRows();
            var ids = [];
            $.each(rows, function(index, row) {
                ids.push(row.master_id);
            });

            if (ids.length > 0) {
                var content = ids.length + '个' + me.name + '将被删除？';
                top.$.messager.confirm('删除' + me.name, content, function(btn) {
                    if (btn == true) {
                        var loading = showLoading();
                        $.post(app.url(me.bill_url + '/delete'), {id: ids}, function(res) {
                            if (res.status) {
                                toastrSuccess(res.data);
                                grid.remoteData();
                            } else {
                                toastrError(res.data);
                            }
                        },'json').complete(function() {
                            layer.close(loading);
                        });
                    }
                });
            } else {
                toastrError('最少选择一行记录。');
            }
        }

        this.created_by = function(data) {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            formDialog({
                title: '私信',
                url: app.url('user/message/create', {user_id: data.id}),
                storeUrl: app.url('model/form/store'),
                id: 'user_message',
                dialogClass:'modal-md',
                success: function(res) {
                    toastrSuccess(res.data);
                    grid.remoteData();
                    $(this).dialog("close");
                },
                error: function(res) {
                    toastrError(res.data);
                }
            });
        }

        this.create = function() {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            if (me.dialogType == 'dialog') {
                formDialog({
                    title: '新建' + me.name,
                    url: app.url(me.bill_url + '/create'),
                    storeUrl: app.url(me.bill_url + '/store'),
                    id: me.table,
                    table: me.table,
                    dialogClass: 'modal-lg',
                    success: function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData();
                        $(this).dialog("close");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                var key = me.bill_url.replace(/\//g,'_') + '_show';
                top.addTab(me.bill_url + '/create', key, me.name);
            }
        }

        this.edit = function(data) {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            if (me.dialogType == 'dialog') {
                formDialog({
                    title: '编辑' + me.name,
                    url: app.url(me.bill_url + '/edit', {id: data.master_id}),
                    storeUrl: app.url(me.bill_url + '/store'),
                    id: me.table,
                    table: me.table,
                    dialogClass: 'modal-lg',
                    success: function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData();
                        $(this).dialog("close");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                var key = me.bill_url.replace(/\//g,'_') + '_show';
                top.addTab(me.bill_url + '/edit?id=' + data.master_id, key, me.name);
            }
        }

        this.audit = function(data) {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            if (me.dialogType == 'dialog') {
                formDialog({
                    title: '审核' + me.name,
                    url: app.url(me.bill_url + '/audit', {id: data.master_id}),
                    storeUrl: app.url(me.bill_url + '/store'),
                    id: me.table,
                    table: me.table,
                    dialogClass: 'modal-lg',
                    success: function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData();
                        $(this).dialog("close");
                    },
                    error: function(res) {
                        toastrError(res.data);
                    }
                });
            } else {
                var key = me.bill_url.replace(/\//g,'_') + '_show';
                top.addTab(me.bill_url + '/audit?id=' + data.master_id, key, me.name);
            }
        }

        this.batchEdit = function() {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            var rows = grid.api.getSelectedRows();
            var ids = [];
            $.each(rows, function(index, row) {
                ids.push(row.master_id);
            });
            if (ids.length > 0) {
                formDialog({
                    title: '批量编辑',
                    dialogClass: 'modal-sm',
                    id: 'batch-edit-form',
                    url: app.url(me.bill_url + '/batchEdit', {ids: ids.join(',')}),
                    success: function(res) {
                        toastrSuccess(res.data);
                        grid.remoteData();
                        $(this).dialog("close");
                    },
                    close: function() {
                        $(this).dialog("close");
                    }
                });
            } else {
                toastrError('最少选择一行记录。');
            }
        }

        // 导出
        this.export = function() {
            var me = this;
            var grid = gdoo.grids[me.table].grid;
            LocalExport(grid, me.name);
        }

        this.filter = function() {
            var me = this;
            var config = gdoo.grids[me.table];
            var grid = config.grid;
            var search = config.search;
            // 过滤数据
            $(search.advanced.el).dialog({
                title: '高级搜索',
                modalClass: 'no-padder',
                buttons: [{
                    text: "取消",
                    'class': "btn-default",
                    click: function() {
                        $(this).dialog("close");
                    }
                },{
                    text: "确定",
                    'class': "btn-info",
                    click: function() {
                        var query = search.advanced.el.serializeArray();
                        var params = {};
                        search.queryType = 'advanced';
                        $.map(query, function(row) {
                            params[row.name] = row.value;
                        });
                        params['page'] = 1;
                        grid.remoteData(params);
                        $(this).dialog("close");
                        return false;
                    }
                }]
            });
        }
    }
    window.gridAction = gridAction;
})(jQuery);