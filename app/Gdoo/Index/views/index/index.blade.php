<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>{{$setting['title']}} - {{$powered}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <link href="{{mix('/assets/dist/vendor.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{mix('/assets/dist/gdoo.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{mix('/assets/dist/index.min.css')}}" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="{{mix('/assets/dist/vendor.min.js')}}"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/layer/layer.js"></script>
    <script type="text/javascript" src="{{$asset_url}}/vendor/datepicker/datepicker.js"></script>
    <script type="text/javascript" src="{{$public_url}}/common?s={{time()}}"></script>

    <script type="text/javascript" src="{{mix('/assets/dist/bundle.min.js')}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/index.min.js')}}"></script>
    <script type="text/javascript" src="{{mix('/assets/dist/gdoo.min.js')}}"></script>

    <style type="text/css">
    @foreach($menus['children'] as $menu_id => $menu)
    .side-nav a.a{{$menu_id}} {box-shadow: inset 3px 0 0 {{$menu['color']}};}
    .side-nav a.a{{$menu_id}} .icon,.side-nav .hover a.a{{$menu_id}} { background-color: {{$menu['color']}}; }
    .side-nav a.a{{$menu_id}} .icon .fa { color: #fff; }
    .side-nav .hover a.a{{$menu_id}} .icon { background-color: #fff; }
    .side-nav .hover a.a{{$menu_id}} .icon .fa { color: {{$menu['color']}}; }
    @endforeach
    </style>
    
</head>

<body class="theme-{{auth()->user()->theme ?: 'lilac'}}">

    <header class="header navbar">

        <div class="navbar-header" id="navbar-left">

            <a href="javascript:;" title="折叠菜单" data-toggle="side-folded" class="folded">
                <i class="fa fa-angle-left text"></i>
                <i class="fa fa-angle-right text-active"></i>
            </a>

            <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".nav-user">
                <i class="icon icon-cog"></i>
            </a>

            <a href="{{url('/')}}" class="navbar-brand">
                <span class="navbar-brand-min-title">
                    <img src="{{$asset_url}}/images/white-logo.svg" width="20" />
                </span>
                <span class="navbar-brand-title">
                    <img src="{{$asset_url}}/images/white-logo.svg" width="20" />
                    {{$setting['title']}}
                </span>
            </a>

            <a class="btn btn-link visible-xs nav-trigger" data-target="#nav">
                <span></span>
            </a>

        </div>
        
        <ul class="nav navbar-nav tabs-list hidden-xs" id="tabs-list">
            <li role='presentation'>
                <a href="#tab_dashboard" aria-controls="0" data-toggle="tab" role="tab">
                    <i class="fa fa-square-o"></i>
                    <span>首页</span>
                </a>
            </li>
        </ul>
        <div id="notificationApp"><gdoo-frame-header /></div>
    </header>

    <div class="nav-scroll">

        <div class="side-nav" id="tabs-left">

            @if(Auth::user()->avatar_show == 1)
            <div class="side-nav-avatar">
                <span class="thumb-md avatar">
                    <a href="javascript:;" data-toggle="addtab" data-url="user/profile/index" data-id="user_profile_index" data-name="个人资料">
                        <img src="{{avatar(Auth::user()->avatar)}}" class="img-circle">
                        <i class="on md b-white bottom"></i>
                    </a>
                </span>
                <span class="text-avatar text-muted text-xs block m-t-xs">
                    <?php echo Auth::user()->name; ?>
                </span>
            </div>
            @endif

            <ul>
                @foreach($menus['children'] as $menu_id => $menu)
                @if($menu['selected'])
                <li class="has-children">
                    <a href="javascript:;" class="a{{$menu_id}}" title="{{$menu['name']}}">

                        <span class="pull-right">
                            <i class="fa fa-fw fa-angle-right text"></i>
                            <i class="fa fa-fw fa-angle-down text-active"></i>
                        </span>

                        <span class="icon">

                            <span class="pulse-box">
                                <span id="badge_menu_{{$menu['id']}}" class="pulse" style="display:none;"></span>
                            </span>
                            <i class="fa {{$menu['icon']}}"></i>

                        </span>

                        <span class="title">{{$menu['name']}}</span>
                    </a>
                    <ul>
                        @foreach($menu['children'] as $groupId => $group)
                        @if($group['selected'])
                        <li class="has-children">
                            <a class="notify-box" href="javascript:;" data-toggle="addtab" data-url="{{$group['url']}}" data-id="{{$group['key']}}" data-name="{{$group['name']}}">
                                @if(count((array)$group['children']))
                                
                                <span class="pull-right">
                                    <i class="fa fa-fw fa-angle-right text"></i>
                                    <i class="fa fa-fw fa-angle-down text-active"></i>
                                </span>

                                <b id="badge_group_{{$group['id']}}" class="pulse pulse-right" style="display:none;"></b>
                                @else
                                <b data-menu_id="{{$menu['id']}}" id="badge_{{$group['key']}}" class="badge bg-danger pull-right" style="display:none;"></b>
                                @endif

                                {{$group['name']}}
                            </a>

                            @if(count((array)$group['children']))
                            <ul>
                                @foreach($group['children'] as $action) 
                                @if($action['selected'])
                                <li class="@if($action['active']) active @endif">
                                    <a href="javascript:;" data-toggle="addtab" data-url="{{$action['url']}}" data-id="{{$action['key']}}" data-name="{{$action['name']}}">
                                        
                                        @if($group['url'])
                                            <b data-menu_id="{{$menu['id']}}" data-group_id="{{$group['id']}}" id="badge_{{$action['key']}}" class="badge bg-danger pull-right" style="display:none;"></b>
                                        @endif
                                        
                                        {{$action['name']}}
                                    </a>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                            @endif

                        </li>
                        @endif 
                        @endforeach
                    </ul>
                </li>
                @endif
                @endforeach
            </ul>
            <ul class="profile">
                <li class="label">个人</li>
                <li>
                    <a href="javascript:;" data-toggle="addtab" data-url="user/message/index" data-id="user_message_index" data-name="通知提醒"
                        title="通知提醒">
                        <i class="fa fa-bell"></i>
                        <span class="title">通知提醒</span>
                        <!--
                        <span class="count badge pull-right bg-danger">3</span>
                        -->
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="tab-content" id="tabs-content">
            <div role="tabpanel" class="tab-pane active" id="tab_dashboard">
                <iframe src="{{url('index/dashboard/index')}}" id="tab_iframe_dashboard" frameBorder=0 scrolling=auto width="100%" height="100%"></iframe>
            </div>
        </div>
    </div>
    <script>
        const vueApp = Vue.createApp({
            components: {
                gdooFrameHeader,
            }
        });
        vueApp.config.globalProperties.url = app.url;
        vueApp.mount('#notificationApp');
    </script>
    
</body>

</html>