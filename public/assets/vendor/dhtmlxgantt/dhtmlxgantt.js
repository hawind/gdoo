/*
@license

dhtmlxGantt v.4.2.1 Stardard
This software is covered by GPL license. You also can obtain Commercial or Enterprise license to use it in non-GPL project - please contact sales@dhtmlx.com. Usage without proper license is prohibited.

(c) Dinamenta, UAB.
*/

window.Gantt = {
    _seed: 0
},
Gantt.plugin = function(t) {
    this._ganttPlugin.push(t),
    t(window.gantt)
},
Gantt._ganttPlugin = [],
Gantt.getGanttInstance = function() {
    var t = {
        version: "4.2.1"
    };
    t = {
        version: "4.2.1"
    },
    t.event = function(t, e, i) {
        t.addEventListener ? t.addEventListener(e, i, !1) : t.attachEvent && t.attachEvent("on" + e, i)
    },
    t.eventRemove = function(t, e, i) {
        t.removeEventListener ? t.removeEventListener(e, i, !1) : t.detachEvent && t.detachEvent("on" + e, i)
    },
    t._eventable = function(t) {
        t._silent_mode = !1,
        t._silentStart = function() {
            this._silent_mode = !0;
        },
        t._silentEnd = function() {
            this._silent_mode = !1
        },
        t.attachEvent = function(t, e, i) {
            return t = "ev_" + t.toLowerCase(),
            this[t] || (this[t] = new this._eventCatcher(i || this)),
            t + ":" + this[t].addEvent(e)
        },
        t.callEvent = function(t, e) {
            return this._silent_mode ? !0 : (t = "ev_" + t.toLowerCase(), this[t] ? this[t].apply(this, e) : !0)
        },
        t.checkEvent = function(t) {
            return !! this["ev_" + t.toLowerCase()]
        },
        t._eventCatcher = function(t) {
            var e = [],
            i = function() {
                for (var i = !0,
                n = 0; n < e.length; n++) if (e[n]) {
                    var a = e[n].apply(t, arguments);
                    i = i && a
                }
                return i
            };
            return i.addEvent = function(t) {
                return "function" == typeof t ? e.push(t) - 1 : !1
            },
            i.removeEvent = function(t) {
                e[t] = null
            },
            i
        },
        t.detachEvent = function(t) {
            if (t) {
                var e = t.split(":");
                this[e[0]].removeEvent(e[1])
            }
        },
        t.detachAllEvents = function() {
            for (var t in this) 0 === t.indexOf("ev_") && delete this[t]
        },
        t = null
    },
    t.copy = function(e) {
        var i, n, a;
        if (e && "object" == typeof e) {
            for (a = {},
            n = [Array, Date, Number, String, Boolean], i = 0; i < n.length; i++) e instanceof n[i] && (a = i ? new n[i](e) : new n[i]);
            for (i in e) Object.prototype.hasOwnProperty.apply(e, [i]) && (a[i] = t.copy(e[i]))
        }
        return a || e
    },
    t.mixin = function(t, e, i) {
        for (var n in e)(!t[n] || i) && (t[n] = e[n]);
        return t
    },
    t.defined = function(t) {
        return "undefined" != typeof t
    },
    t.uid = function() {
        return this._seed || (this._seed = (new Date).valueOf()),
        this._seed++,
        this._seed
    },
    t.bind = function(t, e) {
        return t.bind ? t.bind(e) : function() {
            return t.apply(e, arguments)
        }
    },
    function() {
        function e(t) {
            var e = !1,
            i = !1;
            if (window.getComputedStyle) {
                var n = window.getComputedStyle(t, null);
                e = n.display,
                i = n.visibility
            } else t.currentStyle && (e = t.currentStyle.display, i = t.currentStyle.visibility);
            return "none" != e && "hidden" != i
        }
        function i(t) {
            return ! isNaN(t.getAttribute("tabindex")) && 1 * t.getAttribute("tabindex") >= 0
        }
        function n(t) {
            var e = {
                a: !0,
                area: !0
            };
            return e[t.nodeName.loLowerCase()] ? !!t.getAttribute("href") : !0
        }
        function a(t) {
            var e = {
                input: !0,
                select: !0,
                textarea: !0,
                button: !0,
                object: !0
            };
            return e[t.nodeName.toLowerCase()] ? !t.hasAttribute("disabled") : !0
        }
        t._getFocusableNodes = function(t) {
            for (var r = t.querySelectorAll(["a[href]", "area[href]", "input", "select", "textarea", "button", "iframe", "object", "embed", "[tabindex]", "[contenteditable]"].join(", ")), s = Array.prototype.slice.call(r, 0), o = 0; o < s.length; o++) {
                var _ = s[o],
                l = (i(_) || a(_) || n(_)) && e(_);
                l || (s.splice(o, 1), o--)
            }
            return s
        }
    } (),
    t._get_position = function(t) {
        var e = 0,
        i = 0;
        if (t.getBoundingClientRect) {
            var n = t.getBoundingClientRect(),
            a = document.body,
            r = document.documentElement,
            s = window.pageYOffset || r.scrollTop || a.scrollTop,
            o = window.pageXOffset || r.scrollLeft || a.scrollLeft,
            _ = r.clientTop || a.clientTop || 0,
            l = r.clientLeft || a.clientLeft || 0;
            return e = n.top + s - _,
            i = n.left + o - l,
            {
                y: Math.round(e),
                x: Math.round(i),
                width: t.offsetWidth,
                height: t.offsetHeight
            }
        }
        for (; t;) e += parseInt(t.offsetTop, 10),
        i += parseInt(t.offsetLeft, 10),
        t = t.offsetParent;
        return {
            y: e,
            x: i,
            width: t.offsetWidth,
            height: t.offsetHeight
        }
    },
    t._detectScrollSize = function() {
        var t = document.createElement("div");
        t.style.cssText = "visibility:hidden;position:absolute;left:-1000px;width:100px;padding:0px;margin:0px;height:110px;min-height:100px;overflow-y:scroll;",
        document.body.appendChild(t);
        var e = t.offsetWidth - t.clientWidth;
        return document.body.removeChild(t),
        e
    },
    window.dhtmlx && (dhtmlx.attaches || (dhtmlx.attaches = {}), dhtmlx.attaches.attachGantt = function(t, e, i) {
        var n = document.createElement("DIV");
        i = i || window.gantt,
        n.id = "gantt_" + i.uid(),
        n.style.width = "100%",
        n.style.height = "100%",
        n.cmp = "grid",
        document.body.appendChild(n),
        this.attachObject(n.id),
        this.dataType = "gantt",
        this.dataObj = i;
        var a = this.vs[this.av];
        a.grid = i,
        i.init(n.id, t, e),
        n.firstChild.style.border = "none",
        a.gridId = n.id,
        a.gridObj = n;
        var r = "_viewRestore";
        return this.vs[this[r]()].grid
    }),
    "undefined" != typeof window.dhtmlXCellObject && (dhtmlXCellObject.prototype.attachGantt = function(t, e, i) {
        i = i || window.gantt;
        var n = document.createElement("DIV");
        n.id = "gantt_" + i.uid(),
        n.style.width = "100%",
        n.style.height = "100%",
        n.cmp = "grid",
        document.body.appendChild(n),
        this.attachObject(n.id),
        this.dataType = "gantt",
        this.dataObj = i,
        i.init(n.id, t, e),
        n.firstChild.style.border = "none";
        return n = null,
        this.callEvent("_onContentAttach", []),
        this.dataObj
    }),
    t._eventable(t),
    t.config || (t.config = {}),
    t.config || (t.config = {}),
    t.templates || (t.templates = {}),
    function() {
        t.mixin(t.config, {
            links: {
                finish_to_start: "0",
                start_to_start: "1",
                finish_to_finish: "2",
                start_to_finish: "3"
            },
            types: {
                task: "task",
                project: "project",
                milestone: "milestone"
            },
            duration_unit: "day",
            work_time: !1,
            correct_work_time: !1,
            skip_off_time: !1,
            cascade_delete: !0,
            autosize: !1,
            autosize_min_width: 0,
            autoscroll: !0,
            autoscroll_speed: 30,
            show_links: !0,
            show_task_cells: !0,
            static_background: !1,
            branch_loading: !1,
            show_loading: !1,
            show_chart: !0,
            show_grid: !0,
            min_duration: 36e5,
            xml_date: "%d-%m-%Y %H:%i",
            api_date: "%d-%m-%Y %H:%i",
            start_on_monday: !0,
            server_utc: !1,
            show_progress: !0,
            fit_tasks: !1,
            select_task: !0,
            scroll_on_click: !0,
            preserve_scroll: !0,
            readonly: !1,
            date_grid: "%Y-%m-%d",
            drag_links: !0,
            drag_progress: !0,
            drag_resize: !0,
            drag_move: !0,
            drag_mode: {
                resize: "resize",
                progress: "progress",
                move: "move",
                ignore: "ignore"
            },
            round_dnd_dates: !0,
            link_wrapper_width: 20,
            root_id: 0,
            autofit: !1,
            columns: [{
                name: "text",
                tree: !0,
                width: "*",
                resize: !0
            },
            {
                name: "start_date",
                align: "center",
                resize: !0
            },
            {
                name: "duration",
                align: "center"
            },
            {
                name: "add",
                width: "44"
            }],
            step: 1,
            scale_unit: "day",
            scale_offset_minimal: !0,
            subscales: [],
            inherit_scale_class: !1,
            time_step: 60,
            duration_step: 1,
            date_scale: "%d %M",
            task_date: "%d %F %Y",
            time_picker: "%H:%i",
            task_attribute: "task_id",
            link_attribute: "link_id",
            layer_attribute: "data-layer",
            buttons_left: ["gantt_save_btn", "gantt_cancel_btn"],
            _migrate_buttons: {
                dhx_save_btn: "gantt_save_btn",
                dhx_cancel_btn: "gantt_cancel_btn",
                dhx_delete_btn: "gantt_delete_btn"
            },
            buttons_right: ["gantt_delete_btn"],
            lightbox: {
                sections: [{
                    name: "description",
                    height: 70,
                    map_to: "text",
                    type: "textarea",
                    focus: !0
                },
                {
                    name: "time",
                    type: "duration",
                    map_to: "auto"
                }],
                project_sections: [{
                    name: "description",
                    height: 70,
                    map_to: "text",
                    type: "textarea",
                    focus: !0
                },
                {
                    name: "type",
                    type: "typeselect",
                    map_to: "type"
                },
                {
                    name: "time",
                    type: "duration",
                    readonly: !0,
                    map_to: "auto"
                }],
                milestone_sections: [{
                    name: "description",
                    height: 70,
                    map_to: "text",
                    type: "textarea",
                    focus: !0
                },
                {
                    name: "type",
                    type: "typeselect",
                    map_to: "type"
                },
                {
                    name: "time",
                    type: "duration",
                    single_date: !0,
                    map_to: "auto"
                }]
            },
            drag_lightbox: !0,
            sort: !1,
            details_on_create: !0,
            details_on_dblclick: !0,
            initial_scroll: !0,
            task_scroll_offset: 100,
            order_branch: !1,
            order_branch_free: !1,
            task_height: "full",
            min_column_width: 70,
            min_grid_column_width: 70,
            grid_resizer_column_attribute: "column_index",
            grid_resizer_attribute: "grid_resizer",
            keep_grid_width: !1,
            grid_resize: !1,
            show_unscheduled: !0,
            readonly_property: "readonly",
            editable_property: "editable",
            calendar_property: "calendar_id",
            resource_calendars: {},
            type_renderers: {},
            open_tree_initially: !1,
            optimize_render: !0,
            prevent_default_scroll: !1,
            show_errors: !0,
            wai_aria_attributes: !0,
            smart_scales: !0
        }),
        t.keys = {
            edit_save: 13,
            edit_cancel: 27
        },
        t._init_template = function(t, e, i) {
            var n = this._reg_templates || {};
            i = i || t,
            this.config[t] && n[i] != this.config[t] && (e && this.templates[i] || (this.templates[i] = this.date.date_to_str(this.config[t]), n[i] = this.config[t])),
            this._reg_templates = n
        },
        t._init_templates = function() {
            var e = t.locale.labels;
            e.gantt_save_btn = e.icon_save,
            e.gantt_cancel_btn = e.icon_cancel,
            e.gantt_delete_btn = e.icon_delete;
            var i = this.date.date_to_str,
            n = this.config;
            t._init_template("date_scale", !0),
            t._init_template("date_grid", !0, "grid_date_format"),
            t._init_template("task_date", !0),
            t.mixin(this.templates, {
                xml_date: this.date.str_to_date(n.xml_date, n.server_utc),
                xml_format: i(n.xml_date, n.server_utc),
                api_date: this.date.str_to_date(n.api_date),
                progress_text: function(t, e, i) {
                    return ""
                },
                grid_header_class: function(t, e) {
                    return ""
                },
                task_text: function(t, e, i) {
                    return i.text
                },
                task_class: function(t, e, i) {
                    return ""
                },
                grid_row_class: function(t, e, i) {
                    return ""
                },
                task_row_class: function(t, e, i) {
                    return ""
                },
                task_cell_class: function(t, e) {
                    return ""
                },
                scale_cell_class: function(t) {
                    return ""
                },
                scale_row_class: function(t) {
                    return ""
                },
                grid_indent: function(t) {
                    return "<div class='gantt_tree_indent'></div>"
                },
                grid_folder: function(t) {
                    return "<div class='gantt_tree_icon gantt_folder_" + (t.$open ? "open": "closed") + "'></div>"
                },
                grid_file: function(t) {
                    return "<div class='gantt_tree_icon gantt_file'></div>"
                },
                grid_open: function(t) {
                    return "<div class='gantt_tree_icon gantt_" + (t.$open ? "close": "open") + "'></div>"
                },
                grid_blank: function(t) {
                    return "<div class='gantt_tree_icon gantt_blank'></div>"
                },
                date_grid: function(e, i) {
                    return i && t.isUnscheduledTask(i) && t.config.show_unscheduled ? t.templates.task_unscheduled_time(i) : t.templates.grid_date_format(e);
                },
                task_time: function(e, i, n) {
                    return t.isUnscheduledTask(n) && t.config.show_unscheduled ? t.templates.task_unscheduled_time(n) : t.templates.task_date(e) + " - " + t.templates.task_date(i)
                },
                task_unscheduled_time: function(t) {
                    return ""
                },
                time_picker: i(n.time_picker),
                link_class: function(t) {
                    return ""
                },
                link_description: function(e) {
                    var i = t.getTask(e.source),
                    n = t.getTask(e.target);
                    return "<b>" + i.text + "</b> &ndash;  <b>" + n.text + "</b>"
                },
                drag_link: function(e, i, n, a) {
                    e = t.getTask(e);
                    var r = t.locale.labels,
                    s = "<b>" + e.text + "</b> " + (i ? r.link_start: r.link_end) + "<br/>";
                    return n && (n = t.getTask(n), s += "<b> " + n.text + "</b> " + (a ? r.link_start: r.link_end) + "<br/>"),
                    s
                },
                drag_link_class: function(e, i, n, a) {
                    var r = "";
                    if (e && n) {
                        var s = t.isLinkAllowed(e, n, i, a);
                        r = " " + (s ? "gantt_link_allow": "gantt_link_deny")
                    }
                    return "gantt_link_tooltip" + r
                },
                tooltip_date_format: t.date.date_to_str("%Y-%m-%d"),
                tooltip_text: function(e, i, n) {
                    return "<b>Task:</b> " + n.text + "<br/><b>Start date:</b> " + t.templates.tooltip_date_format(e) + "<br/><b>End date:</b> " + t.templates.tooltip_date_format(i)
                }
            }),
            this.callEvent("onTemplatesReady", []);
        }
    } (),
    t._click = {},
    t._dbl_click = {},
    t._context_menu = {},
    t._on_click = function(e) {
        e = e || window.event;
        var i = e.target || e.srcElement,
        n = t.locate(e),
        a = !0;
        if (null !== n ? a = !t.checkEvent("onTaskClick") || t.callEvent("onTaskClick", [n, e]) : t.callEvent("onEmptyClick", [e]), a) {
            var r = t._find_ev_handler(e, i, t._click, n);
            if (!r) return;
            n && t.getTask(n) && t.config.select_task && t.selectTask(n)
        }
    },
    t._on_contextmenu = function(e) {
        e = e || window.event;
        var i = e.target || e.srcElement,
        n = t.locate(i),
        a = t.locate(i, t.config.link_attribute),
        r = !t.checkEvent("onContextMenu") || t.callEvent("onContextMenu", [n, a, e]);
        return r || (e.preventDefault ? e.preventDefault() : e.returnValue = !1),
        r
    },
    t._find_ev_handler = function(e, i, n, a) {
        for (var r = !0; i;) {
            var s = t._getClassName(i);
            if (s) {
                s = s.split(" ");
                for (var o = 0; o < s.length; o++) if (s[o] && n[s[o]]) {
                    var _ = n[s[o]].call(t, e, a, i);
                    r = r && !("undefined" != typeof _ && _ !== !0)
                }
            }
            i = i.parentNode
        }
        return r
    },
    t._on_dblclick = function(e) {
        e = e || window.event;
        var i = e.target || e.srcElement,
        n = t.locate(e),
        a = !t.checkEvent("onTaskDblClick") || t.callEvent("onTaskDblClick", [n, e]);
        if (a) {
            var r = t._find_ev_handler(e, i, t._dbl_click, n);
            if (!r) return;
            null !== n && t.getTask(n) && a && t.config.details_on_dblclick && t.showLightbox(n)
        }
    },
    t._on_mousemove = function(e) {
        if (t.checkEvent("onMouseMove")) {
            var i = t.locate(e);
            t._last_move_event = e,
            t.callEvent("onMouseMove", [i, e])
        }
    },
    t._DnD = function(e, i) {
        this._obj = e,
        i && (this._settings = i),
        t._eventable(this);
        var n = this.getInputMethods();
        this._drag_start_timer = null,
        t.attachEvent("onGanttScroll", t.bind(function(t, e) {
            this.clearDragTimer()
        },
        this));
        for (var a = 0; a < n.length; a++) t.bind(function(n) {
            t.event(e, n.down, t.bind(function(a) {
                i.original_target = {
                    target: a.target || a.srcElement
                },
                t.config.touch ? (this.clearDragTimer(), this._drag_start_timer = setTimeout(t.bind(function() {
                    this.dragStart(e, a, n)
                },
                this), t.config.touch_drag)) : this.dragStart(e, a, n)
            },
            this)),
            t.event(document.body, n.up, t.bind(function(t) {
                this.clearDragTimer()
            },
            this))
        },
        this)(n[a])
    },
    t._DnD.prototype = {
        traceDragEvents: function(e, i) {
            var n = t.bind(function(t) {
                return this.dragMove(e, t, i.accessor)
            },
            this),
            a = (t.bind(function(t) {
                return this.dragScroll(e, t)
            },
            this), t.bind(function(e) {
                return e && e.preventDefault && e.preventDefault(),
                (e || event).cancelBubble = !0,
                t.defined(this.config.updates_per_second) && !t._checkTimeout(this, this.config.updates_per_second) ? !0 : n(e)
            },
            this)),
            r = t.bind(function(n) {
                return t.eventRemove(document.body, i.move, a),
                t.eventRemove(document.body, i.up, r),
                this.dragEnd(e)
            },
            this);
            t.event(document.body, i.move, a),
            t.event(document.body, i.up, r)
        },
        checkPositionChange: function(t) {
            var e = t.x - this.config.pos.x,
            i = t.y - this.config.pos.y,
            n = Math.sqrt(Math.pow(Math.abs(e), 2) + Math.pow(Math.abs(i), 2));
            return n > this.config.sensitivity ? !0 : !1
        },
        initDnDMarker: function() {
            var t = this.config.marker = document.createElement("div");
            t.className = "gantt_drag_marker",
            t.innerHTML = "Dragging object",
            document.body.appendChild(t)
        },
        backupEventTarget: function(e, i) {
            if (t.config.touch) {
                var n = i(e),
                a = n.target || n.srcElement,
                r = a.cloneNode(!0);
                this.config.original_target = {
                    target: r
                },
                this.config.backup_element = a,
                a.parentNode.appendChild(r),
                a.style.display = "none",
                document.body.appendChild(a)
            }
        },
        getInputMethods: function() {
            var e = [];
            if (e.push({
                move: "mousemove",
                down: "mousedown",
                up: "mouseup",
                accessor: function(t) {
                    return t
                }
            }), t.config.touch) {
                var i = !0;
                try {
                    document.createEvent("TouchEvent")
                } catch(n) {
                    i = !1
                }
                i ? e.push({
                    move: "touchmove",
                    down: "touchstart",
                    up: "touchend",
                    accessor: function(t) {
                        return t.touches && t.touches.length > 1 ? null: t.touches[0] ? {
                            target: document.elementFromPoint(t.touches[0].clientX, t.touches[0].clientY),
                            pageX: t.touches[0].pageX,
                            pageY: t.touches[0].pageY,
                            clientX: t.touches[0].clientX,
                            clientY: t.touches[0].clientY
                        }: t
                    }
                }) : window.navigator.pointerEnabled ? e.push({
                    move: "pointermove",
                    down: "pointerdown",
                    up: "pointerup",
                    accessor: function(t) {
                        return "mouse" == t.pointerType ? null: t
                    }
                }) : window.navigator.msPointerEnabled && e.push({
                    move: "MSPointerMove",
                    down: "MSPointerDown",
                    up: "MSPointerUp",
                    accessor: function(t) {
                        return t.pointerType == t.MSPOINTER_TYPE_MOUSE ? null: t
                    }
                })
            }
            return e
        },
        clearDragTimer: function() {
            this._drag_start_timer && (clearTimeout(this._drag_start_timer), this._drag_start_timer = null)
        },
        dragStart: function(e, i, n) {
            this.config = {
                obj: e,
                marker: null,
                started: !1,
                pos: this.getPosition(i),
                sensitivity: 4
            },
            this._settings && t.mixin(this.config, this._settings, !0),
            this.traceDragEvents(e, n),
            t._prevent_touch_scroll = !0,
            document.body.className += " gantt_noselect",
            t.config.touch && this.dragMove(e, i, n.accessor)
        },
        dragMove: function(e, i, n) {
            var a = n(i);
            if (a) {
                if (!this.config.marker && !this.config.started) {
                    var r = this.getPosition(a);
                    if (t.config.touch || this.checkPositionChange(r)) {
                        if (this.config.started = !0, this.config.ignore = !1, this.callEvent("onBeforeDragStart", [e, this.config.original_target]) === !1) return this.config.ignore = !0,
                        !0;
                        this.backupEventTarget(i, n),
                        this.initDnDMarker(),
                        t._touch_feedback(),
                        this.callEvent("onAfterDragStart", [e, this.config.original_target])
                    } else this.config.ignore = !0
                }
                return this.config.ignore ? void 0 : (a.pos = this.getPosition(a), this.config.marker.style.left = a.pos.x + "px", this.config.marker.style.top = a.pos.y + "px", this.callEvent("onDragMove", [e, a]), !1)
            }
        },
        dragEnd: function(e) {
            var i = this.config.backup_element;
            i && i.parentNode && i.parentNode.removeChild(i),
            t._prevent_touch_scroll = !1,
            this.config.marker && (this.config.marker.parentNode.removeChild(this.config.marker), this.config.marker = null, this.callEvent("onDragEnd", [])),
            document.body.className = document.body.className.replace(" gantt_noselect", "")
        },
        getPosition: function(t) {
            var e = 0,
            i = 0;
            return t = t || window.event,
            t.pageX || t.pageY ? (e = t.pageX, i = t.pageY) : (t.clientX || t.clientY) && (e = t.clientX + document.body.scrollLeft + document.documentElement.scrollLeft, i = t.clientY + document.body.scrollTop + document.documentElement.scrollTop),
            {
                x: e,
                y: i
            }
        }
    },
    t._init_grid = function() {
        this._click.gantt_close = this.bind(function(t, e, i) {
            return this.close(e),
            !1
        },
        this),
        this._click.gantt_open = this.bind(function(t, e, i) {
            return this.open(e),
            !1
        },
        this),
        this._click.gantt_row = this.bind(function(t, e, i) {
            if (null !== e) {
                var n = this.getTask(e);
                this.config.scroll_on_click && this.showDate(n.start_date),
                this.callEvent("onTaskRowClick", [e, i])
            }
        },
        this),
        this._click.gantt_grid_head_cell = this.bind(function(t, e, i) {
            var n = i.getAttribute("column_id");
            if (this.callEvent("onGridHeaderClick", [n, t])) {
                if ("add" == n) return void this._click.gantt_add(t, this.config.root_id);
                if (this.config.sort) {
                    for (var a, r = n,
                    s = 0; s < this.config.columns.length; s++) if (this.config.columns[s].name == n) {
                        a = this.config.columns[s];
                        break
                    }
                    if (a && void 0 !== a.sort && a.sort !== !0 && (r = a.sort, !r)) return;
                    var o = this._sort && this._sort.direction && this._sort.name == n ? this._sort.direction: "desc";
                    o = "desc" == o ? "asc": "desc",
                    this._sort = {
                        name: n,
                        direction: o
                    },
                    this.sort(r, "desc" == o)
                }
            }
        },
        this),
        !this.config.sort && this.config.order_branch && this._init_dnd(),
        this._click.gantt_add = this.bind(function(t, e, i) {
            if (!this.config.readonly) {
                var n = {};
                return this.createTask(n, e ? e: this.config.root_id),
                !1
            }
        },
        this),
        this._init_resize && this._init_resize()
    },
    t._render_grid = function() {
        this._calc_grid_width(),
        this._is_grid_visible() && this._render_grid_header()
    },
    t._calc_grid_width = function() {
        for (var t = this.getGridColumns(), e = 0, i = [], n = [], a = 0; a < t.length; a++) {
            var r = parseInt(t[a].width, 10);
            window.isNaN(r) && (r = 50, i.push(a)),
            n[a] = r,
            e += r
        }
        if (this.config.autofit || i.length) {
            var s = this._get_grid_width() - e;
            s / (i.length > 0 ? i.length: n.length > 0 ? n.length: 1);
            if (i.length > 0) for (var o = s / (i.length ? i.length: 1), a = 0; a < i.length; a++) {
                var _ = i[a];
                n[_] += o
            } else for (var o = s / (n.length ? n.length: 1), a = 0; a < n.length; a++) n[a] += o;
            for (var a = 0; a < n.length; a++) t[a].width = n[a]
        } else this.config.grid_width = e
    },
    t._render_grid_header = function() {
        for (var e = this.getGridColumns(), i = [], n = 0, a = this.locale.labels, r = this.config.scale_height - 2, s = 0; s < e.length; s++) {
            var o = s == e.length - 1,
            _ = e[s];
            _.name || (_.name = t.uid() + "");
            var l = 1 * _.width;
            o && this._get_grid_width() > n + l && (_.width = l = this._get_grid_width() - n),
            n += l;
            var d = this._sort && _.name == this._sort.name ? "<div class='gantt_sort gantt_" + this._sort.direction + "'></div>": "",
            c = ["gantt_grid_head_cell", "gantt_grid_head_" + _.name, o ? "gantt_last_cell": "", this.templates.grid_header_class(_.name, _)].join(" "),
            h = "width:" + (l - (o ? 1 : 0)) + "px;",
            u = _.label || a["column_" + _.name];
            u = u || "";
            var g = this._waiAria.gridScaleCellAttrString(_, u),
            f = "<div class='" + c + "' style='" + h + "' " + g + " column_id='" + _.name + "'>" + u + d + "</div>";
            i.push(f)
        }
        this.$grid_scale.style.height = this.config.scale_height - 1 + "px",
        this.$grid_scale.style.lineHeight = r + "px",
        this.$grid_scale.style.width = n - 1 + "px",
        this.$grid_scale.innerHTML = i.join("")
    },
    t._render_grid_item = function(e) {
        if (!t._is_grid_visible()) return null;
        for (var i, n = this.getGridColumns(), a = [], r = 0; r < n.length; r++) {
            var s, o, _, l = r == n.length - 1,
            d = n[r];
            if ("add" == d.name) {
                var c = this._waiAria.gridAddButtonAttrString(d);
                o = "<div " + c + " class='gantt_add'></div>",
                _ = ""
            } else o = d.template ? d.template(e) : e[d.name],
            o instanceof Date && (o = this.templates.date_grid(o, e)),
            _ = o,
            o = "<div class='gantt_tree_content'>" + o + "</div>";
            var h = "gantt_cell" + (l ? " gantt_last_cell": ""),
            u = "";
            if (d.tree) {
                for (var g = 0; g < e.$level; g++) u += this.templates.grid_indent(e);
                i = this._has_children(e.id),
                i ? (u += this.templates.grid_open(e), u += this.templates.grid_folder(e)) : (u += this.templates.grid_blank(e), u += this.templates.grid_file(e));
            }
            var f = "width:" + (d.width - (l ? 1 : 0)) + "px;";
            this.defined(d.align) && (f += "text-align:" + d.align + ";");
            var c = this._waiAria.gridCellAttrString(d, _);
            s = "<div class='" + h + "' style='" + f + "' " + c + ">" + u + o + "</div>",
            a.push(s)
        }
        var h = t.getGlobalTaskIndex(e.id) % 2 === 0 ? "": " odd";
        if (h += e.$transparent ? " gantt_transparent": "", h += e.$dataprocessor_class ? " " + e.$dataprocessor_class: "", this.templates.grid_row_class) {
            var p = this.templates.grid_row_class.call(this, e.start_date, e.end_date, e);
            p && (h += " " + p)
        }
        this.getState().selected_task == e.id && (h += " gantt_selected");
        var v = document.createElement("div");
        return v.className = "gantt_row" + h,
        v.style.height = this.config.row_height + "px",
        v.style.lineHeight = t.config.row_height + "px",
        v.setAttribute(this.config.task_attribute, e.id),
        this._waiAria.taskRowAttr(e, v),
        v.innerHTML = a.join(""),
        v
    },
    t.open = function(e) {
        t._set_item_state(e, !0),
        this.callEvent("onTaskOpened", [e])
    },
    t.close = function(e) {
        t._set_item_state(e, !1),
        this.callEvent("onTaskClosed", [e])
    },
    t._set_item_state = function(e, i) {
        e && this._pull[e] && (this._pull[e].$open = i, t._refresh_on_toggle_element(e));
    },
    t._refresh_on_toggle_element = function(t) {
        this.refreshData()
    },
    t._is_grid_visible = function() {
        return this.config.grid_width && this.config.show_grid
    },
    t._get_grid_width = function() {
        return this._is_grid_visible() ? this._is_chart_visible() ? this.config.grid_width: this._x: 0
    },
    t.moveTask = function(e, i, n) {
        var a = arguments[3];
        if (a) {
            if (a === e) return;
            n = this.getParent(a),
            i = this.getTaskIndex(a)
        }
        if (e != n) {
            n = n || this.config.root_id;
            var r = this.getTask(e),
            s = this.getParent(r.id),
            o = (this.getChildren(this.getParent(r.id)), this.getChildren(n));
            if ( - 1 == i && (i = o.length + 1), s == n) {
                var _ = this.getTaskIndex(e);
                if (_ == i) return
            }
            if (this.callEvent("onBeforeTaskMove", [e, n, i]) !== !1) {
                this._replace_branch_child(s, e),
                o = this.getChildren(n);
                var l = o[i];
                l ? o = o.slice(0, i).concat([e]).concat(o.slice(i)) : o.push(e),
                this.setParent(r, n),
                this._branches[n] = o;
                var d = this.calculateTaskLevel(r) - r.$level;
                r.$level += d;
                for (var c = this._getTaskTree(e), h = 0; h < c.length; h++) {
                    var u = this._pull[c[h]];
                    u.$level += d
                }
                1 * i > 0 ? a ? r.$drop_target = (this.getTaskIndex(e) > this.getTaskIndex(a) ? "next:": "") + a: r.$drop_target = "next:" + t.getPrevSibling(e) : o[1 * i + 1] ? r.$drop_target = o[1 * i + 1] : r.$drop_target = n,
                this.callEvent("onAfterTaskMove", [e, n, i]) !== !1 && this.refreshData()
            }
        }
    },
    t._init_dnd = function() {
        var e = new t._DnD(this.$grid_data, {
            updates_per_second: 60
        });
        this.defined(this.config.dnd_sensitivity) && (e.config.sensitivity = this.config.dnd_sensitivity),
        e.attachEvent("onBeforeDragStart", this.bind(function(i, n) {
            var a = this._locateHTML(n);
            if (!a) return ! 1;
            this.hideQuickInfo && this._hideQuickInfo();
            var r = this.locate(n),
            s = t.getTask(r);
            return t._is_readonly(s) ? !1 : (e.config.initial_open_state = s.$open, this.callEvent("onRowDragStart", [r, n.target || n.srcElement, n]) ? void 0 : !1);
        },
        this)),
        e.attachEvent("onAfterDragStart", this.bind(function(t, i) {
            var n = this._locateHTML(i);
            e.config.marker.innerHTML = n.outerHTML,
            e.config.id = this.locate(i);
            var a = this.getTask(e.config.id);
            e.config.index = this.getTaskIndex(e.config.id),
            e.config.parent = a.parent,
            a.$open = !1,
            a.$transparent = !0,
            this.refreshData()
        },
        this)),
        e.lastTaskOfLevel = function(e) {
            for (var i = t._order,
            n = t._pull,
            a = null,
            r = 0,
            s = i.length; s > r; r++) n[i[r]].$level == e && (a = n[i[r]]);
            return a ? a.id: null
        },
        e._getGridPos = this.bind(function(e) {
            var i = this._get_position(this.$grid_data),
            n = i.x,
            a = e.pos.y - 10;
            a < i.y && (a = i.y);
            var r = t.getTaskCount() * t.config.row_height;
            return a > i.y + r - this.config.row_height && (a = i.y + r - this.config.row_height),
            i.x = n,
            i.y = a,
            i
        },
        this),
        e._getTargetY = this.bind(function(e) {
            var i = this._get_position(this.$grid_data),
            n = e.pageY - i.y + t.getScrollState().y;
            return 0 > n && (n = 0),
            n
        },
        this),
        e._getTaskByY = this.bind(function(e, i) {
            e = e || 0,
            t.config.smart_rendering && (e += this.$grid_data.scrollTop);
            var n = Math.floor(e / this.config.row_height);
            return n = n > i ? n - 1 : n,
            n > this._order.length - 1 ? null: this._order[n]
        },
        this),
        e.attachEvent("onDragMove", this.bind(function(i, n) {
            function a(e, i) {
                return ! t.isChildOf(d.id, i.id) && (e.$level == i.$level || t.config.order_branch_free)
            }
            var r = e.config,
            s = e._getGridPos(n);
            r.marker.style.left = s.x + 10 + "px",
            r.marker.style.top = s.y + "px";
            var o = this.getTask(e.config.id),
            _ = e._getTargetY(n),
            l = e._getTaskByY(_, t.getGlobalTaskIndex(o.id));
            if (this.isTaskExists(l) || (l = e.lastTaskOfLevel(t.config.order_branch_free ? o.$level: 0), l == e.config.id && (l = null)), this.isTaskExists(l)) {
                var d = this.getTask(l);
                if (t.getGlobalTaskIndex(d.id) * this.config.row_height + this.config.row_height / 2 < _) {
                    var c = this.getGlobalTaskIndex(d.id),
                    h = this._pull[this._order[c + 1]];
                    if (h) {
                        if (h.id == o.id) return this.config.order_branch_free && this.isChildOf(o.id, d.id) && 1 == this.getChildren(d.id).length ? void this.moveTask(o.id, this.getTaskIndex(d.id) + 1, this.getParent(d.id)) : void 0;
                        d = h
                    } else if (h = this._pull[this._order[c]], a(h, o) && h.id != o.id) return void this.moveTask(o.id, -1, this.getParent(h.id))
                } else if (this.config.order_branch_free && d.id != o.id && a(d, o)) {
                    if (!this.hasChild(d.id)) return d.$open = !0,
                    void this.moveTask(o.id, -1, d.id);
                    if (this.getGlobalTaskIndex(d.id) || this.config.row_height / 3 < _) return
                }
                for (var c = this.getGlobalTaskIndex(d.id), u = this._pull[this._order[c - 1]], g = 1; (!u || u.id == d.id) && c - g >= 0;) u = this._pull[this._order[c - g]],
                g++;
                if (o.id == d.id) return;
                a(d, o) && o.id != d.id ? this.moveTask(o.id, 0, 0, d.id) : d.$level != o.$level - 1 || t.getChildren(d.id).length ? u && a(u, o) && o.id != u.id && this.moveTask(o.id, -1, this.getParent(u.id)) : this.moveTask(o.id, 0, d.id)
            }
            return ! 0
        },
        this)),
        e.attachEvent("onDragEnd", this.bind(function() {
            var t = this.getTask(e.config.id);
            t.$transparent = !1,
            t.$open = e.config.initial_open_state,
            this.callEvent("onBeforeRowDragEnd", [e.config.id, e.config.parent, e.config.index]) === !1 ? (this.moveTask(e.config.id, e.config.index, e.config.parent), t.$drop_target = null) : this.callEvent("onRowDragEnd", [e.config.id, t.$drop_target]),
            this.refreshData()
        },
        this))
    },
    t.getGridColumns = function() {
        return this.config.columns
    },
    t._has_children = function(t) {
        return this.getChildren(t).length > 0
    },
    t._render_grid_header_resize = function() {
        for (var t = this.getGridColumns(), e = 0, i = this.config.scale_height, n = 0; n < t.length; n++) {
            var a = (n == t.length - 1, t[n]);
            if (e += a.width, a.resize) {
                var r = document.createElement("div");
                r.className = "gantt_grid_column_resize_wrap",
                r.style.top = "0px",
                r.style.height = i + "px",
                r.innerHTML = "<div class='gantt_grid_column_resize'></div>",
                r.setAttribute(this.config.grid_resizer_column_attribute, n),
                this._waiAria.gridSeparatorAttr(r),
                this.$grid_scale.appendChild(r),
                r.style.left = Math.max(0, e) + "px"
            }
        }
        if (this.config.grid_resize) {
            var s = document.createElement("div");
            s.className = "gantt_grid_resize_wrap",
            s.style.top = "0px",
            s.style.height = this.$grid.offsetHeight + "px",
            s.innerHTML = "<div class='gantt_grid_resize'></div>",
            s.setAttribute(this.config.grid_resizer_attribute, "true"),
            this._waiAria.gridSeparatorAttr(s),
            this.$grid_scale.appendChild(s),
            s.style.left = Math.max(0, this.config.grid_width - 1) + "px"
        }
    },
    t._grid_resize = {
        column_before_start: t.bind(function(e, i, n) {
            var a = this._locateHTML(n, this.config.grid_resizer_column_attribute);
            if (!a) return ! 1;
            var r = this.locate(n, this.config.grid_resizer_column_attribute),
            s = this.getGridColumns()[r];
            return t.callEvent("onColumnResizeStart", [r, s]) === !1 ? !1 : void 0
        },
        t),
        column_after_start: t.bind(function(t, e, i) {
            var n = this.locate(i, this.config.grid_resizer_column_attribute);
            t.config.marker.innerHTML = "",
            t.config.marker.className += " gantt_grid_resize_area",
            t.config.marker.style.height = this.$grid.offsetHeight + "px",
            t.config.marker.style.top = "0px",
            t.config.drag_index = n
        },
        t),
        column_drag_move: t.bind(function(e, i, n) {
            var a = e.config,
            r = this.getGridColumns(),
            s = this._get_position(this.$grid_scale),
            o = parseInt(a.marker.style.left, 10),
            _ = this.config.min_grid_column_width,
            l = this.$grid_data.offsetWidth - this.config.min_grid_column_width * (r.length - a.drag_index - 2),
            d = 0,
            c = 0;
            o -= s.x - 1;
            for (var h = 0; h < a.drag_index; h++) _ += r[h].width,
            d += r[h].width;
            return _ > o && (o = _),
            this.config.keep_grid_width && o > l && (o = l),
            c = o - d,
            a.marker.style.top = s.y + "px",
            a.marker.style.left = s.x - 1 + d + "px",
            a.marker.style.width = c + "px",
            a.left = o - 1,
            t.callEvent("onColumnResize", [a.drag_index, r[a.drag_index], c - 1]),
            !0
        },
        t),
        column_drag_end: t.bind(function(e, i, n) {
            for (var a = this.getGridColumns(), r = 0, s = parseInt(e.config.drag_index, 10), o = a[s], _ = 0; s > _; _++) r += a[_].width;
            var l = o.min_width && e.config.left - r < o.min_width ? o.min_width: e.config.left - r;
            if (t.callEvent("onColumnResizeEnd", [s, o, l]) !== !1 && o.width != l) {
                if (o.width = l, this.config.keep_grid_width) for (var _ = s + 1,
                d = a.length; d > _; _++)"add" == a[_].name ? a[_].width = 1 * a[_].width || 44 : a[_].width = null;
                else {
                    for (var _ = s,
                    d = a.length; d > _; _++) r += a[_].width;
                    this.config.grid_width = r
                }
                this.render()
            }
        },
        t),
        container_before_start: t.bind(function(e, i, n) {
            var a = this._locateHTML(n, this.config.grid_resizer_attribute);
            return a ? t.callEvent("onGridResizeStart", [t.config.grid_width]) === !1 ? !1 : void 0 : !1
        },
        t),
        contianer_after_start: t.bind(function(t, e, i) {
            t.config.marker.innerHTML = "",
            t.config.marker.className += " gantt_grid_resize_area",
            t.config.marker.style.height = this.$grid.offsetHeight + "px",
            t.config.marker.style.top = "0px"
        },
        t),
        container_drag_move: t.bind(function(e, i, n) {
            for (var a = e.config,
            r = this.config.columns,
            s = this._get_position(this.$grid), o = parseInt(a.marker.style.left, 10), _ = 0, l = 0; l < r.length; l++) r[l].hide || (_ += r[l].min_width ? r[l].min_width: this.config.min_grid_column_width);
            return o -= s.x - 1,
            _ > o && (o = _),
            a.marker.style.top = s.y + "px",
            a.marker.style.left = s.x - 1 + "px",
            a.marker.style.width = o + "px",
            a.left = o - 1,
            t.callEvent("onGridResize", [this.config.grid_width, o - 1]),
            !0
        },
        t),
        container_drag_end: t.bind(function(e, i, n) {
            for (var a = this.config.columns,
            r = 0,
            s = e.config.left,
            o = 0,
            _ = a.length; _ > o; o++) a[o].hide || (r += a[o].width);
            for (var l = a.length,
            d = s - r,
            c = 0,
            o = 0; l > o; o++) if (!a[o].hide) {
                var h = Math.floor(d * (a[o].width / r));
                r -= a[o].width,
                c = a[o].width + h,
                (a[o].min_width && c >= a[o].min_width || !a[o].min_width || c > a[o].width) && (d -= h, a[o].width = c)
            }
            for (var u = d > 0 ? 1 : -1; d > 0 && 1 === u || 0 > d && -1 === u;) {
                var g = d;
                for (o = 0; l > o; o++) {
                    var f = a[o].width + u;
                    if ((a[o].min_width && f >= a[o].min_width || !a[o].min_width || c > a[o].width) && (d -= u, a[o].width = f, !d)) break
                }
                if (g == d) break
            }
            t.callEvent("onGridResizeEnd", [this.config.grid_width, s]) !== !1 && (this.config.grid_width = s, this.render())
        },
        t)
    },
    t._init_resize = function() {
        var e = new t._DnD(this.$grid_scale, {
            updates_per_second: 60
        });
        t.defined(this.config.dnd_sensitivity) && (e.config.sensitivity = this.config.dnd_sensitivity),
        e.attachEvent("onBeforeDragStart",
        function(i, n) {
            return t._grid_resize.column_before_start(e, i, n);
        }),
        e.attachEvent("onAfterDragStart",
        function(i, n) {
            return t._grid_resize.column_after_start(e, i, n)
        }),
        e.attachEvent("onDragMove",
        function(i, n) {
            return t._grid_resize.column_drag_move(e, i, n)
        }),
        e.attachEvent("onDragEnd",
        function(i, n) {
            return t._grid_resize.column_drag_end(e, i, n)
        });
        var i = new t._DnD(this.$grid, {
            updates_per_second: 60
        });
        t.defined(this.config.dnd_sensitivity) && (i.config.sensitivity = this.config.dnd_sensitivity),
        i.attachEvent("onBeforeDragStart",
        function(e, n) {
            return t._grid_resize.container_before_start(i, e, n);
        }),
        i.attachEvent("onAfterDragStart",
        function(e, n) {
            return t._grid_resize.contianer_after_start(i, e, n)
        }),
        i.attachEvent("onDragMove",
        function(e, n) {
            return t._grid_resize.container_drag_move(i, e, n)
        }),
        i.attachEvent("onDragEnd",
        function(e, n) {
            return t._grid_resize.container_drag_end(i, e, n)
        })
    },
    t.attachEvent("onGanttRender",
    function() {
        t._render_grid_header_resize()
    }),
    function() {
        function e(t) {
            _ && clearInterval(_);
            var e = {
                x: t.clientX,
                y: t.clientY
            };
            _ = setInterval(function() {
                i(e)
            },
            o)
        }
        function i(e) {
            if (!t.getState().drag_mode && !document.querySelector(".gantt_drag_marker")) return clearInterval(_),
            void(l = null);
            var i = t._get_position(t.$task),
            o = e.x - i.x,
            d = e.y - i.y,
            c = n(o, i.width, l ? l.x: 0, r),
            h = n(d, i.height, l ? l.y: 0, r); ! h && !c || l || (l = {
                x: o,
                y: d
            },
            c = 0, h = 0),
            c *= t.config.scroll_speed || s,
            h *= t.config.scroll_speed || s,
            c && h && (Math.abs(c / 5) > Math.abs(h) ? h = 0 : Math.abs(h / 5) > Math.abs(c) && (c = 0)),
            c || h ? (l.started = !0, a(c, h)) : clearInterval(_)
        }
        function n(t, e, i, n) {
            return n > t && (!l || l.started || i > t) ? -1 : n > e - t && (!l || l.started || t > i) ? 1 : 0
        }
        function a(e, i) {
            var n = t.getScrollState(),
            a = null,
            r = null;
            e && (a = n.x + e),
            i && (r = n.y + i),
            t.scrollTo(a, r);
        }
        var r = 50,
        s = 30,
        o = 50,
        _ = null,
        l = null;
        t.attachEvent("onGanttReady",
        function() {
            t.eventRemove(document.body, "mousemove", e),
            t.event(document.body, "mousemove", e)
        })
    } (),
    t._wbs = {
        _needRecalc: !0,
        reset: function() {
            this._needRecalc = !0
        },
        _isRecalcNeeded: function() {
            return ! this._isGroupSort() && this._needRecalc
        },
        _isGroupSort: function() {
            return ! (!t._groups || !t._groups.is_active())
        },
        _getWBSCode: function(t) {
            return t ? (this._isRecalcNeeded() && this._calcWBS(), t.$virtual ? "": this._isGroupSort() ? t.$wbs || "": (t.$wbs || (this.reset(), this._calcWBS()), t.$wbs)) : ""
        },
        _setWBSCode: function(t, e) {
            t.$wbs = e
        },
        getWBSCode: function(t) {
            return this._getWBSCode(t)
        },
        _calcWBS: function() {
            if (this._isRecalcNeeded()) {
                var e = !0;
                t.eachTask(function(i) {
                    if (e) return e = !1,
                    void this._setWBSCode(i, "1");
                    var n = t.getPrevSibling(i.id);
                    if (null !== n) {
                        var a = t.getTask(n).$wbs;
                        a && (a = a.split("."), a[a.length - 1]++, this._setWBSCode(i, a.join(".")))
                    } else {
                        var r = t.getParent(i.id);
                        this._setWBSCode(i, t.getTask(r).$wbs + ".1")
                    }
                },
                t.config.root_id, this),
                this._needRecalc = !1
            }
        }
    },
    t.getWBSCode = function(e) {
        return t._wbs.getWBSCode(e)
    },
    t.attachEvent("onAfterTaskMove",
    function() {
        return t._wbs.reset(),
        !0
    }),
    t.attachEvent("onBeforeParse",
    function() {
        return t._wbs.reset(),
        !0
    }),
    t.attachEvent("onAfterTaskDelete",
    function() {
        return t._wbs.reset(),
        !0
    }),
    t.attachEvent("onAfterTaskAdd",
    function() {
        return t._wbs.reset(),
        !0
    }),
    function() {
        var e = t._has_children;
        t._has_children = function(t) {
            return e.apply(this, arguments) ? !0 : this.isTaskExists(t) ? this.getTask(t).$has_child: !1
        }
    } (),
    t._need_dynamic_loading = function(e) {
        if (t.config.branch_loading && t._load_url) {
            var i = t.getUserData(e, "was_rendered");
            if (!i && t._has_children(e) && !t.hasChild(e)) return ! 0
        }
        return ! 1
    },
    t._refresh_on_toggle_element = function(e) {
        t._need_dynamic_loading(e) && t.getTask(e).$open || this.refreshData()
    },
    t.attachEvent("onTaskOpened",
    function(e) {
        if (t.config.branch_loading && t._load_url && t._need_dynamic_loading(e)) {
            var i = t._load_url;
            i = i.replace(/(\?|&)?parent_id=.+&?/, "");
            var n = i.indexOf("?") >= 0 ? "&": "?",
            a = 0;
            this._cached_scroll_pos && this._cached_scroll_pos.y && (a = Math.max(this._cached_scroll_pos.y, 0)),
            t.load(i + n + "parent_id=" + encodeURIComponent(e), this._load_type,
            function() {
                a && t.scrollTo(null, a)
            }),
            t.setUserData(e, "was_rendered", !0)
        }
    }),
    t.getGridColumns = function() {
        for (var e = t.config.columns,
        i = [], n = 0; n < e.length; n++) e[n].hide || i.push(e[n]);
        return i
    },
    t.getGridColumn = function(e) {
        for (var i = t.config.columns,
        n = 0; n < i.length; n++) if (i[n].name == e) return i[n];
        return null
    },
    function() {
        function e(t) {
            return (t + "").replace(a, " ").replace(r, " ")
        }
        function i(t) {
            return (t + "").replace(s, "&#39;")
        }
        function n() {
            return ! t.config.wai_aria_attributes;
        }
        var a = new RegExp("<(?:.|\n)*?>", "gm"),
        r = new RegExp(" +", "gm"),
        s = new RegExp("'", "gm");
        t._waiAria = {
            getAttributeString: function(t) {
                var n = [" "];
                for (var a in t) {
                    var r = i(e(t[a]));
                    n.push(a + "='" + r + "'")
                }
                return n.push(" "),
                n.join(" ")
            },
            getTimelineCellAttr: function(e) {
                return t._waiAria.getAttributeString({
                    "aria-label": e
                })
            },
            _taskCommonAttr: function(i, n) {
                n.setAttribute("aria-label", e(t.templates.tooltip_text(i.start_date, i.end_date, i))),
                t._is_readonly(i) && n.setAttribute("aria-readonly", !0),
                i.$dataprocessor_class && n.setAttribute("aria-busy", !0),
                n.setAttribute("aria-selected", t.getState().selected_task == i.id || t.isSelectedTask && t.isSelectedTask(i.id) ? "true": "false")
            },
            setTaskBarAttr: function(e, i) {
                this._taskCommonAttr(e, i),
                !t._is_readonly(e) && t.config.drag_move && (e.id != t.getState().drag_id ? i.setAttribute("aria-grabbed", !1) : i.setAttribute("aria-grabbed", !0))
            },
            taskRowAttr: function(e, i) {
                this._taskCommonAttr(e, i),
                !t._is_readonly(e) && t.config.order_branch && i.setAttribute("aria-grabbed", !1),
                i.setAttribute("role", "row"),
                i.setAttribute("aria-level", e.$level),
                t._has_children(e.id) && i.setAttribute("aria-expanded", e.$open ? "true": "false")
            },
            linkAttr: function(i, n) {
                var a = t.config.links,
                r = i.type == a.finish_to_start || i.type == a.start_to_start,
                s = i.type == a.start_to_start || i.type == a.start_to_finish,
                o = t.locale.labels.link + " " + t.templates.drag_link(i.source, s, i.target, r);
                n.setAttribute("aria-label", e(o)),
                t._is_readonly(i) && n.setAttribute("aria-readonly", !0)
            },
            gridSeparatorAttr: function(t) {
                t.setAttribute("role", "separator")
            },
            lightboxHiddenAttr: function(t) {
                t.setAttribute("aria-hidden", "true");
            },
            lightboxVisibleAttr: function(t) {
                t.setAttribute("aria-hidden", "false")
            },
            lightboxAttr: function(t) {
                t.setAttribute("role", "dialog"),
                t.setAttribute("aria-hidden", "true"),
                t.firstChild.setAttribute("role", "heading")
            },
            lightboxButtonAttrString: function(e) {
                return this.getAttributeString({
                    role: "button",
                    "aria-label": t.locale.labels[e],
                    tabindex: "0"
                })
            },
            lightboxHeader: function(t, e) {
                t.setAttribute("aria-label", e)
            },
            lightboxSelectAttrString: function(e) {
                var i = "";
                switch (e) {
                case "%Y":
                    i = t.locale.labels.years;
                    break;
                case "%m":
                    i = t.locale.labels.months;
                    break;
                case "%d":
                    i = t.locale.labels.days;
                    break;
                case "%H:%i":
                    i = t.locale.labels.hours + t.locale.labels.minutes
                }
                return t._waiAria.getAttributeString({
                    "aria-label": i
                })
            },
            lightboxDurationInputAttrString: function(e) {
                return this.getAttributeString({
                    "aria-label": t.locale.labels.column_duration,
                    "aria-valuemin": "0"
                })
            },
            gridAttrString: function() {
                return [" role='treegrid'", t.config.multiselect ? "aria-multiselectable='true'": "aria-multiselectable='false'", " "].join(" ")
            },
            gridScaleRowAttrString: function() {
                return "role='row'"
            },
            gridScaleCellAttrString: function(e, i) {
                var n = "";
                if ("add" == e.name) n = this.getAttributeString({
                    role: "button",
                    "aria-label": t.locale.labels.new_task
                });
                else {
                    var a = {
                        role: "columnheader",
                        "aria-label": i
                    };
                    t._sort && t._sort.name == e.name && ("asc" == t._sort.direction ? a["aria-sort"] = "ascending": a["aria-sort"] = "descending"),
                    n = this.getAttributeString(a)
                }
                return n
            },
            gridDataAttrString: function() {
                return "role='rowgroup'"
            },
            gridCellAttrString: function(t, e) {
                return this.getAttributeString({
                    role: "gridcell",
                    "aria-label": e
                })
            },
            gridAddButtonAttrString: function(e) {
                return this.getAttributeString({
                    role: "button",
                    "aria-label": t.locale.labels.new_task
                })
            },
            messageButtonAttrString: function(t) {
                return "tabindex='0' role='button' aria-label='" + t + "'"
            },
            messageInfoAttr: function(t) {
                t.setAttribute("role", "alert")
            },
            messageModalAttr: function(t, e) {
                t.setAttribute("role", "dialog"),
                e && t.setAttribute("aria-labelledby", e)
            },
            quickInfoAttr: function(t) {
                t.setAttribute("role", "dialog")
            },
            quickInfoHeaderAttrString: function() {
                return " role='heading' ";
            },
            quickInfoHeader: function(t, e) {
                t.setAttribute("aria-label", e)
            },
            quickInfoButtonAttrString: function(e) {
                return t._waiAria.getAttributeString({
                    role: "button",
                    "aria-label": e,
                    tabindex: "0"
                })
            },
            tooltipAttr: function(t) {
                t.setAttribute("role", "tooltip")
            },
            tooltipVisibleAttr: function(t) {
                t.setAttribute("aria-hidden", "false")
            },
            tooltipHiddenAttr: function(t) {
                t.setAttribute("aria-hidden", "true")
            }
        };
        for (var o in t._waiAria) t._waiAria[o] = function(t) {
            return function() {
                return n() ? "": t.apply(this, arguments)
            }
        } (t._waiAria[o]);
    } (),
    t._scale_helpers = {
        getSum: function(t, e, i) {
            void 0 === i && (i = t.length - 1),
            void 0 === e && (e = 0);
            for (var n = 0,
            a = e; i >= a; a++) n += t[a];
            return n
        },
        setSumWidth: function(t, e, i, n) {
            var a = e.width;
            void 0 === n && (n = a.length - 1),
            void 0 === i && (i = 0);
            var r = n - i + 1;
            if (! (i > a.length - 1 || 0 >= r || n > a.length - 1)) {
                var s = this.getSum(a, i, n),
                o = t - s;
                this.adjustSize(o, a, i, n),
                this.adjustSize( - o, a, n + 1),
                e.full_width = this.getSum(a)
            }
        },
        splitSize: function(t, e) {
            for (var i = [], n = 0; e > n; n++) i[n] = 0;
            return this.adjustSize(t, i),
            i
        },
        adjustSize: function(t, e, i, n) {
            i || (i = 0),
            void 0 === n && (n = e.length - 1);
            for (var a = n - i + 1,
            r = this.getSum(e, i, n), s = 0, o = i; n >= o; o++) {
                var _ = Math.floor(t * (r ? e[o] / r: 1 / a));
                r -= e[o],
                t -= _,
                a--,
                e[o] += _,
                s += _
            }
            e[e.length - 1] += t
        },
        sortScales: function(e) {
            function i(e, i) {
                var n = new Date(1970, 0, 1);
                return t.date.add(n, i, e) - n
            }
            e.sort(function(t, e) {
                return i(t.unit, t.step) < i(e.unit, e.step) ? 1 : i(t.unit, t.step) > i(e.unit, e.step) ? -1 : 0
            });
            for (var n = 0; n < e.length; n++) e[n].index = n
        },
        primaryScale: function() {
            return t._init_template("date_scale"),
            {
                unit: t.config.scale_unit,
                step: t.config.step,
                template: t.templates.date_scale,
                date: t.config.date_scale,
                css: t.templates.scale_cell_class
            }
        },
        prepareConfigs: function(t, e, i, n) {
            for (var a = this.splitSize(n, t.length), r = i, s = [], o = t.length - 1; o >= 0; o--) {
                var _ = o == t.length - 1,
                l = this.initScaleConfig(t[o]);
                _ && this.processIgnores(l),
                this.initColSizes(l, e, r, a[o]),
                this.limitVisibleRange(l),
                _ && (r = l.full_width),
                s.unshift(l)
            }
            for (var o = 0; o < s.length - 1; o++) this.alineScaleColumns(s[s.length - 1], s[o]);
            for (var o = 0; o < s.length; o++) this.setPosSettings(s[o]);
            return s
        },
        setPosSettings: function(t) {
            for (var e = 0,
            i = t.trace_x.length; i > e; e++) t.left.push((t.width[e - 1] || 0) + (t.left[e - 1] || 0))
        },
        _ignore_time_config: function(e, i) {
            if (this.config.skip_off_time) {
                for (var n = !0,
                a = e,
                r = 0; r < i.step; r++) r && (a = t.date.add(e, r, i.unit)),
                n = n && !this.isWorkTime(a, i.unit);
                return n
            }
            return ! 1
        },
        processIgnores: function(t) {
            t.ignore_x = {},
            t.display_count = t.count
        },
        initColSizes: function(e, i, n, a) {
            var r = n;
            e.height = a;
            var s = void 0 === e.display_count ? e.count: e.display_count;
            s || (s = 1),
            e.col_width = Math.floor(r / s),
            i && e.col_width < i && (e.col_width = i, r = e.col_width * s),
            e.width = [];
            for (var o = e.ignore_x || {},
            _ = 0; _ < e.trace_x.length; _++) if (o[e.trace_x[_].valueOf()] || e.display_count == e.count) e.width[_] = 0;
            else {
                var l = 1;
                if ("month" == e.unit) {
                    var d = Math.round((t.date.add(e.trace_x[_], e.step, e.unit) - e.trace_x[_]) / 864e5);
                    l = d
                }
                e.width[_] = l
            }
            this.adjustSize(r - this.getSum(e.width), e.width),
            e.full_width = this.getSum(e.width)
        },
        initScaleConfig: function(e) {
            var i = t.mixin({
                count: 0,
                col_width: 0,
                full_width: 0,
                height: 0,
                width: [],
                left: [],
                trace_x: [],
                trace_indexes: {}
            },
            e);
            return this.eachColumn(e.unit, e.step,
            function(t) {
                i.count++,
                i.trace_x.push(new Date(t)),
                i.trace_indexes[t.valueOf()] = i.trace_x.length - 1
            }),
            i
        },
        iterateScales: function(t, e, i, n, a) {
            for (var r = e.trace_x,
            s = t.trace_x,
            o = i || 0,
            _ = n || s.length - 1,
            l = 0,
            d = 1; d < r.length; d++) {
                var c = t.trace_indexes[ + r[d]];
                void 0 !== c && _ >= c && (a && a.apply(this, [l, d, o, c]), o = c, l = d)
            }
        },
        alineScaleColumns: function(t, e, i, n) {
            this.iterateScales(t, e, i, n,
            function(i, n, a, r) {
                var s = this.getSum(t.width, a, r - 1),
                o = this.getSum(e.width, i, n - 1);
                o != s && this.setSumWidth(s, e, i, n - 1)
            })
        },
        eachColumn: function(e, i, n) {
            var a = new Date(t._min_date),
            r = new Date(t._max_date);
            t.date[e + "_start"] && (a = t.date[e + "_start"](a));
            var s = new Date(a);
            for ( + s >= +r && (r = t.date.add(s, i, e)); + r > +s;) {
                n.call(this, new Date(s));
                var o = s.getTimezoneOffset();
                s = t.date.add(s, i, e),
                s = t._correct_dst_change(s, o, i, e),
                t.date[e + "_start"] && (s = t.date[e + "_start"](s))
            }
        },
        limitVisibleRange: function(e) {
            var i = e.trace_x,
            n = 0,
            a = e.width.length - 1,
            r = 0;
            if ( + i[0] < +t._min_date && n != a) {
                var s = Math.floor(e.width[0] * ((i[1] - t._min_date) / (i[1] - i[0])));
                r += e.width[0] - s,
                e.width[0] = s,
                i[0] = new Date(t._min_date)
            }
            var o = i.length - 1,
            _ = i[o],
            l = t.date.add(_, e.step, e.unit);
            if ( + l > +t._max_date && o > 0) {
                var s = e.width[o] - Math.floor(e.width[o] * ((l - t._max_date) / (l - _)));
                r += e.width[o] - s,
                e.width[o] = s
            }
            if (r) {
                for (var d = this.getSum(e.width), c = 0, h = 0; h < e.width.length; h++) {
                    var u = Math.floor(r * (e.width[h] / d));
                    e.width[h] += u,
                    c += u
                }
                this.adjustSize(r - c, e.width)
            }
        }
    },
    t._scale_helpers.processIgnores = function(e) {
        var i = e.count;
        if (e.ignore_x = {},
        t.ignore_time || t.config.skip_off_time) {
            var n = t.ignore_time ||
            function() {
                return ! 1
            };
            i = 0;
            for (var a = 0; a < e.trace_x.length; a++) n.call(t, e.trace_x[a]) || this._ignore_time_config.call(t, e.trace_x[a], e) ? (e.ignore_x[e.trace_x[a].valueOf()] = !0, e.ignored_colls = !0) : i++
        }
        e.display_count = i
    },
    t._tasks_dnd = {
        drag: null,
        _events: {
            before_start: {},
            before_finish: {},
            after_finish: {}
        },
        _handlers: {},
        init: function() {
            this.clear_drag_state();
            var e = t.config.drag_mode;
            this.set_actions();
            var i = {
                before_start: "onBeforeTaskDrag",
                before_finish: "onBeforeTaskChanged",
                after_finish: "onAfterTaskDrag"
            };
            for (var n in this._events) for (var a in e) this._events[n][a] = i[n];
            this._handlers[e.move] = this._move,
            this._handlers[e.resize] = this._resize,
            this._handlers[e.progress] = this._resize_progress;
        },
        set_actions: function() {
            var e = t.$task_data;
            t.event(e, "mousemove", t.bind(function(t) {
                this.on_mouse_move(t || event)
            },
            this)),
            t.event(e, "mousedown", t.bind(function(t) {
                this.on_mouse_down(t || event)
            },
            this)),
            t.event(e, "mouseup", t.bind(function(t) {
                this.on_mouse_up(t || event)
            },
            this))
        },
        clear_drag_state: function() {
            this.drag = {
                id: null,
                mode: null,
                pos: null,
                start_x: null,
                start_y: null,
                obj: null,
                left: null
            }
        },
        _resize: function(e, i, n) {
            var a = t.config,
            r = this._drag_task_coords(e, n);
            n.left ? (e.start_date = t.dateFromPos(r.start + i), e.start_date || (e.start_date = new Date(t.getState().min_date))) : (e.end_date = t.dateFromPos(r.end + i), e.end_date || (e.end_date = new Date(t.getState().max_date))),
            e.end_date - e.start_date < a.min_duration && (n.left ? e.start_date = t.calculateEndDate({
                start_date: e.end_date,
                duration: -1,
                task: e
            }) : e.end_date = t.calculateEndDate({
                start_date: e.start_date,
                duration: 1,
                task: e
            })),
            t._init_task_timing(e)
        },
        _resize_progress: function(t, e, i) {
            var n = this._drag_task_coords(t, i),
            a = Math.max(0, i.pos.x - n.start);
            t.progress = Math.min(1, a / (n.end - n.start));
        },
        _move: function(e, i, n) {
            var a = this._drag_task_coords(e, n),
            r = t.dateFromPos(a.start + i),
            s = t.dateFromPos(a.end + i);
            r ? s ? (e.start_date = r, e.end_date = s) : (e.end_date = new Date(t.getState().max_date), e.start_date = t.dateFromPos(t.posFromDate(e.end_date) - (a.end - a.start))) : (e.start_date = new Date(t.getState().min_date), e.end_date = t.dateFromPos(t.posFromDate(e.start_date) + (a.end - a.start)))
        },
        _drag_task_coords: function(e, i) {
            var n = i.obj_s_x = i.obj_s_x || t.posFromDate(e.start_date),
            a = i.obj_e_x = i.obj_e_x || t.posFromDate(e.end_date);
            return {
                start: n,
                end: a
            }
        },
        _mouse_position_change: function(t, e) {
            var i = t.x - e.x,
            n = t.y - e.y;
            return Math.sqrt(i * i + n * n)
        },
        _is_number: function(t) {
            return ! isNaN(parseFloat(t)) && isFinite(t)
        },
        on_mouse_move: function(e) {
            if (this.drag.start_drag) {
                var i = t._get_mouse_pos(e),
                n = this.drag.start_drag.start_x,
                a = this.drag.start_drag.start_y; (Date.now() - this.drag.timestamp > 50 || this._is_number(n) && this._is_number(a) && this._mouse_position_change({
                    x: n,
                    y: a
                },
                i) > 20) && this._start_dnd(e)
            }
            var r = this.drag;
            if (r.mode) {
                if (!t._checkTimeout(this, 40)) return;
                this._update_on_move(e)
            }
        },
        _update_on_move: function(e) {
            var i = this.drag;
            if (i.mode) {
                var n = t._get_mouse_pos(e);
                if (i.pos && i.pos.x == n.x) return;
                i.pos = n;
                var a = t.dateFromPos(n.x);
                if (!a || isNaN(a.getTime())) return;
                var r = n.x - i.start_x,
                s = t.getTask(i.id);
                if (this._handlers[i.mode]) {
                    var o = t.mixin({},
                    s),
                    _ = t.mixin({},
                    s);
                    this._handlers[i.mode].apply(this, [_, r, i]),
                    t.mixin(s, _, !0),
                    t.callEvent("onTaskDrag", [s.id, i.mode, _, o, e]),
                    t.mixin(s, _, !0),
                    t._update_parents(i.id),
                    t.refreshTask(i.id)
                }
            }
        },
        on_mouse_down: function(e, i) {
            if (2 != e.button) {
                var n = t.locate(e),
                a = null;
                if (t.isTaskExists(n) && (a = t.getTask(n)), !t._is_readonly(a) && !this.drag.mode) {
                    this.clear_drag_state(),
                    i = i || e.target || e.srcElement;
                    var r = t._getClassName(i);
                    if (!r || !this._get_drag_mode(r)) return i.parentNode ? this.on_mouse_down(e, i.parentNode) : void 0;
                    var s = this._get_drag_mode(r);
                    if (s) if (s.mode && s.mode != t.config.drag_mode.ignore && t.config["drag_" + s.mode]) {
                        if (n = t.locate(i), a = t.copy(t.getTask(n) || {}), t._is_readonly(a)) return this.clear_drag_state(),
                        !1;
                        if (t._is_flex_task(a) && s.mode != t.config.drag_mode.progress) return void this.clear_drag_state();
                        s.id = n;
                        var o = t._get_mouse_pos(e);
                        s.start_x = o.x,
                        s.start_y = o.y,
                        s.obj = a,
                        this.drag.start_drag = s,
                        this.drag.timestamp = Date.now()
                    } else this.clear_drag_state();
                    else if (t.checkEvent("onMouseDown") && t.callEvent("onMouseDown", [r.split(" ")[0]]) && i.parentNode) return this.on_mouse_down(e, i.parentNode)
                }
            }
        },
        _fix_dnd_scale_time: function(e, i) {
            function n(e) {
                t.isWorkTime(e.start_date, void 0, e) || (e.start_date = t.calculateEndDate({
                    start_date: e.start_date,
                    duration: -1,
                    unit: t.config.duration_unit,
                    task: e
                }))
            }
            function a(e) {
                t.isWorkTime(new Date(e.end_date - 1), void 0, e) || (e.end_date = t.calculateEndDate({
                    start_date: e.end_date,
                    duration: 1,
                    unit: t.config.duration_unit,
                    task: e
                }))
            }
            var r = t._tasks.unit,
            s = t._tasks.step;
            t.config.round_dnd_dates || (r = "minute", s = t.config.time_step),
            i.mode == t.config.drag_mode.resize ? i.left ? (e.start_date = t.roundDate({
                date: e.start_date,
                unit: r,
                step: s
            }), n(e)) : (e.end_date = t.roundDate({
                date: e.end_date,
                unit: r,
                step: s
            }), a(e)) : i.mode == t.config.drag_mode.move && (e.start_date = t.roundDate({
                date: e.start_date,
                unit: r,
                step: s
            }), n(e), e.end_date = t.calculateEndDate(e))
        },
        _fix_working_times: function(e, i) {
            var i = i || {
                mode: t.config.drag_mode.move
            };
            t.config.work_time && t.config.correct_work_time && (i.mode == t.config.drag_mode.resize ? i.left ? e.start_date = t.getClosestWorkTime({
                date: e.start_date,
                dir: "future",
                task: e
            }) : e.end_date = t.getClosestWorkTime({
                date: e.end_date,
                dir: "past",
                task: e
            }) : i.mode == t.config.drag_mode.move && t.correctTaskWorkTime(e))
        },
        on_mouse_up: function(e) {
            var i = this.drag;
            if (i.mode && i.id) {
                var n = t.getTask(i.id);
                if (t.config.work_time && t.config.correct_work_time && this._fix_working_times(n, i), this._fix_dnd_scale_time(n, i), t._init_task_timing(n), this._fireEvent("before_finish", i.mode, [i.id, i.mode, t.copy(i.obj), e])) {
                    var a = i.id;
                    t._init_task_timing(n),
                    this.clear_drag_state(),
                    t.updateTask(n.id),
                    this._fireEvent("after_finish", i.mode, [a, i.mode, e])
                } else i.obj._dhx_changed = !1,
                t.mixin(n, i.obj, !0),
                t.updateTask(n.id)
            }
            this.clear_drag_state()
        },
        _get_drag_mode: function(e) {
            var i = t.config.drag_mode,
            n = (e || "").split(" "),
            a = n[0],
            r = {
                mode: null,
                left: null
            };
            switch (a) {
            case "gantt_task_line":
            case "gantt_task_content":
                r.mode = i.move;
                break;
            case "gantt_task_drag":
                r.mode = i.resize,
                n[1] && -1 !== n[1].indexOf("left", n[1].length - "left".length) ? r.left = !0 : r.left = !1;
                break;
            case "gantt_task_progress_drag":
                r.mode = i.progress;
                break;
            case "gantt_link_control":
            case "gantt_link_point":
                r.mode = i.ignore;
                break;
            default:
                r = null
            }
            return r
        },
        _start_dnd: function(e) {
            var i = this.drag = this.drag.start_drag;
            delete i.start_drag;
            var n = t.config,
            a = i.id;
            n["drag_" + i.mode] && t.callEvent("onBeforeDrag", [a, i.mode, e]) && this._fireEvent("before_start", i.mode, [a, i.mode, e]) ? (delete i.start_drag, t.callEvent("onTaskDragStart", [])) : this.clear_drag_state();
        },
        _fireEvent: function(e, i, n) {
            t.assert(this._events[e], "Invalid stage:{" + e + "}");
            var a = this._events[e][i];
            return t.assert(a, "Unknown after drop mode:{" + i + "}"),
            t.assert(n, "Invalid event arguments"),
            t.checkEvent(a) ? t.callEvent(a, n) : !0
        }
    },
    t.roundTaskDates = function(e) {
        var i = t._tasks_dnd.drag;
        i || (i = {
            mode: t.config.drag_mode.move
        }),
        t._tasks_dnd._fix_dnd_scale_time(e, i)
    },
    t._render_link = function(e) {
        for (var i = this.getLink(e), n = t._get_link_renderers(), a = 0; a < n.length; a++) n[a].render_item(i)
    },
    t._get_link_type = function(e, i) {
        var n = null;
        return e && i ? n = t.config.links.start_to_start: !e && i ? n = t.config.links.finish_to_start: e || i ? e && !i && (n = t.config.links.start_to_finish) : n = t.config.links.finish_to_finish,
        n
    },
    t.isLinkAllowed = function(t, e, i, n) {
        var a = null;
        if (a = "object" == typeof t ? t: {
            source: t,
            target: e,
            type: this._get_link_type(i, n)
        },
        !a) return ! 1;
        if (! (a.source && a.target && a.type)) return ! 1;
        if (a.source == a.target) return ! 1;
        var r = !0;
        return this.checkEvent("onLinkValidation") && (r = this.callEvent("onLinkValidation", [a])),
        r
    },
    t._render_link_element = function(e) {
        var i = this._path_builder.get_points(e),
        n = t._drawer,
        a = n.get_lines(i),
        r = document.createElement("div"),
        s = "gantt_task_link";
        e.color && (s += " gantt_link_inline_color");
        var o = this.templates.link_class ? this.templates.link_class(e) : "";
        o && (s += " " + o),
        this.config.highlight_critical_path && this.isCriticalLink && this.isCriticalLink(e) && (s += " gantt_critical_link"),
        r.className = s,
        r.setAttribute(t.config.link_attribute, e.id);
        for (var _ = 0; _ < a.length; _++) {
            _ == a.length - 1 && (a[_].size -= t.config.link_arrow_size);
            var l = n.render_line(a[_], a[_ + 1]);
            e.color && (l.firstChild.style.backgroundColor = e.color),
            r.appendChild(l)
        }
        var d = a[a.length - 1].direction,
        c = t._render_link_arrow(i[i.length - 1], d);
        return e.color && (c.style.borderColor = e.color),
        r.appendChild(c),
        t._waiAria.linkAttr(e, r),
        r
    },
    t._render_link_arrow = function(e, i) {
        var n = document.createElement("div"),
        a = t._drawer,
        r = e.y,
        s = e.x,
        o = t.config.link_arrow_size,
        _ = t.config.row_height,
        l = "gantt_link_arrow gantt_link_arrow_" + i;
        switch (i) {
        case a.dirs.right:
            r -= (o - _) / 2,
            s -= o;
            break;
        case a.dirs.left:
            r -= (o - _) / 2;
            break;
        case a.dirs.up:
            s -= o;
            break;
        case a.dirs.down:
            r += 2 * o,
            s -= o
        }
        return n.style.cssText = ["top:" + r + "px", "left:" + s + "px"].join(";"),
        n.className = l,
        n
    },
    t._drawer = {
        current_pos: null,
        dirs: {
            left: "left",
            right: "right",
            up: "up",
            down: "down"
        },
        path: [],
        clear: function() {
            this.current_pos = null,
            this.path = []
        },
        point: function(e) {
            this.current_pos = t.copy(e)
        },
        get_lines: function(t) {
            this.clear(),
            this.point(t[0]);
            for (var e = 1; e < t.length; e++) this.line_to(t[e]);
            return this.get_path()
        },
        line_to: function(e) {
            var i = t.copy(e),
            n = this.current_pos,
            a = this._get_line(n, i);
            this.path.push(a),
            this.current_pos = i
        },
        get_path: function() {
            return this.path
        },
        get_wrapper_sizes: function(e) {
            var i, n = t.config.link_wrapper_width,
            a = (t.config.link_line_width, e.y + (t.config.row_height - n) / 2);
            switch (e.direction) {
            case this.dirs.left:
                i = {
                    top: a,
                    height: n,
                    lineHeight: n,
                    left: e.x - e.size - n / 2,
                    width: e.size + n
                };
                break;
            case this.dirs.right:
                i = {
                    top: a,
                    lineHeight: n,
                    height: n,
                    left: e.x - n / 2,
                    width: e.size + n
                };
                break;
            case this.dirs.up:
                i = {
                    top: a - e.size,
                    lineHeight: e.size + n,
                    height: e.size + n,
                    left: e.x - n / 2,
                    width: n
                };
                break;
            case this.dirs.down:
                i = {
                    top: a,
                    lineHeight: e.size + n,
                    height: e.size + n,
                    left: e.x - n / 2,
                    width: n
                }
            }
            return i
        },
        get_line_sizes: function(e) {
            var i, n = t.config.link_line_width,
            a = t.config.link_wrapper_width,
            r = e.size + n;
            switch (e.direction) {
            case this.dirs.left:
            case this.dirs.right:
                i = {
                    height: n,
                    width: r,
                    marginTop: (a - n) / 2,
                    marginLeft: (a - n) / 2
                };
                break;
            case this.dirs.up:
            case this.dirs.down:
                i = {
                    height: r,
                    width: n,
                    marginTop: (a - n) / 2,
                    marginLeft: (a - n) / 2
                }
            }
            return i
        },
        render_line: function(t) {
            var e = this.get_wrapper_sizes(t),
            i = document.createElement("div");
            i.style.cssText = ["top:" + e.top + "px", "left:" + e.left + "px", "height:" + e.height + "px", "width:" + e.width + "px"].join(";"),
            i.className = "gantt_line_wrapper";
            var n = this.get_line_sizes(t),
            a = document.createElement("div");
            return a.style.cssText = ["height:" + n.height + "px", "width:" + n.width + "px", "margin-top:" + n.marginTop + "px", "margin-left:" + n.marginLeft + "px"].join(";"),
            a.className = "gantt_link_line_" + t.direction,
            i.appendChild(a),
            i
        },
        _get_line: function(t, e) {
            var i = this.get_direction(t, e),
            n = {
                x: t.x,
                y: t.y,
                direction: this.get_direction(t, e)
            };
            return i == this.dirs.left || i == this.dirs.right ? n.size = Math.abs(t.x - e.x) : n.size = Math.abs(t.y - e.y),
            n
        },
        get_direction: function(t, e) {
            var i = 0;
            return i = e.x < t.x ? this.dirs.left: e.x > t.x ? this.dirs.right: e.y > t.y ? this.dirs.down: this.dirs.up
        }
    },
    t._y_from_ind = function(e) {
        return e * t.config.row_height
    },
    t._path_builder = {
        path: [],
        clear: function() {
            this.path = []
        },
        current: function() {
            return this.path[this.path.length - 1]
        },
        point: function(e) {
            return e ? (this.path.push(t.copy(e)), e) : this.current()
        },
        point_to: function(e, i, n) {
            n = n ? {
                x: n.x,
                y: n.y
            }: t.copy(this.point());
            var a = t._drawer.dirs;
            switch (e) {
            case a.left:
                n.x -= i;
                break;
            case a.right:
                n.x += i;
                break;
            case a.up:
                n.y -= i;
                break;
            case a.down:
                n.y += i
            }
            return this.point(n)
        },
        get_points: function(e) {
            var i = this.get_endpoint(e),
            n = t.config,
            a = i.e_y - i.y,
            r = i.e_x - i.x,
            s = t._drawer.dirs;
            this.clear(),
            this.point({
                x: i.x,
                y: i.y
            });
            var o = 2 * n.link_arrow_size,
            _ = i.e_x > i.x;
            if (e.type == t.config.links.start_to_start) this.point_to(s.left, o),
            _ ? (this.point_to(s.down, a), this.point_to(s.right, r)) : (this.point_to(s.right, r), this.point_to(s.down, a)),
            this.point_to(s.right, o);
            else if (e.type == t.config.links.finish_to_start) if (_ = i.e_x > i.x + 2 * o, this.point_to(s.right, o), _) r -= o,
            this.point_to(s.down, a),
            this.point_to(s.right, r);
            else {
                r -= 2 * o;
                var l = a > 0 ? 1 : -1;
                this.point_to(s.down, l * (n.row_height / 2)),
                this.point_to(s.right, r),
                this.point_to(s.down, l * (Math.abs(a) - n.row_height / 2)),
                this.point_to(s.right, o)
            } else if (e.type == t.config.links.finish_to_finish) this.point_to(s.right, o),
            _ ? (this.point_to(s.right, r), this.point_to(s.down, a)) : (this.point_to(s.down, a), this.point_to(s.right, r)),
            this.point_to(s.left, o);
            else if (e.type == t.config.links.start_to_finish) if (_ = i.e_x > i.x - 2 * o, this.point_to(s.left, o), _) {
                r += 2 * o;
                var l = a > 0 ? 1 : -1;
                this.point_to(s.down, l * (n.row_height / 2)),
                this.point_to(s.right, r),
                this.point_to(s.down, l * (Math.abs(a) - n.row_height / 2)),
                this.point_to(s.left, o)
            } else r += o,
            this.point_to(s.down, a),
            this.point_to(s.right, r);
            return this.path
        },
        get_endpoint: function(e) {
            var i = t.config.links,
            n = !1,
            a = !1;
            e.type == i.start_to_start ? n = a = !0 : e.type == i.finish_to_finish ? n = a = !1 : e.type == i.finish_to_start ? (n = !1, a = !0) : e.type == i.start_to_finish ? (n = !0, a = !1) : t.assert(!1, "Invalid link type");
            var r = t._get_task_visible_pos(t._pull[e.source], n),
            s = t._get_task_visible_pos(t._pull[e.target], a);
            return {
                x: r.x,
                e_x: s.x,
                y: r.y,
                e_y: s.y
            }
        }
    },
    t._init_links_dnd = function() {
        function e(e, i, n) {
            var a = t._get_task_pos(e, !!i);
            return a.y += t.config.row_height / 2,
            n = n || 0,
            a.x += (i ? -1 : 1) * n,
            a
        }
        function i(e) {
            var i = a(),
            n = ["gantt_link_tooltip"];
            i.from && i.to && (t.isLinkAllowed(i.from, i.to, i.from_start, i.to_start) ? n.push("gantt_allowed_link") : n.push("gantt_invalid_link"));
            var r = t.templates.drag_link_class(i.from, i.from_start, i.to, i.to_start);
            r && n.push(r);
            var s = "<div class='" + r + "'>" + t.templates.drag_link(i.from, i.from_start, i.to, i.to_start) + "</div>";
            e.innerHTML = s
        }
        function n(t, e) {
            t.style.left = e.x + 5 + "px",
            t.style.top = e.y + 5 + "px"
        }
        function a() {
            return {
                from: t._link_source_task,
                to: t._link_target_task,
                from_start: t._link_source_task_start,
                to_start: t._link_target_task_start
            }
        }
        function r() {
            t._link_source_task = t._link_source_task_start = t._link_target_task = null,
            t._link_target_task_start = !0
        }
        function s(e, i, n, r) {
            var s = l(),
            d = a(),
            c = ["gantt_link_direction"];
            t.templates.link_direction_class && c.push(t.templates.link_direction_class(d.from, d.from_start, d.to, d.to_start));
            var h = Math.sqrt(Math.pow(n - e, 2) + Math.pow(r - i, 2));
            if (h = Math.max(0, h - 3)) {
                s.className = c.join(" ");
                var u = (r - i) / (n - e),
                g = Math.atan(u);
                2 == _(e, n, i, r) ? g += Math.PI: 3 == _(e, n, i, r) && (g -= Math.PI);
                var f = Math.sin(g),
                p = Math.cos(g),
                v = Math.round(i),
                m = Math.round(e),
                k = ["-webkit-transform: rotate(" + g + "rad)", "-moz-transform: rotate(" + g + "rad)", "-ms-transform: rotate(" + g + "rad)", "-o-transform: rotate(" + g + "rad)", "transform: rotate(" + g + "rad)", "width:" + Math.round(h) + "px"];
                if ( - 1 != window.navigator.userAgent.indexOf("MSIE 8.0")) {
                    k.push('-ms-filter: "' + o(f, p) + '"');
                    var b = Math.abs(Math.round(e - n)),
                    y = Math.abs(Math.round(r - i));
                    switch (_(e, n, i, r)) {
                    case 1:
                        v -= y;
                        break;
                    case 2:
                        m -= b,
                        v -= y;
                        break;
                    case 3:
                        m -= b
                    }
                }
                k.push("top:" + v + "px"),
                k.push("left:" + m + "px"),
                s.style.cssText = k.join(";")
            }
        }
        function o(t, e) {
            return "progid:DXImageTransform.Microsoft.Matrix(M11 = " + e + ",M12 = -" + t + ",M21 = " + t + ",M22 = " + e + ",SizingMethod = 'auto expand')"
        }
        function _(t, e, i, n) {
            return e >= t ? i >= n ? 1 : 4 : i >= n ? 2 : 3
        }
        function l() {
            return c._direction || (c._direction = document.createElement("div"), t.$task_links.appendChild(c._direction)),
            c._direction
        }
        function d() {
            c._direction && (c._direction.parentNode && c._direction.parentNode.removeChild(c._direction), c._direction = null)
        }
        var c = new t._DnD(this.$task_bars, {
            sensitivity: 0,
            updates_per_second: 60
        }),
        h = "task_left",
        u = "task_right",
        g = "gantt_link_point",
        f = "gantt_link_control";
        c.attachEvent("onBeforeDragStart", t.bind(function(i, n) {
            var a = n.target || n.srcElement;
            if (r(), t.getState().drag_id) return ! 1;
            if (t._locate_css(a, g)) {
                t._locate_css(a, h) && (t._link_source_task_start = !0);
                var s = t._link_source_task = this.locate(n),
                o = t.getTask(s);
                if (t._is_readonly(o)) return r(),
                !1;
                var _ = 0;
                return t._get_safe_type(o.type) == t.config.types.milestone && (_ = (t._get_visible_milestone_width() - t._get_milestone_width()) / 2),
                this._dir_start = e(o, !!t._link_source_task_start, _),
                !0
            }
            return ! 1
        },
        this)),
        c.attachEvent("onAfterDragStart", t.bind(function(t, e) {
            this.config.touch && (this._show_link_points = !0, this.refreshData()),
            i(c.config.marker)
        },
        this)),
        c.attachEvent("onDragMove", t.bind(function(a, r) {
            var o = c.config,
            _ = c.getPosition(r);
            n(o.marker, _);
            var l = t._is_link_drop_area(r),
            d = t._link_target_task,
            h = t._link_landing,
            g = t._link_target_task_start,
            p = t.locate(r),
            v = !0;
            if (l && (v = !t._locate_css(r, u), l = !!p), t._link_target_task = p, t._link_landing = l, t._link_target_task_start = v, l) {
                var m = t.getTask(p),
                k = t._locate_css(r, f),
                b = 0;
                k && (b = Math.floor(k.offsetWidth / 2)),
                this._dir_end = e(m, !!t._link_target_task_start, b)
            } else this._dir_end = t._get_mouse_pos(r);
            var y = !(h == l && d == p && g == v);
            return y && (d && t.refreshTask(d, !1), p && t.refreshTask(p, !1)),
            y && i(o.marker),
            s(this._dir_start.x, this._dir_start.y, this._dir_end.x, this._dir_end.y),
            !0
        },
        this)),
        c.attachEvent("onDragEnd", t.bind(function() {
            var e = a();
            if (e.from && e.to && e.from != e.to) {
                var i = t._get_link_type(e.from_start, e.to_start),
                n = {
                    source: e.from,
                    target: e.to,
                    type: i
                };
                n.type && t.isLinkAllowed(n) && t.addLink(n)
            }
            r(),
            this.config.touch ? (this._show_link_points = !1, this.refreshData()) : (e.from && t.refreshTask(e.from, !1), e.to && t.refreshTask(e.to, !1)),
            d()
        },
        this)),
        t._is_link_drop_area = function(e) {
            return !! t._locate_css(e, f)
        }
    },
    t._get_link_state = function() {
        return {
            link_landing_area: this._link_landing,
            link_target_id: this._link_target_task,
            link_target_start: this._link_target_task_start,
            link_source_id: this._link_source_task,
            link_source_start: this._link_source_task_start
        }
    },
    t._task_renderer = function(e, i, n, a) {
        return this._task_area_pulls || (this._task_area_pulls = {}),
        this._task_area_renderers || (this._task_area_renderers = {}),
        this._task_area_renderers[e] ? this._task_area_renderers[e] : (i || this.assert(!1, "Invalid renderer call"), n && n.setAttribute(this.config.layer_attribute, !0), this._task_area_renderers[e] = {
            render_item: function(e, r) {
                if (r = r || n, a && !a(e)) return void this.remove_item(e.id);
                var s = i.call(t, e);
                this.append(e, s, r)
            },
            clear: function(i) {
                this.rendered = t._task_area_pulls[e] = {},
                this.clear_container(i)
            },
            clear_container: function(t) {
                t = t || n,
                t && (t.innerHTML = "")
            },
            render_items: function(t, e) {
                e = e || n;
                var i = document.createDocumentFragment();
                this.clear(e);
                for (var a = 0,
                r = t.length; r > a; a++) this.render_item(t[a], i);
                e.appendChild(i)
            },
            append: function(t, e, i) {
                return e ? (this.rendered[t.id] && this.rendered[t.id].parentNode ? this.replace_item(t.id, e) : i.appendChild(e), void(this.rendered[t.id] = e)) : void(this.rendered[t.id] && this.remove_item(t.id))
            },
            replace_item: function(t, e) {
                var i = this.rendered[t];
                i && i.parentNode && i.parentNode.replaceChild(e, i),
                this.rendered[t] = e
            },
            remove_item: function(t) {
                this.hide(t),
                delete this.rendered[t]
            },
            hide: function(t) {
                var e = this.rendered[t];
                e && e.parentNode && e.parentNode.removeChild(e)
            },
            restore: function(t) {
                var e = this.rendered[t.id];
                e ? e.parentNode || this.append(t, e, n) : this.render_item(t, n)
            },
            change_id: function(t, e) {
                this.rendered[e] = this.rendered[t],
                delete this.rendered[t]
            },
            rendered: this._task_area_pulls[e],
            node: n,
            unload: function() {
                this.clear(),
                delete t._task_area_renderers[e],
                delete t._task_area_pulls[e]
            }
        },
        this._task_area_renderers[e])
    },
    t._clear_renderers = function() {
        for (var t in this._task_area_renderers) this._task_renderer(t).unload()
    },
    t._is_layer = function(t) {
        return t && t.hasAttribute && t.hasAttribute(this.config.layer_attribute)
    },
    t._show_link_points = !1,
    t._init_tasks = function() {
        function e(t, e, i, n) {
            for (var a = 0; a < t.length; a++) t[a].change_id(e, i),
            t[a].render_item(n)
        }
        this._tasks = {
            col_width: this.config.columnWidth,
            width: [],
            full_width: 0,
            trace_x: [],
            rendered: {}
        },
        this._click.gantt_task_link = this.bind(function(e, i) {
            var n = this.locate(e, t.config.link_attribute);
            n && this.callEvent("onLinkClick", [n, e])
        },
        this),
        this._click.gantt_scale_cell = this.bind(function(e, i) {
            var n = t._get_mouse_pos(e),
            a = t.dateFromPos(n.x),
            r = Math.floor(t._day_index_by_date(a)),
            s = t._tasks.trace_x[r];
            t.callEvent("onScaleClick", [e, s])
        },
        this),
        this._dbl_click.gantt_task_link = this.bind(function(e, i, n) {
            var i = this.locate(e, t.config.link_attribute);
            this._delete_link_handler(i, e)
        },
        this),
        this._dbl_click.gantt_link_point = this.bind(function(e, i, n) {
            var i = this.locate(e),
            a = this.getTask(i),
            r = null;
            return n.parentNode && t._getClassName(n.parentNode) && (r = t._getClassName(n.parentNode).indexOf("_left") > -1 ? a.$target[0] : a.$source[0]),
            r && this._delete_link_handler(r, e),
            !1
        },
        this),
        this._tasks_dnd.init(),
        this._init_links_dnd(),
        this._link_layers.clear();
        var i = this.addLinkLayer({
            renderer: this._render_link_element,
            container: this.$task_links,
            filter: t._create_filter([t._filter_link, t._is_chart_visible].concat(this._get_link_filters()))
        });
        this._linkRenderer = this._link_layers.getRenderer(i),
        this._task_layers.clear();
        var n = this.addTaskLayer({
            renderer: this._render_task_element,
            container: this.$task_bars,
            filter: t._create_filter([t._filter_task, t._is_chart_visible].concat(this._get_task_filters()))
        });
        this._taskRenderer = this._task_layers.getRenderer(n),
        this.addTaskLayer({
            renderer: this._render_grid_item,
            container: this.$grid_data,
            filter: t._create_filter([t._filter_task, t._is_grid_visible].concat(this._get_task_filters()))
        }),
        this.addTaskLayer({
            renderer: this._render_bg_line,
            container: this.$task_bg,
            filter: t._create_filter([t._filter_task, t._is_chart_visible, t._is_std_background].concat(this._get_task_filters()))
        }),
        this._onTaskIdChange && this.detachEvent(this._onTaskIdChange),
        this._onTaskIdChange = this.attachEvent("onTaskIdChange",
        function(t, i) {
            var n = this._get_task_renderers();
            e(n, t, i, this.getTask(i))
        }),
        this._onLinkIdChange && this.detachEvent(this._onLinkIdChange),
        this._onLinkIdChange = this.attachEvent("onLinkIdChange",
        function(t, i) {
            var n = this._get_link_renderers();
            e(n, t, i, this.getLink(i))
        })
    },
    t._get_task_filters = function() {
        return []
    },
    t._get_link_filters = function() {
        return []
    },
    t._is_chart_visible = function() {
        return !! this.config.show_chart
    },
    t._filter_task = function(t, e) {
        var i = null,
        n = null;
        if (this.config.start_date && this.config.end_date) {
            if (this._isAllowedUnscheduledTask(e)) return ! 0;
            if (i = this.config.start_date.valueOf(), n = this.config.end_date.valueOf(), +e.start_date > n || +e.end_date < +i) return ! 1
        }
        return ! 0
    },
    t._filter_link = function(e, i) {
        return this.config.show_links ? !t.isTaskVisible(i.source) || !t.isTaskVisible(i.target) || t._isAllowedUnscheduledTask(t.getTask(i.source)) || t._isAllowedUnscheduledTask(t.getTask(i.target)) ? !1 : this.callEvent("onBeforeLinkDisplay", [e, i]) : !1;
    },
    t._is_std_background = function() {
        return ! this.config.static_background
    },
    t._delete_link_handler = function(e, i) {
        if (e && this.callEvent("onLinkDblClick", [e, i])) {
            var n = t.getLink(e);
            if (t._is_readonly(n)) return;
            var a = "",
            r = t.locale.labels.link + " " + this.templates.link_description(this.getLink(e)) + " " + t.locale.labels.confirm_link_deleting;
            window.setTimeout(function() {
                t._dhtmlx_confirm(r, a,
                function() {
                    t.deleteLink(e)
                })
            },
            t.config.touch ? 300 : 1)
        }
    },
    t.getTaskNode = function(t) {
        return this._taskRenderer.rendered[t]
    },
    t.getLinkNode = function(t) {
        return this._linkRenderer.rendered[t]
    },
    t._get_tasks_data = function() {
        for (var t = [], e = this._get_data_range(), i = 0; i < e.length; i++) {
            var n = this._pull[e[i]];
            n.$index = i,
            this.resetProjectDates(n),
            t.push(n)
        }
        return t
    },
    t._get_data_range = function() {
        return this._order
    },
    t._get_links_data = function() {
        return this._links.slice()
    },
    t._render_data = function() {
        this.callEvent("onBeforeDataRender", []),
        this._order_synced ? this._order_synced = !1 : this._sync_order();
        for (var e = this._get_tasks_data(), i = this._get_task_renderers(), n = 0; n < i.length; n++) i[n].clear();
        var a = t._get_links_data();
        i = this._get_link_renderers();
        for (var n = 0; n < i.length; n++) i[n].clear();
        this._update_layout_sizes(),
        this._scroll_resize();
        for (var i = this._get_task_renderers(), n = 0; n < i.length; n++) i[n].render_items(e);
        var a = t._get_links_data();
        i = this._get_link_renderers();
        for (var n = 0; n < i.length; n++) i[n].render_items(a);
        this.callEvent("onDataRender", [])
    },
    t._update_layout_sizes = function() {
        var e = this._tasks;
        e.bar_height = this._get_task_height();
        var i = Math.max(t._y - (t._scroll_sizes().x ? t._scroll_sizes().scroll_size + 1 : 0) - 1, 0);
        this.$task_data.style.height = Math.max(i - this.config.scale_height, 0) + "px",
        t.config.smart_rendering ? this.$task_bg.style.height = t.config.row_height * this.getVisibleTaskCount() + "px": this.$task_bg.style.height = "",
        this.$task_bg.style.backgroundImage = "";
        for (var n = this.$task_data.childNodes,
        a = 0,
        r = n.length; r > a; a++) {
            var s = n[a];
            this._is_layer(s) && s.style && (s.style.width = e.full_width + "px")
        }
        if (this._is_grid_visible()) {
            for (var o = this.getGridColumns(), _ = 0, a = 0; a < o.length; a++) _ += o[a].width;
            this.$grid_data.style.width = Math.max(_ - 1, 0) + "px";
        }
    },
    t._scale_range_unit = function() {
        var t = this.config.scale_unit;
        if (this.config.scale_offset_minimal) {
            var e = this._get_scales();
            t = e[e.length - 1].unit
        }
        return t
    },
    t._init_tasks_range = function() {
        var t = this._scale_range_unit();
        if (this.config.start_date && this.config.end_date) {
            this._min_date = this.date[t + "_start"](new Date(this.config.start_date));
            var e = new Date(this.config.end_date),
            i = this.date[t + "_start"](new Date(e));
            return e = +e != +i ? this.date.add(i, 1, t) : i,
            void(this._max_date = e)
        }
        this._get_tasks_data();
        var n = this.getSubtaskDates();
        this._min_date = n.start_date,
        this._max_date = n.end_date,
        this._max_date && this._min_date || (this._min_date = new Date, this._max_date = new Date),
        this._min_date = this.date[t + "_start"](this._min_date),
        this._min_date = this.calculateEndDate({
            start_date: this.date[t + "_start"](this._min_date),
            duration: -1,
            unit: t
        }),
        this._max_date = this.date[t + "_start"](this._max_date),
        this._max_date = this.calculateEndDate({
            start_date: this._max_date,
            duration: 2,
            unit: t
        })
    },
    t._prepare_scale_html = function(e, i, n) {
        var a = [],
        r = null,
        s = null,
        o = null; (e.template || e.date) && (s = e.template || this.date.date_to_str(e.date));
        var _ = 0,
        l = e.count; ! this.config.smart_scales || isNaN(i) || isNaN(n) || (_ = this._findBinary(e.left, i), l = this._findBinary(e.left, n) + 1),
        o = e.css ||
        function() {},
        !e.css && this.config.inherit_scale_class && (o = t.templates.scale_cell_class);
        for (var d = _; l > d && e.trace_x[d]; d++) {
            r = new Date(e.trace_x[d]);
            var c = s.call(this, r),
            h = e.width[d],
            u = e.height - (this.config.smart_scales && e.index ? 1 : 0),
            g = e.left[d],
            f = "",
            p = "",
            v = "";
            if (h) {
                var m = this.config.smart_scales ? "position:absolute;left:" + g + "px": "";
                f = "width:" + h + "px;height:" + u + "px;" + m,
                v = "gantt_scale_cell" + (d == e.count - 1 ? " gantt_last_cell": ""),
                p = o.call(this, r),
                p && (v += " " + p);
                var k = t._waiAria.getTimelineCellAttr(c),
                b = "<div class='" + v + "'" + k + " style='" + f + "'>" + c + "</div>";
                a.push(b)
            }
        }
        return a.join("")
    },
    t._get_scales = function() {
        var t = this._scale_helpers,
        e = [t.primaryScale()].concat(this.config.subscales);
        return t.sortScales(e),
        e
    },
    t._get_scale_chunk_html = function(t, e, i) {
        for (var n = [], a = this.templates.scale_row_class, r = 0; r < t.length; r++) {
            var s = "gantt_scale_line",
            o = a(t[r]);
            o && (s += " " + o),
            n.push('<div class="' + s + '" style="height:' + t[r].height + "px;position:relative;line-height:" + t[r].height + 'px">' + this._prepare_scale_html(t[r], e, i) + "</div>")
        }
        return n.join("")
    },
    t._refreshScales = function() {
        if (this.config.smart_scales && this.config.show_chart) {
            var e = this._scales,
            i = t.getScrollState().x;
            this.$task_scale.innerHTML = this._get_scale_chunk_html(e, i, i + this._x - this._get_grid_width())
        }
    },
    t.attachEvent("onGanttScroll",
    function(e, i, n, a) {
        t.config.smart_scales && e != n && t._refreshScales()
    }),
    t.attachEvent("onGanttRender",
    function() {
        t.config.smart_scales && t._refreshScales()
    }),
    t._render_tasks_scales = function() {
        this._init_tasks_range(),
        this._scroll_resize(),
        this._set_sizes();
        var t = "",
        e = 0,
        i = 0,
        n = 0;
        if (this._is_chart_visible()) {
            var a = this._scale_helpers,
            r = this._get_scales();
            n = this.config.scale_height - 1;
            var s = this._get_resize_options(),
            o = s.x ? Math.max(this.config.autosize_min_width, 0) : Math.max(this._x - this._get_grid_width() - 2, 0),
            _ = a.prepareConfigs(r, this.config.min_column_width, o, n),
            l = this._tasks = _[_.length - 1];
            this._scales = _,
            t = this._get_scale_chunk_html(_, 0, this._x - this._get_grid_width());
            var d = this._scroll_sizes();
            e = l.full_width + (this._scroll_sizes().y ? d.scroll_size: 0) + "px",
            i = l.full_width + "px",
            n += "px"
        }
        this._is_chart_visible() ? this.$task.style.display = "": this.$task.style.display = "none",
        this.$task_scale.style.height = n,
        this.$task_data.style.width = this.$task_scale.style.width = e,
        this.$task_scale.innerHTML = t
    },
    t._render_bg_line = function(e) {
        var i = t._tasks,
        n = i.count,
        a = document.createElement("div");
        if (t.config.show_task_cells) for (var r = 0; n > r; r++) {
            var s = i.width[r],
            o = "";
            if (s > 0) {
                var _ = document.createElement("div");
                _.style.width = s + "px",
                o = "gantt_task_cell" + (r == n - 1 ? " gantt_last_cell": ""),
                d = this.templates.task_cell_class(e, i.trace_x[r]),
                d && (o += " " + d),
                _.className = o,
                a.appendChild(_)
            }
        }
        var l = t.getGlobalTaskIndex(e.id) % 2 !== 0,
        d = t.templates.task_row_class(e.start_date, e.end_date, e),
        c = "gantt_task_row" + (l ? " odd": "") + (d ? " " + d: "");
        return this.getState().selected_task == e.id && (c += " gantt_selected"),
        a.className = c,
        t.config.smart_rendering && (a.style.position = "absolute", a.style.top = this.getTaskTop(e.id) + "px", a.style.width = "100%"),
        a.style.height = t.config.row_height + "px",
        a.setAttribute(this.config.task_attribute, e.id),
        a
    },
    t._adjust_scales = function() {
        if (this.config.fit_tasks) {
            var t = +this._min_date,
            e = +this._max_date;
            if (this._init_tasks_range(), +this._min_date != t || +this._max_date != e) return this.render(),
            this.callEvent("onScaleAdjusted", []),
            !0
        }
        return ! 1
    },
    t.refreshTask = function(e, i) {
        var n = this._get_task_renderers(),
        a = this.getTask(e);
        if (a && this.isTaskVisible(e)) {
            for (var r = 0; r < n.length; r++) n[r].render_item(a);
            if (void 0 !== i && !i) return;
            for (var r = 0; r < a.$source.length; r++) t.refreshLink(a.$source[r]);
            for (var r = 0; r < a.$target.length; r++) t.refreshLink(a.$target[r])
        }
    },
    t.refreshLink = function(t) {
        if (this.isLinkExists(t)) this._render_link(t);
        else for (var e = this._get_link_renderers(), i = 0; i < e.length; i++) e[i].remove_item(t)
    },
    t._combine_item_class = function(e, i, n) {
        var a = [e];
        i && a.push(i);
        var r = t.getState(),
        s = this.getTask(n);
        this._get_safe_type(s.type) == this.config.types.milestone && a.push("gantt_milestone"),
        this._get_safe_type(s.type) == this.config.types.project && a.push("gantt_project"),
        this._is_flex_task(s) && a.push("gantt_dependent_task"),
        this.config.select_task && n == r.selected_task && a.push("gantt_selected"),
        n == r.drag_id && (a.push("gantt_drag_" + r.drag_mode), r.touch_drag && a.push("gantt_touch_" + r.drag_mode));
        var o = t._get_link_state();
        if (o.link_source_id == n && a.push("gantt_link_source"), o.link_target_id == n && a.push("gantt_link_target"), this.config.highlight_critical_path && this.isCriticalTask && this.isCriticalTask(s) && a.push("gantt_critical_task"), o.link_landing_area && o.link_target_id && o.link_source_id && o.link_target_id != o.link_source_id) {
            var _ = o.link_source_id,
            l = o.link_source_start,
            d = o.link_target_start,
            c = t.isLinkAllowed(_, n, l, d),
            h = "";
            h = c ? d ? "link_start_allow": "link_finish_allow": d ? "link_start_deny": "link_finish_deny",
            a.push(h)
        }
        return a.join(" ")
    },
    t._render_pair = function(e, i, n, a) {
        var r = t.getState(); + n.start_date >= +r.min_date && e.appendChild(a(i + " task_left")),
        +n.end_date <= +r.max_date && e.appendChild(a(i + " task_right"))
    },
    t._get_task_height = function() {
        var t = this.config.task_height;
        return "full" == t && (t = this.config.row_height - 5),
        t = Math.min(t, this.config.row_height),
        Math.max(t, 0)
    },
    t._get_milestone_width = function() {
        return this._get_task_height()
    },
    t._get_visible_milestone_width = function() {
        var e = t._get_task_height();
        return Math.sqrt(2 * e * e)
    },
    t.getTaskPosition = function(e, i, n) {
        var a = this.posFromDate(i || e.start_date),
        r = this.posFromDate(n || e.end_date);
        r = Math.max(a, r);
        var s = this.getTaskTop(e.id),
        o = t._get_task_height();
        return {
            left: a,
            top: s,
            height: o,
            width: Math.max(r - a, 0)
        }
    },
    t._get_task_width = function(t, e, i) {
        return Math.round(this._get_task_pos(t, !1).x - this._get_task_pos(t, !0).x);
    },
    t._is_readonly = function(t) {
        return t && t[this.config.editable_property] ? !1 : t && t[this.config.readonly_property] || this.config.readonly
    },
    t._task_default_render = function(e) {
        if (!this._isAllowedUnscheduledTask(e)) {
            var i = this._get_task_pos(e),
            n = this.config,
            a = this._get_task_height(),
            r = Math.floor((this.config.row_height - a) / 2);
            this._get_safe_type(e.type) == n.types.milestone && n.link_line_width > 1 && (r += 1);
            var s = document.createElement("div"),
            o = t._get_task_width(e),
            _ = this._get_safe_type(e.type);
            s.setAttribute(this.config.task_attribute, e.id),
            n.show_progress && _ != this.config.types.milestone && this._render_task_progress(e, s, o);
            var l = t._render_task_content(e, o);
            e.textColor && (l.style.color = e.textColor),
            s.appendChild(l);
            var d = this._combine_item_class("gantt_task_line", this.templates.task_class(e.start_date, e.end_date, e), e.id); (e.color || e.progressColor || e.textColor) && (d += " gantt_task_inline_color"),
            s.className = d;
            var c = ["left:" + i.x + "px", "top:" + (r + i.y) + "px", "height:" + a + "px", "line-height:" + Math.max(30 > a ? a - 2 : a, 0) + "px", "width:" + o + "px"];
            e.color && c.push("background-color:" + e.color),
            e.textColor && c.push("color:" + e.textColor),
            s.style.cssText = c.join(";");
            var h = this._render_leftside_content(e);
            return h && s.appendChild(h),
            h = this._render_rightside_content(e),
            h && s.appendChild(h),
            t._waiAria.setTaskBarAttr(e, s),
            this._is_readonly(e) || (n.drag_resize && !this._is_flex_task(e) && _ != this.config.types.milestone && t._render_pair(s, "gantt_task_drag", e,
            function(t) {
                var e = document.createElement("div");
                return e.className = t,
                e
            }), n.drag_links && this.config.show_links && t._render_pair(s, "gantt_link_control", e,
            function(e) {
                var i = document.createElement("div");
                i.className = e,
                i.style.cssText = ["height:" + a + "px", "line-height:" + a + "px"].join(";");
                var n = document.createElement("div");
                return n.className = "gantt_link_point",
                n.style.display = t._show_link_points ? "block": "",
                i.appendChild(n),
                i
            })),
            s
        }
    },
    t._render_task_element = function(t) {
        var e = this.config.type_renderers,
        i = e[this._get_safe_type(t.type)],
        n = this._task_default_render;
        return i || (i = n),
        i.call(this, t, this.bind(n, this))
    },
    t._render_side_content = function(t, e, i) {
        if (!e) return null;
        var n = e(t.start_date, t.end_date, t);
        if (!n) return null;
        var a = document.createElement("div");
        return a.className = "gantt_side_content " + i,
        a.innerHTML = n,
        a
    },
    t._render_leftside_content = function(e) {
        var i = "gantt_left " + t._get_link_crossing_css(!0, e);
        return t._render_side_content(e, this.templates.leftside_text, i)
    },
    t._render_rightside_content = function(e) {
        var i = "gantt_right " + t._get_link_crossing_css(!1, e);
        return t._render_side_content(e, this.templates.rightside_text, i)
    },
    t._get_conditions = function(e) {
        return e ? {
            $source: [t.config.links.start_to_start],
            $target: [t.config.links.start_to_start, t.config.links.finish_to_start]
        }: {
            $source: [t.config.links.finish_to_start, t.config.links.finish_to_finish],
            $target: [t.config.links.finish_to_finish]
        }
    },
    t._get_link_crossing_css = function(e, i) {
        var n = t._get_conditions(e);
        for (var a in n) for (var r = i[a], s = 0; s < r.length; s++) for (var o = t.getLink(r[s]), _ = 0; _ < n[a].length; _++) if (o.type == n[a][_]) return "gantt_link_crossing";
        return ""
    },
    t._render_task_content = function(t, e) {
        var i = document.createElement("div");
        return this._get_safe_type(t.type) != this.config.types.milestone && (i.innerHTML = this.templates.task_text(t.start_date, t.end_date, t)),
        i.className = "gantt_task_content",
        i
    },
    t._render_task_progress = function(e, i, n) {
        var a = 1 * e.progress || 0;
        n = Math.max(n - 2, 0);
        var r = document.createElement("div"),
        s = Math.round(n * a);
        if (s = Math.min(n, s), e.progressColor && (r.style.backgroundColor = e.progressColor, r.style.opacity = 1), r.style.width = s + "px", r.className = "gantt_task_progress", r.innerHTML = this.templates.progress_text(e.start_date, e.end_date, e), i.appendChild(r), this.config.drag_progress && !t._is_readonly(e)) {
            var o = document.createElement("div");
            o.style.left = s + "px",
            o.className = "gantt_task_progress_drag",
            r.appendChild(o),
            i.appendChild(o)
        }
    },
    t._get_line = function(t) {
        var e = {
            second: 1,
            minute: 60,
            hour: 3600,
            day: 86400,
            week: 604800,
            month: 2592e3,
            quarter: 7776e3,
            year: 31536e3
        };
        return e[t] || e.hour
    },
    t.dateFromPos = function(e) {
        var i = this._tasks;
        if (0 > e || e > i.full_width || !i.full_width) return null;
        var n = this._findBinary(this._tasks.left, e),
        a = this._tasks.left[n],
        r = i.width[n] || i.col_width,
        s = 0;
        r && (s = (e - a) / r);
        var o = 0;
        s && (o = t._get_coll_duration(i, i.trace_x[n]));
        var _ = new Date(i.trace_x[n].valueOf() + Math.round(s * o));
        return _
    },
    t.posFromDate = function(e) {
        if (!this._is_chart_visible()) return 0;
        var i = t._day_index_by_date(e);
        this.assert(i >= 0, "Invalid day index");
        var n = Math.floor(i),
        a = i % 1,
        r = t._tasks.left[Math.min(n, t._tasks.width.length - 1)];
        return n == t._tasks.width.length && (r += t._tasks.width[t._tasks.width.length - 1]),
        a && (r += n < t._tasks.width.length ? t._tasks.width[n] * (a % 1) : 1),
        r
    },
    t._day_index_by_date = function(e) {
        var i = new Date(e).valueOf(),
        n = t._tasks.trace_x,
        a = t._tasks.ignore_x;
        if (i <= this._min_date) return 0;
        if (i >= this._max_date) return n.length;
        for (var r = t._findBinary(n, i), s = +t._tasks.trace_x[r]; a[s];) s = t._tasks.trace_x[++r];
        return s ? r + (e - n[r]) / t._get_coll_duration(t._tasks, n[r]) : 0
    },
    t._findBinary = function(t, e) {
        for (var i, n, a, r = 0,
        s = t.length - 1; s >= r;) if (i = Math.floor((r + s) / 2), n = +t[i], a = +t[i - 1], e > n) r = i + 1;
        else {
            if (! (n > e)) return i;
            if (!isNaN(a) && e > a) return i - 1;
            s = i - 1
        }
        return t.length - 1
    },
    t._get_coll_duration = function(e, i) {
        return t.date.add(i, e.step, e.unit) - i
    },
    t._get_x_pos = function(e, i) {
        i = i !== !1;
        t.posFromDate(i ? e.start_date: e.end_date)
    },
    t.getTaskTop = function(t) {
        return this._y_from_ind(this.getGlobalTaskIndex(t))
    },
    t._get_task_coord = function(t, e, i) {
        e = e !== !1,
        i = i || 0;
        var n = this._get_safe_type(t.type) == this.config.types.milestone,
        a = null;
        a = e || n ? t.start_date || this._default_task_date(t) : t.end_date || this.calculateEndDate({
            start_date: this._default_task_date(t),
            task: t
        });
        var r = this.posFromDate(a),
        s = this.getTaskTop(t.id);
        return n && (e ? r -= i: r += i),
        {
            x: r,
            y: s
        }
    },
    t._get_task_pos = function(e, i) {
        i = i !== !1;
        var n = t._get_milestone_width() / 2;
        return this._get_task_coord(e, i, n)
    },
    t._get_task_visible_pos = function(e, i) {
        i = i !== !1;
        var n = t._get_visible_milestone_width() / 2;
        return this._get_task_coord(e, i, n)
    },
    t._correct_shift = function(e, i) {
        return e -= 6e4 * (new Date(t._min_date).getTimezoneOffset() - new Date(e).getTimezoneOffset()) * (i ? -1 : 1)
    },
    t._get_mouse_pos = function(e) {
        if (e.pageX || e.pageY) var i = {
            x: e.pageX,
            y: e.pageY
        };
        var n = t.env.isIE ? document.documentElement: document.body,
        i = {
            x: e.clientX + n.scrollLeft - n.clientLeft,
            y: e.clientY + n.scrollTop - n.clientTop
        },
        a = t._get_position(t.$task_data);
        return i.x = i.x - a.x + t.$task_data.scrollLeft,
        i.y = i.y - a.y + t.$task_data.scrollTop,
        i
    },
    t._is_layer = function(t) {
        return t && t.hasAttribute && t.hasAttribute(this.config.layer_attribute)
    },
    t.attachEvent("onDataRender",
    function() {
        t._render_bg_canvas()
    }),
    t._render_bg_canvas = function() {
        function e() {
            for (var e = t._tasks.width,
            i = {},
            n = 0; n < e.length; n++) 1 * e[n] && (i[e[n]] = !0);
            return i
        }
        function i(t) {
            var e = /^rgba?\(([\d]{1,3}), *([\d]{1,3}), *([\d]{1,3}) *(,( *[\d\.]+ *))?\)$/i.exec(t);
            return e ? {
                r: 1 * e[1],
                g: 1 * e[2],
                b: 1 * e[3],
                a: 255 * e[5] || 255
            }: null
        }
        function n(e) {
            return t._canvas_bg_cache ? t._canvas_bg_cache[e] || null: (t._canvas_bg_cache = {},
            null)
        }
        function a(t, e, i) {
            return (t + "" + e + i.bottomBorderColor + i.rightBorderColor).replace(/[^\w\d]/g, "")
        }
        function r() {
            t._staticBgStyleId || (t._staticBgStyleId = "gantt-static-bg-styles-" + t.uid());
            var e = document.getElementById(t._staticBgStyleId);
            return e || (e = document.createElement("style"), e.id = t._staticBgStyleId, document.body.appendChild(e)),
            e
        }
        function s(e, i) {
            t._canvas_bg_cache || (t._canvas_bg_cache = {}),
            t._canvas_bg_cache[e] = i
        }
        function o(t, e, n) {
            function a(t, e, n, a, s, o) {
                var _ = s.createImageData(e * a, t * n);
                _.imageSmoothingEnabled = !1;
                for (var l = 1 * o.rightBorderWidth,
                d = i(o.rightBorderColor), c = 0, h = 0, u = 0, g = 1; a >= g; g++) for (c = g * e - 1, u = 0; l > u; u++) for (h = 0; t * n > h; h++) r(c - u, h, d, _);
                var f = 1 * o.bottomBorderWidth,
                p = i(o.bottomBorderColor);
                h = 0;
                for (var v = 1; n >= v; v++) for (h = v * t - 1, u = 0; f > u; u++) for (c = 0; e * a > c; c++) r(c, h - u, p, _);
                s.putImageData(_, 0, 0)
            }
            function r(e, i, n, a) {
                var r = t * s,
                o = 4 * (i * r + e);
                a.data[o] = n.r,
                a.data[o + 1] = n.g,
                a.data[o + 2] = n.b,
                a.data[o + 3] = n.a
            }
            var s = Math.floor(500 / t) || 1,
            o = Math.floor(500 / e) || 1,
            _ = document.createElement("canvas");
            _.height = e * o,
            _.width = t * s;
            var l = _.getContext("2d");
            return a(e, t, o, s, l, n),
            _.toDataURL()
        }
        function _(t) {
            return "gantt-static-bg-" + t
        }
        function l(i) {
            var l = {},
            d = e(),
            c = t.config.row_height,
            h = "";
            for (var u in d) {
                var g = 1 * u,
                f = a(g, c, i),
                p = n(f);
                if (!p) {
                    var v = o(g, c, i);
                    s(f, v),
                    h += "." + _(f) + "{ background-image: url('" + v + "');}"
                }
                l[u] = _(f)
            }
            if (h) {
                var m = r();
                m.innerHTML += h
            }
            return l
        }
        function d(e) {
            var i, n, a = [],
            r = 0,
            s = t._tasks.width.filter(function(t) {
                return !! t
            }),
            o = 0,
            _ = 1e5;
            if (t.env.isIE) {
                var l = navigator.appVersion || ""; ( - 1 != l.indexOf("Windows NT 6.2") || -1 != l.indexOf("Windows NT 6.1") || -1 != l.indexOf("Windows NT 6.0")) && (_ = 2e4);
            }
            for (var d = 0; d < s.length; d++) {
                var c = s[d];
                if (c != n && void 0 !== n || d == s.length - 1 || r > _) {
                    for (var h = t.config.row_height * t._order.length,
                    u = 0,
                    g = Math.floor(_ / t.config.row_height) * t.config.row_height, f = r; h > 0;) {
                        var p = Math.min(h, g);
                        h -= g,
                        i = document.createElement("div"),
                        i.style.height = p + "px",
                        i.style.position = "absolute",
                        i.style.top = u + "px",
                        i.style.left = o + "px",
                        i.style.whiteSpace = "no-wrap",
                        i.className = e[n || c],
                        d == s.length - 1 && (f = c + f - 1),
                        i.style.width = f + "px",
                        a.push(i),
                        u += p
                    }
                    r = 0,
                    o += f
                }
                c && (r += c, n = c)
            }
            return a
        }
        function c() {
            var e = document.createElement("div");
            e.className = "gantt_task_cell";
            var i = document.createElement("div");
            i.className = "gantt_task_row",
            i.appendChild(e),
            t.$task_bg.appendChild(i);
            var n = getComputedStyle(i),
            a = getComputedStyle(e),
            r = {
                bottomBorderWidth: n.getPropertyValue("border-bottom-width").replace("px", ""),
                rightBorderWidth: a.getPropertyValue("border-right-width").replace("px", ""),
                bottomBorderColor: n.getPropertyValue("border-bottom-color"),
                rightBorderColor: a.getPropertyValue("border-right-color")
            };
            return t.$task_bg.removeChild(i),
            r
        }
        if (t.config.static_background) {
            var h = document.createElement("canvas");
            if (h.getContext) {
                t.$task_bg.innerHTML = "";
                var u = c(),
                g = l(u),
                f = d(g),
                p = document.createDocumentFragment();
                f.forEach(function(t) {
                    p.appendChild(t)
                }),
                t.$task_bg.appendChild(p)
            }
        }
    },
    t.attachEvent("onGanttReady",
    function() {
        t._task_layers.add(),
        t._link_layers.add()
    }),
    t._layers = {
        prepareConfig: function(e) {
            "function" == typeof e && (e = {
                renderer: e
            });
            e.id = t.uid();
            return e.container || (e.container = document.createElement("div")),
            e
        },
        create: function(e, i) {
            return {
                tempCollection: [],
                renderers: {},
                container: e,
                getRenderers: function() {
                    var t = [];
                    for (var e in this.renderers) t.push(this.renderers[e]);
                    return t
                },
                getRenderer: function(t) {
                    return this.renderers[t]
                },
                add: function(e) {
                    if (e && this.tempCollection.push(e), this.container()) for (var n = this.container(), a = this.tempCollection, r = 0; r < a.length; r++) {
                        var e = a[r],
                        s = e.container,
                        o = e.id,
                        _ = e.topmost;
                        if (!s.parentNode) if (_) n.appendChild(s);
                        else {
                            var l = i ? i() : n.firstChild;
                            l ? n.insertBefore(s, l) : n.appendChild(s)
                        }
                        this.renderers[o] = t._task_renderer(o, e.renderer, s, e.filter),
                        this.tempCollection.splice(r, 1),
                        r--
                    }
                },
                remove: function(t) {
                    this.renderers[t].unload(),
                    delete this.renderers[t]
                },
                clear: function() {
                    for (var t in this.renderers) this.renderers[t].unload();
                    this.renderers = {}
                }
            }
        }
    },
    t._create_filter = function(e) {
        return e instanceof Array || (e = Array.prototype.slice.call(arguments, 0)),
        function(i) {
            for (var n = !0,
            a = 0,
            r = e.length; r > a; a++) {
                var s = e[a];
                s && (n = n && s.apply(t, [i.id, i]) !== !1)
            }
            return n
        }
    },
    t._add_generic_layer = function(e, i) {
        return function(n) {
            return void 0 === n.filter && (n.filter = t._create_filter(i)),
            n = t._layers.prepareConfig(n),
            e.add(n),
            n.id
        }
    },
    t._task_layers = t._layers.create(function() {
        return t.$task_data
    },
    function() {
        return t.$task_links
    }),
    t._link_layers = t._layers.create(function() {
        return t.$task_data
    }),
    t.addTaskLayer = t._add_generic_layer(t._task_layers, [t._filter_task, t._is_chart_visible].concat(t._get_task_filters())),
    t.removeTaskLayer = function(e) {
        t._task_layers.remove(e)
    },
    t.addLinkLayer = t._add_generic_layer(t._link_layers, [t._filter_link, t._is_chart_visible].concat(t._get_link_filters())),
    t.removeLinkLayer = function(e) {
        t._link_layers.remove(e)
    },
    t._get_task_renderers = function() {
        return this._task_layers.getRenderers()
    },
    t._get_link_renderers = function() {
        return this._link_layers.getRenderers()
    },
    t._pull = {},
    t._branches = {},
    t._order = [],
    t._lpull = {},
    t._links = [],
    t._order_full = [],
    t.load = function(e, i, n) {
        this._load_url = e,
        this.assert(arguments.length, "Invalid load arguments");
        var a = "json",
        r = null;
        arguments.length >= 3 ? (a = i, r = n) : "string" == typeof arguments[1] ? a = arguments[1] : "function" == typeof arguments[1] && (r = arguments[1]),
        this._load_type = a,
        this.callEvent("onLoadStart", [e, a]),
        this.ajax.get(e, t.bind(function(t) {
            this.on_load(t, a),
            this.callEvent("onLoadEnd", [e, a]),
            "function" == typeof r && r.call(this)
        },
        this))
    },
    t.parse = function(t, e) {
        this.on_load({
            xmlDoc: {
                responseText: t
            }
        },
        e)
    },
    t.serialize = function(t) {
        return t = t || "json",
        this[t].serialize()
    },
    t.on_load = function(t, e) {
        this.callEvent("onBeforeParse", []),
        e || (e = "json"),
        this.assert(this[e], "Invalid data type:'" + e + "'");
        var i = t.xmlDoc.responseText,
        n = this[e].parse(i, t);
        this._process_loading(n);
    },
    t._load_task = function(t) {
        return this._init_task(t),
        this.callEvent("onTaskLoading", [t]) ? (this._pull[t.id] = t, !0) : !1
    },
    t._build_pull = function(t) {
        for (var e = null,
        i = [], n = 0, a = t.length; a > n; n++) e = t[n],
        this._load_task(e) && i.push(e);
        return i
    },
    t._build_hierarchy = function(t) {
        for (var e = null,
        i = 0,
        n = t.length; n > i; i++) e = t[i],
        this.setParent(e, this.getParent(e) || this.config.root_id);
        for (var i = 0,
        n = t.length; n > i; i++) e = t[i],
        this._add_branch(e),
        e.$level = this.calculateTaskLevel(e)
    },
    t._process_loading = function(t) {
        t.collections && this._load_collections(t.collections);
        var e = this._build_pull(t.data);
        if (this._build_hierarchy(e), this._sync_order(), this._order_synced = !0, this._init_links(t.links || (t.collections ? t.collections.links: [])), this.callEvent("onParse", []), this.render(), this.config.initial_scroll) {
            var i = this._order[0] || this.config.root_id;
            i && this.showTask(i)
        }
    },
    t._init_links = function(t) {
        if (t) for (var e = 0; e < t.length; e++) if (t[e]) {
            var i = this._init_link(t[e]);
            this._lpull[i.id] = i
        }
        this._sync_links()
    },
    t._load_collections = function(t) {
        var e = !1;
        for (var i in t) if (t.hasOwnProperty(i)) {
            e = !0;
            var n = t[i],
            a = this.serverList[i];
            if (!a) continue;
            a.splice(0, a.length);
            for (var r = 0; r < n.length; r++) {
                var s = n[r],
                o = this.copy(s);
                o.key = o.value;
                for (var _ in s) if (s.hasOwnProperty(_)) {
                    if ("value" == _ || "label" == _) continue;
                    o[_] = s[_]
                }
                a.push(o)
            }
        }
        e && this.callEvent("onOptionsLoad", [])
    },
    t._sync_order = function(t) {
        this._order = [],
        this._order_full = [],
        this._order_search = {},
        this._sync_order_item({
            parent: this.config.root_id,
            $open: !0,
            $ignore: !0,
            id: this.config.root_id
        }),
        t || (this._scroll_resize(), this._set_sizes())
    },
    t.attachEvent("onBeforeTaskDisplay",
    function(t, e) {
        return ! e.$ignore
    }),
    t._sync_order_item = function(e, i) {
        e.id !== t.config.root_id && (this._order_full.push(e.id), !i && this._filter_task(e.id, e) && this.callEvent("onBeforeTaskDisplay", [e.id, e]) && (this._order.push(e.id), this._order_search[e.id] = this._order.length - 1));
        var n = this.getChildren(e.id);
        if (n) for (var a = 0; a < n.length; a++) this._sync_order_item(this._pull[n[a]], i || !e.$open)
    },
    t.getTaskCount = function() {
        return this._order_full.length
    },
    t.getLinkCount = function() {
        return this._links.length
    },
    t.getVisibleTaskCount = function() {
        return this._order.length
    },
    t.getTaskIndex = function(t) {
        for (var e = this.getChildren(this.getParent(t)), i = 0; i < e.length; i++) if (e[i] == t) return i;
        return - 1
    },
    t.getGlobalTaskIndex = function(t) {
        this.assert(t, "Invalid argument");
        var e = this._order_search[t];
        return void 0 !== e ? e: -1
    },
    t._get_visible_order = t.getGlobalTaskIndex,
    t.eachTask = function(t, e, i) {
        e = e || this.config.root_id,
        i = i || this;
        var n = this.getChildren(e);
        if (n) for (var a = 0; a < n.length; a++) {
            var r = this._pull[n[a]];
            t.call(i, r),
            this.hasChild(r.id) && this.eachTask(t, r.id, i);
        }
    },
    t._eachParent = function(t, e, i) {
        i = i || this;
        for (var n = e; this.getParent(n) && this.isTaskExists(this.getParent(n));) n = this.getTask(this.getParent(n)),
        t.call(i, n)
    },
    t.json = {
        parse: function(e) {
            return t.assert(e, "Invalid data"),
            "string" == typeof e && (window.JSON ? e = JSON.parse(e) : t.assert(!1, "JSON is not supported")),
            e.dhx_security && (t.security_key = e.dhx_security),
            e
        },
        serializeTask: function(t) {
            return this._copyObject(t)
        },
        serializeLink: function(t) {
            return this._copyLink(t)
        },
        _copyLink: function(t) {
            var e = {};
            for (var i in t) e[i] = t[i];
            return e
        },
        _copyObject: function(e) {
            var i = {};
            for (var n in e)"$" != n.charAt(0) && (i[n] = e[n], i[n] instanceof Date && (i[n] = t.templates.xml_format(i[n])));
            return i
        },
        serialize: function() {
            var e = [],
            i = [];
            t.eachTask(function(i) {
                t.resetProjectDates(i),
                e.push(this.serializeTask(i))
            },
            t.config.root_id, this);
            for (var n = t.getLinks(), a = 0; a < n.length; a++) i.push(this.serializeLink(n[a]));
            return {
                data: e,
                links: i
            }
        }
    },
    t.xml = {
        _xmlNodeToJSON: function(t, e) {
            for (var i = {},
            n = 0; n < t.attributes.length; n++) i[t.attributes[n].name] = t.attributes[n].value;
            if (!e) {
                for (var n = 0; n < t.childNodes.length; n++) {
                    var a = t.childNodes[n];
                    1 == a.nodeType && (i[a.tagName] = a.firstChild ? a.firstChild.nodeValue: "")
                }
                i.text || (i.text = t.firstChild ? t.firstChild.nodeValue: "")
            }
            return i
        },
        _getCollections: function(e) {
            for (var i = {},
            n = t.ajax.xpath("//coll_options", e), a = 0; a < n.length; a++) for (var r = n[a].getAttribute("for"), s = i[r] = [], o = t.ajax.xpath(".//item", n[a]), _ = 0; _ < o.length; _++) {
                for (var l = o[_], d = l.attributes, c = {
                    key: o[_].getAttribute("value"),
                    label: o[_].getAttribute("label")
                },
                h = 0; h < d.length; h++) {
                    var u = d[h];
                    "value" != u.nodeName && "label" != u.nodeName && (c[u.nodeName] = u.nodeValue)
                }
                s.push(c)
            }
            return i
        },
        _getXML: function(e, i, n) {
            n = n || "data",
            i.getXMLTopNode || (i = t.ajax.parse(i));
            var a = t.ajax.xmltop(n, i.xmlDoc);
            if (a.tagName != n) throw "Invalid XML data";
            var r = a.getAttribute("dhx_security");
            return r && (t.security_key = r),
            a
        },
        parse: function(e, i) {
            i = this._getXML(e, i);
            for (var n = {},
            a = n.data = [], r = t.ajax.xpath("//task", i), s = 0; s < r.length; s++) a[s] = this._xmlNodeToJSON(r[s]);
            return n.collections = this._getCollections(i),
            n
        },
        _copyLink: function(t) {
            return "<item id='" + t.id + "' source='" + t.source + "' target='" + t.target + "' type='" + t.type + "' />"
        },
        _copyObject: function(t) {
            return "<task id='" + t.id + "' parent='" + (t.parent || "") + "' start_date='" + t.start_date + "' duration='" + t.duration + "' open='" + !!t.open + "' progress='" + t.progress + "' end_date='" + t.end_date + "'><![CDATA[" + t.text + "]]></task>"
        },
        serialize: function() {
            for (var e = [], i = [], n = t.json.serialize(), a = 0, r = n.data.length; r > a; a++) e.push(this._copyObject(n.data[a]));
            for (var a = 0,
            r = n.links.length; r > a; a++) i.push(this._copyLink(n.links[a]));
            return "<data>" + e.join("") + "<coll_options for='links'>" + i.join("") + "</coll_options></data>"
        }
    },
    t.oldxml = {
        parse: function(e, i) {
            i = t.xml._getXML(e, i, "projects");
            for (var n = {
                collections: {
                    links: []
                }
            },
            a = n.data = [], r = t.ajax.xpath("//task", i), s = 0; s < r.length; s++) {
                a[s] = t.xml._xmlNodeToJSON(r[s]);
                var o = r[s].parentNode;
                "project" == o.tagName ? a[s].parent = "project-" + o.getAttribute("id") : a[s].parent = o.parentNode.getAttribute("id")
            }
            r = t.ajax.xpath("//project", i);
            for (var s = 0; s < r.length; s++) {
                var _ = t.xml._xmlNodeToJSON(r[s], !0);
                _.id = "project-" + _.id,
                a.push(_)
            }
            for (var s = 0; s < a.length; s++) {
                var _ = a[s];
                _.start_date = _.startdate || _.est,
                _.end_date = _.enddate,
                _.text = _.name,
                _.duration = _.duration / 8,
                _.open = 1,
                _.duration || _.end_date || (_.duration = 1),
                _.predecessortasks && n.collections.links.push({
                    target: _.id,
                    source: _.predecessortasks,
                    type: t.config.links.finish_to_start
                })
            }
            return n
        },
        serialize: function() {
            t.message("Serialization to 'old XML' is not implemented")
        }
    },
    t.serverList = function(t, e) {
        return e ? this.serverList[t] = e.slice(0) : this.serverList[t] || (this.serverList[t] = []),
        this.serverList[t]
    },
    t._calendars = {},
    t._calendars.calendarArgumentsHelper = {
        getWorkHoursArguments: function() {
            var e = arguments[0];
            return e = e.date instanceof Date ? {
                date: e
            }: t.mixin({},
            e)
        },
        setWorkTimeArguments: function() {
            return arguments[0]
        },
        unsetWorkTimeArguments: function() {
            return arguments[0]
        },
        isWorkTimeArguments: function() {
            var e = arguments[0];
            return e.date ? (e = t.mixin({},
            e), e.unit = e.unit || t.config.duration_unit, e.task = e.task || null, e.calendar = e.calendar || null) : (e = {},
            e.date = arguments[0], e.unit = arguments[1], e.task = arguments[2], e.calendar = arguments[3]),
            e.unit = e.unit || t.config.duration_unit,
            e
        },
        getClosestWorkTimeArguments: function(e) {
            return e = arguments[0],
            e = e instanceof Date ? {
                date: e
            }: t.mixin({},
            e),
            e.dir = e.dir || "any",
            e.unit = e.unit || t.config.duration_unit,
            e
        },
        getDurationConfig: function(t, e, i, n) {
            return this.start_date = t,
            this.end_date = e,
            this.task = i,
            this.calendar = n,
            this.unit = null,
            this.step = null,
            this
        },
        _getStartEndConfig: function(e) {
            var i, n = t._calendars.calendarArgumentsHelper.getDurationConfig;
            return e instanceof n ? e: (e instanceof Date ? i = new n(arguments[0], arguments[1], arguments[2], arguments[3]) : (i = new n(e.start_date, e.end_date, e.task), e.id && (i.task = e)), i.unit = i.unit || t.config.duration_unit, i.step = i.step || t.config.duration_step, i.start_date = i.start_date || i.start || i.date, i)
        },
        getDurationArguments: function(e, i, n, a) {
            return t._calendars.calendarArgumentsHelper._getStartEndConfig.apply(this, arguments)
        },
        hasDurationArguments: function(e, i, n, a) {
            return t._calendars.calendarArgumentsHelper._getStartEndConfig.apply(this, arguments)
        },
        calculateEndDateArguments: function(e, i, n, a) {
            var r = arguments[0];
            return r = r instanceof Date ? {
                start_date: arguments[0],
                duration: arguments[1],
                unit: arguments[2],
                task: arguments[3],
                calendar: arguments[4]
            }: t.mixin({},
            r),
            r.unit = r.unit || t.config.duration_unit,
            r.step = r.step || t.config.duration_step,
            r
        }
    },
    t._calendars.calendarStrategy = {
        units: ["year", "month", "week", "day", "hour", "minute"],
        _workingUnitsCache: {
            get: function(t, e) {
                var i = -1,
                n = this._cache;
                if (n && n[t]) {
                    var a = n[t],
                    r = e.getTime();
                    void 0 !== a[r] && (i = a[r])
                }
                return i
            },
            put: function(t, e, i) {
                if (!t || !e) return ! 1;
                var n = this._cache,
                a = e.getTime();
                return i = !!i,
                n ? (n[t] || (n[t] = {}), n[t][a] = i, !0) : !1
            },
            clear: function() {
                this._cache = {}
            }
        },
        _getUnitOrder: function(t) {
            for (var e = 0,
            i = this.units.length; i > e; e++) if (this.units[e] == t) return e
        },
        _timestamp: function(t) {
            var e = null;
            return t.day || 0 === t.day ? e = t.day: t.date && (e = Date.UTC(t.date.getFullYear(), t.date.getMonth(), t.date.getDate())),
            e
        },
        _checkIfWorkingUnit: function(t, e, i) {
            return void 0 === i && (i = this._getUnitOrder(e)),
            void 0 === i ? !0 : i && !this._isWorkTime(t, this.units[i - 1], i - 1) ? !1 : this["_is_work_" + e] ? this["_is_work_" + e](t) : !0
        },
        _is_work_day: function(t) {
            var e = this._getWorkHours(t);
            return e instanceof Array ? e.length > 0 : !1
        },
        _is_work_hour: function(t) {
            for (var e = this._getWorkHours(t), i = t.getHours(), n = 0; n < e.length; n += 2) {
                if (void 0 === e[n + 1]) return e[n] == i;
                if (i >= e[n] && i < e[n + 1]) return ! 0
            }
            return ! 1
        },
        _internDatesPull: {},
        _nextDate: function(e, i, n) {
            return t.date.add(e, n, i)
        },
        _getWorkUnitsBetweenGeneric: function(e, i, n, a) {
            var r = new Date(e),
            s = new Date(i);
            a = a || 1;
            var o, _, l = 0,
            d = null,
            c = !1;
            o = t.date[n + "_start"](new Date(r)),
            o.valueOf() != r.valueOf() && (c = !0);
            var h = !1;
            _ = t.date[n + "_start"](new Date(i)),
            _.valueOf() != i.valueOf() && (h = !0);
            for (var u = !1; r.valueOf() < s.valueOf();) d = this._nextDate(r, n, a),
            u = d.valueOf() > s.valueOf(),
            this._isWorkTime(r, n) && ((c || h && u) && (o = t.date[n + "_start"](new Date(r)), _ = t.date.add(o, a, n)), c ? (c = !1, d = this._nextDate(o, n, a), l += (_.valueOf() - r.valueOf()) / (_.valueOf() - o.valueOf())) : h && u ? (h = !1, l += (s.valueOf() - r.valueOf()) / (_.valueOf() - o.valueOf())) : l++),
            r = d;
            return l
        },
        _getHoursPerDay: function(t) {
            for (var e = this._getWorkHours(t), i = 0, n = 0; n < e.length; n += 2) i += e[n + 1] - e[n] || 0;
            return i
        },
        _getWorkHoursForRange: function(t, e) {
            for (var i = 0,
            n = new Date(t), a = new Date(e); n.valueOf() < a.valueOf();) this._isWorkTime(n, "day") && (i += this._getHoursPerDay(n)),
            n = this._nextDate(n, "day", 1);
            return i
        },
        _getWorkUnitsBetweenHours: function(e, i, n, a) {
            var r = new Date(e),
            s = new Date(i);
            a = a || 1;
            var o = new Date(r),
            _ = t.date.add(t.date.day_start(new Date(r)), 1, "day");
            if (s.valueOf() <= _.valueOf()) return this._getWorkUnitsBetweenGeneric(e, i, n, a);
            var l = t.date.day_start(new Date(s)),
            d = s,
            c = this._getWorkUnitsBetweenGeneric(o, _, n, a),
            h = this._getWorkUnitsBetweenGeneric(l, d, n, a),
            u = this._getWorkHoursForRange(_, l);
            return u = u / a + c + h
        },
        _getCalendar: function() {
            return this.worktime
        },
        _setCalendar: function(t) {
            this.worktime = t
        },
        _tryChangeCalendarSettings: function(e) {
            var i = JSON.stringify(this._getCalendar());
            return e(),
            this._isEmptyCalendar(this._getCalendar()) ? (t.assert(!1, "Invalid calendar settings, no worktime available"), this._setCalendar(JSON.parse(i)), this._workingUnitsCache.clear(), !1) : !0
        },
        _isEmptyCalendar: function(t) {
            var e = !1,
            i = [],
            n = !0;
            for (var a in t.dates) e |= !!t.dates[a],
            i.push(a);
            for (var r = [], a = 0; a < i.length; a++) i[a] < 10 && r.push(i[a]);
            r.sort();
            for (var a = 0; 7 > a; a++) r[a] != a && (n = !1);
            return n ? !e: !(e || t.hours)
        },
        getWorkHours: function() {
            var e = t._calendars.calendarArgumentsHelper.getWorkHoursArguments.apply(this, arguments);
            return this._getWorkHours(e.date)
        },
        _getWorkHours: function(t) {
            var e = this._timestamp({
                date: t
            }),
            i = !0,
            n = this._getCalendar();
            return void 0 !== n.dates[e] ? i = n.dates[e] : void 0 !== n.dates[t.getDay()] && (i = n.dates[t.getDay()]),
            i === !0 ? n.hours: i ? i: []
        },
        setWorkTime: function(e) {
            return this._tryChangeCalendarSettings(t.bind(function() {
                var t = void 0 !== e.hours ? e.hours: !0,
                i = this._timestamp(e);
                null !== i ? this._getCalendar().dates[i] = t: this._getCalendar().hours = t,
                this._workingUnitsCache.clear()
            },
            this))
        },
        unsetWorkTime: function(e) {
            return this._tryChangeCalendarSettings(t.bind(function() {
                if (e) {
                    var t = this._timestamp(e);
                    null !== t && delete this._getCalendar().dates[t]
                } else this.reset_calendar();
                this._workingUnitsCache.clear()
            },
            this))
        },
        _isWorkTime: function(t, e, i) {
            var n = this._workingUnitsCache.get(e, t);
            return - 1 == n && (n = this._checkIfWorkingUnit(t, e, i), this._workingUnitsCache.put(e, t, n)),
            n
        },
        isWorkTime: function() {
            var e = t._calendars.calendarArgumentsHelper.isWorkTimeArguments.apply(this, arguments);
            return this._isWorkTime(e.date, e.unit)
        },
        calculateDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.getDurationArguments.apply(this, arguments);
            if (!e.unit) return ! 1;
            var i = 0;
            return i = "hour" == e.unit ? this._getWorkUnitsBetweenHours(e.start_date, e.end_date, e.unit, e.step) : this._getWorkUnitsBetweenGeneric(e.start_date, e.end_date, e.unit, e.step),
            Math.round(i);
        },
        hasDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.getDurationArguments.apply(this, arguments),
            i = e.start_date,
            n = e.end_date,
            a = e.unit,
            r = e.step;
            if (!a) return ! 1;
            var s = new Date(i),
            o = new Date(n);
            for (r = r || 1; s.valueOf() < o.valueOf();) {
                if (this._isWorkTime(s, a)) return ! 0;
                s = this._nextDate(s, a, r)
            }
            return ! 1
        },
        calculateEndDate: function() {
            var e = t._calendars.calendarArgumentsHelper.calculateEndDateArguments.apply(this, arguments),
            i = e.start_date,
            n = e.duration,
            a = e.unit,
            r = e.step,
            s = e.duration >= 0 ? 1 : -1;
            return this._calculateEndDate(i, n, a, r * s);
        },
        _calculateEndDate: function(t, e, i, n) {
            if (!i) return ! 1;
            var a = new Date(t),
            r = 0;
            for (n = n || 1, e = Math.abs(1 * e); e > r;) {
                var s = this._nextDate(a, i, n);
                this._isWorkTime(n > 0 ? new Date(s.valueOf() - 1) : new Date(s.valueOf() + 1), i) && r++,
                a = s
            }
            return a
        },
        getClosestWorkTime: function() {
            var e = t._calendars.calendarArgumentsHelper.getClosestWorkTimeArguments.apply(this, arguments);
            return this._getClosestWorkTime(e)
        },
        _getClosestWorkTime: function(e) {
            if (this._isWorkTime(e.date, e.unit)) return e.date;
            var i = e.unit,
            n = t.date[i + "_start"](e.date),
            a = new Date(n),
            r = new Date(n),
            s = !0,
            o = 3e3,
            _ = 0,
            l = "any" == e.dir || !e.dir,
            d = 1;
            for ("past" == e.dir && (d = -1); ! this._isWorkTime(n, i);) {
                l && (n = s ? a: r, d = -1 * d);
                var c = n.getTimezoneOffset();
                if (n = t.date.add(n, d, i), n = t._correct_dst_change(n, c, d, i), t.date[i + "_start"] && (n = t.date[i + "_start"](n)), l && (s ? a = n: r = n), s = !s, _++, _ > o) return t.assert(!1, "Invalid working time check"),
                !1
            }
            return (n == r || "past" == e.dir) && (n = t.date.add(n, 1, i)),
            n
        }
    },
    t._calendars.disabledWorkTimeCalendar = {
        getWorkHours: function() {
            return [0, 24]
        },
        setWorkTime: function() {
            return ! 0
        },
        unsetWorkTime: function() {
            return ! 0
        },
        isWorkTime: function() {
            return ! 0
        },
        getClosestWorkTime: function(e) {
            var e = t._calendars.calendarArgumentsHelper.getClosestWorkTimeArguments.apply(this, arguments);
            return e.date
        },
        calculateDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.getDurationArguments.apply(this, arguments),
            i = e.start_date,
            n = e.end_date,
            a = e.unit,
            r = e.step;
            return this._calculateDuration(i, n, a, r)
        },
        _calculateDuration: function(e, i, n, a) {
            var r = {
                week: 6048e5,
                day: 864e5,
                hour: 36e5,
                minute: 6e4
            },
            s = 0;
            if (r[n]) s = Math.round((i - e) / (a * r[n]));
            else {
                for (var o = new Date(e), _ = new Date(i); o.valueOf() < _.valueOf();) s += 1,
                o = t.date.add(o, a, n);
                o.valueOf() != i.valueOf() && (s += (_ - o) / (t.date.add(o, a, n) - o))
            }
            return Math.round(s)
        },
        hasDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.getDurationArguments.apply(this, arguments),
            i = e.start_date,
            n = e.end_date,
            a = e.unit;
            e.step;
            return a ? (i = new Date(i), n = new Date(n), i.valueOf() < n.valueOf()) : !1
        },
        calculateEndDate: function() {
            var e = t._calendars.calendarArgumentsHelper.calculateEndDateArguments.apply(this, arguments),
            i = e.start_date,
            n = e.duration,
            a = e.unit,
            r = e.step;
            return t.date.add(i, r * n, a);
        }
    },
    function() {
        var e = function() {
            this._cache = {}
        };
        e.prototype = t._calendars.calendarStrategy._workingUnitsCache,
        t._calendars.CalendarAPICore = function() {
            this._workingUnitsCache = new e
        },
        t._calendars.CalendarAPICore.prototype = t._calendars.calendarStrategy
    } (),
    t._calendars.calendarManager = {
        _calendars: {},
        _getDayHoursForMultiple: function(e, i) {
            for (var n = [], a = !0, r = 0, s = !1, o = t.date.day_start(new Date(i)), _ = 0; 24 > _; _++) s = e.reduce(function(t, e) {
                return t && e._is_work_hour(o)
            },
            !0),
            s ? (a ? (n[r] = _, n[r + 1] = _ + 1, r += 2) : n[r - 1] += 1, a = !1) : a || (a = !0),
            o = t.date.add(o, 1, "hour");
            return n.length || (n = !1),
            n
        },
        mergeCalendars: function() {
            var e, i = this.createCalendar(),
            n = [],
            a = Array.prototype.slice.call(arguments, 0);
            i.worktime.hours = [0, 24],
            i.worktime.dates = {};
            var r = t.date.day_start(new Date(2592e5));
            for (e = 0; 7 > e; e++) n = this._getDayHoursForMultiple(a, r),
            i.worktime.dates[e] = n,
            r = t.date.add(r, 1, "day");
            for (var s = 0; s < a.length; s++) for (var o in a[s].worktime.dates) + o > 1e4 && (n = this._getDayHoursForMultiple(a, new Date( + o)), i.worktime.dates[o] = n);
            return i;
        },
        _convertWorktimeSettings: function(t) {
            var e = t.days;
            if (e) {
                t.dates = t.dates || {};
                for (var i = 0; i < e.length; i++) t.dates[i] = e[i],
                e[i] instanceof Array || (t.dates[i] = !!e[i]);
                delete t.days
            }
            return t
        },
        createCalendar: function(e) {
            var i;
            e || (e = {}),
            i = e.worktime ? t.copy(e.worktime) : t.copy(e);
            var n = t.copy(this.defaults.fulltime.worktime);
            t.mixin(i, n);
            var a = t.uid(),
            r = {
                id: a + "",
                worktime: this._convertWorktimeSettings(i)
            },
            s = new t._calendars.CalendarAPICore;
            return t.mixin(s, r),
            s._tryChangeCalendarSettings(function() {}) ? s: null;
        },
        getCalendar: function(t) {
            return t = t || "global",
            this.createDefaultCalendars(),
            this._calendars[t]
        },
        getCalendars: function() {
            var t = [];
            for (var e in this._calendars) t.push(this.getCalendar(e));
            return t
        },
        getTaskCalendar: function(e) {
            if (!e) return this.getCalendar();
            if (e[t.config.calendar_property]) return this.getCalendar(e[t.config.calendar_property]);
            if (t.config.resource_calendars) for (var i in t.config.resource_calendars) {
                var n = t.config.resource_calendars[i];
                if (e[i]) {
                    var a = n[e[i]];
                    if (a) return this.getCalendar(a);
                }
            }
            return this.getCalendar()
        },
        addCalendar: function(e) {
            if (! (e instanceof t._calendars.CalendarAPICore)) {
                var i = e.id;
                e = this.createCalendar(e),
                e.id = i
            }
            return e.id = e.id || t.uid(),
            this._calendars[e.id] = e,
            t.config.worktimes || (t.config.worktimes = {}),
            t.config.worktimes[e.id] = e.worktime,
            e.id
        },
        deleteCalendar: function(e) {
            e && this._calendars[e.id] && delete this._calendars[e.id],
            t.config.worktimes && t.config.worktimes[e.id] && delete t.config.worktimes[e.id]
        },
        restoreConfigCalendars: function(t) {
            for (var e in t) if (!this._calendars[e]) {
                var i = t[e],
                n = this.createCalendar(i);
                n.id = e,
                this.addCalendar(n)
            }
        },
        defaults: {
            global: {
                id: "global",
                worktime: {
                    hours: [8, 17],
                    days: [0, 1, 1, 1, 1, 1, 0]
                }
            },
            fulltime: {
                id: "fulltime",
                worktime: {
                    hours: [0, 24],
                    days: [1, 1, 1, 1, 1, 1, 1]
                }
            }
        },
        createDefaultCalendars: function() {
            this.restoreConfigCalendars(this.defaults),
            this.restoreConfigCalendars(t.config.worktimes)
        }
    },
    t._timeCalculator = {
        _getCalendar: function(e) {
            var i;
            if (t.config.work_time) {
                var n = t._calendars.calendarManager;
                e.task ? i = n.getTaskCalendar(e.task) : e.id ? i = n.getTaskCalendar(e) : e.calendar && (i = e.calendar),
                i || (i = n.getTaskCalendar())
            } else i = t._calendars.disabledWorkTimeCalendar;
            return i
        },
        getWorkHours: function(e) {
            e = t._calendars.calendarArgumentsHelper.getWorkHoursArguments.apply(this, arguments);
            var i = this._getCalendar(e);
            return i.getWorkHours(e.date)
        },
        setWorkTime: function(e, i) {
            return e = t._calendars.calendarArgumentsHelper.setWorkTimeArguments.apply(this, arguments),
            i || (i = t._calendars.calendarManager.getCalendar()),
            i.setWorkTime(e)
        },
        unsetWorkTime: function(e, i) {
            return e = t._calendars.calendarArgumentsHelper.unsetWorkTimeArguments.apply(this, arguments),
            i || (i = t._calendars.calendarManager.getCalendar()),
            i.unsetWorkTime(e)
        },
        isWorkTime: function(e, i, n, a) {
            var r = t._calendars.calendarArgumentsHelper.isWorkTimeArguments.apply(this, arguments);
            return a = this._getCalendar(r),
            a.isWorkTime(r)
        },
        getClosestWorkTime: function(e) {
            e = t._calendars.calendarArgumentsHelper.getClosestWorkTimeArguments.apply(this, arguments);
            var i = this._getCalendar(e);
            return i.getClosestWorkTime(e)
        },
        calculateDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.getDurationArguments.apply(this, arguments),
            i = this._getCalendar(e);
            return i.calculateDuration(e)
        },
        hasDuration: function() {
            var e = t._calendars.calendarArgumentsHelper.hasDurationArguments.apply(this, arguments),
            i = this._getCalendar(e);
            return i.hasDuration(e)
        },
        calculateEndDate: function(e) {
            var e = t._calendars.calendarArgumentsHelper.calculateEndDateArguments.apply(this, arguments),
            i = this._getCalendar(e);
            return i.calculateEndDate(e)
        }
    },
    function() {
        var e = t._calendars.calendarManager;
        t.createCalendar = t.bind(e.createCalendar, e),
        t.addCalendar = t.bind(e.addCalendar, e),
        t.getCalendar = t.bind(e.getCalendar, e),
        t.getCalendars = t.bind(e.getCalendars, e),
        t.getTaskCalendar = t.bind(e.getTaskCalendar, e),
        t.deleteCalendar = t.bind(e.deleteCalendar, e)
    } (),
    t.getTask = function(e) {
        t.assert(e, "Invalid argument for gantt.getTask");
        var i = this._pull[e];
        return t.assert(i, "Task not found id=" + e),
        i
    },
    t.getTaskByTime = function(t, e) {
        var i = this._pull,
        n = [];
        if (t || e) {
            t = +t || -(1 / 0),
            e = +e || 1 / 0;
            for (var a in i) {
                var r = i[a]; + r.start_date < e && +r.end_date > t && n.push(r)
            }
        } else for (var a in i) n.push(i[a]);
        return n
    },
    t.isTaskExists = function(e) {
        return t.defined(this._pull[e]);
    },
    t.isUnscheduledTask = function(t) {
        return !! t.unscheduled || !t.start_date
    },
    t._isAllowedUnscheduledTask = function(e) {
        return ! (!e.unscheduled || !t.config.show_unscheduled)
    },
    t.isTaskVisible = function(e) {
        if (!this._pull[e]) return ! 1;
        var i = this._pull[e];
        return ( + i.start_date < +this._max_date && +i.end_date > +this._min_date || t._isAllowedUnscheduledTask(i)) && void 0 !== this._order_search[e] ? !0 : !1
    },
    t.updateTask = function(e, i) {
        return t.defined(i) || (i = this.getTask(e)),
        this.callEvent("onBeforeTaskUpdate", [e, i]) === !1 ? !1 : (this._pull[i.id] = i, this._is_parent_sync(i) || this._resync_parent(i), this._isAllowedUnscheduledTask(i) && (this._init_task(i), this._sync_links()), this._update_parents(i.id), this.refreshTask(i.id), this._sync_order(!0), this.callEvent("onAfterTaskUpdate", [e, i]), void this._adjust_scales())
    },
    t._add_branch = function(t, e) {
        var i = this.getParent(t);
        this.hasChild(i) || (this._branches[i] = []);
        for (var n = this.getChildren(i), a = !1, r = 0, s = n.length; s > r; r++) if (n[r] == t.id) {
            a = !0;
            break
        }
        a || (1 * e == e ? n.splice(e, 0, t.id) : n.push(t.id)),
        this._sync_parent(t);
    },
    t._move_branch = function(t, e, i) {
        this.setParent(t, i),
        this._sync_parent(t),
        this._replace_branch_child(e, t.id),
        this.isTaskExists(i) || i == this.config.root_id ? this._add_branch(t) : delete this._branches[t.id],
        t.$level = this.calculateTaskLevel(t),
        this.eachTask(function(t) {
            t.$level = this.calculateTaskLevel(t)
        },
        t.id),
        this._sync_order()
    },
    t._resync_parent = function(t) {
        this._move_branch(t, t.$rendered_parent, this.getParent(t))
    },
    t._sync_parent = function(t) {
        t.$rendered_parent = this.getParent(t)
    },
    t._is_parent_sync = function(t) {
        return t.$rendered_parent == this.getParent(t)
    },
    t._replace_branch_child = function(t, e, i) {
        var n = this.getChildren(t);
        if (n) {
            for (var a = [], r = 0; r < n.length; r++) n[r] != e ? a.push(n[r]) : i && a.push(i);
            this._branches[t] = a
        }
        this._sync_order()
    },
    t.addTask = function(e, i, n) {
        return t.defined(e.id) || (e.id = t.uid()),
        t.defined(i) || (i = this.getParent(e) || 0),
        this.isTaskExists(i) || (i = 0),
        this.setParent(e, i),
        e = this._init_task(e),
        this.callEvent("onBeforeTaskAdd", [e.id, e]) === !1 ? !1 : (this._pull[e.id] = e, this._add_branch(e, n), this._sync_order(!0), this.callEvent("onAfterTaskAdd", [e.id, e]), this.refreshData(), this._adjust_scales(), e.id)
    },
    t._default_task_date = function(e, i) {
        var n = i && i != this.config.root_id ? this.getTask(i) : !1,
        a = "";
        if (n) a = n.start_date;
        else {
            var r = this._order[0];
            a = r ? this.getTask(r).start_date ? this.getTask(r).start_date: this.getTask(r).end_date ? this.calculateEndDate({
                start_date: this.getTask(r).end_date,
                duration: -this.config.duration_step
            }) : "": this.config.start_date || this.getState().min_date
        }
        return t.assert(a, "Invalid dates"),
        new Date(a);
    },
    t._set_default_task_timing = function(e) {
        e.start_date = e.start_date || t._default_task_date(e, this.getParent(e)),
        e.duration = e.duration || this.config.duration_step,
        e.end_date = e.end_date || this.calculateEndDate(e)
    },
    t.createTask = function(e, i, n) {
        if (e = e || {},
        t.defined(e.id) || (e.id = t.uid()), e.start_date || (e.start_date = t._default_task_date(e, i)), void 0 === e.text && (e.text = t.locale.labels.new_task), void 0 === e.duration && (e.duration = 1), i) {
            this.setParent(e, i);
            var a = this.getTask(i);
            a.$open = !0
        }
        return this.callEvent("onTaskCreated", [e]) ? (this.config.details_on_create ? (e.$new = !0, this._pull[e.id] = this._init_task(e), this._add_branch(e, n), e.$level = this.calculateTaskLevel(e), this.selectTask(e.id), this.refreshData(), this.showLightbox(e.id)) : this.addTask(e, i, n) && (this.showTask(e.id), this.selectTask(e.id)), e.id) : null
    },
    t.deleteTask = function(t) {
        return this._deleteTask(t)
    },
    t._getChildLinks = function(t) {
        var e = this.getTask(t);
        if (!e) return [];
        for (var i = e.$source.concat(e.$target), n = this.getChildren(e.id), a = 0; a < n.length; a++) i = i.concat(this._getChildLinks(n[a]));
        for (var r = {},
        a = 0; a < i.length; a++) r[i[a]] = !0;
        i = [];
        for (var a in r) this.isLinkExists(a) && i.push(a);
        return i
    },
    t._getTaskTree = function(t) {
        var e = this.getTask(t);
        if (!e) return [];
        for (var i = [], n = this.getChildren(e.id), a = 0; a < n.length; a++) i.push(n[a]),
        i = i.concat(this._getTaskTree(n[a]));
        return i
    },
    t._deleteRelatedLinks = function(t, e) {
        var i = this._dp && !e && this.config.cascade_delete,
        n = "",
        a = i ? "off" != this._dp.updateMode: !1;
        i && (n = this._dp.updateMode, this._dp.setUpdateMode("off"));
        for (var r = 0; r < t.length; r++) i && (this._dp.setGanttMode("links"), this._dp.setUpdated(t[r], !0, "deleted")),
        this._deleteLink(t[r], !0);
        i && (this._dp.setUpdateMode(n), a && this._dp.sendAllData())
    },
    t._deleteRelatedTasks = function(t, e) {
        var i = this._dp && !e && this.config.cascade_delete,
        n = "";
        i && (n = this._dp.updateMode, this._dp.setGanttMode("tasks"), this._dp.setUpdateMode("off"));
        for (var a = this._getTaskTree(t), r = 0; r < a.length; r++) {
            var s = a[r];
            this._unset_task(s),
            i && this._dp.setUpdated(s, !0, "deleted")
        }
        i && this._dp.setUpdateMode(n)
    },
    t._unset_task = function(t) {
        var e = this.getTask(t);
        this._update_flags(t, null),
        delete this._pull[t],
        this._move_branch(e, this.getParent(e), null)
    },
    t._deleteTask = function(e, i) {
        var n = this.getTask(e);
        if (!i && this.callEvent("onBeforeTaskDelete", [e, n]) === !1) return ! 1;
        var a = t._getChildLinks(e);
        return this._deleteRelatedTasks(e, i),
        this._deleteRelatedLinks(a, i),
        this._unset_task(e),
        i || (this._sync_order(!0), this.callEvent("onAfterTaskDelete", [e, n]), this.refreshData()),
        !0
    },
    t.clearAll = function() {
        this._clear_data(),
        this.callEvent("onClear", []),
        this.refreshData()
    },
    t._clear_data = function() {
        this._pull = {},
        this._branches = {},
        this._order = [],
        this._order_full = [],
        this._lpull = {},
        this._links = [],
        this._update_flags(),
        this.userdata = {}
    },
    t._update_flags = function(t, e) {
        void 0 === t ? (this._lightbox_id = this._selected_task = null, this._tasks_dnd.drag && (this._tasks_dnd.drag.id = null)) : (this._lightbox_id == t && (this._lightbox_id = e), this._selected_task == t && (this._selected_task = e), this._tasks_dnd.drag && this._tasks_dnd.drag.id == t && (this._tasks_dnd.drag.id = e))
    },
    t.changeTaskId = function(t, e) {
        var i = this._pull[e] = this._pull[t];
        this._pull[e].id = e,
        delete this._pull[t],
        this._update_flags(t, e),
        this._replace_branch_child(this.getParent(i), t, e);
        for (var n in this._pull) {
            var a = this._pull[n];
            this.getParent(a) == t && (this.setParent(a, e), this._resync_parent(a))
        }
        for (var r = this._get_task_links(i), s = 0; s < r.length; s++) {
            var o = this.getLink(r[s]);
            o.source == t && (o.source = e),
            o.target == t && (o.target = e)
        }
        this.callEvent("onTaskIdChange", [t, e])
    },
    t._get_task_links = function(t) {
        var e = [];
        return t.$source && (e = e.concat(t.$source)),
        t.$target && (e = e.concat(t.$target)),
        e
    },
    t._get_duration_unit = function() {
        return 1e3 * t._get_line(this.config.duration_unit) || this.config.duration_unit
    },
    t._get_safe_type = function(t) {
        return "task"
    },
    t._get_type_name = function(t) {
        for (var e in this.config.types) if (this.config.types[e] == t) return e;
        return "task"
    },
    t.getWorkHours = function(e) {
        return t._timeCalculator.getWorkHours(e)
    },
    t.setWorkTime = function(e) {
        return t._timeCalculator.setWorkTime(e)
    },
    t.unsetWorkTime = function(e) {
        t._timeCalculator.unsetWorkTime(e)
    },
    t.isWorkTime = function(e, i, n) {
        return t._timeCalculator.isWorkTime(e, i, n);
    },
    t.correctTaskWorkTime = function(e) {
        t.config.work_time && t.config.correct_work_time && (t.isWorkTime(e.start_date, e) ? t.isWorkTime(new Date( + e.end_date - 1), e) || (e.end_date = t.calculateEndDate(e)) : (e.start_date = t.getClosestWorkTime({
            date: e.start_date,
            dir: "future",
            task: e
        }), e.end_date = t.calculateEndDate(e)))
    },
    t.getClosestWorkTime = function(e) {
        return t._timeCalculator.getClosestWorkTime(e)
    },
    t.calculateDuration = function(e, i, n) {
        return t._timeCalculator.calculateDuration(e, i, n)
    },
    t._hasDuration = function(e, i, n) {
        return t._timeCalculator.hasDuration(e, i, n)
    },
    t.calculateEndDate = function(e, i, n, a) {
        return t._timeCalculator.calculateEndDate(e, i, n, a)
    },
    t._init_task = function(e) {
        t.defined(e.id) || (e.id = t.uid()),
        e.start_date && (e.start_date = t.date.parseDate(e.start_date, "xml_date")),
        e.end_date && (e.end_date = t.date.parseDate(e.end_date, "xml_date"));
        var i = null;
        return (e.duration || 0 === e.duration) && (e.duration = i = 1 * e.duration),
        i && (e.start_date && !e.end_date ? e.end_date = this.calculateEndDate(e) : !e.start_date && e.end_date && (e.start_date = this.calculateEndDate({
            start_date: e.end_date,
            duration: -e.duration,
            task: e
        }))),
        this._isAllowedUnscheduledTask(e) && this._set_default_task_timing(e),
        t._init_task_timing(e),
        e.start_date && e.end_date && t.correctTaskWorkTime(e),
        e.$source = [],
        e.$target = [],
        void 0 === e.parent && this.setParent(e, this.config.root_id),
        t.defined(e.$open) || (e.$open = t.defined(e.open) ? e.open: this.config.open_tree_initially),
        e.$level = this.calculateTaskLevel(e),
        e
    },
    t._get_task_timing_mode = function(t, e) {
        var i = this._get_safe_type(t.type),
        n = {
            type: i,
            $no_start: !1,
            $no_end: !1
        };
        return e || i != t.$rendered_type ? (i == this.config.types.project ? n.$no_end = n.$no_start = !0 : i != this.config.types.milestone && (n.$no_end = !(t.end_date || t.duration), n.$no_start = !t.start_date, this._isAllowedUnscheduledTask(t) && (n.$no_end = n.$no_start = !1)), n) : (n.$no_start = t.$no_start, n.$no_end = t.$no_end, n)
    },
    t._init_task_timing = function(e) {
        var i = t._get_task_timing_mode(e, !0),
        n = e.$rendered_type != i.type,
        a = i.type;
        n && (e.$no_start = i.$no_start, e.$no_end = i.$no_end, e.$rendered_type = i.type),
        n && a != this.config.types.milestone && a == this.config.types.project && this._set_default_task_timing(e),
        a == this.config.types.milestone && (e.end_date = e.start_date),
        e.start_date && e.end_date && (e.duration = this.calculateDuration(e)),
        e.duration = e.duration || 0
    },
    t._is_flex_task = function(e) {
        var i = t._get_task_timing_mode(e);
        return ! (!i.$no_end && !i.$no_start)
    },
    t.resetProjectDates = function(t) {
        var e = this._get_task_timing_mode(t);
        if (e.$no_end || e.$no_start) {
            var i = this.getSubtaskDates(t.id);
            this._assign_project_dates(t, i.start_date, i.end_date)
        }
    },
    t.getSubtaskDates = function(e) {
        var i = null,
        n = null,
        a = void 0 !== e ? e: t.config.root_id;
        return this.eachTask(function(e) {
            this._get_safe_type(e.type) == t.config.types.project || this.isUnscheduledTask(e) || (e.start_date && !e.$no_start && (!i || i > e.start_date.valueOf()) && (i = e.start_date.valueOf()), e.end_date && !e.$no_end && (!n || n < e.end_date.valueOf()) && (n = e.end_date.valueOf()))
        },
        a),
        {
            start_date: i ? new Date(i) : null,
            end_date: n ? new Date(n) : null
        }
    },
    t._assign_project_dates = function(t, e, i) {
        var n = this._get_task_timing_mode(t);
        n.$no_start && (e && e != 1 / 0 ? t.start_date = new Date(e) : t.start_date = this._default_task_date(t, this.getParent(t))),
        n.$no_end && (i && i != -(1 / 0) ? t.end_date = new Date(i) : t.end_date = this.calculateEndDate({
            start_date: t.start_date,
            duration: this.config.duration_step,
            task: t
        })),
        (n.$no_start || n.$no_end) && this._init_task_timing(t)
    },
    t._update_parents = function(e, i) {
        if (e) {
            var n = this.getTask(e),
            a = this.getParent(n),
            r = this._get_task_timing_mode(n),
            s = !0;
            if (r.$no_start || r.$no_end) {
                var o = n.start_date.valueOf(),
                _ = n.end_date.valueOf();
                t.resetProjectDates(n),
                o == n.start_date.valueOf() && _ == n.end_date.valueOf() && (s = !1),
                s && !i && this.refreshTask(n.id, !0);
            }
            s && a && this.isTaskExists(a) && this._update_parents(a, i)
        }
    },
    t.isChildOf = function(t, e) {
        if (!this.isTaskExists(t)) return ! 1;
        if (e === this.config.root_id) return this.isTaskExists(t);
        for (var i = this.getTask(t), n = this.getParent(t); i && this.isTaskExists(n);) {
            if (i = this.getTask(n), i && i.id == e) return ! 0;
            n = this.getParent(i)
        }
        return ! 1
    },
    t.roundDate = function(e) {
        e instanceof Date && (e = {
            date: e,
            unit: t._tasks.unit,
            step: t._tasks.step
        });
        var i, n, a, r = e.date,
        s = e.step,
        o = e.unit;
        if (o == t._tasks.unit && s == t._tasks.step && +r >= +t._min_date && +r <= +t._max_date) a = Math.floor(t._day_index_by_date(r)),
        t._tasks.trace_x[a] || (a -= 1),
        n = new Date(t._tasks.trace_x[a]),
        i = new Date(n),
        i = t._tasks.trace_x[a + 1] ? new Date(t._tasks.trace_x[a + 1]) : t.date.add(n, s, o);
        else {
            for (a = Math.floor(t._day_index_by_date(r)), i = t.date[o + "_start"](new Date(this._min_date)), t._tasks.trace_x[a] && (i = t.date[o + "_start"](t._tasks.trace_x[a])); + r > +i;) {
                i = t.date[o + "_start"](t.date.add(i, s, o));
                var _ = i.getTimezoneOffset();
                i = t._correct_dst_change(i, _, i, o),
                t.date[o + "_start"] && (i = t.date[o + "_start"](i))
            }
            n = t.date.add(i, -1 * s, o)
        }
        return e.dir && "future" == e.dir ? i: e.dir && "past" == e.dir ? n: Math.abs(r - n) < Math.abs(i - r) ? n: i;
    },
    t.attachEvent("onBeforeTaskUpdate",
    function(e, i) {
        return t._init_task_timing(i),
        !0
    }),
    t.attachEvent("onBeforeTaskAdd",
    function(e, i) {
        return t._init_task_timing(i),
        !0
    }),
    t.calculateTaskLevel = function(t) {
        var e = 0;
        return this._eachParent(function() {
            e++
        },
        t),
        e
    },
    t.sort = function(t, e, i, n) {
        var a = !n;
        this.isTaskExists(i) || (i = this.config.root_id),
        t || (t = "order");
        var r = "string" == typeof t ?
        function(e, i) {
            if (e[t] == i[t]) return 0;
            var n = e[t] > i[t];
            return n ? 1 : -1
        }: t;
        if (e) {
            var s = r;
            r = function(t, e) {
                return s(e, t)
            }
        }
        var o = this.getChildren(i);
        if (o) {
            for (var _ = [], l = o.length - 1; l >= 0; l--) _[l] = this._pull[o[l]];
            _.sort(r);
            for (var l = 0; l < _.length; l++) o[l] = _[l].id,
            this.sort(t, e, o[l], !0)
        }
        a && this.render()
    },
    t.getNext = function(t) {
        for (var e = 0; e < this._order.length - 1; e++) if (this._order[e] == t) return this._order[e + 1];
        return null
    },
    t.getPrev = function(t) {
        for (var e = 1; e < this._order.length; e++) if (this._order[e] == t) return this._order[e - 1];
        return null
    },
    t._get_parent_id = function(t) {
        var e = this.config.root_id;
        return t && (e = t.parent),
        e
    },
    t.getParent = function(e) {
        var i = null;
        return i = void 0 !== e.id ? e: t.getTask(e),
        this._get_parent_id(i)
    },
    t.setParent = function(t, e) {
        t.parent = e
    },
    t.getSiblings = function(t) {
        if (!this.isTaskExists(t)) return [];
        var e = this.getParent(t);
        return this.getChildren(e)
    },
    t.getNextSibling = function(t) {
        for (var e = this.getSiblings(t), i = 0, n = e.length; n > i; i++) if (e[i] == t) return e[i + 1] || null;
        return null
    },
    t.getPrevSibling = function(t) {
        for (var e = this.getSiblings(t), i = 0, n = e.length; n > i; i++) if (e[i] == t) return e[i - 1] || null;
        return null
    },
    t._dp_init = function(e) {
        function i(i) {
            for (var n = e.updatedRows.slice(), a = !1, r = 0; r < n.length && !e._in_progress[i]; r++) n[r] == i && ("inserted" == t.getUserData(i, "!nativeeditor_status") && (a = !0), e.setUpdated(i, !1));
            return a
        }
        e.setTransactionMode("POST", !0),
        e.serverProcessor += ( - 1 != e.serverProcessor.indexOf("?") ? "&": "?") + "editing=true",
        e._serverProcessor = e.serverProcessor,
        e.styles = {
            updated: "gantt_updated",
            order: "gantt_updated",
            inserted: "gantt_inserted",
            deleted: "gantt_deleted",
            invalid: "gantt_invalid",
            error: "gantt_error",
            clear: ""
        },
        e._methods = ["_row_style", "setCellTextStyle", "_change_id", "_delete_task"],
        e.setGanttMode = function(t) {
            var i = e.modes || {};
            e._ganttMode && (i[e._ganttMode] = {
                _in_progress: e._in_progress,
                _invalid: e._invalid,
                updatedRows: e.updatedRows
            });
            var n = i[t];
            n || (n = i[t] = {
                _in_progress: {},
                _invalid: {},
                updatedRows: []
            }),
            e._in_progress = n._in_progress,
            e._invalid = n._invalid,
            e.updatedRows = n.updatedRows,
            e.modes = i,
            e._ganttMode = t
        },
        this._sendTaskOrder = function(t, i) {
            i.$drop_target && (e.setGanttMode("tasks"), this.getTask(t).target = i.$drop_target, e.setUpdated(t, !0, "order"), delete this.getTask(t).$drop_target);
        },
        this.attachEvent("onAfterTaskAdd",
        function(t, i) {
            e.setGanttMode("tasks"),
            e.setUpdated(t, !0, "inserted")
        }),
        this.attachEvent("onAfterTaskUpdate",
        function(i, n) {
            e.setGanttMode("tasks"),
            e.setUpdated(i, !0),
            t._sendTaskOrder(i, n)
        }),
        this.attachEvent("onAfterTaskDelete",
        function(t, n) {
            e.setGanttMode("tasks");
            var a = !i(t);
            a && (e.setUpdated(t, !0, "deleted"), "off" == e.updateMode || e._tSend || e.sendAllData())
        }),
        this.attachEvent("onAfterLinkUpdate",
        function(t, i) {
            e.setGanttMode("links"),
            e.setUpdated(t, !0)
        }),
        this.attachEvent("onAfterLinkAdd",
        function(t, i) {
            e.setGanttMode("links"),
            e.setUpdated(t, !0, "inserted")
        }),
        this.attachEvent("onAfterLinkDelete",
        function(t, n) {
            e.setGanttMode("links");
            var a = !i(t);
            a && e.setUpdated(t, !0, "deleted")
        }),
        this.attachEvent("onRowDragEnd",
        function(e, i) {
            t._sendTaskOrder(e, t.getTask(e))
        });
        var n = null,
        a = null;
        this.attachEvent("onTaskIdChange",
        function(i, r) {
            if (e._waitMode) {
                var s = t.getChildren(r);
                if (s.length) {
                    n = n || {};
                    for (var o = 0; o < s.length; o++) {
                        var _ = this.getTask(s[o]);
                        n[_.id] = _
                    }
                }
                var l = this.getTask(r),
                d = this._get_task_links(l);
                if (d.length) {
                    a = a || {};
                    for (var o = 0; o < d.length; o++) {
                        var c = this.getLink(d[o]);
                        a[c.id] = c
                    }
                }
            }
        }),
        e.attachEvent("onAfterUpdateFinish",
        function() { (n || a) && (t.batchUpdate(function() {
                for (var e in n) t.updateTask(n[e].id);
                for (var e in a) t.updateLink(a[e].id);
                n = null,
                a = null
            }), n ? t._dp.setGanttMode("tasks") : t._dp.setGanttMode("links"))
        }),
        e.attachEvent("onBeforeDataSending",
        function() {
            var e = this._serverProcessor;
            if ("REST" == this._tMode) {
                var i = this._ganttMode.substr(0, this._ganttMode.length - 1);
                e = e.substring(0, e.indexOf("?") > -1 ? e.indexOf("?") : e.length),
                this.serverProcessor = e + ("/" == e.slice( - 1) ? "": "/") + i
            } else this.serverProcessor = e + t._urlSeparator(e) + "gantt_mode=" + this._ganttMode;
            return ! 0
        }),
        this._init_dp_live_update_hooks(e);
        var r = e.afterUpdate;
        e.afterUpdate = function() {
            var t;
            t = 3 == arguments.length ? arguments[1] : arguments[4];
            var i = e._ganttMode,
            n = t.filePath;
            i = "REST" != this._tMode ? -1 != n.indexOf("gantt_mode=links") ? "links": "tasks": n.indexOf("/link") > n.indexOf("/task") ? "links": "tasks",
            e.setGanttMode(i);
            var a = r.apply(e, arguments);
            return e.setGanttMode(i),
            a
        },
        e._getRowData = t.bind(function(i, n) {
            var a;
            a = "tasks" == e._ganttMode ? this.isTaskExists(i) ? this.getTask(i) : {
                id: i
            }: this.isLinkExists(i) ? this.getLink(i) : {
                id: i
            },
            a = t.copy(a);
            var r = {};
            for (var s in a) if ("$" != s.substr(0, 1)) {
                var o = a[s];
                o instanceof Date ? r[s] = this.templates.xml_format(o) : null === o ? r[s] = "": r[s] = o
            }
            var _ = this._get_task_timing_mode(a);
            return _.$no_start && (a.start_date = "", a.duration = ""),
            _.$no_end && (a.end_date = "", a.duration = ""),
            r[e.action_param] = this.getUserData(i, e.action_param),
            r
        },
        this),
        this._change_id = t.bind(function(t, i) {
            "tasks" != e._ganttMode ? this.changeLinkId(t, i) : this.changeTaskId(t, i)
        },
        this),
        this._row_style = function(i, n) {
            if ("tasks" == e._ganttMode && t.isTaskExists(i)) {
                var a = t.getTask(i);
                a.$dataprocessor_class = n,
                t.refreshTask(i)
            }
        },
        this._delete_task = function(t, e) {},
        this._dp = e
    },
    t.getUserData = function(t, e) {
        return this.userdata || (this.userdata = {}),
        this.userdata[t] && this.userdata[t][e] ? this.userdata[t][e] : ""
    },
    t.setUserData = function(t, e, i) {
        this.userdata || (this.userdata = {}),
        this.userdata[t] || (this.userdata[t] = {}),
        this.userdata[t][e] = i;
    },
    t._init_link = function(e) {
        return t.defined(e.id) || (e.id = t.uid()),
        e
    },
    t._sync_links = function() {
        for (var t = null,
        e = 0,
        i = this._order_full.length; i > e; e++) t = this._pull[this._order_full[e]],
        t.$source = [],
        t.$target = [];
        this._links = [];
        for (var n in this._lpull) {
            var a = this._lpull[n];
            this._links.push(a),
            this._pull[a.source] && this._pull[a.source].$source.push(n),
            this._pull[a.target] && this._pull[a.target].$target.push(n)
        }
    },
    t.getLink = function(e) {
        return t.assert(this._lpull[e], "Link doesn't exist"),
        this._lpull[e]
    },
    t._get_linked_task = function(e, i) {
        var n = null,
        a = i ? e.target: e.source;
        t.isTaskExists(a) && (n = t.getTask(a));
        var r = i ? "target": "source";
        return t.assert(n, "Link " + r + " not found. Task id=" + a + ", link id=" + e.id),
        n
    },
    t._get_link_target = function(e) {
        return t._get_linked_task(e, !0)
    },
    t._get_link_source = function(e) {
        return t._get_linked_task(e, !1)
    },
    t.getLinks = function() {
        var e = [];
        for (var i in t._lpull) e.push(t._lpull[i]);
        return e
    },
    t.isLinkExists = function(e) {
        return t.defined(this._lpull[e])
    },
    t.addLink = function(t) {
        return t = this._init_link(t),
        this.callEvent("onBeforeLinkAdd", [t.id, t]) === !1 ? !1 : (this._lpull[t.id] = t, this._sync_links(), this._render_link(t.id), this.callEvent("onAfterLinkAdd", [t.id, t]), t.id)
    },
    t.updateLink = function(e, i) {
        return t.defined(i) || (i = this.getLink(e)),
        this.callEvent("onBeforeLinkUpdate", [e, i]) === !1 ? !1 : (this._lpull[e] = i, this._sync_links(), this._render_link(e), this.callEvent("onAfterLinkUpdate", [e, i]), !0)
    },
    t.deleteLink = function(t) {
        return this._deleteLink(t)
    },
    t._deleteLink = function(t, e) {
        var i = this.getLink(t);
        return e || this.callEvent("onBeforeLinkDelete", [t, i]) !== !1 ? (delete this._lpull[t], this._sync_links(), this.refreshLink(t), e || this.callEvent("onAfterLinkDelete", [t, i]), !0) : !1
    },
    t.changeLinkId = function(t, e) {
        this._lpull[t] && (this._lpull[e] = this._lpull[t], this._lpull[e].id = e, delete this._lpull[t], this._sync_links(), this.callEvent("onLinkIdChange", [t, e]))
    },
    t.getChildren = function(e) {
        return t.defined(this._branches[e]) ? this._branches[e] : []
    },
    t.hasChild = function(e) {
        return t.defined(this._branches[e]) && this._branches[e].length
    },
    t.refreshData = function() {
        this._render_data()
    },
    t._isTask = function(e) {
        var i = this._get_task_timing_mode(e);
        return ! (e.type && e.type == t.config.types.project || i.$no_start || i.$no_end)
    },
    t._isProject = function(t) {
        return ! this._isTask(t)
    },
    t._configure = function(t, e, i) {
        for (var n in e)("undefined" == typeof t[n] || i) && (t[n] = e[n])
    },
    t._init_skin = function() {
        t._get_skin(!1),
        t._init_skin = function() {}
    },
    t._get_skin = function(e) {
        var i = t.skin;
        if (!i || e) for (var n = document.getElementsByTagName("link"), a = 0; a < n.length; a++) {
            var r = n[a].href.match("dhtmlxgantt_([a-z_]+).css");
            if (r && (t.skins[r[1]] || !i)) {
                i = r[1];
                break
            }
        }
        t.skin = i || "terrace";
        var s = t.skins[t.skin] || t.skins.terrace;
        this._configure(t.config, s.config, e);
        var o = t.getGridColumns();
        o[1] && "undefined" == typeof o[1].width && (o[1].width = s._second_column_width),
        o[2] && "undefined" == typeof o[2].width && (o[2].width = s._third_column_width),
        s._lightbox_template && (t._lightbox_template = s._lightbox_template),
        t.resetLightbox()
    },
    t.resetSkin = function() {
        this.skin = "",
        this._get_skin(!0)
    },
    t.skins = {},
    t._lightbox_methods = {},
    t._lightbox_template = "<div class='gantt_cal_ltitle'><span class='gantt_mark'>&nbsp;</span><span class='gantt_time'></span><span class='gantt_title'></span></div><div class='gantt_cal_larea'></div>",
    t.showLightbox = function(e) {
        if (e && !t._is_readonly(this.getTask(e)) && this.callEvent("onBeforeLightbox", [e])) {
            var i = this.getTask(e),
            n = this.getLightbox(this._get_safe_type(i.type));
            this._center_lightbox(n),
            this.showCover(),
            this._fill_lightbox(e, n),
            this._waiAria.lightboxVisibleAttr(n),
            this.callEvent("onLightbox", [e])
        }
    },
    t._get_timepicker_step = function() {
        if (this.config.round_dnd_dates) {
            var e = t._tasks,
            i = this._get_line(e.unit) * e.step / 60;
            return (i >= 1440 || !this._is_chart_visible()) && (i = this.config.time_step),
            i
        }
        return this.config.time_step
    },
    t.getLabel = function(t, e) {
        for (var i = this._get_typed_lightbox_config(), n = 0; n < i.length; n++) if (i[n].map_to == t) for (var a = i[n].options, r = 0; r < a.length; r++) if (a[r].key == e) return a[r].label;
        return ""
    },
    t.updateCollection = function(e, i) {
        i = i.slice(0);
        var n = t.serverList(e);
        return n ? (n.splice(0, n.length), n.push.apply(n, i || []), void t.resetLightbox()) : !1
    },
    t.getLightboxType = function() {
        return this._get_safe_type(this._lightbox_type)
    },
    t.getLightbox = function(e) {
        if (void 0 === e && (e = this.getLightboxType()), !this._lightbox || this.getLightboxType() != this._get_safe_type(e)) {
            this._lightbox_type = this._get_safe_type(e);
            var i = document.createElement("DIV");
            i.className = "gantt_cal_light";
            var n = this._is_lightbox_timepicker(); (t.config.wide_form || n) && (i.className += " gantt_cal_light_wide"),
            n && (t.config.wide_form = !0, i.className += " gantt_cal_light_full"),
            i.style.visibility = "hidden";
            for (var a, r = this._lightbox_template,
            s = this.config.buttons_left,
            o = 0; o < s.length; o++) {
                var _ = this.config._migrate_buttons[s[o]] ? this.config._migrate_buttons[s[o]] : s[o];
                a = this._waiAria.lightboxButtonAttrString(_),
                r += "<div " + a + " class='gantt_btn_set gantt_left_btn_set " + _ + "_set'><div dhx_button='1' class='" + _ + "'></div><div>" + this.locale.labels[_] + "</div></div>"
            }
            s = this.config.buttons_right;
            for (var o = 0; o < s.length; o++) {
                var _ = this.config._migrate_buttons[s[o]] ? this.config._migrate_buttons[s[o]] : s[o];
                a = this._waiAria.lightboxButtonAttrString(_),
                r += "<div " + a + " class='gantt_btn_set gantt_right_btn_set " + _ + "_set' style='float:right;'><div dhx_button='1' class='" + _ + "'></div><div>" + this.locale.labels[_] + "</div></div>";
            }
            r += "</div>",
            i.innerHTML = r,
            t._waiAria.lightboxAttr(i),
            t.config.drag_lightbox && (i.firstChild.onmousedown = t._ready_to_dnd, i.firstChild.onselectstart = function() {
                return ! 1
            },
            i.firstChild.style.cursor = "pointer", t._init_dnd_events()),
            document.body.insertBefore(i, document.body.firstChild),
            this._lightbox = i;
            var l = this._get_typed_lightbox_config(e);
            r = this._render_sections(l);
            for (var d = i.getElementsByTagName("div"), o = 0; o < d.length; o++) {
                var c = d[o];
                if ("gantt_cal_larea" == c.className) {
                    c.innerHTML = r;
                    break
                }
            }
            for (var o = 0; o < l.length; o++) {
                var h = l[o];
                if (h.id && document.getElementById(h.id)) {
                    var u = document.getElementById(h.id),
                    g = u.querySelector("label"),
                    f = u.nextSibling;
                    if (f) {
                        var p = f.querySelector("input, select, textarea");
                        p && (h.inputId = p.id || "input_" + t.uid(), p.id || (p.id = h.inputId), g.setAttribute("for", h.inputId))
                    }
                }
            }
            this.resizeLightbox(),
            this._init_lightbox_events(this),
            i.style.display = "none",
            i.style.visibility = "visible"
        }
        return this._lightbox
    },
    t._render_sections = function(t) {
        for (var e = "",
        i = 0; i < t.length; i++) {
            var n = this.form_blocks[t[i].type];
            if (n) {
                t[i].id = "area_" + this.uid();
                var a = t[i].hidden ? " style='display:none'": "",
                r = "";
                t[i].button && (r = "<div class='gantt_custom_button' index='" + i + "'><div class='gantt_custom_button_" + t[i].button + "'></div><div class='gantt_custom_button_label'>" + this.locale.labels["button_" + t[i].button] + "</div></div>"),
                this.config.wide_form && (e += "<div class='gantt_wrap_section' " + a + ">"),
                e += "<div id='" + t[i].id + "' class='gantt_cal_lsection'><label>" + r + this.locale.labels["section_" + t[i].name] + "</label></div>" + n.render.call(this, t[i]),
                e += "</div>"
            }
        }
        return e
    },
    t.resizeLightbox = function() {
        var t = this._lightbox;
        if (t) {
            var e = t.childNodes[1];
            e.style.height = "0px",
            e.style.height = e.scrollHeight + "px",
            t.style.height = e.scrollHeight + this.config.lightbox_additional_height + "px",
            e.style.height = e.scrollHeight + "px"
        }
    },
    t._center_lightbox = function(t) {
        if (t) {
            t.style.display = "block";
            var e = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop,
            i = window.pageXOffset || document.body.scrollLeft || document.documentElement.scrollLeft,
            n = window.innerHeight || document.documentElement.clientHeight;
            e ? t.style.top = Math.round(e + Math.max((n - t.offsetHeight) / 2, 0)) + "px": t.style.top = Math.round(Math.max((n - t.offsetHeight) / 2, 0) + 9) + "px",
            document.documentElement.scrollWidth > document.body.offsetWidth ? t.style.left = Math.round(i + (document.body.offsetWidth - t.offsetWidth) / 2) + "px": t.style.left = Math.round((document.body.offsetWidth - t.offsetWidth) / 2) + "px"
        }
    },
    t.showCover = function() {
        if (!this._cover) {
            this._cover = document.createElement("DIV"),
            this._cover.className = "gantt_cal_cover";
            var t = void 0 !== document.height ? document.height: document.body.offsetHeight,
            e = document.documentElement ? document.documentElement.scrollHeight: 0;
            this._cover.style.height = Math.max(t, e) + "px",
            document.body.appendChild(this._cover)
        }
    },
    t._init_lightbox_events = function() {
        t.lightbox_events = {},
        t.lightbox_events.gantt_save_btn = function(e) {
            t._save_lightbox()
        },
        t.lightbox_events.gantt_delete_btn = function(e) {
            t.callEvent("onLightboxDelete", [t._lightbox_id]) && (t.isTaskExists(t._lightbox_id) ? t.$click.buttons["delete"](t._lightbox_id) : t.hideLightbox())
        },
        t.lightbox_events.gantt_cancel_btn = function(e) {
            t._cancel_lightbox()
        },
        t.lightbox_events["default"] = function(e, i) {
            if (i.getAttribute("dhx_button")) t.callEvent("onLightboxButton", [i.className, i, e]);
            else {
                var n, a, r, s = t._getClassName(i);
                if ( - 1 != s.indexOf("gantt_custom_button")) if ( - 1 != s.indexOf("gantt_custom_button_")) for (n = i.parentNode.getAttribute("index"), r = i; r && -1 == t._getClassName(r).indexOf("gantt_cal_lsection");) r = r.parentNode;
                else n = i.getAttribute("index"),
                r = i.parentNode,
                i = i.firstChild;
                var o = t._get_typed_lightbox_config();
                n && (n = 1 * n, a = t.form_blocks[o[1 * n].type], a.button_click(n, i, r, r.nextSibling))
            }
        },
        this.event(t.getLightbox(), "click",
        function(e) {
            e = e || window.event;
            var i = e.target ? e.target: e.srcElement,
            n = t._getClassName(i);
            if (n || (i = i.previousSibling, n = t._getClassName(i)), i && n && 0 === n.indexOf("gantt_btn_set") && (i = i.firstChild, n = t._getClassName(i)), i && n) {
                var a = t.defined(t.lightbox_events[i.className]) ? t.lightbox_events[i.className] : t.lightbox_events["default"];
                return a(e, i)
            }
            return ! 1
        }),
        t.getLightbox().onkeydown = function(e) {
            var i = e || window.event,
            n = e.target || e.srcElement,
            a = !!(t._getClassName(n).indexOf("gantt_btn_set") > -1);
            switch ((e || i).keyCode) {
            case 32:
                if ((e || i).shiftKey) return;
                a && n.click && n.click();
                break;
            case t.keys.edit_save:
                if ((e || i).shiftKey) return;
                a && n.click ? n.click() : t._save_lightbox();
                break;
            case t.keys.edit_cancel:
                t._cancel_lightbox()
            }
        }
    },
    t._cancel_lightbox = function() {
        var e = this.getLightboxValues();
        this.callEvent("onLightboxCancel", [this._lightbox_id, e.$new]),
        t.isTaskExists(e.id) && e.$new && this._deleteTask(e.id, !0),
        this.refreshData(),
        this.hideLightbox()
    },
    t._save_lightbox = function() {
        var t = this.getLightboxValues();
        this.callEvent("onLightboxSave", [this._lightbox_id, t, !!t.$new]) && (t.$new ? (delete t.$new, this._replace_branch_child(this.getParent(t.id), t.id), this.addTask(t)) : this.isTaskExists(t.id) && (this.mixin(this.getTask(t.id), t, !0), this.updateTask(t.id)), this.refreshData(), this.hideLightbox())
    },
    t._resolve_default_mapping = function(t) {
        var e = t.map_to,
        i = {
            time: !0,
            time_optional: !0,
            duration: !0,
            duration_optional: !0
        };
        return i[t.type] && ("auto" == t.map_to ? e = {
            start_date: "start_date",
            end_date: "end_date",
            duration: "duration"
        }: "string" == typeof t.map_to && (e = {
            start_date: t.map_to
        })),
        e
    },
    t.getLightboxValues = function() {
        var e = {};
        t.isTaskExists(this._lightbox_id) && (e = this.mixin({},
        this.getTask(this._lightbox_id)));
        for (var i = this._get_typed_lightbox_config(), n = 0; n < i.length; n++) {
            var a = document.getElementById(i[n].id);
            a = a ? a.nextSibling: a;
            var r = this.form_blocks[i[n].type];
            if (r) {
                var s = r.get_value.call(this, a, e, i[n]),
                o = t._resolve_default_mapping(i[n]);
                if ("string" == typeof o && "auto" != o) e[o] = s;
                else if ("object" == typeof o) for (var _ in o) o[_] && (e[o[_]] = s[_])
            }
        }
        return e
    },
    t.hideLightbox = function() {
        var t = this.getLightbox();
        t && (t.style.display = "none"),
        this._waiAria.lightboxHiddenAttr(t),
        this._lightbox_id = null,
        this.hideCover(),
        this.callEvent("onAfterLightbox", [])
    },
    t.hideCover = function() {
        this._cover && this._cover.parentNode.removeChild(this._cover),
        this._cover = null
    },
    t.resetLightbox = function() {
        t._lightbox && !t._custom_lightbox && t._lightbox.parentNode.removeChild(t._lightbox),
        t._lightbox = null
    },
    t._set_lightbox_values = function(e, i) {
        var n = e,
        a = i.getElementsByTagName("span"),
        r = [];
        t.templates.lightbox_header ? (r.push(""), r.push(t.templates.lightbox_header(n.start_date, n.end_date, n)), a[1].innerHTML = "", a[2].innerHTML = t.templates.lightbox_header(n.start_date, n.end_date, n)) : (r.push(this.templates.task_time(n.start_date, n.end_date, n)), r.push((this.templates.task_text(n.start_date, n.end_date, n) || "").substr(0, 70)), a[1].innerHTML = this.templates.task_time(n.start_date, n.end_date, n), a[2].innerHTML = (this.templates.task_text(n.start_date, n.end_date, n) || "").substr(0, 70)),
        a[1].innerHTML = r[0],
        a[2].innerHTML = r[1],
        t._waiAria.lightboxHeader(i, r.join(" "));
        for (var s = this._get_typed_lightbox_config(this.getLightboxType()), o = 0; o < s.length; o++) {
            var _ = s[o];
            if (this.form_blocks[_.type]) {
                var l = document.getElementById(_.id).nextSibling,
                d = this.form_blocks[_.type],
                c = t._resolve_default_mapping(s[o]),
                h = this.defined(n[c]) ? n[c] : _.default_value;
                d.set_value.call(t, l, h, n, _),
                _.focus && d.focus.call(t, l)
            }
        }
        e.id && (t._lightbox_id = e.id)
    },
    t._fill_lightbox = function(t, e) {
        var i = this.getTask(t);
        this._set_lightbox_values(i, e)
    },
    t.getLightboxSection = function(e) {
        var i = this._get_typed_lightbox_config(),
        n = 0;
        for (n; n < i.length && i[n].name != e; n++);
        var a = i[n];
        if (!a) return null;
        this._lightbox || this.getLightbox();
        var r = document.getElementById(a.id),
        s = r.nextSibling,
        o = {
            section: a,
            header: r,
            node: s,
            getValue: function(e) {
                return t.form_blocks[a.type].get_value.call(t, s, e || {},
                a)
            },
            setValue: function(e, i) {
                return t.form_blocks[a.type].set_value.call(t, s, e, i || {},
                a)
            }
        },
        _ = this._lightbox_methods["get_" + a.type + "_control"];
        return _ ? _(o) : o
    },
    t._lightbox_methods.get_template_control = function(t) {
        return t.control = t.node,
        t
    },
    t._lightbox_methods.get_select_control = function(t) {
        return t.control = t.node.getElementsByTagName("select")[0],
        t
    },
    t._lightbox_methods.get_textarea_control = function(t) {
        return t.control = t.node.getElementsByTagName("textarea")[0],
        t
    },
    t._lightbox_methods.get_time_control = function(t) {
        return t.control = t.node.getElementsByTagName("select"),
        t
    },
    t._init_dnd_events = function() {
        this.event(document.body, "mousemove", t._move_while_dnd),
        this.event(document.body, "mouseup", t._finish_dnd),
        t._init_dnd_events = function() {}
    },
    t._move_while_dnd = function(e) {
        if (t._dnd_start_lb) {
            document.gantt_unselectable || (document.body.className += " gantt_unselectable", document.gantt_unselectable = !0);
            var i = t.getLightbox(),
            n = e && e.target ? [e.pageX, e.pageY] : [event.clientX, event.clientY];
            i.style.top = t._lb_start[1] + n[1] - t._dnd_start_lb[1] + "px",
            i.style.left = t._lb_start[0] + n[0] - t._dnd_start_lb[0] + "px"
        }
    },
    t._ready_to_dnd = function(e) {
        var i = t.getLightbox();
        t._lb_start = [parseInt(i.style.left, 10), parseInt(i.style.top, 10)],
        t._dnd_start_lb = e && e.target ? [e.pageX, e.pageY] : [event.clientX, event.clientY]
    },
    t._finish_dnd = function() {
        t._lb_start && (t._lb_start = t._dnd_start_lb = !1, document.body.className = document.body.className.replace(" gantt_unselectable", ""), document.gantt_unselectable = !1)
    },
    t._focus = function(e, i) {
        if (e && e.focus) if (t.config.touch);
        else try {
            i && e.select && e.select(),
            e.focus()
        } catch(n) {}
    },
    t.form_blocks = {
        getTimePicker: function(e, i) {
            var n = e.time_format;
            if (!n) {
                var n = ["%d", "%m", "%Y"];
                t._get_line(t._tasks.unit) < t._get_line("day") && n.push("%H:%i")
            }
            e._time_format_order = {
                size: 0
            };
            var a = this.config,
            r = this.date.date_part(new Date(t._min_date.valueOf())),
            s = 1440,
            o = 0;
            t.config.limit_time_select && (s = 60 * a.last_hour + 1, o = 60 * a.first_hour, r.setHours(a.first_hour));
            for (var _ = "",
            l = 0; l < n.length; l++) {
                var d = n[l];
                l > 0 && (_ += " ");
                var c = "";
                switch (d) {
                case "%Y":
                    e._time_format_order[2] = l,
                    e._time_format_order.size++;
                    var h, u, g, f;
                    e.year_range && (isNaN(e.year_range) ? e.year_range.push && (g = e.year_range[0], f = e.year_range[1]) : h = e.year_range),
                    h = h || 10,
                    u = u || Math.floor(h / 2),
                    g = g || r.getFullYear() - u,
                    f = f || g + h;
                    for (var p = g; f > p; p++) c += "<option value='" + p + "'>" + p + "</option>";
                    break;
                case "%m":
                    e._time_format_order[1] = l,
                    e._time_format_order.size++;
                    for (var p = 0; 12 > p; p++) c += "<option value='" + p + "'>" + this.locale.date.month_full[p] + "</option>";
                    break;
                case "%d":
                    e._time_format_order[0] = l,
                    e._time_format_order.size++;
                    for (var p = 1; 32 > p; p++) c += "<option value='" + p + "'>" + p + "</option>";
                    break;
                case "%H:%i":
                    e._time_format_order[3] = l,
                    e._time_format_order.size++;
                    var p = o,
                    v = r.getDate();
                    for (e._time_values = []; s > p;) {
                        var m = this.templates.time_picker(r);
                        c += "<option value='" + p + "'>" + m + "</option>",
                        e._time_values.push(p),
                        r.setTime(r.valueOf() + 60 * this._get_timepicker_step() * 1e3);
                        var k = r.getDate() != v ? 1 : 0;
                        p = 24 * k * 60 + 60 * r.getHours() + r.getMinutes()
                    }
                }
                if (c) {
                    var b = t._waiAria.lightboxSelectAttrString(d),
                    y = e.readonly ? "disabled='disabled'": "",
                    x = i ? " style='display:none' ": "";
                    _ += "<select " + y + x + b + ">" + c + "</select>"
                }
            }
            return _
        },
        _fill_lightbox_select: function(e, i, n, a, r) {
            if (e[i + a[0]].value = n.getDate(), e[i + a[1]].value = n.getMonth(), e[i + a[2]].value = n.getFullYear(), t.defined(a[3])) {
                var s = 60 * n.getHours() + n.getMinutes();
                s = Math.round(s / t._get_timepicker_step()) * t._get_timepicker_step();
                var o = e[i + a[3]];
                o.value = s,
                o.setAttribute("data-value", s)
            }
        },
        template: {
            render: function(t) {
                var e = (t.height || "30") + "px";
                return "<div class='gantt_cal_ltext gantt_cal_template' style='height:" + e + ";'></div>";
            },
            set_value: function(t, e, i, n) {
                t.innerHTML = e || ""
            },
            get_value: function(t, e, i) {
                return t.innerHTML || ""
            },
            focus: function(t) {}
        },
        textarea: {
            render: function(t) {
                var e = (t.height || "130") + "px";
                return "<div class='gantt_cal_ltext' style='height:" + e + ";'><textarea></textarea></div>"
            },
            set_value: function(t, e, i) {
                this.form_blocks.textarea._get_input(t).value = e || ""
            },
            get_value: function(t, e) {
                return this.form_blocks.textarea._get_input(t).value
            },
            focus: function(e) {
                var i = this.form_blocks.textarea._get_input(e);
                t._focus(i, !0);
            },
            _get_input: function(t) {
                return t.querySelector("textarea")
            }
        },
        select: {
            render: function(t) {
                for (var e = (t.height || "23") + "px", i = "<div class='gantt_cal_ltext' style='height:" + e + ";'><select style='width:100%;'>", n = 0; n < t.options.length; n++) i += "<option value='" + t.options[n].key + "'>" + t.options[n].label + "</option>";
                return i += "</select></div>"
            },
            set_value: function(t, e, i, n) {
                var a = t.firstChild; ! a._dhx_onchange && n.onchange && (a.onchange = n.onchange, a._dhx_onchange = !0),
                "undefined" == typeof e && (e = (a.options[0] || {}).value),
                a.value = e || ""
            },
            get_value: function(t, e) {
                return t.firstChild.value
            },
            focus: function(e) {
                var i = e.firstChild;
                t._focus(i, !0)
            }
        },
        time: {
            render: function(t) {
                var e = this.form_blocks.getTimePicker.call(this, t),
                i = ["<div style='height:" + (t.height || 30) + "px;padding-top:0px;font-size:inherit;text-align:center;' class='gantt_section_time'>"];
                return i.push(e),
                t.single_date ? (e = this.form_blocks.getTimePicker.call(this, t, !0), i.push("<span></span>")) : i.push("<span style='font-weight:normal; font-size:10pt;'> &nbsp;&ndash;&nbsp; </span>"),
                i.push(e),
                i.push("</div>"),
                i.join("")
            },
            set_value: function(e, i, n, a) {
                var r = a,
                s = e.getElementsByTagName("select"),
                o = a._time_format_order;
                a._time_format_size;
                if (r.auto_end_date) for (var _ = function() {
                    c = new Date(s[o[2]].value, s[o[1]].value, s[o[0]].value, 0, 0),
                    h = t.calculateEndDate({
                        start_date: c,
                        duration: 1,
                        task: n
                    }),
                    this.form_blocks._fill_lightbox_select(s, o.size, h, o, r)
                },
                l = 0; 4 > l; l++) s[l].onchange = _;
                var d = t._resolve_default_mapping(a);
                "string" == typeof d && (d = {
                    start_date: d
                });
                var c = n[d.start_date] || new Date,
                h = n[d.end_date] || t.calculateEndDate({
                    start_date: c,
                    duration: 1,
                    task: n
                });
                this.form_blocks._fill_lightbox_select(s, 0, c, o, r),
                this.form_blocks._fill_lightbox_select(s, o.size, h, o, r)
            },
            get_value: function(e, i, n) {
                var a = e.getElementsByTagName("select"),
                r = n._time_format_order,
                s = 0,
                o = 0;
                if (t.defined(r[3])) {
                    var _ = parseInt(a[r[3]].value, 10);
                    s = Math.floor(_ / 60),
                    o = _ % 60
                }
                var l = new Date(a[r[2]].value, a[r[1]].value, a[r[0]].value, s, o);
                if (s = o = 0, t.defined(r[3])) {
                    var _ = parseInt(a[r.size + r[3]].value, 10);
                    s = Math.floor(_ / 60),
                    o = _ % 60
                }
                var d = new Date(a[r[2] + r.size].value, a[r[1] + r.size].value, a[r[0] + r.size].value, s, o);
                l >= d && (d = t.date.add(l, t._get_timepicker_step(), "minute"));
                var c = t._resolve_default_mapping(n),
                h = {
                    start_date: new Date(l),
                    end_date: new Date(d)
                };
                return "string" == typeof c ? h.start_date: h
            },
            focus: function(e) {
                t._focus(e.getElementsByTagName("select")[0])
            }
        },
        duration: {
            render: function(t) {
                var e = this.form_blocks.getTimePicker.call(this, t);
                e = "<div class='gantt_time_selects'>" + e + "</div>";
                var i = this.locale.labels[this.config.duration_unit + "s"],
                n = t.single_date ? ' style="display:none"': "",
                a = t.readonly ? " disabled='disabled'": "",
                r = this._waiAria.lightboxDurationInputAttrString(t),
                s = "<div class='gantt_duration' " + n + "><input type='button' class='gantt_duration_dec' value='−'" + a + "><input type='text' value='5' class='gantt_duration_value'" + a + " " + r + "><input type='button' class='gantt_duration_inc' value='+'" + a + "> " + i + " <span></span></div>",
                o = "<div style='height:" + (t.height || 30) + "px;padding-top:0px;font-size:inherit;' class='gantt_section_time'>" + e + " " + s + "</div>";
                return o
            },
            set_value: function(e, i, n, a) {
                function r() {
                    var i = t.form_blocks.duration._get_start_date.call(t, e, a),
                    r = t.form_blocks.duration._get_duration.call(t, e, a),
                    s = t.calculateEndDate({
                        start_date: i,
                        duration: r,
                        task: n
                    });
                    h.innerHTML = t.templates.task_date(s)
                }
                function s(t) {
                    var e = d.value;
                    e = parseInt(e, 10),
                    window.isNaN(e) && (e = 0),
                    e += t,
                    1 > e && (e = 1),
                    d.value = e,
                    r()
                }
                var o = a,
                _ = e.getElementsByTagName("select"),
                l = e.getElementsByTagName("input"),
                d = l[1],
                c = [l[0], l[2]],
                h = e.getElementsByTagName("span")[0],
                u = a._time_format_order;
                c[0].onclick = t.bind(function() {
                    s( - 1 * this.config.duration_step)
                },
                this),
                c[1].onclick = t.bind(function() {
                    s(1 * this.config.duration_step)
                },
                this),
                _[0].onchange = r,
                _[1].onchange = r,
                _[2].onchange = r,
                _[3] && (_[3].onchange = r),
                d.onkeydown = t.bind(function(t) {
                    t = t || window.event;
                    var e = t.charCode || t.keyCode || t.which;
                    return 40 == e ? (s( - 1 * this.config.duration_step), !1) : 38 == e ? (s(1 * this.config.duration_step), !1) : void window.setTimeout(function(t) {
                        r()
                    },
                    1)
                },
                this),
                d.onchange = t.bind(function(t) {
                    r()
                },
                this);
                var g = t._resolve_default_mapping(a);
                "string" == typeof g && (g = {
                    start_date: g
                });
                var f = n[g.start_date] || new Date,
                p = n[g.end_date] || t.calculateEndDate({
                    start_date: f,
                    duration: 1,
                    task: n
                }),
                v = Math.round(n[g.duration]) || t.calculateDuration({
                    start_date: f,
                    end_date: p,
                    task: n
                });
                t.form_blocks._fill_lightbox_select(_, 0, f, u, o),
                d.value = v,
                r()
            },
            _get_start_date: function(e, i) {
                var n = e.getElementsByTagName("select"),
                a = i._time_format_order,
                r = 0,
                s = 0;
                if (t.defined(a[3])) {
                    var o = n[a[3]],
                    _ = parseInt(o.value, 10);
                    isNaN(_) && o.hasAttribute("data-value") && (_ = parseInt(o.getAttribute("data-value"), 10)),
                    r = Math.floor(_ / 60),
                    s = _ % 60
                }
                return new Date(n[a[2]].value, n[a[1]].value, n[a[0]].value, r, s)
            },
            _get_duration: function(t, e) {
                var i = t.getElementsByTagName("input")[1];
                return i = parseInt(i.value, 10),
                (!i || window.isNaN(i)) && (i = 1),
                0 > i && (i *= -1),
                i
            },
            get_value: function(e, i, n) {
                var a = t.form_blocks.duration._get_start_date(e, n),
                r = t.form_blocks.duration._get_duration(e, n),
                s = t.calculateEndDate({
                    start_date: a,
                    duration: r,
                    task: i
                }),
                o = t._resolve_default_mapping(n),
                _ = {
                    start_date: new Date(a),
                    end_date: new Date(s),
                    duration: r
                };
                return "string" == typeof o ? _.start_date: _;
            },
            focus: function(e) {
                t._focus(e.getElementsByTagName("select")[0])
            }
        },
        parent: {
            _filter: function(e, i, n) {
                var a = i.filter ||
                function() {
                    return ! 0
                };
                e = e.slice(0);
                for (var r = 0; r < e.length; r++) {
                    var s = e[r]; (s.id == n || t.isChildOf(s.id, n) || a(s.id, s) === !1) && (e.splice(r, 1), r--)
                }
                return e
            },
            _display: function(e, i) {
                var n = [],
                a = [];
                i && (n = t.getTaskByTime(), e.allow_root && n.unshift({
                    id: t.config.root_id,
                    text: e.root_label || ""
                }), n = this._filter(n, e, i), e.sort && n.sort(e.sort));
                for (var r = e.template || t.templates.task_text,
                s = 0; s < n.length; s++) {
                    var o = r.apply(t, [n[s].start_date, n[s].end_date, n[s]]);
                    void 0 === o && (o = ""),
                    a.push({
                        key: n[s].id,
                        label: o
                    })
                }
                return e.options = a,
                e.map_to = e.map_to || "parent",
                t.form_blocks.select.render.apply(this, arguments)
            },
            render: function(e) {
                return t.form_blocks.parent._display(e, !1)
            },
            set_value: function(e, i, n, a) {
                var r = document.createElement("div");
                r.innerHTML = t.form_blocks.parent._display(a, n.id);
                var s = r.removeChild(r.firstChild);
                return e.onselect = null,
                e.parentNode.replaceChild(s, e),
                t.form_blocks.select.set_value.apply(t, [s, i, n, a]);
            },
            get_value: function() {
                return t.form_blocks.select.get_value.apply(t, arguments)
            },
            focus: function() {
                return t.form_blocks.select.focus.apply(t, arguments)
            }
        }
    },
    t._is_lightbox_timepicker = function() {
        for (var t = this._get_typed_lightbox_config(), e = 0; e < t.length; e++) if ("time" == t[e].name && "time" == t[e].type) return ! 0;
        return ! 1
    },
    t._dhtmlx_confirm = function(e, i, n, a) {
        if (!e) return n();
        var r = {
            text: e
        };
        i && (r.title = i),
        a && (r.ok = a),
        n && (r.callback = function(t) {
            t && n()
        }),
        t.confirm(r)
    },
    t._get_typed_lightbox_config = function(e) {
        void 0 === e && (e = this.getLightboxType());
        var i = this._get_type_name(e);
        return t.config.lightbox[i + "_sections"] ? t.config.lightbox[i + "_sections"] : t.config.lightbox.sections
    },
    t._silent_redraw_lightbox = function(t) {
        var e = this.getLightboxType();
        if (this.getState().lightbox) {
            var i = this.getState().lightbox,
            n = this.getLightboxValues(),
            a = this.copy(this.getTask(i));
            this.resetLightbox();
            var r = this.mixin(a, n, !0),
            s = this.getLightbox(t ? t: void 0);
            this._center_lightbox(this.getLightbox()),
            this._set_lightbox_values(r, s)
        } else this.resetLightbox(),
        this.getLightbox(t ? t: void 0);
        this.callEvent("onLightboxChange", [e, this.getLightboxType()])
    },
    t._extend_to_optional = function(e) {
        var i = e,
        n = {
            render: i.render,
            focus: i.focus,
            set_value: function(e, a, r, s) {
                var o = t._resolve_default_mapping(s);
                if (!r[o.start_date] || "start_date" == o.start_date && this._isAllowedUnscheduledTask(r)) {
                    n.disable(e, s);
                    var _ = {};
                    for (var l in o) _[o[l]] = r[l];
                    return i.set_value.call(t, e, a, _, s)
                }
                return n.enable(e, s),
                i.set_value.call(t, e, a, r, s)
            },
            get_value: function(e, n, a) {
                return a.disabled ? {
                    start_date: null
                }: i.get_value.call(t, e, n, a);
            },
            update_block: function(e, i) {
                if (t.callEvent("onSectionToggle", [t._lightbox_id, i]), e.style.display = i.disabled ? "none": "block", i.button) {
                    var n = e.previousSibling.querySelector(".gantt_custom_button_label"),
                    a = t.locale.labels,
                    r = i.disabled ? a[i.name + "_enable_button"] : a[i.name + "_disable_button"];
                    n.innerHTML = r
                }
                t.resizeLightbox()
            },
            disable: function(t, e) {
                e.disabled = !0,
                n.update_block(t, e)
            },
            enable: function(t, e) {
                e.disabled = !1,
                n.update_block(t, e)
            },
            button_click: function(e, i, a, r) {
                if (t.callEvent("onSectionButton", [t._lightbox_id, a]) !== !1) {
                    var s = t._get_typed_lightbox_config()[e];
                    s.disabled ? n.enable(r, s) : n.disable(r, s)
                }
            }
        };
        return n
    },
    t.form_blocks.duration_optional = t._extend_to_optional(t.form_blocks.duration),
    t.form_blocks.time_optional = t._extend_to_optional(t.form_blocks.time),
    t.dataProcessor = function(e) {
        return this.serverProcessor = e,
        this.action_param = "!nativeeditor_status",
        this.object = null,
        this.updatedRows = [],
        this.autoUpdate = !0,
        this.updateMode = "cell",
        this._tMode = "GET",
        this._headers = null,
        this._payload = null,
        this.post_delim = "_",
        this._waitMode = 0,
        this._in_progress = {},
        this._invalid = {},
        this.mandatoryFields = [],
        this.messages = [],
        this.styles = {
            updated: "font-weight:bold;",
            inserted: "font-weight:bold;",
            deleted: "text-decoration : line-through;",
            invalid: "background-color:FFE0E0;",
            invalid_cell: "border-bottom:2px solid red;",
            error: "color:red;",
            clear: "font-weight:normal;text-decoration:none;"
        },
        this.enableUTFencoding(!0),
        t._eventable(this),
        this
    },
    t.dataProcessor.prototype = {
        setTransactionMode: function(e, i) {
            "object" == typeof e ? (this._tMode = e.mode || this._tMode, t.defined(e.headers) && (this._headers = e.headers), t.defined(e.payload) && (this._payload = e.payload)) : (this._tMode = e, this._tSend = i),
            "REST" == this._tMode && (this._tSend = !1, this._endnm = !0),
            "JSON" == this._tMode && (this._tSend = !1, this._endnm = !0, this._headers = this._headers || {},
            this._headers["Content-type"] = "application/json")
        },
        escape: function(t) {
            return this._utf ? encodeURIComponent(t) : escape(t)
        },
        enableUTFencoding: function(t) {
            this._utf = !!t
        },
        setDataColumns: function(t) {
            this._columns = "string" == typeof t ? t.split(",") : t;
        },
        getSyncState: function() {
            return ! this.updatedRows.length
        },
        enableDataNames: function(t) {
            this._endnm = !!t
        },
        enablePartialDataSend: function(t) {
            this._changed = !!t
        },
        setUpdateMode: function(t, e) {
            this.autoUpdate = "cell" == t,
            this.updateMode = t,
            this.dnd = e
        },
        ignore: function(t, e) {
            this._silent_mode = !0,
            t.call(e || window),
            this._silent_mode = !1
        },
        setUpdated: function(t, e, i) {
            if (!this._silent_mode) {
                var n = this.findRow(t);
                i = i || "updated";
                var a = this.obj.getUserData(t, this.action_param);
                a && "updated" == i && (i = a),
                e ? (this.set_invalid(t, !1), this.updatedRows[n] = t, this.obj.setUserData(t, this.action_param, i), this._in_progress[t] && (this._in_progress[t] = "wait")) : this.is_invalid(t) || (this.updatedRows.splice(n, 1), this.obj.setUserData(t, this.action_param, "")),
                e || this._clearUpdateFlag(t),
                this.markRow(t, e, i),
                e && this.autoUpdate && this.sendData(t)
            }
        },
        _clearUpdateFlag: function(t) {},
        markRow: function(t, e, i) {
            var n = "",
            a = this.is_invalid(t);
            if (a && (n = this.styles[a], e = !0), this.callEvent("onRowMark", [t, e, i, a]) && (n = this.styles[e ? i: "clear"] + n, this.obj[this._methods[0]](t, n), a && a.details)) {
                n += this.styles[a + "_cell"];
                for (var r = 0; r < a.details.length; r++) a.details[r] && this.obj[this._methods[1]](t, r, n)
            }
        },
        getState: function(t) {
            return this.obj.getUserData(t, this.action_param)
        },
        is_invalid: function(t) {
            return this._invalid[t]
        },
        set_invalid: function(t, e, i) {
            i && (e = {
                value: e,
                details: i,
                toString: function() {
                    return this.value.toString()
                }
            }),
            this._invalid[t] = e
        },
        checkBeforeUpdate: function(t) {
            return ! 0
        },
        sendData: function(t) {
            return ! this._waitMode || "tree" != this.obj.mytype && !this.obj._h2 ? (this.obj.editStop && this.obj.editStop(), "undefined" == typeof t || this._tSend ? this.sendAllData() : this._in_progress[t] ? !1 : (this.messages = [], !this.checkBeforeUpdate(t) && this.callEvent("onValidationError", [t, this.messages]) ? !1 : void this._beforeSendData(this._getRowData(t), t))) : void 0
        },
        _beforeSendData: function(t, e) {
            return this.callEvent("onBeforeUpdate", [e, this.getState(e), t]) ? void this._sendData(t, e) : !1
        },
        serialize: function(e, i) {
            if ("string" == typeof e) return e;
            if ("undefined" != typeof i) return this.serialize_one(e, "");
            var n = [],
            a = [];
            for (var r in e) e.hasOwnProperty(r) && (n.push(this.serialize_one(e[r], r + this.post_delim)), a.push(r));
            return n.push("ids=" + this.escape(a.join(","))),
            t.security_key && n.push("dhx_security=" + t.security_key),
            n.join("&")
        },
        serialize_one: function(t, e) {
            if ("string" == typeof t) return t;
            var i = [];
            for (var n in t) if (t.hasOwnProperty(n)) {
                if (("id" == n || n == this.action_param) && "REST" == this._tMode) continue;
                i.push(this.escape((e || "") + n) + "=" + this.escape(t[n]))
            }
            return i.join("&")
        },
        _applyPayload: function(e) {
            if (this._payload) for (var i in this._payload) e = e + t._urlSeparator(e) + this.escape(i) + "=" + this.escape(this._payload[i]);
            return e
        },
        _sendData: function(e, i) {
            if (e) {
                if (!this.callEvent("onBeforeDataSending", i ? [i, this.getState(i), e] : [null, null, e])) return ! 1;
                i && (this._in_progress[i] = (new Date).valueOf());
                var n = this,
                a = function(t) {
                    var a = [];
                    if (i) a.push(i);
                    else if (e) for (var r in e) a.push(r);
                    return n.afterUpdate(n, t, a)
                },
                r = this.serverProcessor + (this._user ? t._urlSeparator(this.serverProcessor) + ["dhx_user=" + this._user, "dhx_version=" + this.obj.getUserData(0, "version")].join("&") : ""),
                s = this._applyPayload(r);
                if ("GET" == this._tMode) t.ajax.query({
                    url: s + t._urlSeparator(s) + this.serialize(e, i),
                    method: "GET",
                    callback: a,
                    headers: this._headers
                });
                else if ("POST" == this._tMode) t.ajax.query({
                    url: s,
                    method: "POST",
                    headers: this._headers,
                    data: this.serialize(e, i),
                    callback: a
                });
                else if ("JSON" == this._tMode) {
                    var o = e[this.action_param],
                    _ = {};
                    for (var l in e) _[l] = e[l];
                    delete _[this.action_param],
                    delete _.id,
                    delete _.gr_id,
                    t.ajax.query({
                        url: s,
                        method: "POST",
                        headers: this._headers,
                        callback: a,
                        data: JSON.stringify({
                            id: i,
                            action: o,
                            data: _
                        })
                    })
                } else if ("REST" == this._tMode) {
                    var d = this.getState(i),
                    c = r.replace(/(\&|\?)editing\=true/, ""),
                    _ = "",
                    h = "post";
                    "inserted" == d ? _ = this.serialize(e, i) : "deleted" == d ? (h = "DELETE", c = c + ("/" == c.slice( - 1) ? "": "/") + i) : (h = "PUT", _ = this.serialize(e, i), c = c + ("/" == c.slice( - 1) ? "": "/") + i),
                    c = this._applyPayload(c),
                    t.ajax.query({
                        url: c,
                        method: h,
                        headers: this._headers,
                        data: _,
                        callback: a
                    })
                }
                this._waitMode++
            }
        },
        sendAllData: function() {
            if (this.updatedRows.length) {
                this.messages = [];
                for (var t = !0,
                e = 0; e < this.updatedRows.length; e++) t &= this.checkBeforeUpdate(this.updatedRows[e]);
                if (!t && !this.callEvent("onValidationError", ["", this.messages])) return ! 1;
                if (this._tSend) this._sendData(this._getAllData());
                else for (var e = 0; e < this.updatedRows.length; e++) if (!this._in_progress[this.updatedRows[e]]) {
                    if (this.is_invalid(this.updatedRows[e])) continue;
                    if (this._beforeSendData(this._getRowData(this.updatedRows[e]), this.updatedRows[e]), this._waitMode && ("tree" == this.obj.mytype || this.obj._h2)) return
                }
            }
        },
        _getAllData: function(t) {
            for (var e = {},
            i = !1,
            n = 0; n < this.updatedRows.length; n++) {
                var a = this.updatedRows[n];
                if (!this._in_progress[a] && !this.is_invalid(a)) {
                    var r = this._getRowData(a);
                    this.callEvent("onBeforeUpdate", [a, this.getState(a), r]) && (e[a] = r, i = !0, this._in_progress[a] = (new Date).valueOf())
                }
            }
            return i ? e: null
        },
        setVerificator: function(t, e) {
            this.mandatoryFields[t] = e ||
            function(t) {
                return "" !== t
            }
        },
        clearVerificator: function(t) {
            this.mandatoryFields[t] = !1
        },
        findRow: function(t) {
            var e = 0;
            for (e = 0; e < this.updatedRows.length && t != this.updatedRows[e]; e++);
            return e
        },
        defineAction: function(t, e) {
            this._uActions || (this._uActions = []),
            this._uActions[t] = e
        },
        afterUpdateCallback: function(t, e, i, n) {
            var a = t,
            r = "error" != i && "invalid" != i;
            if (r || this.set_invalid(t, i), this._uActions && this._uActions[i] && !this._uActions[i](n)) return delete this._in_progress[a];
            "wait" != this._in_progress[a] && this.setUpdated(t, !1);
            var s = t;
            switch (i) {
            case "inserted":
            case "insert":
                e != t && (this.setUpdated(t, !1), this.obj[this._methods[2]](t, e), t = e);
                break;
            case "delete":
            case "deleted":
                return this.obj.setUserData(t, this.action_param, "true_deleted"),
                this.obj[this._methods[3]](t),
                delete this._in_progress[a],
                this.callEvent("onAfterUpdate", [t, i, e, n])
            }
            "wait" != this._in_progress[a] ? (r && this.obj.setUserData(t, this.action_param, ""), delete this._in_progress[a]) : (delete this._in_progress[a], this.setUpdated(e, !0, this.obj.getUserData(t, this.action_param))),
            this.callEvent("onAfterUpdate", [s, i, e, n])
        },
        afterUpdate: function(e, i, n) {
            if (window.JSON) {
                var a;
                try {
                    a = JSON.parse(i.xmlDoc.responseText)
                } catch(r) {
                    i.xmlDoc.responseText.length || (a = {})
                }
                if (a) {
                    var s = a.action || this.getState(n) || "updated",
                    o = a.sid || n[0],
                    _ = a.tid || n[0];
                    return e.afterUpdateCallback(o, _, s, a),
                    void e.finalizeUpdate()
                }
            }
            var l = t.ajax.xmltop("data", i.xmlDoc);
            if (!l) return this.cleanUpdate(n);
            var d = t.ajax.xpath("//data/action", l);
            if (!d.length) return this.cleanUpdate(n);
            for (var c = 0; c < d.length; c++) {
                var h = d[c],
                s = h.getAttribute("type"),
                o = h.getAttribute("sid"),
                _ = h.getAttribute("tid");
                e.afterUpdateCallback(o, _, s, h)
            }
            e.finalizeUpdate()
        },
        cleanUpdate: function(t) {
            if (t) for (var e = 0; e < t.length; e++) delete this._in_progress[t[e]]
        },
        finalizeUpdate: function() {
            this._waitMode && this._waitMode--,
            ("tree" == this.obj.mytype || this.obj._h2) && this.updatedRows.length && this.sendData(),
            this.callEvent("onAfterUpdateFinish", []),
            this.updatedRows.length || this.callEvent("onFullSync", [])
        },
        init: function(t) {
            this.obj = t,
            this.obj._dp_init && this.obj._dp_init(this)
        },
        setOnAfterUpdate: function(t) {
            this.attachEvent("onAfterUpdate", t)
        },
        enableDebug: function(t) {},
        setOnBeforeUpdateHandler: function(t) {
            this.attachEvent("onBeforeDataSending", t)
        },
        setAutoUpdate: function(t, e) {
            t = t || 2e3,
            this._user = e || (new Date).valueOf(),
            this._need_update = !1,
            this._update_busy = !1,
            this.attachEvent("onAfterUpdate",
            function(t, e, i, n) {
                this.afterAutoUpdate(t, e, i, n)
            }),
            this.attachEvent("onFullSync",
            function() {
                this.fullSync()
            });
            var i = this;
            window.setInterval(function() {
                i.loadUpdate()
            },
            t)
        },
        afterAutoUpdate: function(t, e, i, n) {
            return "collision" == e ? (this._need_update = !0, !1) : !0
        },
        fullSync: function() {
            return this._need_update && (this._need_update = !1, this.loadUpdate()),
            !0
        },
        getUpdates: function(e, i) {
            return this._update_busy ? !1 : (this._update_busy = !0, void t.ajax.get(e, i))
        },
        _v: function(t) {
            return t.firstChild ? t.firstChild.nodeValue: ""
        },
        _a: function(t) {
            for (var e = [], i = 0; i < t.length; i++) e[i] = this._v(t[i]);
            return e
        },
        loadUpdate: function() {
            var e = this,
            i = this.obj.getUserData(0, "version"),
            n = this.serverProcessor + t._urlSeparator(this.serverProcessor) + ["dhx_user=" + this._user, "dhx_version=" + i].join("&");
            n = n.replace("editing=true&", ""),
            this.getUpdates(n,
            function(i) {
                var n = t.ajax.xpath("//userdata", i);
                e.obj.setUserData(0, "version", e._v(n[0]));
                var a = t.ajax.xpath("//update", i);
                if (a.length) {
                    e._silent_mode = !0;
                    for (var r = 0; r < a.length; r++) {
                        var s = a[r].getAttribute("status"),
                        o = a[r].getAttribute("id"),
                        _ = a[r].getAttribute("parent");
                        switch (s) {
                        case "inserted":
                            e.callEvent("insertCallback", [a[r], o, _]);
                            break;
                        case "updated":
                            e.callEvent("updateCallback", [a[r], o, _]);
                            break;
                        case "deleted":
                            e.callEvent("deleteCallback", [a[r], o, _])
                        }
                    }
                    e._silent_mode = !1
                }
                e._update_busy = !1,
                e = null
            })
        }
    },
    t._init_dp_live_update_hooks = function(e) {
        e.attachEvent("insertCallback", t._insert_callback),
        e.attachEvent("updateCallback", t._update_callback),
        e.attachEvent("deleteCallback", t._delete_callback)
    },
    t._update_callback = function(e, i) {
        var n = e.data || t.xml._xmlNodeToJSON(e.firstChild);
        if (t.isTaskExists(i)) {
            var a = t.getTask(i);
            for (var r in n) {
                var s = n[r];
                switch (r) {
                case "id":
                    continue;
                case "start_date":
                case "end_date":
                    s = t.templates.xml_date(s);
                    break;
                case "duration":
                    a.end_date = t.calculateEndDate({
                        start_date: a.start_date,
                        duration: s,
                        task: a
                    })
                }
                a[r] = s
            }
            t.updateTask(i),
            t.refreshData()
        }
    },
    t._insert_callback = function(e, i, n, a) {
        var r = e.data || t.xml._xmlNodeToJSON(e.firstChild),
        s = {
            add: t.addTask,
            isExist: t.isTaskExists
        };
        "links" == a && (s.add = t.addLink, s.isExist = t.isLinkExists),
        s.isExist.call(t, i) || (r.id = i, s.add.call(t, r))
    },
    t._delete_callback = function(e, i, n, a) {
        var r = {
            "delete": t.deleteTask,
            isExist: t.isTaskExists
        };
        "links" == a && (r["delete"] = t.deleteLink, r.isExist = t.isLinkExists),
        r.isExist.call(t, i) && r["delete"].call(t, i)
    },
    t._get_safe_type = function(e) {
        for (var i in this.config.types) if (this.config.types[i] == e) return e;
        return t.config.types.task
    },
    t.form_blocks.typeselect = {
        render: function(e) {
            var i = t.config.types,
            n = t.locale.labels,
            a = [],
            r = e.filter ||
            function() {
                return ! 0
            };
            for (var s in i) ! r(s, i[s]) == !1 && a.push({
                key: i[s],
                label: n["type_" + s]
            });
            e.options = a;
            var o = e.onchange;
            return e.onchange = function() {
                t.getState().lightbox;
                t.changeLightboxType(this.value),
                "function" == typeof o && o.apply(this, arguments)
            },
            t.form_blocks.select.render.apply(t, arguments)
        },
        set_value: function() {
            return t.form_blocks.select.set_value.apply(t, arguments)
        },
        get_value: function() {
            return t.form_blocks.select.get_value.apply(t, arguments)
        },
        focus: function() {
            return t.form_blocks.select.focus.apply(this, arguments)
        }
    },
    function() {
        function e() {
            return t._cached_functions.update_if_changed(t),
            t._cached_functions.active || t._cached_functions.activate(),
            !0
        }
        t._cached_functions = {
            cache: {},
            mode: !1,
            critical_path_mode: !1,
            wrap_methods: function(t, e) {
                if (e._prefetch_originals) for (var i in e._prefetch_originals) e[i] = e._prefetch_originals[i];
                e._prefetch_originals = {};
                for (var i = 0; i < t.length; i++) this.prefetch(t[i], e)
            },
            prefetch: function(t, e) {
                var i = e[t];
                if (i) {
                    var n = this;
                    e._prefetch_originals[t] = i,
                    e[t] = function() {
                        for (var e = new Array(arguments.length), a = 0, r = arguments.length; r > a; a++) e[a] = arguments[a];
                        if (n.active) {
                            var s = n.get_arguments_hash(Array.prototype.slice.call(e));
                            n.cache[t] || (n.cache[t] = {});
                            var o = n.cache[t];
                            if (n.has_cached_value(o, s)) return n.get_cached_value(o, s);
                            var _ = i.apply(this, e);
                            return n.cache_value(o, s, _),
                            _
                        }
                        return i.apply(this, e)
                    }
                }
                return i
            },
            cache_value: function(t, e, i) {
                this.is_date(i) && (i = new Date(i)),
                t[e] = i
            },
            has_cached_value: function(t, e) {
                return t.hasOwnProperty(e)
            },
            get_cached_value: function(t, e) {
                var i = t[e];
                return this.is_date(i) && (i = new Date(i)),
                i
            },
            is_date: function(t) {
                return t && t.getUTCDate
            },
            get_arguments_hash: function(t) {
                for (var e = [], i = 0; i < t.length; i++) e.push(this.stringify_argument(t[i]));
                return "(" + e.join(";") + ")"
            },
            stringify_argument: function(t) {
                var e = "";
                return e = t.id ? t.id: this.is_date(t) ? t.valueOf() : t,
                e + ""
            },
            activate: function() {
                this.clear(),
                this.active = !0
            },
            deactivate: function() {
                this.clear(),
                this.active = !1
            },
            clear: function() {
                this.cache = {}
            },
            setup: function(t) {
                var e = [],
                i = ["_isCriticalTask", "isCriticalLink", "_isProjectEnd", "_getProjectEnd", "_getSlack"];
                "auto" == this.mode ? t.config.highlight_critical_path && (e = i) : this.mode === !0 && (e = i),
                this.wrap_methods(e, t)
            },
            update_if_changed: function(t) {
                var e = this.critical_path_mode != t.config.highlight_critical_path || this.mode !== t.config.optimize_render;
                e && (this.critical_path_mode = t.config.highlight_critical_path, this.mode = t.config.optimize_render, this.setup(t))
            }
        },
        t.attachEvent("onBeforeGanttRender", e),
        t.attachEvent("onBeforeDataRender", e),
        t.attachEvent("onBeforeSmartRender",
        function() {
            e()
        }),
        t.attachEvent("onBeforeParse", e),
        t.attachEvent("onDataRender",
        function() {
            t._cached_functions.deactivate()
        });
        var i = null;
        t.attachEvent("onSmartRender",
        function() {
            i && clearTimeout(i),
            i = setTimeout(function() {
                t._cached_functions.deactivate()
            },
            1e3)
        }),
        t.attachEvent("onBeforeGanttReady",
        function() {
            return t._cached_functions.update_if_changed(t),
            !0
        })
    } (),
    t.assert = function(e, i) {
        e || t.config.show_errors && t.callEvent("onError", [i]) !== !1 && t.message({
            type: "error",
            text: i,
            expire: -1
        })
    },
    t.init = function(e, i, n) {
        this.callEvent("onBeforeGanttReady", []),
        i && n && (this.config.start_date = this._min_date = new Date(i), this.config.end_date = this._max_date = new Date(n)),
        this._init_skin(),
        this.date.init(),
        this.config.scroll_size || (this.config.scroll_size = this._detectScrollSize());
        var a;
        t.event(window, "resize",
        function() {
            clearTimeout(a),
            a = setTimeout(function() {
                t.render()
            },
            300)
        }),
        this.init = function(t) {
            this.$container && this.$container.parentNode && (this.$container.parentNode.removeChild(this.$container), this.$container = null),
            this._reinit(t)
        },
        this._reinit(e)
    },
    t._reinit = function(e) {
        this._init_html_area(e),
        this._set_sizes(),
        this._clear_renderers(),
        this.resetLightbox(),
        this._update_flags(),
        this._init_touch_events(),
        this._init_templates(),
        this._init_grid(),
        this._init_tasks(),
        this._set_scroll_events(),
        t.event(this.$container, "click", this._on_click),
        t.event(this.$container, "dblclick", this._on_dblclick),
        t.event(this.$container, "mousemove", this._on_mousemove),
        t.event(this.$container, "contextmenu", this._on_contextmenu),
        this.callEvent("onGanttReady", []),
        this.render()
    },
    t._init_html_area = function(e) {
        "string" == typeof e ? this._obj = document.getElementById(e) : this._obj = e,
        window.gantt !== t && (this._obj.gantt = t),
        this.assert(this._obj, "Invalid html container: " + e);
        var i = this._waiAria.gridAttrString(),
        n = this._waiAria.gridDataAttrString(),
        a = "<div class='gantt_container'><div class='gantt_grid' " + i + "></div><div class='gantt_task'></div>";
        a += "<div class='gantt_ver_scroll'><div></div></div><div class='gantt_hor_scroll'><div></div></div></div>",
        this._obj.innerHTML = a,
        this.$container = this._obj.firstChild;
        var r = this.$container.childNodes;
        this.$grid = r[0],
        this.$task = r[1],
        this.$scroll_ver = r[2],
        this.$scroll_hor = r[3],
        this.$grid.innerHTML = "<div class='gantt_grid_scale' " + t._waiAria.gridScaleRowAttrString() + "></div><div class='gantt_grid_data' " + n + "></div>",
        this.$grid_scale = this.$grid.childNodes[0],
        this.$grid_data = this.$grid.childNodes[1],
        this.$task.innerHTML = "<div class='gantt_task_scale'></div><div class='gantt_data_area'><div class='gantt_task_bg'></div><div class='gantt_links_area'></div><div class='gantt_bars_area'></div></div>",
        this.$task_scale = this.$task.childNodes[0],
        this.$task_data = this.$task.childNodes[1],
        this.$task_bg = this.$task_data.childNodes[0],
        this.$task_links = this.$task_data.childNodes[1],
        this.$task_bars = this.$task_data.childNodes[2]
    },
    t.$click = {
        buttons: {
            edit: function(e) {
                t.showLightbox(e)
            },
            "delete": function(e) {
                var i = t.locale.labels.confirm_deleting,
                n = t.locale.labels.confirm_deleting_title;
                t._dhtmlx_confirm(i, n,
                function() {
                    if (!t.isTaskExists(e)) return void t.hideLightbox();
                    var i = t.getTask(e);
                    i.$new ? (t._deleteTask(e, !0), t.refreshData()) : t.deleteTask(e),
                    t.hideLightbox()
                })
            }
        }
    },
    t._calculate_content_height = function() {
        var t = this.config.scale_height,
        e = this._order.length * this.config.row_height,
        i = this._scroll_hor ? this.config.scroll_size + 1 : 0;
        return this._is_grid_visible() || this._is_chart_visible() ? t + e + 2 + i: 0
    },
    t._calculate_content_width = function() {
        var t = this._get_grid_width(),
        e = this._tasks ? this._tasks.full_width: 0;
        this._scroll_ver ? this.config.scroll_size + 1 : 0;
        return this._is_chart_visible() || (e = 0),
        this._is_grid_visible() || (t = 0),
        t + e + 1
    },
    t._get_resize_options = function() {
        var t = {
            x: !1,
            y: !1
        };
        return "xy" == this.config.autosize ? t.x = t.y = !0 : "y" == this.config.autosize || this.config.autosize === !0 ? t.y = !0 : "x" == this.config.autosize && (t.x = !0),
        t
    },
    t._clean_el_size = function(t) {
        return 1 * (t || "").toString().replace("px", "") || 0
    },
    t._get_box_styles = function() {
        var t = null;
        t = window.getComputedStyle ? window.getComputedStyle(this._obj, null) : {
            width: this._obj.clientWidth,
            height: this._obj.clientHeight
        };
        var e = ["width", "height", "paddingTop", "paddingBottom", "paddingLeft", "paddingRight", "borderLeftWidth", "borderRightWidth", "borderTopWidth", "borderBottomWidth"],
        i = {
            boxSizing: "border-box" == t.boxSizing
        };
        t.MozBoxSizing && (i.boxSizing = "border-box" == t.MozBoxSizing);
        for (var n = 0; n < e.length; n++) i[e[n]] = t[e[n]] ? this._clean_el_size(t[e[n]]) : 0;
        var a = {
            horPaddings: i.paddingLeft + i.paddingRight + i.borderLeftWidth + i.borderRightWidth,
            vertPaddings: i.paddingTop + i.paddingBottom + i.borderTopWidth + i.borderBottomWidth,
            borderBox: i.boxSizing,
            innerWidth: i.width,
            innerHeight: i.height,
            outerWidth: i.width,
            outerHeight: i.height
        };
        return a.borderBox ? (a.innerWidth -= a.horPaddings, a.innerHeight -= a.vertPaddings) : (a.outerWidth += a.horPaddings, a.outerHeight += a.vertPaddings),
        a
    },
    t._do_autosize = function() {
        var t = this._get_resize_options(),
        e = this._get_box_styles();
        if (t.y) {
            var i = this._calculate_content_height();
            e.borderBox && (i += e.vertPaddings),
            this._obj.style.height = i + "px"
        }
        if (t.x) {
            var n = this._calculate_content_width();
            e.borderBox && (n += e.horPaddings),
            this._obj.style.width = n + "px"
        }
    },
    t._set_sizes = function() {
        this._do_autosize();
        var t = this._get_box_styles();
        if (this._y = t.innerHeight, !(this._y < 20)) {
            this.$grid.style.height = this.$task.style.height = Math.max(this._y - this.$scroll_hor.offsetHeight - 2, 0) + "px";
            var e = Math.max(this._y - (this.config.scale_height || 0) - this.$scroll_hor.offsetHeight - 2, 0);
            this.$grid_data.style.height = this.$task_data.style.height = e + "px";
            var i = Math.max(this._get_grid_width() - 1, 0);
            this.$grid.style.width = i + "px",
            this.$grid.style.display = 0 === i ? "none": "",
            t = this._get_box_styles(),
            this._x = t.innerWidth,
            this._x < 20 || (this.$grid_data.style.width = Math.max(this._get_grid_width() - 1, 0) + "px", this.$task.style.width = Math.max(this._x - this._get_grid_width() - 2, 0) + "px")
        }
    },
    t.getScrollState = function() {
        return this.$task && this.$task_data ? {
            x: this.$task.scrollLeft,
            y: this.$task_data.scrollTop
        }: null
    },
    t._save_scroll_state = function(t, e) {
        var i = {};
        this._cached_scroll_pos = this._cached_scroll_pos || {},
        void 0 !== t && (i.x = Math.max(t, 0)),
        void 0 !== e && (i.y = Math.max(e, 0)),
        this.mixin(this._cached_scroll_pos, i, !0)
    },
    t._restore_scroll_state = function() {
        var t = {
            x: 0,
            y: 0
        };
        return this._cached_scroll_pos && (t.x = this._cached_scroll_pos.x || t.x, t.y = this._cached_scroll_pos.y || t.y),
        t
    },
    t.scrollTo = function(e, i) {
        var n = this._restore_scroll_state();
        1 * e == e && (this.$task.scrollLeft = e, this._save_scroll_state(e, void 0)),
        1 * i == i && (this.$scroll_ver.scrollTop = i, this.$task_data.scrollTop = i, this.$grid_data.scrollTop = i, this._save_scroll_state(void 0, this.config.show_chart ? this.$task_data.scrollTop: this.$scroll_ver.scrollTop));
        var a = t._restore_scroll_state();
        this.callEvent("onGanttScroll", [n.x, n.y, a.x, a.y])
    },
    t.showDate = function(t) {
        var e = this.posFromDate(t),
        i = Math.max(e - this.config.task_scroll_offset, 0);
        this.scrollTo(i)
    },
    t.showTask = function(t) {
        var e, i = this._get_task_pos(this.getTask(t)),
        n = Math.max(i.x - this.config.task_scroll_offset, 0),
        a = this._scroll_sizes().y;
        e = a ? i.y - (a - this.config.row_height) / 2 : i.y,
        this.scrollTo(n, e)
    },
    t._on_resize = t.setSizes = function() {
        t._set_sizes(),
        t._scroll_resize(),
        t._set_sizes()
    },
    t.render = function() {
        this.callEvent("onBeforeGanttRender", []);
        var e = this.copy(this._restore_scroll_state()),
        i = null;
        if (e && (i = t.dateFromPos(e.x + this.config.task_scroll_offset)), this._render_grid(), this._render_tasks_scales(), this._scroll_resize(), this._on_resize(), this._render_data(), this.config.preserve_scroll && e) {
            var n = t._restore_scroll_state(),
            a = t.dateFromPos(n.x); ( + i != +a || n.y != e.y) && (i && this.showDate(i), t.scrollTo(void 0, e.y))
        }
        this.callEvent("onGanttRender", [])
    },
    t._set_scroll_events = function() {
        function e(e) {
            var n = t._get_resize_options();
            t._wheel_time = new Date;
            var a = i ? -20 * e.deltaX: 2 * e.wheelDeltaX,
            r = i ? -40 * e.deltaY: e.wheelDelta;
            if (!e.shiftKey || e.deltaX || e.wheelDeltaX || (a = 2 * r, r = 0), a && Math.abs(a) > Math.abs(r)) {
                if (n.x) return ! 0;
                if (!t.$scroll_hor || !t.$scroll_hor.offsetWidth) return ! 0;
                var s = a / -40,
                o = t.$task.scrollLeft,
                _ = o + 30 * s;
                if (t.scrollTo(_, null), t.$scroll_hor.scrollLeft = _, o == t.$task.scrollLeft) return ! 0
            } else {
                if (n.y) return ! 0;
                if (!t.$scroll_ver || !t.$scroll_ver.offsetHeight) return ! 0;
                var s = r / -40;
                "undefined" == typeof r && (s = e.detail);
                var l = t.$scroll_ver.scrollTop,
                d = t.$scroll_ver.scrollTop + 30 * s;
                if (!t.config.prevent_default_scroll && t._cached_scroll_pos && (t._cached_scroll_pos.y == d || t._cached_scroll_pos.y <= 0 && 0 >= d)) return ! 0;
                if (t.scrollTo(null, d), t.$scroll_ver.scrollTop = d, l == t.$scroll_ver.scrollTop) return ! 0
            }
            return e.preventDefault && e.preventDefault(),
            e.cancelBubble = !0,
            !1
        }
        this.event(this.$scroll_hor, "scroll",
        function() {
            if (new Date - (t._wheel_time || 0) < 100) return ! 0;
            if (!t._touch_scroll_active) {
                var e = t.$scroll_hor.scrollLeft;
                t.scrollTo(e)
            }
        }),
        this.event(this.$scroll_ver, "scroll",
        function() {
            if (!t._touch_scroll_active) {
                var e = t.$scroll_ver.scrollTop,
                i = t.$scroll_ver.prevTop;
                e != i && (t.$scroll_ver.prevTop = e, t.scrollTo(null, e))
            }
        }),
        this.event(this.$task, "scroll",
        function() {
            var e = t.$task.scrollLeft,
            i = t.$scroll_hor.scrollLeft;
            i != e && (t.$scroll_hor.scrollLeft = e)
        }),
        this.event(this.$task_data, "scroll",
        function() {
            var e = t.$task_data.scrollTop,
            i = t.$scroll_ver.scrollTop;
            i != e && (t.$scroll_ver.scrollTop = e)
        });
        var i = t.env.isFF;
        i ? this.event(t.$container, "wheel", e) : this.event(t.$container, "mousewheel", e)
    },
    t._scroll_resize = function() {
        if (! (this._x < 20 || this._y < 20)) {
            var t = this._scroll_sizes();
            t.x ? (this.$scroll_hor.style.display = "block", this.$scroll_hor.style.height = t.scroll_size + "px", this.$scroll_hor.style.width = t.x + "px", this.$scroll_hor.firstChild.style.width = t.x_inner + "px") : (this.$scroll_hor.style.display = "none", this.$scroll_hor.style.height = this.$scroll_hor.style.width = "0px"),
            t.y ? (this.$scroll_ver.style.display = "block", this.$scroll_ver.style.width = t.scroll_size + "px", this.$scroll_ver.style.height = t.y + "px", this.$scroll_ver.style.top = this.config.scale_height + "px", this.$scroll_ver.firstChild.style.height = t.y_inner + "px") : (this.$scroll_ver.style.display = "none", this.$scroll_ver.style.width = this.$scroll_ver.style.height = "0px")
        }
    },
    t._scroll_sizes = function() {
        var t = this._get_grid_width(),
        e = Math.max(this._x - t, 0),
        i = Math.max(this._y - this.config.scale_height, 0),
        n = this.config.scroll_size + 1,
        a = this._get_resize_options(),
        r = this.config.row_height * this._order.length,
        s = this._scroll_ver = a.y ? !1 : r > i,
        o = Math.max(this._tasks.full_width - (s ? 0 : n), 0),
        _ = this._scroll_hor = a.x ? !1 : o > e,
        l = {
            x: !1,
            y: !1,
            scroll_size: n,
            x_inner: o + t + n + 2,
            y_inner: r
        };
        return _ && (l.x = Math.max(this._x - (s ? n: 2), 0)),
        s && (l.y = Math.max(this._y - (_ ? n: 2) - this.config.scale_height, 0)),
        l
    },
    t._getClassName = function(e) {
        if (!e) return "";
        var i = e.className || "";
        return i.baseVal && (i = i.baseVal),
        i.indexOf || (i = ""),
        t._trim(i)
    },
    t.locate = function(e) {
        var i = t._get_target_node(e),
        n = t._getClassName(i);
        if ((n || "").indexOf("gantt_task_cell") >= 0) return null;
        for (var a = arguments[1] || this.config.task_attribute; i;) {
            if (i.getAttribute) {
                var r = i.getAttribute(a);
                if (r) return r
            }
            i = i.parentNode
        }
        return null
    },
    t._get_target_node = function(t) {
        var e;
        return t.tagName ? e = t: (t = t || window.event, e = t.target || t.srcElement),
        e
    },
    t._trim = function(t) {
        var e = String.prototype.trim ||
        function() {
            return this.replace(/^\s+|\s+$/g, "")
        };
        return e.apply(t)
    },
    t._locate_css = function(e, i, n) {
        void 0 === n && (n = !0);
        for (var a = t._get_target_node(e), r = ""; a;) {
            if (r = t._getClassName(a)) {
                var s = r.indexOf(i);
                if (s >= 0) {
                    if (!n) return a;
                    var o = 0 === s || !t._trim(r.charAt(s - 1)),
                    _ = s + i.length >= r.length || !t._trim(r.charAt(s + i.length));
                    if (o && _) return a
                }
            }
            a = a.parentNode
        }
        return null
    },
    t._locateHTML = function(e, i) {
        var n = t._get_target_node(e);
        for (i = i || this.config.task_attribute; n;) {
            if (n.getAttribute) {
                var a = n.getAttribute(i);
                if (a) return n
            }
            n = n.parentNode
        }
        return null
    },
    t.getTaskRowNode = function(t) {
        for (var e = this.$grid_data.childNodes,
        i = this.config.task_attribute,
        n = 0; n < e.length; n++) if (e[n].getAttribute) {
            var a = e[n].getAttribute(i);
            if (a == t) return e[n]
        }
        return null
    },
    t.getState = function() {
        return {
            drag_id: this._tasks_dnd.drag ? this._tasks_dnd.drag.id: void 0,
            drag_mode: this._tasks_dnd.drag ? this._tasks_dnd.drag.mode: void 0,
            drag_from_start: this._tasks_dnd.drag ? this._tasks_dnd.drag.left: void 0,
            selected_task: this._selected_task,
            min_date: this._min_date ? new Date(this._min_date) : void 0,
            max_date: this._max_date ? new Date(this._max_date) : void 0,
            lightbox: this._lightbox_id,
            touch_drag: this._touch_drag,
            scale_unit: this._tasks ? this._tasks.unit: void 0,
            scale_step: this._tasks ? this._tasks.step: void 0
        }
    },
    t._checkTimeout = function(t, e) {
        if (!e) return ! 0;
        var i = 1e3 / e;
        return 1 > i ? !0 : t._on_timeout ? !1 : (setTimeout(function() {
            delete t._on_timeout
        },
        i), t._on_timeout = !0, !0)
    },
    t.selectTask = function(t) {
        if (!this.config.select_task) return ! 1;
        if (t) {
            if (this._selected_task == t) return this._selected_task;
            if (!this.callEvent("onBeforeTaskSelected", [t])) return ! 1;
            this.unselectTask(),
            this._selected_task = t,
            this.refreshTask(t),
            this.callEvent("onTaskSelected", [t])
        }
        return this._selected_task
    },
    t.unselectTask = function(t) {
        var t = t || this._selected_task;
        t && (this._selected_task = null, this.refreshTask(t), this.callEvent("onTaskUnselected", [t]))
    },
    t.getSelectedId = function() {
        return this.defined(this._selected_task) ? this._selected_task: null
    },
    t.changeLightboxType = function(e) {
        return this.getLightboxType() == e ? !0 : void t._silent_redraw_lightbox(e)
    },
    t._is_render_active = function() {
        return ! this._skip_render
    },
    t._correct_dst_change = function(e, i, n, a) {
        var r = t._get_line(a) * n;
        if (r > 3600 && 86400 > r) {
            var s = e.getTimezoneOffset() - i;
            s && (e = t.date.add(e, s, "minute"))
        }
        return e
    },
    function() {
        var e = {};
        t._disableMethod = function(t, i) {
            i = "function" == typeof i ? i: function() {},
            e[t] || (e[t] = this[t], this[t] = i);
        },
        t._restoreMethod = function(t) {
            e[t] && (this[t] = e[t], e[t] = null)
        },
        t._disableMethods = function(t) {
            for (var e in t) this._disableMethod(e, t[e])
        },
        t._restoreMethods = function() {
            for (var t in e) this._restoreMethod(t)
        }
    } (),
    t._batchUpdatePayload = function(t) {
        try {
            t()
        } catch(e) {
            window.console.error(e)
        }
    },
    t.batchUpdate = function(t, e) {
        if (!this._is_render_active()) return void this._batchUpdatePayload(t);
        var i, n = this._dp && "off" != this._dp.updateMode;
        n && (i = this._dp.updateMode, this._dp.setUpdateMode("off"));
        var a = {},
        r = {
            _sync_order: !0,
            _sync_links: !0,
            _adjust_scales: !0,
            render: !0,
            _render_data: !0,
            refreshTask: !0,
            refreshLink: !0,
            resetProjectDates: function(t) {
                a[t.id] = t
            }
        };
        this._disableMethods(r),
        this._skip_render = !0,
        this.callEvent("onBeforeBatchUpdate", []),
        this._batchUpdatePayload(t),
        this.callEvent("onAfterBatchUpdate", []),
        this._restoreMethods(),
        this._sync_order(),
        this._sync_links();
        for (var s in a) this.resetProjectDates(a[s]);
        this._adjust_scales(),
        this._skip_render = !1,
        e || this.render(),
        n && (this._dp.setUpdateMode(i), this._dp.setGanttMode("tasks"), this._dp.sendData(), this._dp.setGanttMode("links"), this._dp.sendData())
    },
    t.env = {
        isIE: navigator.userAgent.indexOf("MSIE") >= 0 || navigator.userAgent.indexOf("Trident") >= 0,
        isIE6: !window.XMLHttpRequest && navigator.userAgent.indexOf("MSIE") >= 0,
        isIE7: navigator.userAgent.indexOf("MSIE 7.0") >= 0 && navigator.userAgent.indexOf("Trident") < 0,
        isIE8: navigator.userAgent.indexOf("MSIE 8.0") >= 0 && navigator.userAgent.indexOf("Trident") >= 0,
        isOpera: navigator.userAgent.indexOf("Opera") >= 0,
        isChrome: navigator.userAgent.indexOf("Chrome") >= 0,
        isKHTML: navigator.userAgent.indexOf("Safari") >= 0 || navigator.userAgent.indexOf("Konqueror") >= 0,
        isFF: navigator.userAgent.indexOf("Firefox") >= 0,
        isIPad: navigator.userAgent.search(/iPad/gi) >= 0,
        isEdge: -1 != navigator.userAgent.indexOf("Edge")
    },
    t.ajax = {
        cache: !0,
        method: "get",
        parse: function(e) {
            if ("string" != typeof e) return e;
            var i;
            return e = e.replace(/^[\s]+/, ""),
            window.DOMParser && !t.env.isIE ? i = (new window.DOMParser).parseFromString(e, "text/xml") : window.ActiveXObject !== window.undefined && (i = new window.ActiveXObject("Microsoft.XMLDOM"), i.async = "false", i.loadXML(e)),
            i
        },
        xmltop: function(e, i, n) {
            if ("undefined" == typeof i.status || i.status < 400) {
                var a = i.responseXML ? i.responseXML || i: t.ajax.parse(i.responseText || i);
                if (a && null !== a.documentElement && !a.getElementsByTagName("parsererror").length) return a.getElementsByTagName(e)[0]
            }
            return - 1 !== n && t.callEvent("onLoadXMLError", ["Incorrect XML", arguments[1], n]),
            document.createElement("DIV")
        },
        xpath: function(e, i) {
            if (i.nodeName || (i = i.responseXML || i), t.env.isIE) return i.selectNodes(e) || [];
            for (var n, a = [], r = (i.ownerDocument || i).evaluate(e, i, null, XPathResult.ANY_TYPE, null);;) {
                if (n = r.iterateNext(), !n) break;
                a.push(n)
            }
            return a
        },
        query: function(e) {
            t.ajax._call(e.method || "GET", e.url, e.data || "", e.async || !0, e.callback, null, e.headers)
        },
        get: function(t, e) {
            this._call("GET", t, null, !0, e)
        },
        getSync: function(t) {
            return this._call("GET", t, null, !1)
        },
        put: function(t, e, i) {
            this._call("PUT", t, e, !0, i)
        },
        del: function(t, e, i) {
            this._call("DELETE", t, e, !0, i)
        },
        post: function(t, e, i) {
            1 == arguments.length ? e = "": 2 != arguments.length || "function" != typeof e && "function" != typeof window[e] ? e = String(e) : (i = e, e = ""),
            this._call("POST", t, e, !0, i);
        },
        postSync: function(t, e) {
            return e = null === e ? "": String(e),
            this._call("POST", t, e, !1)
        },
        getLong: function(t, e) {
            this._call("GET", t, null, !0, e, {
                url: t
            })
        },
        postLong: function(t, e, i) {
            2 == arguments.length && (i = e, e = ""),
            this._call("POST", t, e, !0, i, {
                url: t,
                postData: e
            })
        },
        _call: function(e, i, n, a, r, s, o) {
            var _ = window.XMLHttpRequest && !t.env.isIE ? new XMLHttpRequest: new ActiveXObject("Microsoft.XMLHTTP"),
            l = null !== navigator.userAgent.match(/AppleWebKit/) && null !== navigator.userAgent.match(/Qt/) && null !== navigator.userAgent.match(/Safari/);
            if (a && (_.onreadystatechange = function() {
                if (4 == _.readyState || l && 3 == _.readyState) {
                    if ((200 != _.status || "" === _.responseText) && !t.callEvent("onAjaxError", [_])) return;
                    window.setTimeout(function() {
                        "function" == typeof r && r.apply(window, [{
                            xmlDoc: _,
                            filePath: i
                        }]),
                        s && ("undefined" != typeof s.postData ? t.ajax.postLong(s.url, s.postData, r) : t.ajax.getLong(s.url, r)),
                        r = null,
                        _ = null
                    },
                    1)
                }
            }), "GET" != e || this.cache || (i += (i.indexOf("?") >= 0 ? "&": "?") + "dhxr" + (new Date).getTime() + "=1"), _.open(e, i, a), o) for (var d in o) _.setRequestHeader(d, o[d]);
            else "POST" == e.toUpperCase() || "PUT" == e || "DELETE" == e ? _.setRequestHeader("Content-Type", "application/x-www-form-urlencoded") : "GET" == e && (n = null);
            return _.setRequestHeader("X-Requested-With", "XMLHttpRequest"),
            _.send(n),
            a ? void 0 : {
                xmlDoc: _,
                filePath: i
            }
        }
    },
    t._urlSeparator = function(t) {
        return - 1 != t.indexOf("?") ? "&": "?"
    },
    function() {
        function e(e, i) {
            var n = e.callback;
            t.modalbox.hide(e.box),
            g = e.box = null,
            n && n(i)
        }
        function i(i) {
            if (g) {
                i = i || event;
                var n = i.which || event.keyCode,
                a = !1;
                if (t.message.keyboard) {
                    if (13 == n || 32 == n) {
                        var r = i.target || i.srcElement;
                        t._getClassName(r).indexOf("gantt_popup_button") > -1 && r.click ? r.click() : (e(g, !0), a = !0)
                    }
                    27 == n && (e(g, !1), a = !0)
                }
                if (a) return i.preventDefault && i.preventDefault(),
                !(i.cancelBubble = !0)
            } else;
        }
        function n(t) {
            n.cover || (n.cover = document.createElement("DIV"), n.cover.onkeydown = i, n.cover.className = "dhx_modal_cover", document.body.appendChild(n.cover));
            document.body.scrollHeight;
            n.cover.style.display = t ? "inline-block": "none"
        }
        function a(e, i) {
            var n = t._waiAria.messageButtonAttrString(e),
            a = "gantt_" + e.toLowerCase().replace(/ /g, "_") + "_button dhtmlx_" + e.toLowerCase().replace(/ /g, "_") + "_button";
            return "<div " + n + " class='gantt_popup_button dhtmlx_popup_button " + a + "' result='" + i + "' ><div>" + e + "</div></div>";
        }
        function r(e) {
            f.area || (f.area = document.createElement("DIV"), f.area.className = "gantt_message_area dhtmlx_message_area", f.area.style[f.position] = "5px", document.body.appendChild(f.area)),
            f.hide(e.id);
            var i = document.createElement("DIV");
            return i.innerHTML = "<div>" + e.text + "</div>",
            i.className = "gantt-info dhtmlx-info gantt-" + e.type + " dhtmlx-" + e.type,
            i.onclick = function() {
                f.hide(e.id),
                e = null
            },
            t._waiAria.messageInfoAttr(i),
            "bottom" == f.position && f.area.firstChild ? f.area.insertBefore(i, f.area.firstChild) : f.area.appendChild(i),
            e.expire > 0 && (f.timers[e.id] = window.setTimeout(function() {
                f.hide(e.id)
            },
            e.expire)),
            f.pull[e.id] = i,
            i = null,
            e.id
        }
        function s() {
            for (var t = [].slice.apply(arguments, [0]), e = 0; e < t.length; e++) if (t[e]) return t[e]
        }
        function o(i, n, r) {
            var o = document.createElement("DIV"),
            _ = t.uid();
            t._waiAria.messageModalAttr(o, _),
            o.className = " gantt_modal_box dhtmlx_modal_box gantt-" + i.type + " dhtmlx-" + i.type,
            o.setAttribute("dhxbox", 1);
            var l = "";
            if (i.width && (o.style.width = i.width), i.height && (o.style.height = i.height), i.title && (l += '<div class="gantt_popup_title dhtmlx_popup_title">' + i.title + "</div>"), l += '<div class="gantt_popup_text dhtmlx_popup_text" id="' + _ + '"><span>' + (i.content ? "": i.text) + '</span></div><div  class="gantt_popup_controls dhtmlx_popup_controls">', n && (l += a(s(i.ok, t.locale.labels.message_ok, "OK"), !0)), r && (l += a(s(i.cancel, t.locale.labels.message_cancel, "Cancel"), !1)), i.buttons) for (var d = 0; d < i.buttons.length; d++) l += a(i.buttons[d], d);
            if (l += "</div>", o.innerHTML = l, i.content) {
                var c = i.content;
                "string" == typeof c && (c = document.getElementById(c)),
                "none" == c.style.display && (c.style.display = ""),
                o.childNodes[i.title ? 1 : 0].appendChild(c)
            }
            return o.onclick = function(t) {
                t = t || event;
                var n = t.target || t.srcElement;
                if (n.className || (n = n.parentNode), "gantt_popup_button" == n.className.split(" ")[0]) {
                    var a = n.getAttribute("result");
                    a = "true" == a || ("false" == a ? !1 : a),
                    e(i, a)
                }
            },
            i.box = o,
            (n || r) && (g = i),
            o
        }
        function _(e, a, r) {
            var s = e.tagName ? e: o(e, a, r);
            e.hidden || n(!0),
            document.body.appendChild(s);
            var _ = Math.abs(Math.floor(((window.innerWidth || document.documentElement.offsetWidth) - s.offsetWidth) / 2)),
            l = Math.abs(Math.floor(((window.innerHeight || document.documentElement.offsetHeight) - s.offsetHeight) / 2));
            return "top" == e.position ? s.style.top = "-3px": s.style.top = l + "px",
            s.style.left = _ + "px",
            s.onkeydown = i,
            t.modalbox.focus(s),
            e.hidden && t.modalbox.hide(s),
            t.callEvent("onMessagePopup", [s]),
            s
        }
        function l(t) {
            return _(t, !0, !1)
        }
        function d(t) {
            return _(t, !0, !0)
        }
        function c(t) {
            return _(t)
        }
        function h(t, e, i) {
            return "object" != typeof t && ("function" == typeof e && (i = e, e = ""), t = {
                text: t,
                type: e,
                callback: i
            }),
            t
        }
        function u(t, e, i, n) {
            return "object" != typeof t && (t = {
                text: t,
                type: e,
                expire: i,
                id: n
            }),
            t.id = t.id || f.uid(),
            t.expire = t.expire || f.expire,
            t
        }
        var g = null;
        document.attachEvent ? document.attachEvent("onkeydown", i) : document.addEventListener("keydown", i, !0),
        t.alert = function() {
            var t = h.apply(this, arguments);
            return t.type = t.type || "confirm",
            l(t)
        },
        t.confirm = function() {
            var t = h.apply(this, arguments);
            return t.type = t.type || "alert",
            d(t)
        },
        t.modalbox = function() {
            var t = h.apply(this, arguments);
            return t.type = t.type || "alert",
            c(t)
        },
        t.modalbox.hide = function(e) {
            for (; e && e.getAttribute && !e.getAttribute("dhxbox");) e = e.parentNode;
            e && (e.parentNode.removeChild(e), n(!1), t.callEvent("onAfterMessagePopup", [e]))
        },
        t.modalbox.focus = function(e) {
            setTimeout(function() {
                var i = t._getFocusableNodes(e);
                i.length && i[0].focus && i[0].focus()
            },
            1)
        };
        var f = t.message = function(t, e, i, n) {
            t = u.apply(this, arguments),
            t.type = t.type || "info";
            var a = t.type.split("-")[0];
            switch (a) {
            case "alert":
                return l(t);
            case "confirm":
                return d(t);
            case "modalbox":
                return c(t);
            default:
                return r(t)
            }
        };
        f.seed = (new Date).valueOf(),
        f.uid = function() {
            return f.seed++
        },
        f.expire = 4e3,
        f.keyboard = !0,
        f.position = "top",
        f.pull = {},
        f.timers = {},
        f.hideAll = function() {
            for (var t in f.pull) f.hide(t)
        },
        f.hide = function(t) {
            var e = f.pull[t];
            e && e.parentNode && (window.setTimeout(function() {
                e.parentNode.removeChild(e),
                e = null
            },
            2e3), e.className += " hidden", f.timers[t] && window.clearTimeout(f.timers[t]), delete f.pull[t])
        }
    } (),
    t.date = {
        init: function() {
            for (var e = t.locale.date.month_short,
            i = t.locale.date.month_short_hash = {},
            n = 0; n < e.length; n++) i[e[n]] = n;
            for (var e = t.locale.date.month_full,
            i = t.locale.date.month_full_hash = {},
            n = 0; n < e.length; n++) i[e[n]] = n
        },
        date_part: function(t) {
            var e = new Date(t);
            return t.setHours(0),
            this.hour_start(t),
            t.getHours() && (t.getDate() < e.getDate() || t.getMonth() < e.getMonth() || t.getFullYear() < e.getFullYear()) && t.setTime(t.getTime() + 36e5 * (24 - t.getHours())),
            t
        },
        time_part: function(t) {
            return (t.valueOf() / 1e3 - 60 * t.getTimezoneOffset()) % 86400
        },
        week_start: function(e) {
            var i = e.getDay();
            return t.config.start_on_monday && (0 === i ? i = 6 : i--),
            this.date_part(this.add(e, -1 * i, "day"))
        },
        month_start: function(t) {
            return t.setDate(1),
            this.date_part(t)
        },
        year_start: function(t) {
            return t.setMonth(0),
            this.month_start(t)
        },
        day_start: function(t) {
            return this.date_part(t)
        },
        hour_start: function(t) {
            return t.getMinutes() && t.setMinutes(0),
            this.minute_start(t),
            t
        },
        minute_start: function(t) {
            return t.getSeconds() && t.setSeconds(0),
            t.getMilliseconds() && t.setMilliseconds(0),
            t
        },
        _add_days: function(t, e) {
            var i = new Date(t.valueOf());
            return i.setDate(i.getDate() + e),
            e >= 0 && !t.getHours() && i.getHours() && (i.getDate() <= t.getDate() || i.getMonth() < t.getMonth() || i.getFullYear() < t.getFullYear()) && i.setTime(i.getTime() + 36e5 * (24 - i.getHours())),
            i
        },
        add: function(e, i, n) {
            var a = new Date(e.valueOf());
            switch (n) {
            case "day":
                a = t.date._add_days(a, i);
                break;
            case "week":
                a = t.date._add_days(a, 7 * i);
                break;
            case "month":
                a.setMonth(a.getMonth() + i);
                break;
            case "year":
                a.setYear(a.getFullYear() + i);
                break;
            case "hour":
                a.setTime(a.getTime() + 60 * i * 60 * 1e3);
                break;
            case "minute":
                a.setTime(a.getTime() + 60 * i * 1e3);
                break;
            default:
                return t.date["add_" + n](e, i, n)
            }
            return a
        },
        to_fixed: function(t) {
            return 10 > t ? "0" + t: t
        },
        copy: function(t) {
            return new Date(t.valueOf())
        },
        date_to_str: function(t, e) {
            return t = t.replace(/%[a-zA-Z]/g,
            function(t) {
                switch (t) {
                case "%d":
                    return '"+gantt.date.to_fixed(date.getDate())+"';
                case "%m":
                    return '"+gantt.date.to_fixed((date.getMonth()+1))+"';
                case "%j":
                    return '"+date.getDate()+"';
                case "%n":
                    return '"+(date.getMonth()+1)+"';
                case "%y":
                    return '"+gantt.date.to_fixed(date.getFullYear()%100)+"';
                case "%Y":
                    return '"+date.getFullYear()+"';
                case "%D":
                    return '"+gantt.locale.date.day_short[date.getDay()]+"';
                case "%l":
                    return '"+gantt.locale.date.day_full[date.getDay()]+"';
                case "%M":
                    return '"+gantt.locale.date.month_short[date.getMonth()]+"';
                case "%F":
                    return '"+gantt.locale.date.month_full[date.getMonth()]+"';
                case "%h":
                    return '"+gantt.date.to_fixed((date.getHours()+11)%12+1)+"';
                case "%g":
                    return '"+((date.getHours()+11)%12+1)+"';
                case "%G":
                    return '"+date.getHours()+"';
                case "%H":
                    return '"+gantt.date.to_fixed(date.getHours())+"';
                case "%i":
                    return '"+gantt.date.to_fixed(date.getMinutes())+"';
                case "%a":
                    return '"+(date.getHours()>11?"pm":"am")+"';
                case "%A":
                    return '"+(date.getHours()>11?"PM":"AM")+"';
                case "%s":
                    return '"+gantt.date.to_fixed(date.getSeconds())+"';
                case "%W":
                    return '"+gantt.date.to_fixed(gantt.date.getISOWeek(date))+"';
                default:
                    return t
                }
            }),
            e && (t = t.replace(/date\.get/g, "date.getUTC")),
            new Function("date", 'return "' + t + '";')
        },
        str_to_date: function(t, e) {
            for (var i = "var temp=date.match(/[a-zA-Z]+|[0-9]+/g);",
            n = t.match(/%[a-zA-Z]/g), a = 0; a < n.length; a++) switch (n[a]) {
            case "%j":
            case "%d":
                i += "set[2]=temp[" + a + "]||1;";
                break;
            case "%n":
            case "%m":
                i += "set[1]=(temp[" + a + "]||1)-1;";
                break;
            case "%y":
                i += "set[0]=temp[" + a + "]*1+(temp[" + a + "]>50?1900:2000);";
                break;
            case "%g":
            case "%G":
            case "%h":
            case "%H":
                i += "set[3]=temp[" + a + "]||0;";
                break;
            case "%i":
                i += "set[4]=temp[" + a + "]||0;";
                break;
            case "%Y":
                i += "set[0]=temp[" + a + "]||0;";
                break;
            case "%a":
            case "%A":
                i += "set[3]=set[3]%12+((temp[" + a + "]||'').toLowerCase()=='am'?0:12);";
                break;
            case "%s":
                i += "set[5]=temp[" + a + "]||0;";
                break;
            case "%M":
                i += "set[1]=gantt.locale.date.month_short_hash[temp[" + a + "]]||0;";
                break;
            case "%F":
                i += "set[1]=gantt.locale.date.month_full_hash[temp[" + a + "]]||0;"
            }
            var r = "set[0],set[1],set[2],set[3],set[4],set[5]";
            return e && (r = " Date.UTC(" + r + ")"),
            new Function("date", "var set=[0,0,1,0,0,0]; " + i + " return new Date(" + r + ");")
        },
        getISOWeek: function(t) {
            if (!t) return ! 1;
            var e = t.getDay();
            0 === e && (e = 7);
            var i = new Date(t.valueOf());
            i.setDate(t.getDate() + (4 - e));
            var n = i.getFullYear(),
            a = Math.round((i.getTime() - new Date(n, 0, 1).getTime()) / 864e5),
            r = 1 + Math.floor(a / 7);
            return r
        },
        getUTCISOWeek: function(t) {
            return this.getISOWeek(t)
        },
        convert_to_utc: function(t) {
            return new Date(t.getUTCFullYear(), t.getUTCMonth(), t.getUTCDate(), t.getUTCHours(), t.getUTCMinutes(), t.getUTCSeconds());
        },
        parseDate: function(e, i) {
            return e && !e.getFullYear && (t.defined(i) && (i = "string" == typeof i ? t.defined(t.templates[i]) ? t.templates[i] : t.date.str_to_date(i) : t.templates.xml_date), e = e ? i(e) : null),
            e
        }
    },
    t.date.quarter_start = function(e) {
        t.date.month_start(e);
        var i, n = e.getMonth();
        return i = n >= 9 ? 9 : n >= 6 ? 6 : n >= 3 ? 3 : 0,
        e.setMonth(i),
        e
    },
    t.date.add_quarter = function(e, i) {
        return t.date.add(e, 3 * i, "month")
    },
    window.jQuery && !
    function(t) {
        var e = [];
        t.fn.dhx_gantt = function(i) {
            if (i = i || {},
            "string" != typeof i) {
                var n = [];
                return this.each(function() {
                    if (this && this.getAttribute) if (this.gantt || window.gantt._obj == this) n.push("object" == typeof this.gantt ? this.gantt: window.gantt);
                    else {
                        var t = window.gantt.$container ? Gantt.getGanttInstance() : window.gantt;
                        for (var e in i)"data" != e && (t.config[e] = i[e]);
                        t.init(this),
                        i.data && t.parse(i.data),
                        n.push(t)
                    }
                }),
                1 === n.length ? n[0] : n
            }
            return e[i] ? e[i].apply(this, []) : void t.error("Method " + i + " does not exist on jQuery.dhx_gantt")
        }
    } (jQuery),
    t.locale = {
        date: {
            month_full: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            month_short: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            day_full: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            day_short: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
        },
        labels: {
            new_task: "New task",
            icon_save: "Save",
            icon_cancel: "Cancel",
            icon_details: "Details",
            icon_edit: "Edit",
            icon_delete: "Delete",
            confirm_closing: "",
            confirm_deleting: "Task will be deleted permanently, are you sure?",
            section_description: "Description",
            section_time: "Time period",
            section_type: "Type",
            column_text: "Task name",
            column_start_date: "Start time",
            column_duration: "Duration",
            column_add: "",
            link: "Link",
            confirm_link_deleting: "will be deleted",
            link_start: " (start)",
            link_end: " (end)",
            type_task: "Task",
            type_project: "Project",
            type_milestone: "Milestone",
            minutes: "Minutes",
            hours: "Hours",
            days: "Days",
            weeks: "Week",
            months: "Months",
            years: "Years",
            message_ok: "OK",
            message_cancel: "Cancel"
        }
    },
    t.skins.skyblue = {
        config: {
            grid_width: 350,
            row_height: 27,
            scale_height: 27,
            link_line_width: 1,
            link_arrow_size: 8,
            lightbox_additional_height: 75
        },
        _second_column_width: 95,
        _third_column_width: 80
    },
    t.skins.meadow = {
        config: {
            grid_width: 350,
            row_height: 27,
            scale_height: 30,
            link_line_width: 2,
            link_arrow_size: 6,
            lightbox_additional_height: 72
        },
        _second_column_width: 95,
        _third_column_width: 80
    },
    t.skins.terrace = {
        config: {
            grid_width: 360,
            row_height: 35,
            scale_height: 35,
            link_line_width: 2,
            link_arrow_size: 6,
            lightbox_additional_height: 75
        },
        _second_column_width: 90,
        _third_column_width: 70
    },
    t.skins.broadway = {
        config: {
            grid_width: 360,
            row_height: 35,
            scale_height: 35,
            link_line_width: 1,
            link_arrow_size: 7,
            lightbox_additional_height: 86
        },
        _second_column_width: 90,
        _third_column_width: 80,
        _lightbox_template: "<div class='gantt_cal_ltitle'><span class='gantt_mark'>&nbsp;</span><span class='gantt_time'></span><span class='gantt_title'></span><div class='gantt_cancel_btn'></div></div><div class='gantt_cal_larea'></div>",
        _config_buttons_left: {},
        _config_buttons_right: {
            gantt_delete_btn: "icon_delete",
            gantt_save_btn: "icon_save"
        }
    },
    t.skins.contrast_black = {
        config: {
            grid_width: 360,
            row_height: 35,
            scale_height: 35,
            link_line_width: 2,
            link_arrow_size: 6,
            lightbox_additional_height: 75
        },
        _second_column_width: 100,
        _third_column_width: 80
    },
    t.skins.contrast_white = {
        config: {
            grid_width: 360,
            row_height: 35,
            scale_height: 35,
            link_line_width: 2,
            link_arrow_size: 6,
            lightbox_additional_height: 75
        },
        _second_column_width: 100,
        _third_column_width: 80
    },
    t.config.touch_drag = 500,
    t.config.touch = !0,
    t.config.touch_feedback = !0,
    t.config.touch_feedback_duration = 1,
    t._prevent_touch_scroll = !1,
    t._touch_feedback = function() {
        t.config.touch_feedback && navigator.vibrate && navigator.vibrate(t.config.touch_feedback_duration);
    },
    t._init_touch_events = function() {
        if ("force" != this.config.touch && (this.config.touch = this.config.touch && ( - 1 != navigator.userAgent.indexOf("Mobile") || -1 != navigator.userAgent.indexOf("iPad") || -1 != navigator.userAgent.indexOf("Android") || -1 != navigator.userAgent.indexOf("Touch"))), this.config.touch) {
            var t = !0;
            try {
                document.createEvent("TouchEvent")
            } catch(e) {
                t = !1
            }
            t ? this._touch_events(["touchmove", "touchstart", "touchend"],
            function(t) {
                return t.touches && t.touches.length > 1 ? null: t.touches[0] ? {
                    target: t.target,
                    pageX: t.touches[0].pageX,
                    pageY: t.touches[0].pageY,
                    clientX: t.touches[0].clientX,
                    clientY: t.touches[0].clientY
                }: t
            },
            function() {
                return ! 1
            }) : window.navigator.pointerEnabled ? this._touch_events(["pointermove", "pointerdown", "pointerup"],
            function(t) {
                return "mouse" == t.pointerType ? null: t
            },
            function(t) {
                return ! t || "mouse" == t.pointerType
            }) : window.navigator.msPointerEnabled && this._touch_events(["MSPointerMove", "MSPointerDown", "MSPointerUp"],
            function(t) {
                return t.pointerType == t.MSPOINTER_TYPE_MOUSE ? null: t
            },
            function(t) {
                return ! t || t.pointerType == t.MSPOINTER_TYPE_MOUSE;
            })
        }
    },
    t._touch_events = function(e, i, n) {
        function a(t) {
            return t && t.preventDefault && t.preventDefault(),
            (t || event).cancelBubble = !0,
            !1
        }
        function r(e) {
            var i = t._task_area_pulls,
            n = t.getTask(e);
            if (n && t.isTaskVisible(e)) for (var a in i) if (n = i[a][e], n && n.getAttribute("task_id") && n.getAttribute("task_id") == e) {
                var r = n.cloneNode(!0);
                return h = n,
                i[a][e] = r,
                n.style.display = "none",
                r.className += " gantt_drag_move ",
                n.parentNode.appendChild(r),
                r
            }
        }
        var s, o = 0,
        _ = !1,
        l = !1,
        d = null,
        c = null,
        h = null;
        this._gantt_touch_event_ready || (this._gantt_touch_event_ready = 1, t.event(t.$container, e[0],
        function(e) {
            if (!n(e) && _) {
                c && clearTimeout(c);
                var r = i(e);
                if (t._tasks_dnd.drag.id || t._tasks_dnd.drag.start_drag) return t._tasks_dnd.on_mouse_move(r),
                e.preventDefault && e.preventDefault(),
                e.cancelBubble = !0,
                !1;
                if (!t._prevent_touch_scroll) {
                    if (r && d) {
                        var h = d.pageX - r.pageX,
                        u = d.pageY - r.pageY;
                        if (!l && (Math.abs(h) > 5 || Math.abs(u) > 5) && (t._touch_scroll_active = l = !0, o = 0, s = t.getScrollState()), l) {
                            t.scrollTo(s.x + h, s.y + u);
                            var g = t.getScrollState();
                            if (s.x != g.x && u > 2 * h || s.y != g.y && h > 2 * u) return a(e);
                        }
                    }
                    return a(e)
                }
                return ! 0
            }
        })),
        t.event(this.$container, "contextmenu",
        function(t) {
            return _ ? a(t) : void 0
        }),
        t.event(this.$container, e[1],
        function(e) {
            if (!n(e)) {
                if (e.touches && e.touches.length > 1) return void(_ = !1);
                d = i(e),
                t._locate_css(d, "gantt_hor_scroll") || t._locate_css(d, "gantt_ver_scroll") || (_ = !0),
                c = setTimeout(function() {
                    var e = t.locate(d); ! e || t._locate_css(d, "gantt_link_control") || t._locate_css(d, "gantt_grid_data") || (t._tasks_dnd.on_mouse_down(d), t._tasks_dnd.drag && t._tasks_dnd.drag.start_drag && (r(e), t._tasks_dnd._start_dnd(d), t._touch_drag = !0, t.refreshTask(e), t._touch_feedback())),
                    c = null
                },
                t.config.touch_drag)
            }
        }),
        t.event(this.$container, e[2],
        function(e) {
            if (!n(e)) {
                c && clearTimeout(c),
                t._touch_drag = !1,
                _ = !1;
                var r = i(e);
                if (t._tasks_dnd.on_mouse_up(r), h && (t.refreshTask(t.locate(h)), h.parentNode && (h.parentNode.removeChild(h), t._touch_feedback())), t._touch_scroll_active = _ = l = !1, h = null, d && o) {
                    var s = new Date;
                    500 > s - o ? (t._on_dblclick(d), a(e)) : o = s
                } else o = new Date
            }
        })
    },
    function() {
        function e(e, i) {
            var n = t.env.isIE ? "": "%c",
            a = [n, '"', e, '"', n, " has been deprecated in dhtmlxGantt v4.0 and will stop working in v5.0. Use ", n, '"', i, '"', n, " instead. \nSee more details at http://docs.dhtmlx.com/gantt/migrating.html "].join(""),
            r = window.console.warn || window.console.log,
            s = [a];
            t.env.isIE || (s = s.concat(["font-weight:bold", "font-weight:normal", "font-weight:bold", "font-weight:normal"])),
            r.apply(window.console, s)
        }
        function i(i) {
            return function() {
                return e("dhtmlx." + i, "gantt." + i),
                t[i].apply(t, arguments)
            }
        }
        window.dhtmlx || (window.dhtmlx = {});
        for (var n = ["message", "alert", "confirm", "modalbox", "uid", "copy", "mixin", "defined", "bind", "assert"], a = 0; a < n.length; a++) window.dhtmlx[n[a]] || (dhtmlx[n[a]] = i(n[a]));
        window.dataProcessor || (window.dataProcessor = function(i) {
            return e("new dataProcessor(url)", "new gantt.dataProcessor(url)"),
            new t.dataProcessor(i)
        })
    } ();
    for (var e = 0; e < Gantt._ganttPlugin.length; e++) Gantt._ganttPlugin[e](t);
    return t._internal_id = Gantt._seed++,
    Gantt.$syncFactory && Gantt.$syncFactory(t),
    t
},
window.gantt = Gantt.getGanttInstance(),
dhtmlx && (dhtmlx.attaches = dhtmlx.attaches || [], dhtmlx.attaches.attachGantt = function(t, e, i) {
    var n = document.createElement("DIV");
    return n.id = "dhxSchedObj_" + this._genStr(12),
    n.style.cssText = "width:100%; height:100%;",
    document.body.appendChild(n),
    this.attachObject(n.id, !1, !0),
    i = i || (window.gantt.$container ? Gantt.getGanttInstance() : window.gantt),
    this.vs[this.av].sched = i,
    this.vs[this.av].schedId = n.id,
    i.destructor = function() {},
    i.init(n.id, t, e),
    this.vs[this._viewRestore()].sched
});