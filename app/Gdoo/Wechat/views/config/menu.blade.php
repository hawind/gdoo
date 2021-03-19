<div class="panel">

@include('tabs2')

<style>
    .mobile-preview {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }
    .menu-editor {
        left: 317px;
        display: block;
        max-width: 500px;
        width: 500px;
        height: 581px;
        border-radius: 0;
        border-color: #dbdbdb;
        box-shadow: 0 5px 10px rgba(0,0,0,.1);
    }
    .menu-editor .arrow {
        top: auto !important;
        bottom: 15px
    }
    .menu-editor .popover-title {
        margin-top: 0
    }
    .menu-delete {
        font-weight: 400;
        font-size: 12px;
    }
</style>

<div class="panel-body">

    <div class="mp-menu">
        <div class='mobile-preview pull-left'>
            <div class='mobile-header'>{{$mp['name']}}</div>
            <div class='mobile-body'></div>
            <ul class='mobile-footer'>
                @foreach($menus as $menu)
                <li class="parent-menu">
                    <a>
                        <i class="icon-sub hide"></i>
                        <span data-type="{{$menu['type']}}" data-content="{{$menu['content']}}">{{$menu['name']}}</span>
                    </a>
                    <div class="sub-menu text-center hide">
                        <ul>
                            @if($menu['sub']) @foreach($menu['sub'] as $submenu)
                            <li>
                                <a class="bottom-border">
                                    <span data-type="{{$submenu['type']}}" data-content="{{$submenu['content']}}">{{$submenu['name']}}</span>
                                </a>
                            </li>
                            @endforeach @endif
                            <li class="menu-add">
                                <a>
                                    <i class="icon-add"></i>
                                </a>
                            </li>
                        </ul>
                        <i class="arrow arrow_out"></i>
                        <i class="arrow arrow_in"></i>
                    </div>
                </li>
                @endforeach
                <li class="parent-menu menu-add">
                    <a>
                        <i class="icon-add"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="pull-left" style="position:absolute">
            <div class="popover fade right up in menu-editor">
                <div class="arrow"></div>
                <h3 class="popover-title">
                    菜单名称
                    <a class="pull-right menu-delete">删除</a>
                </h3>
                <div class="popover-content menu-content"></div>
            </div>
        </div>
        <div class="hide menu-editor-parent-tpl">
            <form class="form-horizontal">
                <p>已添加子菜单，仅可设置菜单名称。</p>

                <div class="form-group">
                    <label class="control-label col-sm-2">菜单名称</label>
                    <div class="col-sm-6">
                        <input name="menu-name" class="form-control input-sm">
                        <span class="layui-form-mid layui-word-aux">字数不超过5个汉字或16个字母</span>
                    </div>
                </div>
        
            </form>
        </div>
        <div class="hide menu-editor-content-tpl">
            <form class="form-horizontal m-t-xs">

                <div class="form-group">
                    <label class="control-label col-sm-3">菜单名称</label>
                    <div class="col-sm-9">
                        <input name="menu-name" class="form-control input-sm">
                        <span class="layui-form-mid layui-word-aux">字数不超过13个汉字或40个字母</span>
                    </div>
                </div>
                <!--
                <div class="layui-form-item" style="margin-top:50px">
                    <label class="layui-form-label">菜单名称</label>
                    <div class="layui-input-block">
                        <input name="menu-name" class="form-control input-sm">
                        <span class="layui-form-mid layui-word-aux">字数不超过13个汉字或40个字母</span>
                    </div>
                </div>
                -->

                <div class="form-group">
                    <label class="control-label col-sm-3">菜单名称</label>
                    <div class="col-sm-9">
                        <!--<label class="col-xs-5 font-noraml">-->
                        <!--<input class="cuci-radio" type="radio" name="menu-type" value="text"> 文字消息-->
                        <!--</label>-->
                        <div class="radio">
                            <label class="col-xs-5">
                                <input class="cuci-radio" type="radio" name="menu-type" value="keys"> 关键字
                            </label>
                            <label class="col-xs-5">
                                <input class="cuci-radio" type="radio" name="menu-type" value="view"> 跳转网页
                            </label>
                            <label class="col-xs-5">
                                <input class="cuci-radio" type="radio" name="menu-type" value="event"> 事件功能
                            </label>
                            <label class="col-xs-5">
                                <input class="cuci-radio" type="radio" name="menu-type" value="miniprogram"> 小程序
                            </label>
                            <!--<label class="col-xs-5 font-noraml">-->
                            <!--<input class="cuci-radio" type="radio" name="menu-type" value="customservice"> 多客服-->
                            <!--</label>-->
                            </div>
                    </div>
                </div>
                <div class="editor-content-input"></div>
            </form>
        </div>
    </div>

</div>

<div class="panel-footer">
    <button class="btn btn-info menu-submit" lay-filter="formDemo">保存发布</button>
    <button class="btn btn-danger">取消发布</button>
</div>

</div>

<script>
$(function () {
    /**
        * 菜单事件构造方法
        * @returns {menu.index_L2.menu}
        */
    var menu = function () {
        this.version = '1.0';
        this.$btn;
        this.listen();
    };
    /**
        * 控件默认事件
        * @returns {undefined}
        */
    var layer;
    layui.use('layer', function () {
        layer = layui.layer;

    });
    menu.prototype.listen = function () {
        var self = this;
        $('.mobile-footer').on('click', 'li a', function () {
            self.$btn = $(this);
            self.$btn.parent('li').hasClass('menu-add') ? self.add() : self.checkShow();
        }).find('li:first a:first').trigger('click');
        $('.menu-delete').on('click', function () {
            var index = layer.confirm('确定删除吗？', function () {
                self.del(), layer.close(index);
            });
        });
        $('.menu-submit').on('click', function () {
            self.submit();
        });
    };
    /**
        * 添加一个菜单
        * @returns {undefined}
        */
    menu.prototype.add = function () {
        var $add = this.$btn.parent('li'), $ul = $add.parent('ul');
        if ($ul.hasClass('mobile-footer')) { /* 添加一级菜单 */
            var $li = $('<li class="parent-menu"><a class="active"><i class="icon-sub hide"></i> <span>一级菜单</span></a></li>').insertBefore($add);
            this.$btn = $li.find('a');
            $('<div class="sub-menu text-center hide"><ul><li class="menu-add"><a><i class="icon-add"></i></a></li></ul><i class="arrow arrow_out"></i><i class="arrow arrow_in"></i></div>').appendTo($li);
        } else { /* 添加二级菜单 */
            this.$btn = $('<li><a class="bottom-border"><span>二级菜单</span></a></li>').prependTo($ul).find('a');
        }
        this.checkShow();
    };
    /**
        * 数据校验显示
        * @returns {unresolved}
        */
    menu.prototype.checkShow = function () {
        var $li = this.$btn.parent('li'), $ul = $li.parent('ul');
        /* 选中一级菜单时显示二级菜单 */
        if ($li.hasClass('parent-menu')) {
            $('.parent-menu .sub-menu').not(this.$btn.parent('li').find('.sub-menu').removeClass('hide')).addClass('hide');
        }

        /* 一级菜单添加按钮 */
        var $add = $('li.parent-menu:last');
        $add.siblings('li').size() >= 3 ? $add.addClass('hide') : $add.removeClass('hide');
        /* 二级菜单添加按钮 */
        $add.siblings('li').map(function () {
            var $add = $(this).find('ul li:last');
            $add.siblings('li').size() >= 5 ? $add.addClass('hide') : $add.removeClass('hide');
        });
        /* 处理一级菜单 */
        var parentWidth = 100 / $('li.parent-menu:visible').size() + '%';
        $('li.parent-menu').map(function () {
            var $icon = $(this).find('.icon-sub');
            $(this).width(parentWidth).find('ul li').size() > 1 ? $icon.removeClass('hide') : $icon.addClass('hide');
        });
        /* 更新选择中状态 */
        $('.mobile-footer a.active').not(this.$btn.addClass('active')).removeClass('active');
        this.renderEdit();
        return $ul;
    };
    /**
        * 删除当前菜单
        * @returns {undefined}
        */
    menu.prototype.del = function () {
        var $li = this.$btn.parent('li'), $ul = $li.parent('ul');
        var $default = function () {
            if ($li.prev('li').size() > 0) {
                return $li.prev('li');
            }
            if ($li.next('li').size() > 0 && !$li.next('li').hasClass('menu-add')) {
                return $li.next('li');
            }
            if ($ul.parents('li.parent-menu').size() > 0) {
                return $ul.parents('li.parent-menu');
            }
            return $('null');
        }.call(this);
        $li.remove();
        this.$btn = $default.find('a:first');
        this.checkShow();
    };
    /**
        * 显示当前菜单的属性值
        * @returns {undefined}
        */
    menu.prototype.renderEdit = function () {
        var $span = this.$btn.find('span'), $li = this.$btn.parent('li'), $ul = $li.parent('ul');
        var $html = '';
        if ($li.find('ul li').size() > 1) { /*父菜单*/
            $html = $($('.menu-editor-parent-tpl').html());
            $html.find('input[name="menu-name"]').val($span.text()).on('change keyup', function () {
                $span.text(this.value || ' ');
            });
            $('.menu-editor .menu-content').html($html);
        } else {
            $html = $($('.menu-editor-content-tpl').html());
            $html.find('input[name="menu-name"]').val($span.text()).on('change keyup', function () {
                $span.text(this.value || ' ');
            });
            $('.menu-editor .menu-content').html($html);
            var type = $span.attr('data-type') || 'text';
            $html.find('input[name="menu-type"]').on('click', function () {
                $span.attr('data-type', this.value || 'text');
                var content = $span.data('content') || '';
                var type = this.value;
                var html = function () {
                    switch (type) {
                        case 'miniprogram':
                            var tpl = '<div><div class="form-group"><label class="control-label col-sm-3">appid</label><div class="col-sm-9"><input type="text" name="appid" required="" lay-verify="required" placeholder="appid" autocomplete="off" class="form-control input-sm" value="{appid}"></div></div><div class="form-group"><label class="control-label col-sm-3">url</label><div class="col-sm-9"><input type="text" name="url" required="" lay-verify="required" placeholder="url" autocomplete="off" class="form-control input-sm" value="{url}"></div></div><div class="form-group"><label class="control-label col-sm-3">pagepath</label><div class="col-sm-9"><input type="text" name="pagepath" required="" lay-verify="required" placeholder="pagepath" autocomplete="off" class="form-control input-sm" value="{pagepath}"></div></div></div>';
                            var _appid = '', _pagepath = '', _url = '';
                            if (content.indexOf(',') > 0) {
                                _appid = content.split(',')[0] || '';
                                _url = content.split(',')[1] || '';
                                _pagepath = content.split(',')[2] || '';
                            }
                            $span.data('appid', _appid), $span.data('url', _url), $span.data('pagepath', _pagepath);
                            return tpl.replace('{appid}', _appid).replace('{url}', _url).replace('{pagepath}', _pagepath);
                        case 'customservice':
                        case 'text':
                            return '<div>回复内容<textarea style="resize:none;height:225px" name="content" class="form-control input-sm">{content}</textarea></div>'.replace('{content}', content);
                        case 'view':
                            return '<div class="form-group"><label class="control-label col-sm-3">跳转地址</label><div class="col-sm-9"><textarea placeholder="请输入内容" class="form-control" name="content">{content}</textarea></div></div>'.replace('{content}', content);
                        case 'keys':
                            return '<div class="form-group"><label class="control-label col-sm-3">匹配内容</label><div class="col-sm-9"><textarea placeholder="请输入内容" class="form-control" name="content">{content}</textarea></div></div>'.replace('{content}', content);
                        case 'event':
                            var options = {
                                'scancode_push': '扫码推事件',
                                'scancode_waitmsg': '扫码推事件且弹出“消息接收中”提示框',
                                'pic_sysphoto': '弹出系统拍照发图',
                                'pic_photo_or_album': '弹出拍照或者相册发图',
                                'pic_weixin': '弹出微信相册发图器',
                                'location_select': '弹出地理位置选择器'
                            };
                            var select = [], tpl = '<div class="form-group" style="margin-bottom:auto;"><label class="control-label col-sm-3"></label><div class="col-sm-9"><div class="radio"><label><input class="cuci-radio" name="content" type="radio" {checked} value="{value}"> {title}</label></div></div></div>';
                            if (!(options[content] || false)) {
                                content = 'scancode_push';
                                $span.data('content', content);
                            }
                            for (var i in options) {
                                select.push(tpl.replace('{value}', i).replace('{title}', options[i]).replace('{checked}', (i === content) ? 'checked' : ''));
                            }
                            return select.join('');
                    }
                }.call(this);
                var $html = $(html), $input = $html.find('input,textarea');
                $input.on('change keyup click', function () {
                    // 将input值写入到span上
                    $span.data(this.name, $(this).val() || $(this).html());
                    // 如果是小程序，合并内容到span的content上
                    if (type === 'miniprogram') {
                        $span.data('content', $span.data('appid') + ',' + $span.data('url') + ',' + $span.data('pagepath'));
                    }
                });
                $('.editor-content-input').html($html);
            }).filter('input[value="{type}"]'.replace('{type}', type)).trigger('click');
        }
    };
    
    /**
    * 提交数据
    * @returns {undefined}
    */
    menu.prototype.submit = function () {
        var data = [];
        function getdata($span) {
            var menudata = {};
            menudata.name = $span.text();
            menudata.type = $span.attr('data-type');
            menudata.content = $span.data('content') || '';
            return menudata;
        }

        $('li.parent-menu').map(function (index, item) {
            if (!$(item).hasClass('menu-add')) {
                var menudata = getdata($(item).find('a:first span'));
                menudata.index = index + 1;
                menudata.pindex = 0;
                menudata.sub = [];
                menudata.sort = index;
                data.push(menudata);
                $(item).find('.sub-menu ul li:not(.menu-add) span').map(function (ii, span) {
                    var submenudata = getdata($(span));
                    submenudata.index = (index + 1) + '' + (ii + 1);
                    submenudata.pindex = menudata.index;
                    submenudata.sort = ii;
                    data.push(submenudata);
                });
            }
        });
        var data = (data == '') ? '' : data;
        var loading = showLoading();
        $.post('{{url()}}', {data: data}, function (res) {
            layer.close(loading);
            if (res.status) {
                layer.msg(res.data, {time: 1000});
            } else {
                layer.alert(res.data);
            }
        }, 'json')};
    new menu();
});
</script>