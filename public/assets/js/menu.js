
function checkMQ() {
	// 检查移动或桌面设备
	return window.getComputedStyle(document.querySelector('.main-content'), '::before').getPropertyValue('content').replace(/'/g, "").replace(/"/g, "");
}

function addTab(url, id, name) {

    var size = $('#tabs-list').find('#tab_tab_' + id).length;
    if (size > 0) {
		$.addtabs.add({
			id: id,
			iframeId: id,
			target: "#tabs-list",
			url: app.url(url),
			title: name,
			loadbar: true,
			refresh: true
		});
    } else {
        $.addtabs.add({
            id: id,
            target: "#tabs-list",
            url: app.url(url),
			loadbar: true,
            title: name
        });
    }
    
    /*
	// 不刷新页面改变地址
	if(history.replaceState) {
		var i = url.replace(settings.public_url + '/', '');
		history.replaceState(null, '', settings.public_url + '?i=' + i);
	}
    */
    
	if ($('.side-nav').hasClass('nav-is-visible')) {
		var sidebar = $('.side-nav'),
		scroll = $('.nav-scroll'),
		sidebarTrigger = $('.nav-trigger');
		$([sidebar, sidebarTrigger, scroll]).toggleClass('nav-is-visible');
	}
	  
	// 删除hover样式
	$('.side-nav').find('.hover').removeClass('hover');
	$('.side-nav').find('.selected').removeClass('selected');
}

$(function() {
	
	var $body = $('body');

	$('[data-toggle="addtab"]').on('click', function(event) {
		event.preventDefault();
		// 无ID不触发事件
		var data = $(this).data();
		if(data.id == undefined) {
			return;
		}
		addTab(data.url, data.id, data.name);
	});

	// 缓存dom元素
	var mainContent = $('.main-content'),
		header = $('.main-header'),
		sidebar = $('.side-nav'),
		scroll = $('.nav-scroll'),
		sidebarTrigger = $('.nav-trigger');

	// 仅移动 - 当用户单击菜单时打开侧边栏
	sidebarTrigger.on('click', function(event) {
		event.preventDefault();
		$([sidebar, sidebarTrigger, scroll]).toggleClass('nav-is-visible');
	});

	// 初始化折叠菜单
	var folded = localStorage.getItem("side-folded");
	if (folded === 'on') {
		$body.addClass('side-folded');
		$('.folded').addClass('active');
	}
	
	// 折叠左边菜单
    $(document).on('click', "[data-toggle=side-folded]", function(event) {
		event.preventDefault();
		$(this).toggleClass('active');
		$body.toggleClass('side-folded');
		// 保存折叠菜单到本地
		localStorage.setItem("side-folded", $body.hasClass('side-folded') === true ? 'on' : 'off');
    });

	// 单击项并显示子菜单
	$('.has-children > a').on('click', function(event) {

		var mq = checkMQ(),
			selectedItem = $(this);

		if( mq == 'mobile' || mq == 'tablet' ) {

			event.preventDefault();

			if (selectedItem.parent('li').hasClass('selected')) {
				selectedItem.parent('li').removeClass('selected');
			} else {
				selectedItem.parent().parent().find('>.has-children.selected').removeClass('selected');
				selectedItem.parent('li').addClass('selected');
			}
		}
	});

	$('.has-children').on('mouseover mouseout', function(event) {

		var mq = checkMQ();

		if (mq == 'desktop') {
			// 鼠标悬浮
			if (event.type == 'mouseover') {
				var wh = $(window).height();
				$(this).addClass('hover');
				var list = $(this).find('ul:visible').not('.fix');
				list.each(function(i) {
					var uh = $(this).height();
					var p = $(this).offset();
					var c = wh - p.top - uh - 25;
					/* 二级菜单和三级菜单高出window */
					if (c < 0) {
						$(this).css({top:c});
						$(this).addClass('fix');
					}
				});
			// 鼠标离开
			} else if(event.type == 'mouseout') {
				$(this).removeClass('hover');
			}
		}
	});
});