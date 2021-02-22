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
    window.gdoo = gdoo;
})(jQuery);