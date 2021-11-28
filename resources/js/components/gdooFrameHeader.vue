<template>
    <ul class="nav navbar-nav navbar-right m-n hidden-xs nav-user">
        
        <li class="dropdown hidden-xs">
            <a href="#" data-toggle="dropdown" title="通知" class="dropdown-toggle">
                <i class="fa fa-bell-o pulse-box">
                    <span :class="[countTotal > 0 ? 'pulse' : 'hidden']"></span>
                </i>
                <span class="visible-xs-inline">通知</span>
            </a>

            <div class="dropdown-menu w-xl">
                <div class="panel bg-white">
                    <div class="list-group no-radius">
                        <a href="#" :class="[countArticle > 0 ? 'list-group-item' : 'hidden']">
                            <span class="pull-left thumb-sm">
                                <i class="fa fa-bullhorn fa-2x text-danger"></i>
                            </span>
                            <span class="block m-b-none">
                                <span><span class="text-danger pull-right-xs">0</span> 条未读公告</span>
                                <br />
                                <small class="text-muted text-xs">点击阅读</small>
                            </span>
                        </a>
                        <a href="#" :class="[countMail > 0 ? 'list-group-item' : 'hidden']">
                            <span class="pull-left thumb-sm">
                                <i class="fa fa-envelope-o fa-2x text-success"></i>
                            </span>
                            <span class="block m-b-none">
                                <span><span class="text-danger pull-right-xs">2</span> 条未读邮件</span>
                                <br />
                                <small class="text-muted text-xs">点击阅读</small>
                            </span>
                        </a>
                        <a href="#" :class="[countNotification > 0 ? 'list-group-item' : 'hidden']" data-toggle="addtab" data-url="user/message/index" data-id="00" data-name="通知提醒">
                            <span class="pull-left thumb-sm">
                                <i class="fa fa-bell-o fa-2x text-info"></i>
                            </span>
                            <span class="block m-b-none">
                                <span><span class="text-danger pull-right-xs">{{countNotification}}</span> 条未读通知</span>
                                <br />
                                <span class="text-muted text-xs">点击阅读</span>
                            </span>
                        </a>
                    </div>
                    <div class="panel-footer text-sm">
                        <a href="#" class="pull-right"><i class="fa fa-cog"></i></a>
                        <a href="#">提醒设置</a>
                    </div>
                </div>
            </div>
        </li>
        <li class="dropdown">

            <a href="javascript:;" data-toggle="dropdown" class="dropdown-toggle clear hidden-xs">

              <span class="thumb-xs avatar m-t-n-sm m-b-n-sm m-r-sm">
                <img :src="user.avatar" class="img-circle">
                <i class="on md b-white bottom"></i>
              </span>
              <span class="hidden-sm hidden-md">{{user.name}}</span> <b class="caret"></b>
            </a>
            <!-- dropdown -->
            <ul class="dropdown-menu animated fadeInRight">
                <li>
                    <a href="javascript:;" data-toggle="addtab" data-url="user/profile/index" data-id="02" data-name="个人资料">个人资料</a>
                </li>
                <li>
                    <a href="javascript:;" @click="support">关于</a>
                </li>
                <li class="divider"></li>
                <li>
                    <a :href="url('user/auth/logout')">退出</a>
                </li>
            </ul>
        </li>
    </ul>
</template>

<script>
import { defineComponent } from 'vue'
export default defineComponent({
    name: 'gdoo-frame-header',
    data() {
        return {
            user: {},
            countNotification: 0,
            countTotal: 0,
            countArticle: 0,
            countMail: 0,
            realtime: settings.realtime
        };
    },
    mounted() {
        var me = this;
        this.getUser();
        this.tick();
        this.timer = setInterval(() => this.tick(), 1000 * 60);

    },
    methods: {
        // 获取用户信息
        getUser() {
            let me = this;
            $.get(app.url('user/profile/getUser'), function (res) {
                me.user = res;
            }, 'json');
        },
        support() {
            viewDialog({
                title: '关于',
                dialogClass: 'modal-md',
                url: app.url('index/index/support'),
                close: function() {
                    $(this).dialog("close");
                }
            });
        },
        chatToggle() {
            function openWin(u, w, h) {
                var l = (screen.width - w) / 2;
                var t = (screen.height - h) / 2;
                var s = 'width=' + w + ', height=' + h + ', top=' + t + ', left=' + l; s += ', status=no, toolbar=no, scrollbars=no, menubar=no, location=no, resizable=no';             
                open(u, 'gdooChatBox', s);
            }
            openWin(app.url('chat/chat/index'), 900, 600);
        },
        tick() {
            let me = this;
            $.get(app.url('user/message/count'), function (count) {
                if (me.countNotification == count) {
                    return;
                }
                me.countTotal = count;
                me.countNotification = count;
            }, 'json');

            // 查询待办列表
            $.post(app.url('index/index/badges'), function (res) {
                let menus  = {};
                let groups = {};
                $.each(res, function(key, rows) {
                    let badge = $('#badge_' + key);
                    let menu_id  = badge.data('menu_id');
                    let group_id = badge.data('group_id');
                    if (rows.total) {
                        if (menu_id > 0) {
                            menus[menu_id] = menu_id;
                        }
                        if (group_id > 0) {
                            groups[group_id] = group_id;
                        }
                        badge.show();
                        badge.text(rows.total);
                    } else {
                        badge.hide();
                    }
                });

                $.each(menus, function(menu_id) {
                    $('#badge_menu_' + menu_id).show();
                });

                $.each(groups, function(group_id) {
                    $('#badge_group_' + group_id).show();
                });
            });
        }
    }
});
</script>
