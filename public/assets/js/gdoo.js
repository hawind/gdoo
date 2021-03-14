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
            by_title: '全部',
            bys: {items:[]}
        });

        this.search = {
            simple: {el: null, query: {}},
            advanced: {el: null, query: {}}
        };

        this.action = new gridAction();

        this.init = function(res) {
            var me = this;
            if (me.header.init == false) {

                var header = res.header;
                me.header.init = true;
                me.header.create_btn = header.create_btn;
                me.header.trash_btn = header.trash_btn;
                me.header.access = header.access;
                me.header.name = header.name;
                me.header.table = table;
    
                // 搜索
                var search_form = header.search_form;
                search_form.simple_search = header.simple_search_form;

                me.header.search_form = search_form;
                me.search.forms = search_form.forms;

                // 设置栏目
                me.grid.api.setColumnDefs(header.columns);
                me.grid.columnDefs = header.columns;

                me.grid.remoteParams = search_form.query;
    
                // 操作
                me.action.table = table;
                me.action.name = header.master_name;
                me.action.bill_url = header.bill_uri;
    
                // 按钮
                me.header.right_buttons = header.right_buttons;
                me.header.left_buttons = header.left_buttons;
                me.header.center_buttons = header.buttons;
    
                // tabs
                me.header.bys = header.bys;
                me.header.tabs = header.tabs;
                me.header.tabs.active = search_form.params['tab'] ? search_form.params['tab'] : header.tabs.items[0].value;
                
                // 渲染完成显示div
                $('#' + table + '-controller').show();

                setTimeout(function() {
                    me.searchForm();
                }, 1);

                // 绑定自定义事件
                var $gridDiv = $("#" + table + "-grid");
                $gridDiv.on('click', '[data-toggle="event"]', function () {
                    var data = $(this).data();
                    if (data.master_id > 0) {
                        me.action[data.action](data);
                    }
                });
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
    
        this.tabBtn = function(tab) {
            root.header.tabs.active = tab.value;
            root.grid.remoteData({page:1, tab:tab.value});
        }
        this.actBtn = function(act) {
            root.action[act]();
        }
        this.linkBtn = function(btn) {
            if (btn.action) {
                root.action[btn.action]();
            }
        }
        this.byBtn = function(by) {
            root.header.by_title = by.name;
            root.grid.remoteData({page:1, by:by.value});
        }

        function url(url, query) {
            let params = this.toRaw(me.header.search_form.params);
            for (const key in query) {
                params[key] = query[key];
            }
            return app.url(url, params);
        }

        this.setup = {
            header: this.header, 
            tabBtn: this.tabBtn,
            actBtn: this.actBtn,
            linkBtn: this.linkBtn,
            byBtn: this.byBtn
        };

        gdoo.grids[table] = {
            grid: this.grid, 
            search: this.search
        };
    }

    window.gdoo = gdoo;
})(jQuery);