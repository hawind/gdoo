(function() {
	$.fn.Paging = function(settings) {
		var arr = [];
		$(this).each(function() {
			var options = $.extend({
				target: $(this)
			}, settings);
			var lz = new Paging();
			lz.init(options);
			arr.push(lz);
		});
		return arr;
	};

	function Paging() {
		var rnd = Math.random().toString().replace('.', '');
		this.id = 'paging_' + rnd;
	}
	Paging.prototype = {
		init: function(settings) {
			this.settings = $.extend({
				callback: null,
				pagesize: 10,
				current: 1,
				prevTpl: "上一页",
				nextTpl: "下一页",
				firstTpl: "首页",
				lastTpl: "末页",
				ellipseTpl: "...",
				toolbar: true,
				hash: false,
				pageSizeList: [5, 10, 15, 20]
			}, settings);
			this.target = $(this.settings.target);
			this.container = $('<div id="' + this.id + '" class="ui-paging-container" /><div class="clearfix"></div>');
			this.target.append(this.container);
			this.render(this.settings);
			this.format();
			this.bindEvent();
		},
		render: function(ops) {
			this.count = ops.count || this.settings.count;
			this.pagesize = ops.pagesize || this.settings.pagesize;
			this.current = ops.current || this.settings.current;
			this.pagecount = Math.ceil(this.count / this.pagesize);
            if (ops.count === 0) {
                this.count = 0;
                this.pagecount = 0;
                this.current = 0;
            }
			this.format();
		},
		bindEvent: function() {
			var me = this;
			this.container.on('click', 'li.js-page-action, li.ui-pager', function(e) {
				if ($(this).hasClass('ui-pager-disabled') || $(this).hasClass('focus')) {
					return false;
				}
				if ($(this).hasClass('js-page-action')) {
					if ($(this).hasClass('js-page-first')) {
						me.current = 1;
					}
					if ($(this).hasClass('js-page-prev')) {
						me.current = Math.max(1, me.current - 1);
					}
					if ($(this).hasClass('js-page-next')) {
						me.current = Math.min(me.pagecount, me.current + 1);
					}
					if ($(this).hasClass('js-page-last')) {
						me.current = me.pagecount;
					}
				} else if ($(this).data('page')) {
					me.current = parseInt($(this).data('page'));
				}
				me.go();
			});
		},
		go: function(p) {
			var me = this;
			this.current = p || this.current;
			this.current = Math.max(1, me.current);
			this.current = Math.min(this.current, me.pagecount);
			this.format();
			if(this.settings.hash) {
				Query.setHash({
					page:this.current
				});
			}
			this.settings.callback && this.settings.callback(this.current, this.pagesize, this.pagecount);
		},
		changePagesize: function(ps) {
			this.render({
				pagesize: ps
			});
            this.settings.callback && this.settings.callback(this.current, this.pagesize, this.pagecount);
		},
		format: function() {
			var html = '<ul>'
			html += '<li class="js-page-first js-page-action ui-pager">' + this.settings.firstTpl + '</li>';
			html += '<li class="js-page-prev js-page-action ui-pager">' + this.settings.prevTpl + '</li>';
			if (this.pagecount > 3) {
				// html += '<li data-page="1" class="ui-pager">1</li>';
				if (this.current <= 1) {
                    html += '<li data-page="1" class="ui-pager">1</li>';
					html += '<li data-page="2" class="ui-pager">2</li>';
					html += '<li data-page="3" class="ui-pager">3</li>';
					// html += '<li class="ui-paging-ellipse">' + this.settings.ellipseTpl + '</li>';
				} else if (this.current > 1 && this.current <= this.pagecount - 1) {
					// html += '<li>' + this.settings.ellipseTpl + '</li>';
					html += '<li data-page="' + (this.current - 1) + '" class="ui-pager">' + (this.current - 1) + '</li>';
					html += '<li data-page="' + this.current + '" class="ui-pager">' + this.current + '</li>';
					html += '<li data-page="' + (this.current + 1) + '" class="ui-pager">' + (this.current + 1) + '</li>';
					// html += '<li class="ui-paging-ellipse" class="ui-pager">' + this.settings.ellipseTpl + '</li>';
				} else {
					//html += '<li class="ui-paging-ellipse" >' + this.settings.ellipseTpl + '</li>';
					for (var i = this.pagecount - 2; i < this.pagecount + 1; i++) {
						html += '<li data-page="' + i + '" class="ui-pager">' + i + '</li>'
					}
				}
				// html += '<li data-page="' + this.pagecount + '" class="ui-pager">' + this.pagecount + '</li>';
			} else {
				for (var i = 1; i <= this.pagecount; i++) {
					html += '<li data-page="' + i + '" class="ui-pager">' + i + '</li>'
				}
			}
			html += '<li class="js-page-next js-page-action ui-pager">' + this.settings.nextTpl + '</li>';
			html += '<li class="js-page-last js-page-action ui-pager">' + this.settings.lastTpl + '</li>';
			html += '</ul>';
            html += '<div class="js-page-total">共' + this.count + '条记录 '+ this.current +'/' + this.pagecount + '页</div>';

			$(this.container[0]).html(html);
			if (this.current == 0 || this.current == 1) {
				$('.js-page-prev', this.container).addClass('ui-pager-disabled');
				$('.js-page-first', this.container).addClass('ui-pager-disabled');
			}
			if (this.current == this.pagecount) {
				$('.js-page-next', this.container).addClass('ui-pager-disabled');
				$('.js-page-last', this.container).addClass('ui-pager-disabled');
			}
			this.container.find('li[data-page="' + this.current + '"]').addClass('focus').siblings().removeClass('focus');
			if (this.settings.toolbar) {
				this.bindToolbar();
			}
		},
		bindToolbar: function() {
			var me = this;
			var html = $('<li class="ui-paging-toolbar"><select class="ui-select-pagesize form-control input-sm input-inline"></select><input type="text" class="form-control input-sm input-inline ui-paging-count"/><a href="javascript:;">跳转</a></li>');
			var sel = $('.ui-select-pagesize', html);
			var str = '';
			for (var i = 0, l = this.settings.pageSizeList.length; i < l; i++) {
				str += '<option value="' + this.settings.pageSizeList[i] + '">' + this.settings.pageSizeList[i] + '条/页</option>';
			}
			sel.html(str);
			sel.val(this.pagesize);
			$('input', html).val(this.current);
			$('input', html).click(function() {
				$(this).select();
			}).keydown(function(e) {
				if (e.keyCode == 13) {
					var current = parseInt($(this).val()) || 1;
					me.go(current);
				}
			});
			$('a', html).click(function() {
				var current = parseInt($(this).prev().val()) || 1;
				me.go(current);
			});
			sel.change(function() {
				me.changePagesize($(this).val());
			});
			this.container.children('ul').append(html);
		}
	}
	return Paging;
})();