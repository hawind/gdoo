<link href="{{$asset_url}}/vendor/element-plus/index.css" rel="stylesheet" type="text/css" />
<script src="{{$asset_url}}/vendor/element-plus/index.js" type="text/javascript"></script>

<style type="text/css">
html {
    overflow: hidden;
}
a { outline: none; }

.dashboard-widget {
    overflow: hidden;
    overflow-y: auto;
    height: calc(100vh - 32px);
    padding-top: 10px;
}

.dashboard-config {
    text-align: center;
    position: fixed;
    right: 22px;
    top: 0;
    background-color: rgba(255, 255, 255, 0.8);
    height: 26px;
    line-height: 25px;
    width: 26px;
    z-index: 1;
    border-radius: 0 0 4px 4px;
    border: solid 1px rgba(255, 255, 255, 0.2);
    box-shadow: -1px 1px 5px rgba(0, 0, 0, 0.10);
}
.dashboard-config .fa {
    color: #999;
}
.dashboard-config:hover {
    border: solid 1px rgba(255, 255, 255, 0.1);
}
.dashboard-config:hover .fa {
    color: #2490f8;
}

.panel-heading {
    padding: 5px 10px;
}

.content-body { margin: 0; }
.content-body .panel:last-child {
    margin-bottom: 10px;
}

.frame-green .dashboard-title {
    color: #fff;
}
.frame-primary .dashboard-title {
    color: #58666e;
}
.frame-blue .dashboard-title {
    color: #fff;
}
.frame-blue2 .dashboard-title {
    color: #1890ff;
}

.frame-blue .quick-text .title,
.frame-purple .quick-text .title,
.frame-green .quick-text .title,
.frame-lilac .quick-text .title,
.frame-wood .quick-text .title {
    color: #fff;
}

.panel-shadow {
    box-shadow: 0px 3px 6px 0px rgba(0, 0, 0, 0.03);
    border: solid 1px rgba(0, 0, 0, 0.08);
    border-radius: 4px !important;
}

.frame-blue .panel-shadow,
.frame-purple .panel-shadow,
.frame-green .panel-shadow,
.frame-lilac .panel-shadow,
.frame-wood .panel-shadow {
    border: solid 0;
}

.row-sm { margin-left: 5px; margin-right: 5px; }
.row-sm > div { padding-left: 5px; padding-right: 5px; }
.row-sm > div > .panel {
    text-align: center; 
}

.info-skin1 { padding-bottom: 10px; display: flex; position: relative; text-align: center; border-radius: 4px !important; }
.info-skin1 .info-l { color: #fff; margin-top:18px; margin-left: 15px; border-radius: 50%; width: 50px; height:50px; line-height:55px; vertical-align: middle; }
.info-skin1 .info-l .fa { font-size: 19px; }

.info-skin1 .info-r {
    flex:1;
    padding-right:5px;
}
.info-skin1 .info-a { display: flex; align-items:center; justify-content:space-between; padding-left: 15px; text-align: left; }
.info-skin1 .info-a .info-name { padding-top:15px; font-size: 14px; color: #666; }
.info-skin1 .info-a .el-input__inner { border: 0; text-align: right; }

.info-skin1 .info-b { padding-right:10px; display: flex; align-items:center; justify-content:space-between; padding-left: 15px; padding-top:5px; }
.info-skin1 .info-b .info-item { font-size: 24px; color: #333; }
.info-skin1 .info-b .rate { color: #999; }
.info-skin1 .info-b .red { color:#f00; }
.info-skin1 .info-b .green { color: #39c15b; }
.info-items { height: 94px; }

.app-title {
    padding: 15px;
}

.app-title a {
    color: #999;
    line-height: 22px;
}

.app-title a:hover {
    color: #0e90d2;
}

@media (min-width: 768px) {
    .widget-item {
        min-height: 200px;
    }
    .todo-text { margin-left: 60px; }
}

.row-widget .panel-heading { 
    padding: 8px;
    height: 45px;
    color: #2490f8;
    text-align: left;
}
.row-widget .panel-heading .widget_name {
    font-size: 15px;
    padding-top: 4px;
    padding-left: 5px;
}
.row-widget .widget-item {
    text-align: left;
}
.row-widget .widget-item .red {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}
.row-widget .widget-droppable div {
    border-radius: 4px;
}

.ag-theme-balham .ag-header {
    border-bottom: 1px solid #dee5e7;
}
.ag-theme-balham .ag-header-cell::after, .ag-theme-balham .ag-header-group-cell::after {
    border-right: 0 !important;
}

.dashboard-footer {
    background: #fff;
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
    box-shadow: 20px 0px 8px 0 rgba(29,35,41,.05);
    border-top: 1px solid #e8e8e8;
}

.dashboard-footer .box {
    padding: 6px;
    padding-bottom: 8px;
    text-align: right;
    color: #999;
}
.dashboard-footer .box a {
    color: #999;
    font-weight: bold;

    background: -webkit-linear-gradient(-70deg, #db469f, #2188ff);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.dashboard-footer .box a:hover {
    color: #0e90d2;
}

.row-quick {
    padding: 10px;
}
.quick-text {
    float: left;
    margin-right: 10px;
}
.quick-text .title {
    text-align: center;
    padding-top: 5px;
    color: #333;
}

.quick-icon .quick-num {
    font-family: Arial;
    position: absolute;
    width: 22px;
    height: 22px;
    line-height: 22px;
    text-align: center;
    font-size: 12px;
    color: #fff;
    right: -5px;
    top: -5px;
    background: #f00;
    border-radius: 100%;
    border: solid 1px #f05050;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.12);
}

.quick-icon {
    width: 48px;
    height: 48px;
    text-align: center;
    line-height: 50px;
    border-radius: 5px;
    border: solid 1px rgba(255,255,255,0.1);
    box-shadow: -1px 1px 5px rgba(0, 0, 0, 0.15);
    position: relative;
}
.quick-icon .fa {
    font-size: 20px;
    color: #fff;
}

.dropdown-toggle {
    border: solid 1px rgba(0, 0, 0, 0.1);
}

[v-cloak] {
  display: none;
}

.gd-el-card { margin-left: 0; margin-right: 0; }
.gd-el-card .el-col { padding: 5px; }
.gd-el-card-header {
    display:flex; align-items:center; justify-content:space-between; padding: 10px 0px;
}
.gd-el-card-header .gd-el-left {
    font-size: 16px;
    margin-left: 10px;
}
.gd-el-card-header .gd-el-right {
    margin-right: 10px;
}

</style>

<div class="dashboard-widget">

    @verbatim
    <div id="gdoo-app" v-cloak>

    <div class="pull-right hidden-xs">
        <a href="javascript:;" class="dashboard-config" @click="dashboardConfig" title="仪表盘设置">
            <i class="fa fa-gear"></i>
        </a>
    </div>

    <div class="row-quick">
        <div class="row row-sm">
            <div class="quick-text" v-for="quick in dashboard.quicks">
                <a href="javascript:;" data-toggle="addtab" :data-url="quick.url" :data-id="quick.key" :data-name="quick.name">
                    <div class="quick-icon quick-item" :style="'background-color:' + quick.color" :data-url="quick.url" :data-key="quick.key">
                        <i :class="'fa fa-3x ' + quick.icon"></i>
                        <span class="quick-num" v-if="quick.total > 0">{{quick.total}}</span>
                    </div>
                    <div class="title">{{quick.name}}</div>
                </a>
            </div>
            <div class="quick-text" v-if="dashboard.add_quick">
                <a href="javascript:;" @click="dashboardConfig">
                    <div class="quick-icon" style="background-color:#13D06C;">
                    <i class="fa fa-3x fa-plus"></i>
                    </div>
                    <div class="title">添加快捷</div>
                </a>
            </div>
        </div>
    </div>

    <div class="row row-sm gd-el-card">
        <el-row>
            <template v-for="info in dashboard.infos">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" v-if="info.status">
                <el-card class="box-card panel-shadow" shadow="hover" body-style="padding:0px;">
                    <div class="info-skin1">
                        <div class="info-l" :style="'background-color:' + info.color">
                            <i :class="'fa fa-2x ' + info.icon"></i>
                        </div>
                        <div class="info-r">
                            <div class="info-a">
                                <div class="info-name">{{info.name}}</div>
                                <div>
                                <el-select @change="getInfoData(info)" style="width:90px;padding-top:10px;" v-model="info.params.date" size="mini" placeholder="请选择">
                                    <el-option v-for="(v, k) in dashboard.dates" :key="k" :label="v" :value="k">
                                    </el-option>
                                </el-select>
                                </div>
                            </div>
                            <div class="info-b">
                                <div>
                                    <a href="javascript:;" data-toggle="addtab" :data-url="info.more_url" :data-id="info.key" :data-name="info.name">
                                        <div class="text-info info-item">{{info.res.count || 0}}</div>
                                    </a>
                                </div>
                                <div class="rate" v-if="info.params.date">
                                    <span>比{{dashboard.dates2[info.params.date]}}:</span>
                                    <span :class="info.res.rate > 0 ? 'red' : 'green'">
                                        {{info.res.rate}}%<i :class="info.res.rate > 0 ? 'el-icon-caret-top' : 'el-icon-caret-bottom'"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </el-card>
            </el-col>
            </template>
        </el-row>
    </div>

    <div class="row row-sm gd-el-card">
        <el-row>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" v-for="card in dashboard.cards">
                <el-card class="box-card panel-shadow" shadow="hover" body-style="padding:0px;">
                    <div class="gd-el-card-header">
                    <span class="gd-el-left">名称</span>
                    <span class="gd-el-right">
                        <el-select style="width:90px;" v-model="value" size="mini" placeholder="请选择">
                            <el-option v-for="(v, k) in dashboard.dates" :key="k" :label="v" :value="k"></el-option>
                        </el-select>
                    </span>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>

    <div class="row row-sm row-widget m-t-xs">
        <div :class="'col-xs-12 col-sm-' + grid" v-for="grid in dashboard.grids">
            <template v-for="widget in dashboard.widgets">
                <div class="panel panel-shadow" v-if="widget.status && widget.grid == grid">
                    <div class="panel-heading text-base b-b">
                        <div class="pull-right">
                            <el-select @change="getWidgetData(widget)" style="width:100px;" v-if="widget.params && widget.params.date" v-model="widget.params.date" size="mini" placeholder="请选择">
                                <el-option v-for="(v, k) in dashboard.dates3" :key="k" :label="v" :value="k">
                                </el-option>
                            </el-select>
                        </div>
                        <div class="widget_name">
                            <a @click="widgetRefresh(widget.key)">{{widget.name}}</a>
                        </div>
                    </div>
                    <div class="widget-item" :id="'widget_item_' + widget.id"></div>
                </div>
            </template>
        </div>
    </div>

    </div>
    @endverbatim

</div>

<div class="dashboard-footer">
    <div class="box">
    {{$version}}
    </div>
</div>

<style>
.ag-theme-balham .ag-root {
    border: 0;
}
.ag-theme-balham .ag-status-bar {
    border: 0;
}
.ag-theme-balham .ag-header {
    background-color: #fff;
}
.ag-theme-balham .ag-header-cell, .ag-theme-balham .ag-header-group-cell {
    border-right: transparent;
}
.ag-theme-balham .ag-ltr .ag-cell {
    border-width: 0 0 0 0;
    border-right-color: #d9dcde;
}
.ag-theme-balham .ag-header-cell::after, .ag-theme-balham .ag-header-group-cell::after {
    border-right: 1px solid rgba(189, 195, 199, 0.5);
}
</style>

<script>
(function(window, undefined) {

    var myProcess = null;

    const GdooApp = {
        data() {
            return {
                dashboard: {
                    add_quick: 0,
                    infos: [],
                    quicks: [],
                    widgets: [],
                    cards: [],
                    grids: [],
                    dates: {},
                    dates2: {}
                },
            };
        },
        created() {
            var me = this;
            $.post('index/dashboard/index', function(res) {
                if (res.status) {
                    me.dashboard = res.data;

                    me.dashboard.add_quick = me.dashboard.quicks.length > 0 ? 0 : 1;
                    me.dashboard.quicks.forEach((quick) => {
                        me.getBadge(quick);
                    });

                    me.dashboard.infos.forEach((info) => {
                        me.getInfoData(info);
                    });

                    me.dashboard.widgets.forEach((widget) => {
                        me.getWidgetData(widget);
                    });
                } else {
                    toastrError(res.data);
                }
            });
            me.widgetsRefresh();
        },
        methods: {
            dashboardConfig() {
                formDialog({
                    title: '仪表盘设置',
                    url: '/index/dashboard/config',
                    id: 'widget-edit',
                    dialogClass:'modal-lg',
                    onSubmit: function() {
                        var me = this;
                        var data = settingWidget.save();
                        $.post('/index/dashboard/config', data, function(res) {
                            if (res.status) {
                                location.reload();
                                toastrSuccess(res.data);
                                $(me).dialog("close");
                            } else {
                                toastrError(res.data);
                            }
                        });
                    }
                });
            },
            getBadge(quick) {
                $.get('/index/index/badge', {key: quick.key}, function(res) {
                    quick.total = res.total;
                });
            },
            getInfoData(info) {
                info.res = {};
                $.post('/' + info.url, info, function(res) {
                    info.res = res.data;
                });
            },
            getWidgetData(widget) {
                if (widget.url) {
                    $.get('/' + widget.url, {id: widget.id}, function(res) {
                        $('#widget_item_' + widget.id).html(res);
                    });
                }
            },
            widgetRefresh(key) {
                if (key) {
                    gdoo.widgets[key].remoteData({page: 1});
                }
            },
            widgetsRefresh() {
                var me = this;
                if (myProcess) {
                    me.dashboard.widgets.forEach((widget) => {
                        me.widgetRefresh(widget.key);
                    });
                }
                myProcess = setTimeout(function() {
                    me.widgetsRefresh();
                }, 1000 * 60 * 3);
            }
        }
    };
    const app = Vue.createApp(GdooApp);
    app.use(ElementPlus);
    app.mount("#gdoo-app");
})(window);
</script>

<script>

(function($) {
    var $document = $(document);

    $document.on('click', '[data-toggle="addtab"]', function(event) {
        event.preventDefault();
        // 触屏设备不触发事件
        var mq = top.checkMQ();
        if ($(this).parent().find('ul').length) {
            if(mq == 'mobile' || mq == 'tablet') {
                return false;
            }
        }
        // 无ID不触发事件
        var data = $(this).data();
        if(data.id == undefined) {
            return false;
        }
        top.addTab(data.url, data.id, data.name);
    });
})(jQuery);
</script>