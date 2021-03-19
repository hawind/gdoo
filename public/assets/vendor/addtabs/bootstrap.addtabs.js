/**
 * Website: http://git.oschina.net/hbbcs/bootStrap-addTabs
 *
 * Version : 2.1
 *
 * Created by joe on 2016-2-4.Update 2017-10-24
 */

(function ($) {

    var settings = {
        /**
         * 直接指定所有页面TABS内容
         * @type {String}
         */
        content: '',
        /**
         * 是否可以关闭
         * @type {Boolean}
         */
        close: true,
        /**
         * 监视的区域
         * @type {String}
         */
        monitor: 'body',
        /**
         * 默认使用iframe还是ajax,true 是iframe,false是ajax
         * @type {Boolean}
         */
        iframe: true,
        /**
         * 固定TAB中IFRAME高度,根据需要自己修改
         * @type {Number}
         */
        height: $(window).height() - 118,
        /**
         * 目标
         * @type {String}
         */
        target: '#tabs-list',
        /**
         * 显示加载条
         * @type {Boolean}
         */
        loadbar: true,
        /**
         * 是否使用右键菜单
         * @type {Boolean}
         */
        contextmenu: false,
        /**
         * 将打开的tab页记录到本地中，刷新页面时自动打开，默认不使用
         * @type {Boolean}
         */
        store: false,
        /**
         * 保存的项目名称，为了区分项目
         * @type {String}
         */
        storeName: '',
        /**
         * 内容样式表
         * @type {String}
         */
        contentStyle: 'content',
        /**
         * ajax 的参数
         * @type {Object}
         */
        ajax: {
            'async': true,
            'dataType': 'html',
            'type': 'get'
        },
        /**
         *
         * @type {Object}
         */
        local: {
            'refreshLabel': '刷新此标签',
            'closeThisLabel': '关闭此标签',
            'closeOtherLabel': '关闭其他标签',
            'closeLeftLabel': '关闭左侧标签',
            'closeRightLabel': '关闭右侧标签',
            'loadbar': '正在加载内容，请稍候...'
        },
        /**
         * 关闭tab回调函数
         * @return {[type]} [description]
         */
        callback: function () {
        }
    };

    var target;

    _store = function () {
        if (typeof (arguments[0]) == 'object') {
            arguments[0].each(function (name, val) {
                localStorage.setItem(name, val);
            })
        } else if (arguments[1]) {
            localStorage.setItem(arguments[0], arguments[1]);
        } else {
            return localStorage.getItem(arguments[0]);
        }
    }

    _click = function (obj) {
        var a_obj, a_target;

        a_obj = (typeof obj.data('addtab') == 'object') ? obj.data('addtab') : obj.data();

        if (!a_obj.id && !a_obj.addtab) {
            a_obj.id = Math.random().toString(36).substring(3, 35);
            obj.data('id', a_obj.id);
        }

        $.addtabs.add({
            'target': a_obj.target ? a_obj.target : target,
            'id': a_obj.id ? a_obj.id : a_obj.addtab,
            'title': a_obj.title ? a_obj.title : obj.html(),
            'content': settings.content ? settings.content : a_obj.content,
            'url': a_obj.url ? a_obj.url : obj.attr('href'),
            'ajax': a_obj.ajax ? a_obj.ajax : false
        });
    };

    _createMenu = function (right, icon, text) {
        return $('<a>', {
            'href': 'javascript:void(0);',
            'class': "list-group-item",
            'data-right': right
        }).append(
            $('<i>', {
                'class': 'fa ' + icon
            })
        ).append(text);
    }

    _pop = function (id, e, mouse) {
        $('body').find('#popMenu').remove();
        var refresh = e.attr('id') ? _createMenu('refresh', 'fa-refresh', settings.local.refreshLabel) : '';
        var remove = e.attr('id') ? _createMenu('remove', 'fa-remove', settings.local.closeThisLabel) : '';
        var left = e.prev('li').attr('id') ? _createMenu('remove-left', 'fa-chevron-left', settings.local.closeLeftLabel) : '';
        var right = e.next('li').attr('id') ? _createMenu('remove-right', 'fa-chevron-right', settings.local.closeRightLabel) : '';
        var popHtml = $('<ul>', {
            'aria-controls': id,
            'class': 'rightMenu list-group',
            id: 'popMenu',
            'aria-url': e.attr('aria-url'),
            'aria-ajax': e.attr('aria-ajax')
        }).append(refresh)
            .append(remove)
            .append(_createMenu('remove-circle', 'fa-remove-circle', settings.local.closeOtherLabel))
            .append(left)
            .append(right);

        popHtml.css({
            'top': mouse.pageY,
            'left': mouse.pageX
        });
        popHtml.appendTo($('body')).show();
        // 刷新页面
        $('ul.rightMenu a[data-right=refresh]').on('click', function () {
            var id = $(this).parent('ul').attr("aria-controls").substring(4);
            var url = $(this).parent('ul').attr('aria-url');
            var ajax = $(this).parent('ul').attr('aria-ajax');
            $.addtabs.add({
                'id': id,
                'url': url,
                'refresh': true,
                'ajax': ajax
            });
        });

        // 关闭自身
        $('ul.rightMenu a[data-right=remove]').on('click', function () {
            var id = $(this).parent("ul").attr("aria-controls");
            if (id.substring(0, 4) != 'tab_') return;
            $.addtabs.close({
                "id": id
            });
            $.addtabs.drop();
        });

        // 关闭其他
        $('ul.rightMenu a[data-right=remove-circle]').on('click', function () {
            var tab_id = $(this).parent('ul').attr("aria-controls");
            target.find('li').each(function () {
                var id = $(this).attr('id');
                if (id && id != 'tab_' + tab_id) {
                    $.addtabs.close({
                        "id": $(this).children('a').attr('aria-controls')
                    });
                }
            });
            $.addtabs.drop();
        });

        // 关闭左侧
        $('ul.rightMenu a[data-right=remove-left]').on('click', function () {
            var tab_id = $(this).parent('ul').attr("aria-controls");
            $('#tab_' + tab_id).prevUntil().each(function () {
                var id = $(this).attr('id');
                if (id && id != 'tab_' + tab_id) {
                    $.addtabs.close({
                        "id": $(this).children('a').attr('aria-controls')
                    });
                }
            });
            $.addtabs.drop();
        });

        // 关闭右侧
        $('ul.rightMenu a[data-right=remove-right]').on('click', function () {
            var tab_id = $(this).parent('ul').attr("aria-controls");
            $('#tab_' + tab_id).nextUntil().each(function () {
                var id = $(this).attr('id');
                if (id && id != 'tab_' + tab_id) {
                    $.addtabs.close({
                        "id": $(this).children('a').attr('aria-controls')
                    });
                }
            });
            $.addtabs.drop();
        });
        popHtml.mouseleave(function () {
            $(this).hide();
        });
        $('body').click(function () {
            popHtml.hide();
        })
    };

    _listen = function () {
        $(settings.monitor).on('click', '[data-addtab]', function () {
            _click($(this));
            $.addtabs.drop();
        });

        $('body').on('click', '.tab-close', function () {
            var id = $(this).prev("a").attr("aria-controls");
            $.addtabs.close({
                'id': id
            });
            $.addtabs.drop();
        });

        $('body').on('mouseover', 'li[role = "presentation"]', function () {
            $(this).find('.tab-close').show();
        });

        $('body').on('mouseleave', 'li[role = "presentation"]', function () {
            $(this).find('.tab-close').hide();
        });

        if (settings.contextmenu) {
            //obj上禁用右键菜单
            $('body').on('contextmenu', 'li[role=presentation]', function (e) {
                var id = $(this).children('a').attr('aria-controls');
                _pop(id, $(this), e);
                return false;
            });
        }

        var el;
        $('body').on('dragstart.h5s', '.tabs-list li', function (e) {
            el = $(this);
            // 清除拖动操作携带的数据，否者在部分浏览器上会打开新页面
            if(e.originalEvent && e.originalEvent.dataTransfer
                && 'function' == typeof e.originalEvent.dataTransfer.clearData){
                e.originalEvent.dataTransfer.clearData();
            }
        }).on('dragover.h5s dragenter.h5s drop.h5s', '.tabs-list li', function (e) {
            if (el == $(this)) return;
            $('.dragBack').removeClass('dragBack');
            $(this).addClass('dragBack');
            // 支持前后调整标签顺序
            if (el.index() < $(this).index()) {
                el.insertAfter($(this))
            } else {
                $(this).insertAfter(el)
            }
        }).on('dragend.h5s', '.tabs-list li', function () {
            $('.dragBack').removeClass('dragBack');
        });

        $('body').on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
            var id = $(this).parent('li').attr('id');
            id = id ? id.substring(8) : '';
            if (settings.store) {
                var tabs = $.parseJSON(_store('addtabs'+settings.storeName));
                $.each(tabs, function (k, t) {
                    (t.id == id) ?(t.active = 'true'):(delete t.active);
                });
                tabs = JSON.stringify(tabs);
                _store('addtabs'+settings.storeName, tabs);
            }
        });

        // 浏览器大小改变时自动收放tab
        $(window).on('resize', function() {
            $.addtabs.drop();
        });
    };

    $.addtabs = function (options) {
        $.addtabs.set(options);
        _listen();
        if (settings.store) {
            var tabs = _store('addtabs'+settings.storeName) ? $.parseJSON(_store('addtabs'+settings.storeName)) : {};
            var active;
            $.each(tabs, function (k, t) {
                if (t.active) active = k;
                $.addtabs.add(t);
            });
            if (active) {
              target.children('.active').removeClass('active');
              $('#tab_' + active).addClass('active');
              $('#tabs-content').children('.active').removeClass('active');
              $('#' + active).addClass('active');
            }
        }
    };

    $.addtabs.set = function () {
        if (arguments[0]) {
            if (typeof arguments[0] == 'object') {
                settings = $.extend(settings, arguments[0] || {});
            } else {
                settings[arguments[0]] = arguments[1];
            }
        }
        if (typeof settings.target == 'object') {
            target = settings.target;
        } else {
            target = $('body').find(settings.target).length > 0 ? $(settings.target).first() : $('body').find('.tabs-list').first();
        }
    }

    $.addtabs.add = function (opts) {
        var a_target, content;
        opts.id = opts.id ? opts.id : Math.random().toString(36).substring(3, 35);
        if (typeof opts.target == 'object') {
            a_target = opts.target;
        } else if (typeof opts.target == 'string') {
            a_target = $('body').find(opts.target).first();
        } else {
            a_target = target;
        }

        var id = 'tab_' + opts.id;
        var tab_li = a_target;
        // 写入cookie
        if (settings.store) {
          var tabs = _store('addtabs'+settings.storeName) ? $.parseJSON(_store('addtabs'+settings.storeName)) : {};
            tabs[id] = opts;
            tabs[id].target = (typeof tabs[id].target == 'object') ? settings.target : tabs[id].target;
            $.each(tabs, function (k, t) {
                delete t.active;
            });
            tabs[id].active = 'true';
            tabs = JSON.stringify(tabs);
            _store('addtabs'+settings.storeName, tabs);
        }

        var tab_content = $('#tabs-content');

        var lastTabId = tab_content.children('div[role="tabpanel"].active');
        localStorage.setItem('addtabs_active_id', lastTabId.attr('id'));

        tab_li.children('li[role="presentation"].active').removeClass('active');
        tab_content.children('div[role="tabpanel"].active').removeClass('active');
        // 如果TAB不存在，创建一个新的TAB
        if (tab_li.find('#tab_' + id).length < 1) {
            var cover = $('<div>', {
                'id': 'tabCover',
                'class': 'tab-cover'
            });
            // 创建新TAB的title
            var title = $('<li>', {
                'role': 'presentation',
                'id': 'tab_' + id,
                'aria-url': opts.url,
                'aria-ajax': opts.ajax ? true : false
            }).append(
                $('<a>', {
                    'href': '#' + id,
                    'aria-controls': id,
                    'role': 'tab',
                    'data-toggle': 'tab'
                }).html(opts.title)
            );

            // 是否允许关闭
            if (settings.close) {
                title.append(
                    $('<i>', {
                        'class': 'tab-close fa fa-remove',
                        'style': 'display:none',
                    })
                );
            }
            // 创建新TAB的内容
            var content = $('<div>', {
                'class': 'tab-pane',
                //'class': 'tab-pane ' + settings.contentStyle,
                'id': id,
                // 'height': settings.height - 5,
                'role': 'tabpanel'
            });

            // 加入TABS
            tab_li.append(title);
            tab_content.append(content.append(cover));

            $.addtabs.drop();

        } else if (!opts.refresh) {
            $('#tab_' + id).addClass('active');
            $('#' + id).addClass('active');
            return;
        } else {
            content = $('#' + id);
            content.html('');
        }
        // 加载条
        if (settings.loadbar) {

            content.html($('<div>', {
                'class': ''
            }).append(
                $('<div>', {
                    'class': 'progress-bar progress-bar-striped progress-bar-success active',
                    'role': 'progressbar',
                    'aria-valuenow': '100',
                    'aria-valuemin': '0',
                    'aria-valuemax': '100',
                    'style': 'width:100%;position:absolute;height:20px;z-index:9999;'
                }).append('<span class="sr-only">100% Complete</span>')
                    .append('<span>' + settings.local.loadbar + '</span>')
            ));
        }

        // 是否指定TAB内容
        if (opts.content) {
            content.html(opts.content);
        } else if (settings.iframe == true && (opts.ajax == 'false' || !opts.ajax)) { //没有内容，使用IFRAME打开链接
            
            var iframe = $('<iframe>', {
                'class': 'tabIframe',
                //'height': settings.height,
                'id': 'iframe_' + opts.id,
                'name': 'iframe_' + opts.id,
                'width': "100%",
                'height': "100%",
                'frameborder': "no",
                'border': "0",
                'src': opts.url
            });
            content.html(iframe);
            
            layer.load(2);
            iframe.load(function() {
                layer.closeAll('loading');
            })

        } else {
            var ajaxOption = $.extend(settings.ajax, opts.ajax || {});
            ajaxOption.url = opts.url;
            ajaxOption.error = function(XMLHttpRequest, textStatus) { content.html(XMLHttpRequest.responseText); };
            ajaxOption.success = function (result) {
                content.html(result);
            }
            $.ajax(ajaxOption);
        }
        
        // 激活TAB
        tab_li.find('#tab_' + id).addClass('active');
        tab_content.find('#' + id).addClass('active');
        tab_content.find('#' + id).find('#tabCover').remove();
    };

    $.addtabs.close = function (opts) {

        var lastTabId = localStorage.getItem('addtabs_active_id');
        // 如果关闭的是当前激活的TAB，激活他的前一个TAB
        if ($("#tab_" + opts.id).hasClass('active')) {
            if ($('#tab_' + opts.id).parents('li.tabdrop').length > 0 && !$('#tab_' + opts.id).parents('li.tabdrop').hasClass('hide')) {
                
                var lastTab = $("#tab_" + lastTabId);
                if (lastTab.size() > 0) {
                    $("#tab_" + lastTabId).tab('show');
                } else {
                    $('#tab_' + opts.id).parents('.tabs-list').find('li').last().tab('show');
                }
            
            } else {
                var lastTab = $("#tab_" + lastTabId);
                if (lastTab.size() > 0) {
                    $("#tab_" + lastTabId).tab('show');
                } else {
                    $("#tab_" + opts.id).prev('li').tab('show');
                }
            }

            var lastTab = $("#tab_" + lastTabId);
            if (lastTab.size() > 0) {
                $("#" + lastTabId).addClass('active');
            } else {
                $("#" + opts.id).prev().addClass('active');
            }
        }
        // 关闭TAB
        $("#tab_" + opts.id).remove();
        $("#" + opts.id).remove();
        if (settings.store) {
            var tabs = $.parseJSON(_store('addtabs'+settings.storeName));
            delete tabs[opts.id];
            tabs = JSON.stringify(tabs);
            _store('addtabs'+settings.storeName, tabs);
        }
   
        $.addtabs.drop();

        settings.callback();
    };

    $.addtabs.closeAll = function (target) {
        if (typeof target == 'string') {
            target = $('body').find(target);
        }
        $.each(target.find('li[id]'), function () {
            var id = $(this).children('a').attr('aria-controls');
            $("#tab_" + id).remove();
            $("#" + id).remove();
        });
        target.find('li[role = "presentation"]').first().addClass('active');
        var firstID = target.find('li[role = "presentation"]').first().children('a').attr('aria-controls');
        $('#' + firstID).addClass('active');
        $.addtabs.drop();
    };

    $.addtabs.drop = function () {
        // 创建下拉标签
        var dropdown = $('<li>', {
            'class': 'dropdown pull-right hide tabdrop'
        }).append(
            $('<a>', {
                'class': 'dropdown-toggle',
                'data-toggle': 'dropdown',
                'href': '#'
            }).append(
                $('<i>', {
                    'class': "fa fa-align-justify"
                })
            ).append(
                $(' <b>', {
                    'class': 'caret'
                })
            )
        ).append(
            $('<ul>', {
                'class': "dropdown-menu"
            })
        )

        $('body').find('.tabs-list').each(function () {
            var element = $(this);
            // 检测是否已增加
            if (element.find('.tabdrop').length < 1) {
                dropdown.prependTo(element);
            } else {
                dropdown = element.find('.tabdrop');
            }
            // 检测是否有下拉样式
            if (element.parent().is('.tabs-below')) {
                dropdown.addClass('dropup');
            }
            var collection = 0;

            var ww = $(window).width();
            var left = $('#navbar-left').width();
            var right = $('.nav-user').width();
            var www = ww - left - right;
            // 检查超过一行的标签页
            element.append(dropdown.find('li'))
                .find('>li')
                .not('.tabdrop')
                .each(function() {
                    www = www - $(this).width();
                    // this.offsetTop > 0 || 
                    /*
                    if (element.width() - $(this).position().left - $(this).width() < 83) {
                        dropdown.find('ul').prepend($(this));
                        collection++;
                    }
                    */
                    if (www < $(this).width()) {
                        dropdown.find('ul').prepend($(this));
                        collection++;
                    }
                });

            // 如果有超出的，显示下拉标签
            if (collection > 0) {
                dropdown.removeClass('hide');
                if (dropdown.find('.active').length == 1) {
                    dropdown.addClass('active');
                } else {
                    dropdown.removeClass('active');
                }
            } else {
                dropdown.addClass('hide');
            }
        })

    }
})(jQuery);

$(function () {
    $.addtabs();
})
