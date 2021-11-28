(function($) {
    var GdooEvent = function(args) {
        var me = this;
        me.args = args;
        me.trigger = function(fun) {
            if (typeof me.args[fun] == 'function') {
                var args = [];
                for (var i = 1; i < arguments.length; i++) {
                    args.push(arguments[i]);
                }
                return me.args[fun].apply(me, args);
            }
        }
        me.exist = function(fun) {
            if (typeof me.args[fun] == 'function') {
                return true;
            }
            return false;
        }
    };

    var gdoo = {
        formKey(params) {
            if (params.form_id) {
                var key = params.form_id + '.' + params.id;
                var id = params.form_id + '_' + params.id;
                var name = params.form_id + '_' + params.name;
            } else {
                var key = params.id;
                var id = params.id;
                var name = params.name;
            }
            return {id: id, key: key, name: name};
        },
        widgets: {},
        forms: {},
        dialogs: {},
        grids: {},
        event: {
            events: {},
            set: function(tag, fun) {
                this.events[tag] = new GdooEvent(fun);
            },
            get: function(tag) {
                return this.events[tag] || new GdooEvent({});
            }
        }
    };

    /**
     * 对话框初始化
     */
    gdoo.dialogInit = function(params, grid) {

        var option = gdoo.formKey(params);
        var event = gdoo.event.get(option.key);
        event.trigger('query', params);

        // 键盘按下和弹起事件
        var ctrlNotActive = true;
        document.onkeydown = function (event) {
            event = event || window.event;
            if (event.keyCode == 17) {
                ctrlNotActive = false;
            }
        }
        document.onkeyup = function (event) {
            event = event || window.event;
            if (event.keyCode == 17) {
                ctrlNotActive = true;
            }
        }

        var multiple = params.multi == 0 ? false : true;

        // 点击行不勾选
        grid.suppressRowClickSelection = true;
        // 多选还是单选
        grid.rowSelection = multiple ? 'multiple' : 'single';
        grid.multiple = multiple;

        grid.onRowClicked = function(row) {
            var selected = row.node.isSelected();
            if (multiple) {
                if (selected === false) {
                    row.node.setSelected(true, ctrlNotActive);
                }
                if (selected === true && ctrlNotActive === false) {
                    row.node.setSelected(false, false);
                }
            } else {
                if (selected === false) {
                    row.node.setSelected(true, true);
                }
            }
        };

        grid.onRowSelected = function(row) {
            if (params.is_grid) {
            } else {
                var sid = params.prefix == 1 ? 'sid' : 'id';
                var res = dialogCacheSelected[option.id];
                if (row.node.isSelected()) {
                    res[row.data[sid]] = row.data.name;
                } else {
                    delete res[row.data[sid]];
                }
                dialogCacheSelected[option.id] = res;
            }
        }
    
        grid.onRowDoubleClicked = function () {
            var ret = gdoo.dialogSelected(event, params, option, grid);
            if (ret == true) {
                $('#gdoo-dialog-' + params.dialog_index).dialog('close');
            }
        };

        // 数据加载后执行
        grid.remoteAfterSuccess = function() {
            gdoo.dialogInitSelected(params, option, grid);
        }

        gdoo.dialogs[option.id] = grid;
        return option;
    }

    /**
     * 对话框字段写入选中
     */
    gdoo.dialogSelected = function(event, params, option, grid) {
        var rows = grid.api.getSelectedRows();
        if (params.is_grid) {
            var list = gdoo.forms[params.form_id];
            list.api.dialogSelected(params, rows);
        } else {
            var sid = params.prefix == 1 ? 'sid' : 'id';
            var multiple = params.multi == 0 ? false : true;

            var doc = getIframeDocument(params.iframe_id);
            if (doc) {
                var $option_id = $('#' + option.id, doc);
                var $option_text = $('#'+option.id + '_text', doc);
            } else {
                var $option_id = $('#' + option.id);
                var $option_text = $('#' + option.id + '_text');
            }

            var res = dialogCacheSelected[option.id] || {};

            $.each(rows, function(k, row) {
                res[row[sid]] = row.name;
            });

            $option_id.val(Object.keys(res).join(','));
            $option_text.val(Object.values(res).join(','));

            if (event.exist('onSelect')) {
                return event.trigger('onSelect', multiple ? rows : rows[0]);
            }
        }
        return true;
    }
    
    /**
     * 初始化选择
     */
    gdoo.dialogInitSelected = function(params, option, grid) {
        if (params.is_grid) {
        } else {
            var sid = params.prefix == 1 ? 'sid' : 'id';
            var doc = getIframeDocument(params.iframe_id);
            if (doc) {
                var $option_id = $('#' + option.id, doc);
                var $option_text = $('#'+option.id + '_text', doc);
            } else {
                var $option_id = $('#' + option.id);
                var $option_text = $('#' + option.id + '_text');
            }

            if (params.is_org) {
                var res = dialogCacheSelected[option.id];
            } else {
                var id = $option_id.val();
                var text = $option_text.val();
                var res = {};
                if (id) {
                    var ids = id.split(',');
                    var texts = text.split(',');
                    for (var i = 0; i < ids.length; i++) {
                        res[ids[i]] = texts[i];
                    }
                }
                dialogCacheSelected[option.id] = res;
            }

            grid.api.forEachNode(function(node) {
                var key = node.data[sid];
                if (res[key] != undefined) {
                    node.setSelected(true);
                }
            });
        }
    }

    /**
     * grid列表显示构建
     * @param {*} table
     */
    gdoo.grid = function(table) {
        var root = this;
        this.table = table;
        this.grid = new agGridOptions();

        this.header = Vue.reactive({
            init: false,
            name: '',
            master_table: '',
            access: {},
            create_btn: false,
            trash_btn: false,
            simple_search_form: true,
            right_buttons: [],
            left_buttons: [],
            buttons: [],
            tabs: {items:[], active:''},
            search_form:{columns:[]},
            bys: {name: '全部',items:[]}
        });

        this.search = {
            simple: {el: null, query: {}},
            advanced: {el: null, query: {}}
        };

        this.action = new gridAction();
        
        // 默认不自动计算栏目宽度
        this.grid.autoColumnsToFit = false;

        // 默认点击行触发
        this.grid.onRowDoubleClicked = function (params) {
            if (params.node.rowPinned) {
                return;
            }
            if (params.data == undefined) {
                return;
            }
            if (params.data.master_id > 0) {
                if (root.action.rowDoubleClick) {
                    root.action.rowDoubleClick(params.data);
                }
            }
        };

        this.div = function(height) {
            var gridDiv = document.querySelector("#" + this.table + "-grid");
            // 因为panel高度根据页面原素高度不一致，这里设置修正值
            gridDiv.style.height = this.getPanelHeight(height);
            new agGrid.Grid(gridDiv, this.grid);

            // 绑定自定义事件
            $(gridDiv).on('click', '[data-toggle="event"]', function () {
                var data = $(this).data();
                if (data.master_id > 0) {
                    root.action[data.action](data);
                }
            });

            return gridDiv;
        }

        /**
         * 获取panel计算整体窗口高度
         */
        this.getPanelHeight = function(v) {
            var list = $('.gdoo-list-grid').position();
            var position = list.top + v +'px';
            return 'calc(100vh - ' + position + ')';
        }

        this.init = function(res) {
            var me = this;
            if (me.header.init == false) {

                var header = res.header;
                me.header.init = true;
                me.header.create_btn = header.create_btn;
                me.header.trash_btn = header.trash_btn;
                me.header.name = header.name;
                me.header.table = table;

                // 搜索
                var search_form = header.search_form;
                search_form.simple_search = header.simple_search_form;
                me.header.search_form = search_form;
                me.search.forms = search_form.forms;

                // 操作
                me.action.table = table;
                me.action.name = header.master_name;
                me.action.bill_url = header.bill_uri;

                // access
                if (header.access) {
                    me.header.access = header.access;
                }
    
                // 按钮
                if (header.right_buttons) {
                    me.header.right_buttons = header.right_buttons;
                }
                if (header.left_buttons) {
                    me.header.left_buttons = header.left_buttons;
                }
                if (header.buttons) {
                    me.header.center_buttons = header.buttons;
                }
    
                // bys
                if (header.bys) {
                    me.header.bys = header.bys;
                    if (search_form.params['by']) {
                        me.header.bys.active = search_form.params['by'];
                        for (let i = 0; i < header.bys.items.length; i++) {
                            const item = header.bys.items[i];
                            if (item.value == me.header.bys.active) {
                                me.header.bys.name = item.name;
                            }
                        }
                    } else {
                        me.header.bys.active = header.bys.items[0].value;
                        me.header.bys.name = header.bys.items[0].name;
                    }
                }

                // tabs
                if (header.tabs) {
                    me.header.tabs = header.tabs;
                    me.header.tabs.active = search_form.params['tab'] ? search_form.params['tab'] : header.tabs.items[0].value;
                }

                // 设置栏目
                me.grid.api.setColumnDefs(header.columns);
                me.grid.columnDefs = header.columns;
                me.grid.remoteParams = search_form.query;

                // 渲染完成显示div
                $('#' + table + '-page').show();

                setTimeout(function() {
                    me.searchForm();
                }, 1);

            }
        }
    
        this.searchForm = function() {
            var me = this;
            me.search.advanced.el = $('#' + me.table + '-search-form-advanced').searchForm({
                data: me.search.forms,
                advanced: true
            });
            me.search.simple.el = $('#' + me.table + '-search-form').searchForm({
                data: me.search.forms
            });
            me.search.simple.el.find('#search-submit').on('click', function() {
                var query = me.search.simple.el.serializeArray();
                var params = {};
                me.search.queryType = 'simple';
                $.map(query, function(row) {
                    params[row.name] = row.value;
                });
                params['page'] = 1;
                me.grid.remoteData(params);
                return false;
            });
        }
    
        this.setup = {
            header: this.header,
            action: this.action,
            grid: this.grid
        };

        gdoo.grids[table] = {
            grid: this.grid, 
            search: this.search
        };
    }

    window.gdoo = gdoo;
})(jQuery);