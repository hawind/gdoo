<style type="text/css">
html {
    overflow: hidden;
}
a { outline: none; }

.dashboard-widget-header {
    margin-top: 49px;
    height: calc(100vh - 80px);
    overflow: hidden;
    overflow-y: auto;
}

.dashboard-title {
    padding: 10px;
    position: absolute;
    top: 0;
    width: 100vw;
    border-bottom: solid 1px rgba(0, 0, 0, 0.05);
}
.dashboard-title .btn {
    border: solid 1px rgba(0, 0, 0, 0.15);
    background-color: #fff;
}

.dashboard-config {
    text-align: center;
    height: 26px;
    line-height: 26px;
    width: 26px;
    display: block;
    border-radius: 4px;
    background-color: #fff;
    border: solid 1px rgba(0, 0, 0, 0.1);
}
.dashboard-config .fa {
    color: #999;
}
.dashboard-config:hover {
    border: solid 1px rgba(0, 0, 0, 0.2);
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
.frame-blue .dashboard-title .btn,
.frame-purple .dashboard-title .btn,
.frame-green .dashboard-title .btn,
.frame-lilac .dashboard-title .btn,
.frame-wood .dashboard-title .btn {
    border: solid 1px rgba(0, 0, 0, 0.15);
    background-color: rgba(0, 0, 0, 0.15);
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

.row-info .panel { display: flex; padding-bottom: 10px; position: relative; text-align: center; border-radius: 4px !important; }
.row-info .widget-droppable div { border-radius: 4px; }
.info-l { color: #fff; margin-top:16px; margin-left: 15px; border-radius: 50%; width: 50px; height:50px; line-height:58px; vertical-align: middle; }

.info-c { flex:1; margin-left: 15px; text-align: left; }
.info-c .info-name { margin-top:18px; font-size: 14px; color: #666; }
.info-c .info-item { font-size: 24px; color: #333; }

.info-r { margin-left: auto; margin-top:18px; width: 70px; line-height:22px; }
.info-r .rate { color: #2bbf24; }
.info-r::before { position:absolute;top:22px;content:"";width:1px;height:40px;background-color:#e6e6e6;display:block; }

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

.widget-droppable div {
    border: 1px dashed #23b7e5;
    background: #dcf2f8;
    text-align: center;
    color: #ccc;
    padding-bottom: 10px;
}

.row-widget .panel-heading { 
    padding: 10px;
    color: #2490f8;
    font-size: 14px;
    text-align: left;
}
.row-widget .widget-item {
    text-align: left;
}
.row-widget .widget-item .red {
    font-size: 15px;
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
    margin-bottom: 10px;
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
    display: none;
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
</style>

<div class="dashboard-widget">

    <div class="dashboard-title">
        <div class="pull-right">
            <a class="dashboard-config" data-toggle="dashboard-config" title="仪表盘设置">
                <i class="fa fa-gear"></i>
            </a>
        </div>
        <div class="font-thin">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-filter"></span> 本部门下属部门
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#">本人</a></li>
                    <li><a href="#">本人和下属</a></li>
                    <li><a href="#">本部门</a></li>
                    <li class="active"><a href="#">本部门下属部门</a></li>
                    <li class="divider"></li>
                    <li class=""><a href="#">自定义</a></li>
                </ul>
            </div>
            <div class="btn-group m-l-xs" role="group">
                <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="fa fa-filter"></span> 本月
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#">今天</a></li>
                    <li><a href="#">昨天</a></li>
                    <li><a href="#">本周</a></li>
                    <li><a href="#">上周</a></li>
                    <li class="active"><a href="#">本月</a></li>
                    <li><a href="#">上月</a></li>
                    <li><a href="#">本季度</a></li>
                    <li><a href="#">上季度</a></li>
                    <li><a href="#">本年</a></li>
                    <li><a href="#">去年</a></li>
                    <li class="divider"></li>
                    <li class=""><a href="#">自定义</a></li>
                </ul>
            </div>
        </div>
  </div>

    <div class="dashboard-widget-header">
    <div class="row-quick m-t-sm">
        <div class="row row-sm">
            @forelse($quicks as $quick)
                <div class="quick-text">
                    <a href="javascript:;" data-toggle="addtab" data-url="{{$quick['url']}}" data-id="{{$quick['key']}}" data-name="{{$quick['name']}}">
                        <div class="quick-icon quick-item" style="background-color:{{$quick['color']}}" data-url="{{$quick['url']}}" data-key="{{$quick['key']}}">
                            <i class="fa fa-3x {{$quick['icon']}}"></i>
                            <span class="quick-num">0</span>
                        </div>
                        <div class="title">{{$quick['name']}}</div>
                    </a>
                </div>
            @empty
            <div class="quick-text">
                <a href="javascript:;" data-toggle="dashboard-config">
                    <div class="quick-icon" style="background-color:#13D06C;">
                    <i class="fa fa-3x fa-plus"></i>
                    </div>
                    <div class="title">添加快捷</div>
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <div class="row row-sm row-info">
        @foreach($infos as $info)
        @if($info['status'])
        <div class="col-xs-6 col-sm-4 col-md-3 col-lg-2">

            <div class="panel panel-shadow">
                <div class="info-l hidden-xs bg-{{$info['color']}}">
                    <i class="fa fa-2x {{$info['icon']}}"></i>
                </div>
                <div class="info-c">
                    <div class="info-name">{{$info['name']}}</div>
                    <a href="javascript:;" data-toggle="addtab" data-url="{{$info['more_url']}}" data-id="{{str_replace(['/', '?', '='], ['_', '_', '_'], $info['more_url'])}}" data-name="{{$info['name']}}">
                        <div class="text-info info-item" data-url="{{$info['url']}}" data-more_url="{{$info['more_url']}}"></div>
                    </a>
                </div>
                <div class="info-r">
                    <div>较上月</div>
                    <div class="rate">50%</div>
                </div>
            </div>

        </div>
        @endif
        @endforeach
    </div>

    <div class="row row-sm row-widget">
        @foreach($grids as $grid)
            <div class="col-xs-12 col-sm-{{$grid}}">
                @foreach($widgets as $widget)
                    @if($widget['status'])
                        @if($widget['grid'] == $grid)
                            <div class="panel panel-shadow">
                                <div class="panel-heading text-base b-b">
                                    <div class="pull-right"></div>
                                    <a data-toggle='widget-refresh' data-url="{{$widget['url']}}" data-key="{{str_replace(['/', '?', '='], ['_', '_', '_'], $widget['url'])}}" data-id="{{$widget['id']}}">{{$widget['name']}}</a>
                                </div>
                                <div class="widget-item" id="widget_item_{{$widget['id']}}" data-url="{{$widget['url']}}"> 
                            </div>

                        </div>
                        @endif
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>
</div>

<div class="dashboard-footer">
    <div class="box">
    {{$version}} {{$openSource ? '开源版' : '企业版'}}
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
(function($) {
    var $document = $(document);

    var myProcess = null;

    function widgetRefresh() {
        if (myProcess) {
            var items = $('.widget-item');
            items.each(function(index, item) {
                var data = $(item).data();
                if (data.key) {
                    gdoo.widgets[data.key].remoteData({page: 1});
                    console.log('refresh item:' + data.key);
                }
            });
        }
        myProcess = setTimeout(function() {
            widgetRefresh();
        }, 1000 * 60 * 5);
    }

    widgetRefresh();

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

    $('[data-toggle="dashboard-config"]').on('click', function() {
        formDialog({
            title: '仪表盘设置',
            url: app.url('index/dashboard/config'),
            id: 'widget-edit',
            dialogClass:'modal-lg',
            success: function(res) {
                location.reload();
                toastrSuccess(res.data);
                $(this).dialog("close");
            },
            error: function(res) {
                toastrError(res.data);
            }
        });
    });

    $('[data-toggle="widget-refresh"]').on('click', function() {
        var data = $(this).data();
        if (data.key) {
            gdoo.widgets[data.key].remoteData({page: 1});
        }
    });

    function widgetInit() {
        var items = $('.widget-item');
        items.each(function(index, item) {
            var data = $(item).data();
            if (data == undefined) {
                return false;
            }
            if (data.url) {
                $(item).load(app.url(data.url, {id: data.id}));
            }
        });

        var items = $('.info-item');
        items.each(function(index, item) {
            var me = $(item);
            var data = me.data();
            $.get(app.url('index/index/info', {type: data.url}), function(res) {
                if(res > 0) {
                    me.removeClass('text-info').addClass('text-danger');
                } else {
                    me.removeClass('text-danger').addClass('text-info');
                }
                me.text(res);
            });
        });

        var items = $('.quick-item');
        items.each(function(index, item) {
            var me = $(item);
            var data = me.data();
            $.get(app.url('index/index/badge', {key: data.key}), function(res) {
                if(res.total > 0) {
                    me.find('.quick-num').show().text(res.total);
                }
            });
        });
    }
    widgetInit();

})(jQuery);
</script>