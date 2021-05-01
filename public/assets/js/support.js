var select2List = {};
var dialogCacheSelected = {};

$(function() {

    var $document = $(document);

    // 注册jQuery Ajax全局错误提示
    $document.ajaxError(function(event, xhr) {
        if (xhr.responseJSON) {
            toastrError(xhr.responseJSON.message);
        }
    });

    var input_select2 = $document.find('.input-select2');
    if(input_select2.length) {
        input_select2.select2();
    }

    // 新提示 
    $document.tooltip({
        container: 'body',
        placement: 'auto',
        selector:'.hinted',
        delay: {show: 200, hide: 0}
    });

    // 批量操作
    $('.select-all').on('click', function() {
        var tr = $('.select-row').closest('tr');
        if($(this).prop('checked')) {
            tr.addClass('success');
        } else {
            tr.removeClass('success');
        }
        $(".select-row").prop('checked', $(this).prop('checked'));
    });

    // 从子窗口关闭tab
    $document.on('click', '[data-toggle="closetab"]', function() {
        // 获取框架的名称
        if (window.name) {
            var id = window.name.replace('iframe_', '');
        } else {
            var id = $(this).data('id');
        }
        top.$.addtabs.close({id:'tab_' + id});
    });

    // 点击td选择行
    $('.table tbody tr').on('click', function(e) {

        var tr = $(this);
        var checkbox = tr.find('.select-row');
        var checked = checkbox.prop('checked');

        if(checkbox.length == 0) {
            return;
        }

        if(e.target.tagName == 'INPUT') {
            setCheckbox(checked);
        }

        if(e.target.tagName == 'DIV') {
            setCheckbox(!checked);
        }

        if(e.target.tagName == 'TD') {
            setCheckbox(!checked);
        }

        function setCheckbox(checked) {
            if(checked) {
                tr.addClass('success');
            } else {
                tr.removeClass('success');
            }
            checkbox.prop('checked', checked);
        }
    });

    // 转到某些地址，单页面内类型切换
    $document.on('change', '[data-toggle="redirect"]', function() {
        // 从select的rel传递过来的地址
        var url = $(this).data('url');
        var id  = $(this).attr('id');
        var selected = $(this).find("option:selected").val();
        location.href = url.replace(new RegExp('('+id+'=)[^&]*','g'),'$1'+selected);
    });

    // 清除弹出层id
    $document.on('click.dialog.search', '[data-toggle="dialog-clear"]', function() {
        var params = $(this).data();
        $('#' + params.id).val('');
        $('#' + params.id + '_text').val('');
        var event = gdoo.event.get(params.id);
        event.trigger('clear', params);
    });

    // 弹出对话框表单
    $document.on('click.dialog.view', '[data-toggle="dialog-view"]', function() {
        var params = $(this).data();
        var query = {};
        $.each(params, function(k, v) {
            if (k == 'url' || k == 'title' || k == 'toggle') {
                return true;
            }
            query[k] = v;
        });

        // 传递当前iframe
        var iframe_id = getIframeName();
        if (iframe_id) {
            query.iframe_id = iframe_id;
        }

        var option = gdoo.formKey(params);
        var event = gdoo.event.get(option.key);
        event.trigger('open', params, query);

        var url = params['url'];
        var title = params['title'];

        var url = app.url(url, query);
        $.dialog({
            title: title,
            url: url,
            dialogClass: 'modal-lg',
            buttons: [{
                text: '取消',
                class: 'btn-default',
                click: function() {
                    var me = this;
                    $(me).dialog("close");
                }
            },{
                text: "确定",
                'class': "btn-info",
                click: function() {
                    var grid = gdoo.dialogs[option.id];
                    if (grid) {
                        var ret = gdoo.dialogSelected(event, params, option, grid);
                        if (ret === true) {
                            $(this).dialog("close");
                        }
                    } else {
                        $(this).dialog("close");
                    }
                }
            }]
        });
    });

    var gdoo_dialog_input = $document.find('.gdoo-dialog-input');
    if (gdoo_dialog_input.length) {
        gdoo_dialog_input.gdooDialogInput();
    }

    // 弹出对话框表单
    $document.on('click.dialog.image', '[data-toggle="dialog-image"]', function() {
        var params = $(this).data();
        $.dialog({
            title: params.title,
            html: '<img style="text-align:center;max-width:100%;" src="'+params.url+'" />',
            buttons: [{
                text: '确定',
                'class': 'btn-default',
                click: function() {
                    $(this).dialog("close");
                }
            }]
        });
    });

    // 弹出对话框表单
    $document.on('click.dialog.form', '[data-toggle="dialog-form"]', function() {
        var params = $(this).data();
        params.id = params.id || 'myform';
        params.size = params.size || 'md';
        $.dialog({
            title: params.title,
            url: params.url,
            dialogClass: 'modal-' + params.size,
            buttons: [{
                text: '取消',
                class: 'btn-default',
                click: function() {
                    var me = this;
                    if (typeof error === 'function') {
                        error.call(me, res); 
                    } else {
                        $(me).dialog("close");
                    }
                }
            },{
                text: '保存',
                class: 'btn-info',
                click: function() {
                    var me = this;
                    var action = $('#'+params.id).attr('action');
                    var formData = $('#'+params.id).serialize();

                    $.post(action, formData, function(res) {

                        if (typeof success === 'function') {
                            success.call(me, res); 
                        } else {
                            if(res.status) {
                                if (res.data == 'reload') {
                                    window.location.reload();
                                } else {
                                    toastrSuccess(res.data);
                                    $(me).dialog('close');
                                }
                            } else {
                                toastrError(res.data);
                            }
                        }

                    },'json');
                }
            }]
        });
    });

    // 日期选择
    $document.on('click.date', '[data-toggle="date"]', function() {
        var data = $(this).data();
        var ops = {};
        ops['dateFmt'] = data['format'] || 'yyyy-MM-dd';
        var onpicked = window[this.id + '.onpicked'];
        if (typeof onpicked == 'function') {
            ops['onpicked'] = onpicked;
        }
        if (data['dchanging']) {
            ops['dchanging'] = data['dchanging'];
        }
        datePicker(ops);
    });

    // 日期时间选项
    $document.on('click.datetime', '[data-toggle="datetime"]', function() {
        var data = $(this).data();
        var ops = {};
        ops['dateFmt'] = data['format'] || 'yyyy-MM-dd';
        if  (data['dchanging']) {
            ops['dchanging'] = data['dchanging'];
        }
        datePicker(ops);
    });

    // 关闭layerFrame
    $document.on('click.frame.close', '[data-toggle="layer-frame-close"]', function() {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    });

    // 打开layerFrame
    $document.on('click.frame.url', '[data-toggle="layer-frame-url"]', function() {
        var url = $(this).data('url');
        var title = $(this).data('title') || false;
        var skin = $(this).data('skin') || 'frame';
        var close = $(this).data('close') || false;
        var index = layer.open({
            skin: 'layui-layer-' + skin,
            scrollbar: false,
            closeBtn: close,
            title: title,
            type: 2,
            move: false,
            area: ['100%', '100%'],
            content: url,
        });
    });

    // 打开tabFrame
    $document.on('click.tab.frame', '[data-toggle="tab-frame-url"]', function() {
        var url = $(this).data('url');
        var id = $(this).data('id');
        var name = $(this).data('name');
        top.addTab(url, id, name);
    });

    // 媒体管理删除
    $document.on('click', '[data-toggle="media-delete"]', function() {
        let me = this;
        let media = $(me).parent();
        let rows = $(me).closest('.media-controller').find('.media-item');
        if (rows.length > 1) {
            media.remove();
        } else {
            media.find('img').attr('src', app.url('assets/images/nopic.jpg'));
            media.find('input').val('');
        }
    });

    $('a.image-show').hover(function(e) {
        var params = $(this).data();
        var img = $('<p id="image"><img src="'+ params.url + '" alt="" /></p>');
		$("body").append(img);
        $(this).find('img').stop().fadeTo('slow', 0.5);
        var $window = $(window);
        var $image = $(document).find('#image');

		var height = $image.height();
        var width  = $image.width();

        var left = ($window.scrollLeft() + ($window.width() - width) / 2) + 'px';
        var top = ($window.scrollTop() + ($window.height() - height) / 2) +'px';

        var offset = $(this).offset();
        $image.css({left: offset.left + 100, top: top});
        $image.fadeIn('fast');

	}, function() {
	    $(this).find('img').stop().fadeTo('slow', 1);
		$("#image").remove();
    });

    // 表格拖动排序
    $('#table-sortable tbody').sortable({
        // opacity: 0.6,
        delay: 50,
        cursor: "move",
        axis:"y",
        items: "tr",
        handle: 'td.move',
        // containmentType:"parent",
        // placeholder: "ui-sortable-placeholder",
        helper: function(event, ui) {
            // 在拖动时，拖动行的cell（单元格）宽度会发生改变。
            ui.children().each(function() {
                $(this).width($(this).width());
            });  
            return ui;
        },
        stop: function (event, ui) {
        }, 
        start: function (event, ui) {
            ui.placeholder.outerHeight(ui.item.outerHeight());
        },
        update: function() {
            var url = $(this).parent().attr('url');
            var orders = $(this).sortable("toArray");
            $.post(url, {sort:orders}, function(res) {
                toastrSuccess(res.data);
            });
        }
    })//.disableSelection();
});

var app = {
    /**
     * 确认窗口
     */
    confirm: function(url, content, title) {
        title = title || '操作警告';
        $.messager.confirm(title, content, function(btn) {
            if (btn == true) {
                location.href = url;
            }
        });
    },
    /**
     * 警告窗口
     */
    alert: function(title, content) {
        $.messager.alert(title, content);
    },
    /**
     * 获取附带基本路径的URL
     */
    url: function(uri, params) {
        if(uri == '/') {
            return settings.public_url;
        }
        query = (params == '' || params === undefined) ? '' : '?' + $.param(params);
        return settings.public_url + '/' + uri + query;
    },
    redirect: function(uri, params) {
        return window.location.href = app.url(uri, params);
    },
    /**
     * 汉字转换为拼音
     */
    pinyin: function(read, write, type) {
        type = type || 'first';
        var field = $('#'+write).val();
        if (field == '') {
            $.get(app.url('index/api/pinyin?type='+ type +'&id='+Math.random()), {name:$('#'+read).val()}, function(data) {
                $('#'+write).val(data);
            });
        }
    }
}

var uploader = {
    file: function(fileId) {
        var id = $('#'+fileId).find(".id").val();
        location.href = app.url('index/attachment/download',{id:id});
    },
    cancel: function(fileId) {
        var id = $('#'+fileId).find(".id").val();
        if (id > 0) {
            var name = $('#'+fileId).find(".file-name a").text();
            $.messager.confirm('删除文件', '确定要删除 <strong>'+name+'</strong> 此文件吗', function(btn) {
                if (btn == true) {
                    $.get(app.url('index/attachment/delete'), {id:id}, function(res) {
                        if(res == 1) {
                            $('#'+fileId).remove();
                        }
                    });
                }
            });
        } else {
            $('#'+fileId).remove();
        }
    },
    insert: function(fileId) {
        var id = $('#'+fileId).find(".id").val();
        var name = $('#'+fileId).find(".file-name a").text();
        // 检查图片类型
        if(/\.(gif|jpg|jpeg|png|GIF|JPG|PNG)$/.test(name)) {
            var html = '<img src="' + app.url('index/attachment/show',{id: id}) + '" title="'+name+'">';
        } else {
            var html = '<a href="' + app.url('index/attachment/download',{id: id}) + '" title="'+name+'">'+name+'</a>';
        }
        UE.getEditor("content").execCommand('insertHtml', html);
    }
}

/**
 * 媒体对话框
 */
function mediaDialog(url, name, id, multi)
{
    let params = {id:id,name:name,multi:multi};
    var url = app.url(url, params);
    $.dialog({
        title: '媒体管理',
        url: url,
        dialogClass: 'modal-lg',
        buttons: [{
            text: '<i class="fa fa-remove"></i> 取消',
            'class': "btn-default",
            click: function() {
                $(this).dialog('close');
            }
        },{
            text: '<i class="fa fa-check"></i> 确定',
            'class': "btn-info",
            click: function() {
                if (window.saveMedia) {
                    window.saveMedia.call(this, params);
                    $(this).dialog('close');
                }
            }
        }]
    });
}

/**
 * 显示窗口
 */
function viewBox(name, title, url, size) {
    size = size || 'md'
    $.dialog({
        title: title,
        url: url,
        dialogClass: 'modal-' + size,
        buttons: [{
            text: "确定",
            'class': "btn-default",
            click: function() {
                $(this).dialog("close");
            }
        }]
    });
}

var viewDialogIndex = 0;
/**
 * 表单窗口
 */
function viewDialog(options) {

    if (options.id === undefined) {
        options.id = 'view-dialog-' + viewDialogIndex;
        viewDialogIndex ++;
    }

    var exist = $('#modal-' + options.id);
    if (exist.length > 0) {
        exist.dialog('show');
    }
    var defaults = {
        title: name,
        url: url,
        buttons: [{
            text: '确定',
            'class': 'btn-default',
            click: function() {
                $(this).dialog('close');
            }
        }]
    };
    var settings = $.extend({}, defaults, options);
    $.dialog(settings);
}

var formDialogIndex = 0;
/**
 * 表单窗口
 */
function formDialog(options)
{
    if(options.id === undefined) {
        options.id = 'form-dialog-' + formDialogIndex;
        formDialogIndex ++;
    }

    var exist = $('#modal-' + options.id);
    if(exist.length > 0) {
        exist.dialog('show');
    } else {
        var defaults = {
            title: 'formDialog',
            backdrop: 'static',
            buttons: [{
                text: '取消',
                class: 'btn-default',
                click: function() {
                    var me = this;
                    if (typeof error === 'function') {
                        error.call(me); 
                    } else {
                        $(me).dialog("close");
                    }
                }
            },{
                text: '保存',
                class: 'btn-info',
                click: function() {
                    var me = this;
                    var options = me.options;

                    // 自定义提交函数
                    if (typeof options.onSubmit === 'function') {
                        options.onSubmit.call(me);
                    } else {
                        // 默认提交方法
                        if (me.options.storeUrl) {
                            var action = me.options.storeUrl;
                        } else {
                            var action = $('#' + options.id).attr('action');
                        }
                        var query = $('#' + options.id).serialize();
                        // 循环子表
                        var gets = gridListData(options.table);
                        if(gets === false) {
                            return;
                        }

                        var loading = showLoading();

                        $.post(action, query + '&' + $.param(gets), function(res) {
                            if (res.status) {
                                if (typeof options.success === 'function') {
                                    options.success.call(me, res); 
                                }
                            } else {
                                if (typeof options.error === 'function') {
                                    options.error.call(me, res); 
                                }
                            }
                        },'json').complete(function() {
                            layer.close(loading);
                        });
                    }
                }
            }]
        };
        var settings = $.extend({}, defaults, options);
        $.dialog(settings);
    }
}

/**
 * 转换时间，计算差值
 */
function niceTime(timestamp) {
    // 当前时间戳
    var nowtime = (new Date).getTime();
    // 计算时间戳差值
    var secondNum = parseInt((nowtime-timestamp*1000)/1000);

    if(secondNum >= 0 && secondNum < 60) {
        return secondNum+'秒前';
    } else if (secondNum >= 60 && secondNum < 3600) {
        var nTime = parseInt(secondNum/60);
        return nTime+'分钟前';
    } else if (secondNum >= 3600 && secondNum < 3600*24) {
        var nTime = parseInt(secondNum/3600);
        return nTime+'小时前';
    }  else {
        var nTime = parseInt(secondNum/86400);
        return nTime+'天前';
    }
}

/**
 * 首字母大写
 */ 
function ucfirst(str) {
    if(str) {
        return str[0].toUpperCase() + str.substr(1);
    } else {
        return str;
    }
}

/** 
* 数字金额大写转换(可以处理整数,小数,负数)
*/
function digitUppercase(n) {
    var fraction = ['角','分'];
    var digit = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
    var unit = [
        ['元', '万', '亿'],
        ['', '拾', '佰', '仟']
    ];
    var head = n < 0 ? '欠' : '';
    n = Math.abs(n);
    var s = '';
    for (var i = 0; i < fraction.length; i++) {  
        s += (digit[Math.floor(n * 10 * Math.pow(10, i)) % 10] + fraction[i]).replace(/零./, '');  
    }  
    s = s || '整';
    n = Math.floor(n);
    for (var i = 0; i < unit[0].length && n > 0; i++) {
        var p = '';
        for (var j = 0; j < unit[1].length && n > 0; j++) {
            p = digit[n % 10] + unit[1][j] + p;
            n = Math.floor(n / 10);
        }
        s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
    }
    return head + s.replace(/(零.)*零元/, '元').replace(/(零.)+/g, '零').replace(/^整$/, '零元整');  
};

/**
 * 数字格式化
 */
function number_format(number, decimals, decPoint, thousandsSep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
    var n = !isFinite( + number) ? 0 : + number
    var prec = !isFinite( + decimals) ? 0 : Math.abs(decimals)
    var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
    var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
    var s = ''

    var toFixedFix = function (n, prec) {
        if (('' + n).indexOf('e') === -1) {
            return + (Math.round(n + 'e+' + prec) + 'e-' + prec)
        } else {
            var arr = ('' + n).split('e')
            var sig = ''
            if ( + arr[1] + prec > 0) {
                sig = '+'
            }
            return (+(Math.round(+ arr[0] + 'e' + sig + (+ arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
        }
    }
    // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || ''
        s[1] += new Array(prec - s[1].length + 1).join('0')
    }
    return s.join(dec)
}

/**
 * 检查变量是否为空
 */
function isEmpty(val) {
    try {
        if (val == '' || val == null || val == undefined) {
            return true;
        }
        // 判断数字是否是NaN
        if (typeof val === "number") {
            if (isNaN(val)) {
                return true;
            } else {
                return false;
            }
        }
        // 判断参数是否是布尔、函数、日期、正则，是则返回false
        if (typeof val === "boolean" || typeof val === "function" || val instanceof Date || val instanceof RegExp) {
            return false;
        }
        //判断参数是否是字符串，去空，如果长度为0则返回true
        if (typeof val === "string") {
            if (val.trim().length == 0) {
                return true;
            } else {
                return false;
            }
        }
    
        if (typeof val === 'object') {
            // 判断参数是否是数组，数组为空则返回true
            if (val instanceof Array) {
                if (val.length == 0) {
                    return true;
                } else {
                    return false;
                }
            }
    
            //判断参数是否是对象，判断是否是空对象，是则返回true
            if (val instanceof Object) {
                //判断对象属性个数
                if (Object.getOwnPropertyNames(val).length == 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    } catch(e) {
        return false;
    }
}

/**
 * 检查变量是否不为空
 */
function isNotEmpty(value) {
    return !isEmpty(value);
}

/**
 * 清除字符串两边的空格
 */
String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
}

/**
 * 封装全部替换字符串
 */
String.prototype.replaceAll = function(search, replace) { 
    return this.replace(new RegExp(search, "gm"), replace); 
}

/**
 * 正则去掉所有的html标记
 * @param {*} str 
 */
function delHtmlTag(str) {
    return str.replace(/<[^>]+>/g, "");
}

function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        return true;
    } else {
        return false;
    }
}

function toastrSuccess(content) {
    if (isWeiXin()) {
        $.toastr('success', content);
    } else {
        top.$.toastr('success', content);
    }
}

function toastrError(content) {
    if (isWeiXin()) {
        $.toastr('error', content);
    } else {
        top.$.toastr('error', content);
    }
}

function url(uri, params) {
    query = (params == '' || params === undefined) ? '' : '?' + $.param(params);
    return settings.public_url + '/' + uri + query;
}

/**
 *  时间戳格式化
 */
function format_datetime(value)
{
    function add0(v) {
        return v < 10 ? '0' + v : v;
    }
    value = parseInt(value) * 1000;
    var time = new Date(value);
    var y = time.getFullYear();
    var m = time.getMonth()+1;
    var d = time.getDate();
    var h = time.getHours();
    var mm = time.getMinutes();
    var s = time.getSeconds();
    return y+'-'+add0(m)+'-'+add0(d)+' '+add0(h)+':'+add0(mm);
}

/**
 *  时间戳格式化
 */
function format_date(value)
{
    function add0(v) {
        return v < 10 ? '0' + v : v;
    }
    value = parseInt(value) * 1000;
    var time = new Date(value);
    var y = time.getFullYear();
    var m = time.getMonth()+1;
    var d = time.getDate();
    var h = time.getHours();
    var mm = time.getMinutes();
    var s = time.getSeconds();
    return y+'-'+add0(m)+'-'+add0(d);
}

/**
 * ajax 提交
 * @param {*} table 
 * @param {*} callback 
 */
function ajaxSubmit(table, callback) {
    // 监听提交事件
    $('#' + table + '-form-submit').on('click', function() {
        var me   = $('#' + table);
        var url  = me.attr('action');
        var data = me.serialize();
        var rows = {};

        var rows = gridListData(table);
        if(rows === false) {
            return;
        }
        
        data = data +'&'+ $.param(rows);

        var loading = showLoading();

        $.post(url, data, function(res) {
            if (typeof callback === 'function') {
                callback(res);
            } else {
                if(res.status) {
                    toastrSuccess(res.data);
                    if(res.url) {
                        self.location.href = res.url;
                    }
                } else {
                    toastrError(res.data);
                }
            }
        }, 'json').complete(function() {
            layer.close(loading);
        });

        return false;
    });
}

/**
 * 获取框架的name
 * @returns
 */
function getIframeName() {
    var name = window.name;
    return name ? name.replace('iframe_', '') : '';
}

/**
 * 获取框架的document
 * @param {*} iframe_id 
 * @returns 
 */
function getIframeDocument(iframe_id) {
    if (iframe_id) {
        var iframe = window.frames['iframe_' + iframe_id];
        if (iframe) {
            return iframe.document;
        } else {
            //toastrError('iframe_id参数无效。');
        }
    }
    return null;
}

/**
 * 数据加载提示
 */
function showLoading(msg) {
    var loading = layer.msg(msg || '数据提交中...', {
        icon: 16, 
        shade: 0.1, 
        time: 1000 * 120
    });
    return loading;
}

/**
 * 格式化文件大小
 * @param {*} fileSize 
 */
function fileFormatSize(fileSize) {
    if (fileSize < 1024) {
        return fileSize + 'B';
    } else if (fileSize < (1024*1024)) {
        var temp = fileSize / 1024;
        temp = temp.toFixed(2);
        return temp + 'KB';
    } else if (fileSize < (1024*1024*1024)) {
        var temp = fileSize / (1024*1024);
        temp = temp.toFixed(2);
        return temp + 'MB';
    } else {
        var temp = fileSize / (1024*1024*1024);
        temp = temp.toFixed(2);
        return temp + 'GB';
    }
}

/** 
 * 扫码上传功能
 */
function FindFile(inputId, key) {
    $.post(app.url('index/attachment/draft'), {key: key}, function(data) {
        var qrArray = [];
        var fileDraft = "#fileDraft_" + inputId;
        var items = $(fileDraft).find(".id");
        $.each(items, function(i, row) {
            qrArray.push($(this).val());
        });
        $.each(data, function(i, row) {
            if (qrArray.indexOf(row.id + "") == -1) {
                row.size = fileFormatSize(row.size);
                var html = template("uploader-item-tpl", row);
                $(fileDraft).append(html);
            }
        });
    });
}

/**
 * 格式为数值
 * @param {*} value 
 */
function toNumber(value) {
    value = parseFloat(value);
    return isNaN(value) ? 0 : isFinite(value) ? value : 0;
}

/**
 * 实现StringBuilder
 */ 
function StringBuilder() {
    this._stringArray = new Array();
}
StringBuilder.prototype.append = function(str) {
    this._stringArray.push(str);
}
StringBuilder.prototype.appendLine = function(str) {
    this._stringArray.push(str + "\n");
}
StringBuilder.prototype.toString = function(joinGap) {
    return this._stringArray.join(joinGap);
}

/**
 * 本地导出excel
 * @param {*} grid 
 * @param {*} name
 */
function LocalExport(grid, name) {

    if (grid.api.getDisplayedRowCount() == 0) {
        toastrError("表格无数据，无法导出.");
        return;
    }

    function getColumnsTable(columns) {
        var table = [];
        getColumnsRows(columns, 0, table);
        return table;
    }

    function getColumnsRows(columns, level, table) {
        var row = null;
        if (table.length > level) {
            row = table[level];
        } else {
            row = [];
            table.push(row);
        }

        $.each(columns, function (name, column) {
            var children = column['children'];
            if (children != null) {
                column['colspan'] = children.length;
                getColumnsRows(children, level + 1, table);
            }
            column['rowspan'] = 1;
            row.push(column);
        });
    }

    function getColumnsBottom(columns) {
        var columnsBottom = [];
        $.each(columns, function(i, column) {
            if (column['children'] != null) {
                var children = column['children'];
                $.each(getColumnsBottom(children), function(j, v) {
                    columnsBottom.push(v);
                });
            } else {
                columnsBottom.push(column);
            }
        });
        return columnsBottom;
    }

    var columns = [];
    $.each(grid.columnDefs, function(i, column) {
        if (column['checkboxSelection'] == true) {
            return;
        }
        if (column['cellRenderer'] == '"actionCellRenderer"') {
            return;
        }
        columns.push(column);
    });
    var columnsBottom = getColumnsBottom(columns);
    var columnsTable = getColumnsTable(columns);

    console.log("开始导出任务:" + name);
    var sb = new StringBuilder();
    // 写出列名
    var columnsCount = columnsTable.length - 1;
    $.each(columnsTable, function(i, rows) {
        var columnsRow = rows;
        sb.appendLine('<tr style="font-weight:bold;white-space:nowrap;">');
        $.each(columnsRow, function(j, column) {

            var rowspan = toNumber(column['rowspan']);
            var colspan = toNumber(column['colspan']);

            if (columnsCount > i) {
                if (column['children'] == null) {
                    rowspan = rowspan + (i + 1);
                }
            }

            var s = '<td colspan="' + colspan + '" rowspan="' + rowspan + '"';
            var style = ['text-align:center'];
            if (column['headerName'] == '序号') {
                style.push('mso-number-format:\'@\'');
            }
            s = s + ' style="' + style.join(';') + '"';
            s = s + '>' + column['headerName'] + '</td>';
            sb.appendLine(s);
        });
        sb.appendLine('</tr>');
    });

    // 写出数据
    var count = 0;
    grid.api.forEachNode(function(rowNode, index) {
        var row = rowNode.data;
        sb.append("<tr>");
        count++;
        $.each(columnsBottom, function (n, column) {

            var value;
            if (column['field'] == null) {
                value = '';
            } else {
                value = row[column['field']] || '';
            }

            if (column['cellRenderer'] == 'htmlCellRenderer') {
                value = delHtmlTag(value);
            }

            if (column['headerName'] == '序号') {
                value = count;
            }

            var style = [];
            if (column.type == "number") {
                var options = column.numberOptions || {};
                var places = options.places == undefined ? 2 : options.places;
                value = parseFloat(value);
                value = isNaN(value) ? 0 : value.toFixed(places);
            } else if (column.form_type == 'date') {
            } else {
                style.push('mso-number-format:\'@\'');
            }
    
            sb.appendLine('<td style="' + style.join(';') + '">' + value + '</td>');
        });
        sb.appendLine("</tr>");
    });

    console.log("结束导出任务:" + name);
    var worksheet = 'Sheet1';
    var excel = sb.toString(" ");
    // 下载的表格模板数据
    var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ' +
        'xmlns:x="urn:schemas-microsoft-com:office:excel" ' +
        'xmlns="http://www.w3.org/TR/REC-html40">' +
        '<head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>' +
        '<x:Name>' + worksheet + '</x:Name>' +
        '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>' +
        '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->' +
        '</head><body><table>' + excel + '</table></body></html>';
    // 保存文件到本地
    function saveShareContent(content, fileName)
    {
        var downLink = document.createElement('a');
        downLink.download = fileName;
        // 字符内容转换为blod地址
        var blob = new Blob([content]);
        downLink.href = URL.createObjectURL(blob)
        // 链接插入到页面
        document.body.appendChild(downLink);
        downLink.click();
        // 移除下载链接
        document.body.removeChild(downLink);
    }
    var d = new Date();
    var date = [d.getFullYear(), d.getMonth() + 1, d.getDate()].join('-');
    saveShareContent(template, name + "-" + date + ".xls");
}

/**
 * 导出普通table成xls
 * @param {*} id 
 * @param {*} name 
 */
function LocalTableExport(id, name) {
    var table = $('#' + id);
    var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
    var d = new Date();
    var date = [d.getFullYear(), d.getMonth() + 1, d.getDate()].join('-');
    table.table2excel({
        exclude: ".noExl",
        name: name,
        filename: name + date + ".xls",
        fileext: ".xls",
        exclude_img: true,
        exclude_links: true,
        exclude_inputs: true,
        preserveColors: preserveColors
    });
}

/**
 * 选择行政区域
 */
function regionSelect() {
    var params = arguments;
    var a = {'a1': '省', 'a2': '市', 'a3': '县'};
    function getRegion(id, layer, parent_id, value) {
        $.get(app.url('index/api/region', {layer:layer,parent_id:parent_id}), function(res) {
            var option = '';
            $.map(res, function(row) {
                option += '<option value="'+row.id+'">'+row.name+'</option>';
            });
            var e = $('#'+id).html(option);
            if (value > 0) {
                e.val(value);
            }
        });
    }
    $('#'+params[0]).on('change', function() {
        getRegion(params[1], 2, this.value, 0);
        $('#'+params[1]).html('<option value="">'+ a['a'+2] +'</option>');
        $('#'+params[2]).html('<option value="">'+ a['a'+3] +'</option>');
    });
    $('#'+params[1]).on('change', function() {
        getRegion(params[2], 3, this.value, 0);
        $('#'+params[2]).html('<option value="">'+ a['a'+3] +'</option>');
    });
    getRegion(params[0], 1, 0, params[3]);
    if (params[3]) {
        getRegion(params[1], 2, params[3], params[4]);
        if (params[4]) {
            getRegion(params[2], 3, params[4], params[5]);
        }
    }
};