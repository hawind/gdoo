(function ($) {
	// 模式提示
	$.rockmodelmsg = function (lx, txt, sj, fun) {
		clearTimeout($.rockmodelmsgtime);
		$('#rockmodelmsg').remove();
		js.msg('none');
		if (!fun) fun = function () { };
		if (lx == 'none') return;
		var s = '<div id="rockmodelmsg" onclick="$(this).remove()" align="center" style="position:fixed;left:45%;top:30%;z-index:9999;border-radius:10px;padding:30px; background:rgba(0,0,0,0.7);color:white;font-size:18px">';
		if (lx == 'wait') {
			if (!txt) txt = '处理中...';
			s += '<div><img src="/assets/chat/images/mloading.gif"></div>';
			s += '<div style="padding-top:5px">' + txt + '</div>';
			if (!sj) sj = 60;
		}
		if (lx == 'ok') {
			if (!txt) txt = '处理成功';
			s += '<div style="font-size:40px">✔</div>';
			s += '<div>' + txt + '</div>';
		}
		if (lx == 'msg' || !lx) {
			if (!txt) txt = '提示';
			s += '<div style="font-size:40px;color:red">☹</div>';
			s += '<div style="color:red">' + txt + '</div>';
		}
		s += '</div>';
		$('body').append(s);
		if (!sj) sj = 3;
		var le = (winWb() - $('#rockmodelmsg').width()) * 0.5 - 20;
		$('#rockmodelmsg').css('left', '' + le + 'px');
		$.rockmodelmsgtime = setTimeout(function () {
			$('#rockmodelmsg').remove();
			fun();
		}, sj * 1000);
	}
	js.msgok = function (msg, fun, sj) {
		$.rockmodelmsg('ok', msg, sj, fun);
	};
	js.msgerror = function (msg, fun, sj) {
		$.rockmodelmsg('msg', msg, sj, fun);
	};
	js.loading = function (msg, sj) {
		$.rockmodelmsg('wait', msg, sj);
	};
	js.unloading = function () {
		$.rockmodelmsg('none');
	};

})(jQuery); 