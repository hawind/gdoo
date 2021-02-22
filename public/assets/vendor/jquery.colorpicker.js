/**
 * 颜色拾取器
 * 
 * @author hawind
 * @url http://gdoo.net
 * @name jquery.colorpicker.js
 * @since 2020-11-16
 */
(function($) {
    var ColorHex = new Array('00','33','66','99','CC','FF');
    var SpColorHex = new Array('FF0000','00FF00','0000FF','FFFF00','00FFFF','FF00FF');
    $.fn.colorpicker = function(options) {
        var opts = jQuery.extend({}, jQuery.fn.colorpicker.defaults, options);
        initColor();
        return this.each(function() {
            var obj = $(this);
            obj.on(opts.event + '.colorpicker', function() {

                var $panel = $("#color-panel");

                // 定位
                var ttop = $(this).offset().top; // 控件的定位点高
                var thei = $(this).height(); //控件本身的高
                var tleft = $(this).offset().left; //控件的定位点宽
                $panel.css({
                    top: ttop + thei + 15,
                    left: tleft
                }).show();

                var target = opts.target ? $(opts.target) : obj;
                if (target.data("color") == null) {
                    target.data("color", target.css("color"));
                }
                if (target.data("value") == null) {
                    target.data("value", target.val());
                }

                $("#color-panel-reset").on("click.colorpicker", function() {
                    target.css("color", target.data("color")).val(target.data("value"));
                    var color = target.data("value");
                    color = opts.ishex ? color : getRGBColor(color);

                    $panel.hide();
                    opts.reset(obj, color);
                });
          
                $("#color-panel-body").off("click.colorpicker").on('mouseover.colorpicker', 'tr td', function() {
                    var color = $(this).css("background-color");
                    $("#color-panel-color").css("background", color);
                    $("#color-panel-hex-color").val($(this).attr("rel"));
                }).on('click.colorpicker', 'tr td', function() {
                    var color = $(this).attr("rel");
                    color = opts.ishex ? color : getRGBColor(color);
                    if (opts.fillcolor) target.val(color);
                    target.css("color", color);

                    $panel.hide();
                    $("#color-panel-reset").off("click.colorpicker");
                    opts.change(obj, color);
                });

                setColor(target.val());
            });
        });

        function setColor(color) {
            $("#color-panel-color").css("background", color);
            $("#color-panel-hex-color").val(color);
        }
        function initColor() {
            $("body").append('<style>.colorpicker-controller{background-color:#fff;border: 1px solid #bbb;width:18px;height:18px;}.colorpicker{margin:2px;outline:none;display:inline-block;cursor:pointer;width:12px;height:12px;}</style><div id="color-panel" style="background-color:#fff;border-radius:6px;box-shadow:0 5px 10px rgba(0,0,0,0.2);padding:5px;border:solid 1px #ccc;position:absolute;z-index:1051;display:none;"></div>');
            var colorTable = '';
            var colorValue = '';
            for(i = 0;i < 2; i++) {
                for(j = 0; j < 6; j++) {
                    colorTable = colorTable + '<tr height="12">'
                    colorValue = i == 0 ? ColorHex[j] + ColorHex[j] + ColorHex[j] : SpColorHex[j];
                    colorTable = colorTable + '<td width="11" rel="#'+ colorValue +'" style="background-color:#'+ colorValue +'">'
                    for (k=0; k < 3; k++) {
                        for (l = 0; l < 6; l++) {
                            colorValue = ColorHex[k + i * 3]+ColorHex[l] + ColorHex[j];
                            colorTable = colorTable + '<td width="11" rel="#'+ colorValue +'" style="background-color:#'+ colorValue +'">'
                        }
                    }
                }
            }

            colorTable = '<table width="230" border="0" cellspacing="0" cellpadding="0">'
            + '<tr height="30"><td colspan="21" bgcolor="#fff">'
            + '<table cellpadding="0" cellspacing="1" border="0" style="border-collapse:collapse">'
            + '<tr><td width="3"><td><input type="text" id="color-panel-color" size="6" disabled style="border:inset 1px #ccc;"></td>'
            + '<td width="3"><td><input type="text" id="color-panel-hex-color" size="7" style="border:inset 1px #ccc; font-family:Arial;"><a href="javascript:;" id="color-panel-close" style="padding-left:15px;">关闭</a> <a href="javascript:;" style="padding-left:5px;" id="color-panel-reset">重置</a></td></tr></table></td></table>'
            + '<table width="230" id="color-panel-body" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse" style="cursor:pointer;">'
            + colorTable + '</table>';

            var $panel = $("#color-panel");

            $panel.html(colorTable);

            $(document).on('mousedown.colorpicker', function() {
                $panel.hide();
            });

            $panel.on('mousedown.colorpicker', function(e) {
                e.stopPropagation();
            });

            $("#color-panel-close").on('click.colorpicker', function() {
                $panel.hide();
                return false;
            });
        }
        
        function getRGBColor(color) {
            var result;
            if (color && color.constructor == Array && color.length == 3)
                color = color;
            if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
                color = [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];
            if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
                color =[parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];
            if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
                color =[parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];
            if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
                color =[parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];
            return "rgb("+color[0]+","+color[1]+","+color[2]+")";
        }
    };
    jQuery.fn.colorpicker.defaults = {
        ishex : true, // 是否使用16进制颜色值
        fillcolor: false, // 是否将颜色值填充至对象的val中
        target: null, // 目标对象
        event: 'click', // 颜色框显示的事件
        change: function() {}, // 回调函数
        reset: function() {}
    };
})(jQuery);