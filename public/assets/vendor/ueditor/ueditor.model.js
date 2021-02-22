UE.plugins["model-field"] = function() {
	var me = this;
	me.commands['model-field'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function()
		{
			var dialog = UE.ui.Dialog({
				iframeUrl: app.url('model/template/field'),
				editor:editor,
				className:'edui-for-text',
				title:'单行输入框',
				buttons:[{
					className:'edui-okbutton',
					label:'确认',
					onclick: function() {
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function() {
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['model-Dialog'] = dialog;
			dialog.open();
		}
	}

	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content:'',
		className:'edui-bubble',
		_edittext:function() {
			baidu.editor.plugins['model-field'].editdom = popup.anchorEl;
			me.execCommand('model-field');
			this.hide();
		},
		_delete:function() {
			if(window.confirm('确认删除该控件吗？')) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	});
	
	popup.render();
	me.addListener('mouseover',function(cmd, evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;

		if (el.getAttribute('data-toggle') == 'model-field') {
			var html = popup.formatHtml('<nobr>字段操作: <span onclick="$$._edittext()" class="edui-clickable">编辑</span>&nbsp;<span onclick="$$._delete()" class="edui-clickable">删除</span></nobr>');
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	});
};

/*
UE.plugins["text"] = function() {
	var me = this;
	me.commands['text'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function()
		{
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/text.html?_=' + Math.random();
			var dialog = UE.ui.Dialog({
				iframeUrl:sUrl,
				editor:editor,
				className:'edui-for-text',
				title:'单行输入框',
				buttons:[{
					className:'edui-okbutton',
					label:'确认',
					onclick: function() {
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function() {
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['textDialog'] = dialog;
			dialog.open();
		}
	}

	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content:'',
		className:'edui-bubble',
		_edittext:function() {
			baidu.editor.plugins['text'].editdom = popup.anchorEl;
			me.execCommand('text');
			this.hide();
		},
		_delete:function() {
			if(window.confirm('确认删除该控件吗？')) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(cmd, evt)
	{
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'text') {
			var html = popup.formatHtml('<nobr>单行输入框: <span onclick="$$._edittext()" class="edui-clickable">编辑</span>&nbsp;<span onclick="$$._delete()" class="edui-clickable">删除</span></nobr>');
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	});
};

UE.plugins["textarea"] = function() {
	var me = this;
	me.commands['textarea'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/textarea.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl:sUrl,
				editor:editor,
				className:'edui-for-textarea',
				title:'多行输入框',
				buttons:[{
					className:'edui-okbutton',
					label:'确认',
					onclick:function() {
						dialog.close(true);
					}
				}, {
					className:'edui-cancelbutton',
					label:'取消',
					onclick:function() {
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['textareaDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content:'',
		className:'edui-bubble',
		_edittext:function() {
			  baidu.editor.plugins['textarea'].editdom = popup.anchorEl;
			  me.execCommand('textarea');
			  this.hide();
		},
		_delete:function() {
			if(window.confirm('确认删除该控件吗？'))
			{
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	});
	popup.render();
	me.addListener('mouseover',function(t, evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'textarea')
		{
			var html = popup.formatHtml('<nobr>多行输入框: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	});
};

UE.plugins["listmenu"] = function() {
	var me = this;
	me.commands['listmenu'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/listmenu.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor:editor,
				className:'edui-for-listmenu',
				title:'下拉菜单',
				buttons:[{
					className: 'edui-okbutton',
					label:'确认',
					onclick:function() {
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['listmenuDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function() {
			  baidu.editor.plugins['listmenu'].editdom = popup.anchorEl;
			  me.execCommand('listmenu');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？')) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover', function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'select') {
			var html = popup.formatHtml('<nobr>下拉菜单: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	} );
};
UE.plugins["radio"] = function() {
	var me = this;
	me.commands['radio'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/radio.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-radio',
				title: '单选框',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['radioDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['radio'].editdom = popup.anchorEl;
			  me.execCommand('radio');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'radio') {
			var html = popup.formatHtml(
				'<nobr>单选框: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	} );
};
UE.plugins["checkbox"] = function() {
	var me = this;
	me.commands['checkbox'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/checkbox.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-checkbox',
				title: '复选框',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['checkboxDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['checkbox'].editdom = popup.anchorEl;
			  me.execCommand('checkbox');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function( t,evt ) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'checkbox') {
			var html = popup.formatHtml(
				'<nobr>复选框: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	} );
};
UE.plugins["listview"] = function() {
	var me = this;
	me.commands['listview'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function () {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/listview.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-listview',
				title: '列表控件',
				buttons: [{
						className: 'edui-helpbutton',
						label: '控件说明',
						onclick: function (){
							return dialog.onhelp();
						}
					},{
						className: 'edui-okbutton',
						label: '确认',
						onclick: function (){
							dialog.close(true);
						}
					}, {
						className: 'edui-cancelbutton',
						label: '取消',
						onclick: function (){
							dialog.close(false);
						}
					}]
			});
			dialog.render();
			editor.ui._dialogs['listviewDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['listview'].editdom = popup.anchorEl;
			  me.execCommand('listview');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'listview') {
			var html = popup.formatHtml(
				'<nobr>列表控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			}else {
				popup.hide();
			}
		}
	} );
};
UE.plugins["auto"] = function() {
	var me = this;
	me.commands['auto'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/auto.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-auto',
				title: '宏控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['autoDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['auto'].editdom = popup.anchorEl;
			  me.execCommand('auto');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？')) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover', function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'auto') {
			var html = popup.formatHtml('<nobr>宏控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			}else {
				popup.hide();
			}
		}
	} );
};
UE.plugins["calendar"] = function() {
	var me = this;
	me.commands['calendar'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/calendar.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-calendar',
				title: '日历控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['calendarDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['calendar'].editdom = popup.anchorEl;
			  me.execCommand('calendar');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'date') {
			var html = popup.formatHtml('<nobr>日历控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			}else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["calc"] = function() {
	var me = this;
	me.commands['calc'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/calc.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-calc',
				title: '计算控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['calcDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['calc'].editdom = popup.anchorEl;
			  me.execCommand('calc');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'calc') {
			var html = popup.formatHtml('<nobr>计算控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			}else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["user"] = function() {
	var me = this;
	me.commands['user'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/user.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-user',
				title: '部门人员控件',
				buttons:[{
					className:'edui-okbutton',
					label:'确认',
					onclick:function() {
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function() {
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['userDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup({
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['user'].editdom = popup.anchorEl;
			  me.execCommand('user');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'user') {
			var html = popup.formatHtml('<nobr>部门人员控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			}else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["sign"] = function() {
	var me = this;
	me.commands['sign'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/sign.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-sign',
				title: '签章控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['signDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['sign'].editdom = popup.anchorEl;
			  me.execCommand('sign');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？')) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener('mouseover',function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'sign') {
			var html = popup.formatHtml('<nobr>签章控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if (html) {
				popup.getDom('content').innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor(popup.anchorEl);
			} else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["data_select"] = function() {
	var me = this;
	me.commands['data_select'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/data_select.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-data-select',
				title: '数据选择控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['dataselectDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['data_select'].editdom = popup.anchorEl;
			  me.execCommand('data_select');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'data') {
			var html = popup.formatHtml(
				'<nobr>数据选择控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			} else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["data_fetch"] = function() {
	var me = this;
	me.commands['data_fetch'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/data_fetch.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-data-select',
				title: '表单数据控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['datafetchDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['data_fetch'].editdom = popup.anchorEl;
			  me.execCommand('data_fetch');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function(t,evt) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'fetch') {
			var html = popup.formatHtml('<nobr>表单数据控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			} else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["progressbar"] = function() {
	var me = this;
	me.commands['progressbar'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/progressbar.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-progressbar',
				title: '进度条控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['progressbarDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['progressbar'].editdom = popup.anchorEl;
			  me.execCommand('progressbar');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function( t,evt ) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'progressbar') {
			var html = popup.formatHtml(
				'<nobr>进度条控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			} else {
				popup.hide();
			}
		}
	} );
}
UE.plugins["imgupload"] = function() {
	var me = this;
	me.commands['imgupload'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/imgupload.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-imgupload',
				title: '图片上传控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['imguploadDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['imgupload'].editdom = popup.anchorEl;
			  me.execCommand('imgupload');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function( t,evt ) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'imgupload') {
			var html = popup.formatHtml(
				'<nobr>图片上传控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			} else {
				popup.hide();
			}
		}
	} );
}

UE.plugins["qrcode"] = function() {
	var me = this;
	me.commands['qrcode'] = {
		queryCommandState:function () {
			return 0;
		},
		execCommand:function ( ) {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/qrcode.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-qrcode',
				title: '二维码控件',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['qrcodeDialog'] = dialog;
			dialog.open();
		}
	}
	
	var popup = new baidu.editor.ui.Popup( {
		editor:editor,
		content: '',
		className: 'edui-bubble',
		_edittext: function () {
			  baidu.editor.plugins['qrcode'].editdom = popup.anchorEl;
			  me.execCommand('qrcode');
			  this.hide();
		},
		_delete:function(){
			if( window.confirm('确认删除该控件吗？') ) {
				baidu.editor.dom.domUtils.remove(this.anchorEl,false);
			}
			this.hide();
		}
	} );
	popup.render();
	me.addListener( 'mouseover', function( t,evt ) {
		evt = evt || window.event;
		var el = evt.target || evt.srcElement;
		if (el.getAttribute('class') == 'qrcode') {
			var html = popup.formatHtml(
				'<nobr>二维码控件: <span onclick=$$._edittext() class="edui-clickable">编辑</span>&nbsp;<span onclick=$$._delete() class="edui-clickable">删除</span></nobr>' );
			if ( html ) {
				popup.getDom( 'content' ).innerHTML = html;
				popup.anchorEl = el;
				popup.showAnchor( popup.anchorEl );
			} else {
				popup.hide();
			}
		}
	} );
}


UE.plugins["jsext"] = function() {
	var me = this;
	me.commands['jsext'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/jsext.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-ext',
				title: 'JS脚本扩展',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick:function() {
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['jsextDialog'] = dialog;
			dialog.open();
		}
	}
}

UE.plugins['cssext'] = function() {
    var me = this;
	me.commands['cssext'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/cssext.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-ext',
				title: 'CSS样式扩展',
				buttons: [{
					className: 'edui-okbutton',
					label: '确认',
					onclick: function (){
						dialog.close(true);
					}
				}, {
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function (){
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['cssextDialog'] = dialog;
			dialog.open();
		}
	}
};

UE.plugins['macro'] = function() {
    var me = this;
	me.commands['macro'] = {
		queryCommandState:function() {
			return 0;
		},
		execCommand:function() {
			var sUrl = this.options.UEDITOR_HOME_URL + 'dialogs/workflow/macro.html?_=' + Math.random();
			var dialog = new baidu.editor.ui.Dialog({
				iframeUrl: sUrl,
				editor: editor,
				className: 'edui-for-ext',
				title: '宏标记',
				buttons: [{
					className: 'edui-cancelbutton',
					label: '取消',
					onclick: function() {
						dialog.close(false);
					}
				}]
			});
			dialog.render();
			editor.ui._dialogs['macroDialog'] = dialog;
			dialog.open();
		}
	}
};
*/