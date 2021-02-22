(function ($, undefined) {

	var pluginName = 'agDropdownCellEditor',
		dataKey = 'ag.dropdown.celleditor';

	var defaults = {
		maxHeight: 200
	};

	// Utility functions
	var keys = {
		ESC: 27,
		TAB: 9,
		RETURN: 13,
		LEFT: 37,
		UP: 38,
		RIGHT: 39,
		DOWN: 40,
		ENTER: 13,
		SHIFT: 16
	}
	
	/**
	 * Constructor
	 * @param {[Node]} element [Select element]
	 * @param {[Object]} options [Option object]
	 */
	var Plugin = function (input, options) {
		this.grid = options.grid;
		this.config = options.config;
	 	this.items = options.data.items;
		this.selected = options.data.selected;
        this.hook = options.hook;

		this.arrow = options.arrow || 'icon-search';

		this.name = options.name;

		this.onSelect = options.select || function() {};

		this.$input = $(input);

		// Settings
		this.settings = $.extend({}, defaults, options);

		// Initialize
		this.init();

		$.fn[pluginName].instances.push(this);

	};

	$.extend(Plugin.prototype, {
		init: function () {
			// Construct the comboselect
			this._construct();

			// Add event bindings
			this._events();
		},
		_construct: function () {

			var me = this;

			// Wrap the Select
			this.$container = $('<div class="combo-select combo-open combo-'+ me.name +'" />');

			// Append dropdown arrow
			this.$arrow = $('<div class="combo-arrow"><i class="fa '+ this.arrow + '"></i></div>');

			// Append dropdown
			this.$dropdown = $('<ul class="combo-dropdown" />').appendTo(this.$container);

			// Create dropdown options
			this._build();

			this.$input.after(this.$arrow);

			$('body').append(this.$container);

			var height = this.$input.outerHeight();
			var width  = this.$input.outerWidth();
			var offset = this.$input.offset();
			this.$container.css({width: width + 2, left: offset.left - 1, top: offset.top + height + 1});
		},
		_build: function () {

			var me = this;

			var o = '', k = 0;

			o += '<li class="option-item-empty">无匹配选项</li>';

			$.each(this.items, function (i, e) {

				if (e == 'optgroup') {
					return o += '<li class="option-group">' + this.label + '</li>';
				}
				o += '<li class="' + (this.disabled ? 'option-disabled' : "option-item") + ' ' + (this.id == me.selected ? 'option-selected' : '') + '" data-index="' + (k) + '" data-value="' + this.id + '">' + (this.name) + '<i>' + this.code + '</i></li>';
				k++;
			})

			this.$dropdown.html(o)
			// Items
			this.$items = this.$dropdown.children();
		},

		_events: function () {
			var me = this;

			this.$arrow.off();
			this.$container.off();
			this.$input.off();
			
			// Dropdown Arrow: click
			this.$arrow.on('click.arrow', $.proxy(this._toggle, this));

			// Dropdown: close
			this.$container.on('dropdown:close', $.proxy(this._close, this));

			// Dropdown: open
			this.$container.on('dropdown:open', $.proxy(this._open, this));

			// Dropdown: update
			this.$container.on('dropdown:update', $.proxy(this._update, this));

			// Input: keydown
			this.$input.on('keydown', $.proxy(this._keydown, this));

			// Input: keyup
			this.$input.on('keyup', $.proxy(this._keyup, this));

			// Dropdown item: click
			this.$container.on('click.item', '.option-item', $.proxy(this._select, this));
		},
		_keydown: function (event) {

			switch (event.which) {
				case keys.ESC:
					this.$container.trigger('dropdown:close');
					break;

				case keys.UP:
					this._move('up', event);
                    event.stopPropagation();
					break;

				case keys.DOWN:
					this._move('down', event);
                    event.stopPropagation();
					break;

				case keys.TAB:
					this._enter(event);
					break;

				case keys.RIGHT:
					//this._autofill(event);
					break;

				case keys.ENTER:
					this._enter(event);
					break;

				default:
					break;
			}
		},

		_keyup: function (event) {

			switch (event.which) {
				
				case keys.ESC:
				case keys.ENTER:
				case keys.UP:
				case keys.DOWN:
				case keys.LEFT:
				case keys.RIGHT:
				case keys.TAB:
				case keys.SHIFT:
					break;

				default:
					this._filter(event.target.value);
					break;
			}
		},
		_enter: function (event) {
			var item = this._getHovered();
			this._select(item);
		},
		_move: function (dir, event) {
			var items = this._getVisible(),
				current = this._getHovered(),
				index = current.prevAll('.option-item').filter(':visible').length,
				total = items.length;

			switch (dir) {
				case 'up':
					index--;
					(index < 0) && (index = (total - 1));
					break;

				case 'down':
					index++;
					(index >= total) && (index = 0);
					break;
			}

			items.removeClass('option-hover')
				.eq(index)
				.addClass('option-hover');

			if (!this.opened) this.$container.trigger('dropdown:open');

			this._fixScroll();
		},

		_select: function (event) {

			var item = event.currentTarget ? $(event.currentTarget) : $(event);

			//if (!item.length) return;

			var index = item.data('index');
			this._selectByIndex(index);
			this.$container.trigger('dropdown:close');
		},

        /**
         * Set selected index and trigger change
         * @type {[type]}
         */
		_selectByIndex: function (index) {

			if (typeof index == 'undefined') {
				// 为空设置不选中
				index = -1;
			}

			this._getAll()
				.removeClass('option-selected')
				.filter(function() {
					return $(this).data('index') == index
				}).addClass('option-selected')

			this._change();
		},

		_autofill: function () {
			var item = this._getHovered();
			if (item.length) {
				var index = item.data('index');
				this._selectByIndex(index);
			}
		},
		_filter: function (search) {

			var self = this,
				items = this._getAll(),
				needle = $.trim(search).toLowerCase(),
				reEscape = new RegExp('(\\' + ['/', '.', '*', '+', '?', '|', '(', ')', '[', ']', '{', '}', '\\'].join('|\\') + ')', 'g'),
				pattern = '(' + search.replace(reEscape, '\\$1') + ')';

			// Unwrap all markers
			$('.combo-marker', items).contents().unwrap();
			// Search
			if (needle) {
				// Hide Disabled and optgroups
				this.$items.filter('.option-group, .option-disabled').hide();
				items
					.hide()
					.filter(function () {

						var $this = $(this),
							text = $.trim($this.text()).toLowerCase();

						// Found
						if (text.toString().indexOf(needle) != -1) {

							// Wrap the selection
							$this
								.html(function (index, oldhtml) {
									return oldhtml.replace(new RegExp(pattern, 'gi'), '<span class="combo-marker">$1</span>');
								});

							return true;
						}
					})
					.show();
			} else {
				items.show();
			}

			// Open the dropdown
			this.$container.trigger('dropdown:open');

			// 搜索结果为不存在时候显示
			if(this._getVisible().length == 0) {
				this.$items.filter('.option-item-empty').show();
			} else {
				this.$items.filter('.option-item-empty').hide();
			}
		},

		_highlight: function() {
			/*
			1. Check if there is a selected item
			2. Add hover class to it
			3. If not add hover class to first item
			*/
			var visible = this._getVisible().removeClass('option-hover'),
				$selected = visible.filter('.option-selected');
			
			if ($selected.length) {
				$selected.addClass('option-hover');
			} else {
				visible.removeClass('option-hover')
				.first()
				.addClass('option-hover');
			}
		},

		_updateInput: function() {
			var item = this._getAll().filter('.option-selected');
			var index = item.data('index');

			this.onSelect.call(this, this.items[index]);
			/*
			if (index) {
				this.onSelect.call(this, this.items[index]);
			} else {
				this.onSelect.call(this, this.items[index]);
			}*/
		},
		_focus: function (event) {
			// Toggle focus class
			this.$container.toggleClass('combo-focus', !this.opened);
			// Open combo
			if (!this.opened) this.$container.trigger('dropdown:open');
		},
		_change: function () {
			this._updateInput();
		},
		_getAll: function () {
			return this.$items.filter('.option-item');
		},
		_getVisible: function () {
			return this.$items.filter('.option-item').filter(':visible')
		},
		_getHovered: function () {
			return this._getVisible().filter('.option-hover');
		},
		_open: function () {
			var self = this
			this.$container.addClass('combo-open');
			this.$arrow.addClass('combo-arrow-open');
			this.opened = true;

			// Highligh the items
			this._highlight();

			// Fix scroll
			this._fixScroll();

			// Close all others
			$.each($.fn[pluginName].instances, function(i, plugin) {
				if (plugin != self && plugin.opened) plugin.$container.trigger('dropdown:close');
			})
		},

		_toggle: function(e) {
			this.opened ? this._close.call(this) : this._open.call(this);
			this.$input.focus();
			e.stopPropagation();
		},
		_close: function() {
			this.$container.removeClass('combo-open combo-focus');
			this.$arrow.removeClass('combo-arrow-open');
			this.$container.trigger('dropdown:closed');
			this.opened = false;
			// Show all items
			this.$items.filter('.option-item').show();
		},
		_fixScroll: function() {

			// If dropdown is hidden
			if (this.$dropdown.is(':hidden')) return;

			// Else
			var item = this._getHovered();

			if (!item.length) return;

			// Scroll
			var offsetTop,
				upperBound,
				lowerBound,
				heightDelta = item.outerHeight();

			offsetTop = item[0].offsetTop;
			upperBound = this.$dropdown.scrollTop();
			lowerBound = upperBound + this.settings.maxHeight - heightDelta;

			if (offsetTop < upperBound) {
				this.$dropdown.scrollTop(offsetTop);
			} else if (offsetTop > lowerBound) {
				this.$dropdown.scrollTop(offsetTop - this.settings.maxHeight + heightDelta);
			}
		},

		/**
		 * 更新
		 */
		_update: function() {
			this.$dropdown.empty();
			this._build();
		},

		/**
		 * 销毁
		 */
		dispose: function() {
			// 删除dom
			this.$arrow.remove();
			this.$input.remove();
			this.$dropdown.remove();
		}
	});

	$.fn[pluginName] = function(options, args) {

		this.each(function() {

			var $e = $(this),
				instance = $e.data('plugin_' + dataKey);

			if (typeof options === 'string') {
				if (instance && typeof instance[options] === 'function') {
					instance[options](args);
				}
			} else {
				if (instance && instance.dispose) {
					instance.dispose();
				}
				$.data(this, "plugin_" + dataKey, new Plugin(this, options));
			}
		});
		return this;
	};
	$.fn[pluginName].instances = [];

})(jQuery);