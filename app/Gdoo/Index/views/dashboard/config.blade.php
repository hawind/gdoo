<style type="text/css">
.dashboard-edit { background-color: #f0f3f4; }
.panel-shadow {
    box-shadow: 0px 3px 6px 0px rgba(0, 0, 0, 0.03);
    border: solid 1px rgba(0, 0, 0, 0.08);
    border-radius: 4px !important;
}
.edit-droppable div {
    border: 1px dashed #23b7e5;
    background: #dcf2f8;
    text-align: center;
    color: #ccc;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-radius: 4px;
}

.widget-edit { display: flex; flex-direction: row; align-items:stretch; }
.widget-edit .col-xs-12 {
    min-height: 100px;
}

.widget-edit-item .panel-heading {
    padding-left: 5px; 
    padding-right: 5px;
    cursor:move; 
}
.widget-edit-item > div {
    padding-left: 5px; 
    padding-right: 5px;
}

.todo-edit .panel { display: flex; padding-bottom: 10px; text-align: center; }
.todo-edit-l-t { cursor:move; color: #fff; margin-top:12px; margin-left: 10px; border-radius: 50%; width: 50px; height:50px; line-height:58px; vertical-align: middle; }

.todo-edit-c-t { flex:1; margin-left: 12px; text-align: left; }
.todo-edit-c-t .todo-name { margin-top:13px; color: #666; }
.todo-edit-c-t .todo-item { font-size: 24px; color: #666; }
.todo-edit-r-t { text-align: left; margin-right: 10px; margin-top:5px; }

.widget-option {
    padding-top:10px;
    text-align: right;
    padding-right: 8px;
}
.widget-option .fa {
    font-size: 14px;
    color: #999;
}
.widget-option:hover .fa {
    color: #0e90d2;
}

.quick-edit {
    margin-bottom: 10px;
    padding: 3px;
    padding-top: 10px;
}
.quick-edit-text {
    float: left;
    margin-right: 10px;
    padding: 5px;
}
.quick-edit-text .title {
    width: 48px;
    text-overflow: hidden;
    text-align: center;
    padding-top: 5px;
    color: #333;
}
.quick-edit-item {
    position: relative;
    cursor:move;
}
.quick-edit-item .quick-remove {
    line-height: 0;
    display: none;
}
.quick-edit-item:hover .quick-remove {
    display: block;
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #fff;
    height: 20px;
    line-height: 20px;
    width: 20px;
    z-index: 1;
    border-radius: 50%;
    box-shadow: -1px 1px 5px rgba(0, 0, 0, 0.10);
}
.quick-edit-item:hover .quick-remove .fa {
    font-size: 12px;
    color: #999;
}
.quick-edit-item:hover .quick-remove:hover .fa {
    color: #f00;
}
.quick-edit-add {
    background-color:#fff;
    line-height: 52px;
}
.quick-edit-add .fa {
    color:#999;
}
.quick-edit-add:hover .fa {
    color:#2490f8;
}
</style>

@verbatim
<div id="dashboard-config">
    <div class="dashboard-edit">
        <form method="post" id="widget-edit" name="widget_edit">
        <div class="quick-edit">
            <div class="row row-sm">
                <gdoo-draggable
                    v-model="menus"
                    item-key="id"
                    group="menus"
                    @start="drag=true"
                    @end="drag=false">
                    <template #item="{element, index}">
                        <div class="quick-edit-text">
                            <div class="quick-icon quick-edit-item" :style="'background-color:' + element.color">
                                <a class="quick-remove" @click="quickRemove(index);">
                                    <i class="fa fa-times"></i>
                                </a>
                                <i :class="'fa fa-3x ' + element.icon"></i>
                            </div>
                            <div class="title">{{element.name}}</div>
                        </div>
                    </template>
                </gdoo-draggable>
                <div class="quick-edit-text">
                    <a href="javascript:;" @click="quickAdd">
                        <div class="quick-icon quick-edit-add">
                        <i class="fa fa-3x fa-plus"></i>
                        </div>
                        <div class="title">添加快捷</div>
                    </a>
                </div>
            </div>
        </div>
        
        <gdoo-draggable 
            class="row row-sm todo-edit"
            v-model="infos"
            handle=".todo-edit-l-t"
            item-key="id"
            group="infos"
            @start="dragging=true"
            @end="dragging=false">
            <template #item="{element, index}">
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
                <div class="panel panel-shadow">
                    <div class="todo-edit-l-t" :style="'background-color:' + element.color">
                        <i :class="'fa fa-2x ' + element.icon"></i>
                    </div>
                    <div class="todo-edit-c-t">
                        <div class="todo-name">{{element.name}}</div>
                        <span class="todo-node">
                            <div class="text-info todo-item">0</div>
                        </span>
                    </div>
                    <div class="todo-edit-r-t">
                        <label title="显示/隐藏" class="i-switch bg-success m-t-xs">
                            <input type="checkbox" v-model="element.status" :true-value="1" :false-value="0"><i></i>
                        </label>
                        <div class="widget-option">
                            <a @click="infoOption(index, element)" title="配置">
                                <i class="fa fa-gear"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </gdoo-draggable>
        
        <div class="row row-sm widget-edit">
            <gdoo-draggable
                class="col-xs-12 col-sm-8"
                v-model="leftWidgets"
                handle=".panel-heading"
                item-key="id"
                group='widgets'
                @start="dragging=true"
                @end="dragging=false">
                    <template #item="{element, index}">
                    <div class="widget-edit-item" :id="'widget_item_' + element.id">
                        <div class="panel panel-shadow">
                            <div class="panel-heading text-base">
                                <div class="pull-right">
                                    <label title="显示/隐藏" class="i-switch bg-primary m-t-xs">
                                        <input type="checkbox" v-model="element.status" :true-value="1" :false-value="0"><i></i>
                                    </label>
                                    <div class="widget-option">
                                        <a @click="widgetOption(index, element)" title="配置">
                                            <i class="fa fa-gear"></i>
                                        </a>
                                    </div>
                                </div>
                                {{element.name}}
                            </div>
                            <div class="panel-body wrapper-sm"></div>
                        </div>
                    </div>
                </template>
            </gdoo-draggable>

            <gdoo-draggable
                class="col-xs-12 col-sm-4"
                v-model="rightWidgets"
                handle=".panel-heading"
                item-key="id"
                group='widgets'
                @start="dragging=true"
                @end="dragging=false">
                    <template #item="{element, index}">
                    <div class="widget-edit-item" :id="'widget_item_' + element.id">
                        <div class="panel panel-shadow">
                            <div class="panel-heading text-base">
                                <div class="pull-right">
                                    <label title="显示/隐藏" class="i-switch bg-primary m-t-xs">
                                        <input type="checkbox" v-model="element.status" :true-value="1" :false-value="0"><i></i>
                                    </label>
                                    <div class="widget-option">
                                        <a @click="widgetOption(index, element)" title="配置">
                                            <i class="fa fa-gear"></i>
                                        </a>
                                    </div>
                                </div>
                                {{element.name}}
                            </div>
                            <div class="panel-body wrapper-sm"></div>
                        </div>
                    </div>
                </template>
            </gdoo-draggable>
        </div>
    </div>
</div>
@endverbatim

<script>

var configData = {{$json}};
var settingWidget = Vue.createApp({
    components: {
        gdooDraggable,
    },
    data() {
        return {
            infos: configData.infos,
            widgets: configData.widgets,
            menus: configData.menus,
            leftWidgets: [],
            rightWidgets: [],
            dragging: false
        }
    },
    methods: {
        quickRemove(index) {
            this.menus.splice(index, 1);
        },
        quickAdd() {
            var me = this;
            formDialog({
                title: '添加菜单',
                url: app.url('index/dashboard/quickMenu'),
                id: 'quick-menu',
                dialogClass:'modal-sm',
                onSubmit: function() {
                    var items = $('#quick-menu').serializeArray();
                    var row = {};
                    $.each(items, function(index, item) {
                        row[item.name] = item.value;
                    });
                    me.menus.push(row);
                    $(this).dialog("close");
                }
            });
        },
        infoOption(index, item) {
            var me = this;
            formDialog({
                title: '配置',
                url: app.url('index/dashboard/settingInfo', {info_id:item.info_id}),
                id: 'setting-info',
                dialogClass:'modal-sm',
                onSubmit: function() {
                    var info = $('#setting-info').serializeArray();
                    $.each(info, function(k, v) {
                        item[v.name] = v.value;
                    });
                    me.infos[index] = item;
                    $(this).dialog("close");
                }
            });
        },
        widgetOption(index, item) {
            var me = this;
            formDialog({
                title: '配置',
                url: app.url('index/dashboard/settingWidget', {widget_id:item.widget_id}),
                id: 'setting-widget',
                dialogClass:'modal-sm',
                onSubmit: function() {
                    var widget = $('#setting-widget').serializeArray();
                    $.each(widget, function(k, v) {
                        item[v.name] = v.value;
                    });
                    if (item.grid == 8) {
                        me.leftWidgets[index] = item;
                    } else {
                        me.rightWidgets[index] = item;
                    }
                    $(this).dialog("close");
                }
            });
        },
        save() {
            var me = this;
            var res = {
                menu: me.menus,
                info: me.infos,
                widget: [],
            };
            me.leftWidgets.forEach(function(item) {
                item.grid = 8;
                res.widget.push(item);
            });
            me.rightWidgets.forEach(function(item) {
                item.grid = 4;
                res.widget.push(item);
            });
            return res;
        }
    },
    created () {
        this.leftWidgets = this.widgets.filter(function(item) {
            return item.grid == 8;
        });
        this.rightWidgets = this.widgets.filter(function(item) {
            return item.grid == 4;
        });
    }
}).mount('#dashboard-config');

</script>
<script src="{{$asset_url}}/vendor/fontawesome-iconpicker/js/fontawesome-iconpicker.min.js"></script>
<link href="{{$asset_url}}/vendor/fontawesome-iconpicker/css/fontawesome-iconpicker.min.css" rel="stylesheet">