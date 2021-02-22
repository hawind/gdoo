(function(window) {

	var calc = {
		/**
		 * 人民币计算大写
		 * @param {type} currencyDigits
		 * @returns {String}
		 */
		'rmb': function(currencyDigits) {
			// Constants:
			var MAXIMUM_NUMBER = 99999999999.99;
			// Predefine the radix characters and currency symbols for output:
			var YUANCAPITAL = {
				ZERO: "零",
				ONE: "壹",
				TWO: "贰",
				THREE: "叁",
				FOUR: "肆",
				FIVE: "伍",
				SIX: "陆",
				SEVEN: "柒",
				EIGHT: "捌",
				NINE: "玖",
				TEN: "拾",
				HUNDRED: "佰",
				THOUSAND: "仟",
				TEN_THOUSAND: "万",
				HUNDRED_MILLION: "亿",
				DOLLAR: "元",
				TEN_CENT: "角",
				CENT: "分",
				INTEGER: "整"
			};

			var CN_ZERO = YUANCAPITAL.ZERO;
			var CN_ONE = YUANCAPITAL.ONE;
			var CN_TWO = YUANCAPITAL.TWO;
			var CN_THREE = YUANCAPITAL.THREE;
			var CN_FOUR = YUANCAPITAL.FOUR;
			var CN_FIVE = YUANCAPITAL.FIVE;
			var CN_SIX = YUANCAPITAL.SIX;
			var CN_SEVEN = YUANCAPITAL.SEVEN;
			var CN_EIGHT = YUANCAPITAL.EIGHT;
			var CN_NINE = YUANCAPITAL.NINE;
			var CN_TEN = YUANCAPITAL.TEN;
			var CN_HUNDRED = YUANCAPITAL.HUNDRED;
			var CN_THOUSAND = YUANCAPITAL.THOUSAND;
			var CN_TEN_THOUSAND = YUANCAPITAL.TEN_THOUSAND;
			var CN_HUNDRED_MILLION = YUANCAPITAL.HUNDRED_MILLION;
			var CN_DOLLAR = YUANCAPITAL.DOLLAR;
			var CN_TEN_CENT = YUANCAPITAL.TEN_CENT;
			var CN_CENT = YUANCAPITAL.CENT;
			var CN_INTEGER = YUANCAPITAL.INTEGER;
			// Variables:
			var integral; // Represent integral part of digit number.
			var decimal; // Represent decimal part of digit number.
			var outputCharacters; // The output result.
			var parts;
			var digits, radices, bigRadices, decimals;
			var zeroCount;
			var i, p, d;
			var quotient, modulus;
			// Validate input string:
			currencyDigits = currencyDigits.toString();
			if (currencyDigits == "") {
				return "";
			}
			if (currencyDigits.match(/[^,.\d]/) != null) {
				return "";
			}
			if ((currencyDigits).match(/^((\d{1,3}(,\d{3})*(.((\d{3},)*\d{1,3}))?)|(\d+(.\d+)?))$/) == null) {
				return "";
			}
			// Normalize the format of input digits:
			currencyDigits = currencyDigits.replace(/,/g, ""); // Remove comma delimiters.
			currencyDigits = currencyDigits.replace(/^0+/, ""); // Trim zeros at the beginning.
			// Assert the number is not greater than the maximum number.
			if (Number(currencyDigits) > MAXIMUM_NUMBER) {
				return "";
			}
			// Process the coversion from currency digits to characters:
			// Separate integral and decimal parts before processing coversion:
			parts = currencyDigits.split(".");
			if (parts.length > 1) {
				integral = parts[0];
				decimal = parts[1];
				// Cut down redundant decimal digits that are after the second.
				decimal = decimal.substr(0, 2);
			}
			else {
				integral = parts[0];
				decimal = "";
			}
			// Prepare the characters corresponding to the digits:
			digits = new Array(CN_ZERO, CN_ONE, CN_TWO, CN_THREE, CN_FOUR, CN_FIVE, CN_SIX, CN_SEVEN, CN_EIGHT, CN_NINE);
			radices = new Array("", CN_TEN, CN_HUNDRED, CN_THOUSAND);
			bigRadices = new Array("", CN_TEN_THOUSAND, CN_HUNDRED_MILLION);
			decimals = new Array(CN_TEN_CENT, CN_CENT);
			// Start processing:
			outputCharacters = "";
			// Process integral part if it is larger than 0:
			if (Number(integral) > 0) {
				zeroCount = 0;
				for (i = 0; i < integral.length; i++) {
					p = integral.length - i - 1;
					d = integral.substr(i, 1);
					quotient = p / 4;
					modulus = p % 4;
					if (d == "0") {
						zeroCount++;
					}
					else {
						if (zeroCount > 0) {
							outputCharacters += digits[0];
						}
						zeroCount = 0;
						outputCharacters += digits[Number(d)] + radices[modulus];
					}
					if (modulus == 0 && zeroCount < 4) {
						outputCharacters += bigRadices[quotient];
					}
				}
				outputCharacters += CN_DOLLAR;
			}
			// Process decimal part if there is:
			if (decimal != "") {
				for (i = 0; i < decimal.length; i++) {
					d = decimal.substr(i, 1);
					if (d != "0") {
						outputCharacters += digits[Number(d)] + decimals[i];
					}
				}
			}
			// Confirm and return the final output string:
			if (outputCharacters == "") {
				outputCharacters = CN_ZERO + CN_DOLLAR;
			}
			if (decimal == "") {
				outputCharacters += CN_INTEGER;
			}
			//outputCharacters = CN_SYMBOL + outputCharacters;
			return outputCharacters;
		},
		/**
		 * 最大值
		 * @returns {unresolved}
		 */
		'max': function() {
			if (arguments.length == 0) {
				return;
			}
			var maxNum = arguments[0];
			for (var i = 0; i < arguments.length; i++) {
				maxNum = Math.max(maxNum, arguments[i]);
			}
			return parseFloat(maxNum);
		},
		/**
		 * 最小值
		 * @returns {unresolved}
		 */
		'min': function() {
			if (arguments.length == 0) {
				return;
			}
			var minNum = arguments[0];
			for (var i = 0; i < arguments.length; i++) {
				minNum = Math.min(minNum, arguments[i]);
			}
			return parseFloat(minNum);
		},		
		/**
		 * 平均值
		 * @returns {unresolved}
		 */
		'avg': function() {
			var args = arguments,
				len = args.length,
				i = 0,
				sum = 0;

			for(; i < len && (sum += parseFloat(args[i])); i++ ){
			}
			return sum / len;
		},
		/**
		 * 取模运算
		 * @returns {String}
		 */
		'mod': function() {
			if (arguments.length == 0) {
				return;
			}
			var firstNum = arguments[0];
			var secondNum = arguments[1];
			var result = firstNum % secondNum;
			result = isNaN(result) ? "" : parseFloat(result);
			return result;
		},
		/**
		 * 绝对值
		 * @param {type} val
		 * @returns {@exp;Math@call;abs}
		 */
		'abs': function(val) {
			return Math.abs(parseFloat(val));
		},
		/**
		 * 获取值
		 * @param {type} val
		 * @returns {@exp;Math@call;floor|Number}
		 */
		'val': function(val, prec) {
			return (isNaN(val) || val === Infinity) ? 0 : val.toFixed(prec);
		},
		/**
		 * 天数计算
		 * @param {type} val
		 * @returns {Number|@exp;Math@call;floor}
		 */
		'day': function(val) {
			return val == 0 ? 0 : Math.floor(val / 86400);
		},
		/**
		 * 小时
		 * @param {type} val
		 * @returns {@exp;Math@call;floor|Number}
		 */
		'hour': function(val) {
			return val == 0 ? 0 : Math.floor(val / 3600);
		},
		/**
		 * 日期计算
		 * @param {type} val
		 * @returns {String}
		 */
		'date': function(val) {
			var TIME = {
				YEAR: "年",
				HALFYEAR: "半年",
				QUARTER: "季",
				MONTH: "月",
				WEEK: "周",
				DAY: "天",
				HOUR: "小时",
				MIN: "分",
				MINS: "分钟",
				SEC: "秒",
				SECS: "秒钟",
				INVALID_DATE: "日期格式无效",
				WEEKS: "星期",
				WEEKDAYS: "日一二三四五六"
			};
			return (val >= 0) ? Math.floor(val / 86400) + TIME.DAY + Math.floor((val % 86400) / 3600) + TIME.HOUR + Math.floor((val % 3600) / 60) + TIME.MIN + Math.floor(val % 60) + TIME.SEC : TIME.INVALID_DATE; //'日期格式无效'
		},
		/**
		 * 列表控件计算
		 * @param {type} olist
		 * @param {type} col
		 * @returns {Number}
		 */
		'list': function(table_id, col) {

			var output = 0;
			var tbody = document.getElementById('body_' + table_id);

			for (var i = 0; i < tbody.rows.length; i++) {

				for (var j = 0; j < tbody.rows[i].cells.length; j++) {

					if (j == col) {

						var child = tbody.rows[i].cells[j].firstChild;

						if (child && child.tagName) {

							var val = child.value || child.innerText;

							val = (val == "" || val.replace(/\s/g, '') == "") ? 0 : val;
							val = (isNaN(val)) ? NaN : val;
							output += parseFloat(val);
						} else {
							output += parseFloat(child.data);
						}
					}
				}
			}
			return parseFloat(output);
		},
		/**
		 * 计算控件获取item的值
		 * @param {type} $item
		 * @returns {@exp;d@call;getTime|Number|@exp;document@call;getElementById}
		 */
		'getVal': function(id, type) {
			var $item = $('#' + id);
			if ($item.length == 0) {
				return 0;
			}
			// $item.data('flag')
			if (type == 'listview') {
				return document.getElementById('lv_' + id);
			} else if (type == 'date') {
				var val = $('#' + id).val();
				//var val = $item.parent().data("datetimepicker").getDate();
				var d = new Date(val);
				return d.getTime() / 1000;
			} else {
				var val = $item.val();
				if (val == "") {
					val = 0;
				}
				return val;
			}
		},
		/**
		  数字合计
		 */
		sum: function() {
            var args = [].slice.call(arguments, 0),
                len, item, sum = 0;

            for (len = args.length; len--;) {
                item = parseFloat(args[len]);
                // if item=>NaN
                sum += (item === item ? item : 0);
            }
            return sum;
        }
	}

    var listView = {
		calc: calc,
    	field: {},
        data: {},
        total: {},
     	editor: function(key, i, j) {
    		var field = listView.field[key];
    		var type = field.type[j];
    		var size = field.size[j];

			var css = field.checks[j] == 'SYS_NOT_NULL' ? 'input-required' : 'input-text';
            var readonly = field.writes[j] == true ? false : true;

            var value = '';
    		if(listView.data[key][i])
    		{
    			value = listView.data[key][i][j] == undefined ? '' : listView.data[key][i][j];
    		}

    		var name = key+'['+i+']['+j+']';
    		var id = key+'_'+i+'_'+j;
    		switch(type)
    		{
    		    case "empty":
    		    	var out = '<span tyle="width:'+size+'px;" id="'+id+'"></span>';
                    break;
    		    case "text":
    		    	var out = readonly == 0 ? '<input autocomplete="off" type="text" style="width:'+size+'px;" class="'+css+'" name="'+name+'" id="'+id+'" value="'+value+'">' : '<span id="'+id+'">'+value+'</span>';
                    break;
    		    case "textarea":
    		    	var out = readonly == 0 ? '<textarea style="width:'+size+'px;" class="'+css+'" name="'+name+'" id="'+id+'">'+value+'</textarea>' : '<span id="'+id+'">'+value+'</span>';
                    break;                
    		    case "calc":
    		    	var out = readonly == 0 ? '<input type="text" style="width:'+size+'px;" class="readonly" name="'+name+'" id="'+id+'" value="'+value+'" readonly>' : '<span id="'+id+'">'+value+'</span>';
    		          break;

    		    case "select":
    		     	var option = field.value[j].split(',');
    		     	var r = [];
    		    	r.push('<select style=width:'+size+'px;" name="'+name+'" id="'+id+'">');
    		    	for (var i = 0; i < option.length; i++)
    		    	{
    		    		var selected = value == option[i] ? ' selected' : '';
    		    		r.push('<option value="'+option[i]+'"'+selected+'>'+option[i]+'</option>');
    		    	}
    		    	r.push('</select>');
    		    	out = readonly == 0 ? r.join("\n") : '<span id="'+id+'">'+value+'</span>';
    		    	break;

    		    case "radio":
    		     	var option = field.value[j].split(',');
    		    	var r = [];
    		    	for (var i = 0; i < option.length; i++)
    		    	{
    		    		var checked = value == option[i] ? ' checked' : '';
    		    		r.push('<label class="checkbox"><input type="radio" name="'+name+'" id="'+id+'"'+checked+'>'+option[i]+'</label>');
    		    	}
    		    	out = readonly == 0 ? r.join("\n") : '<span id="'+id+'">'+value+'</span>';
    		    break;

    		    case "checkbox":
    		     	var option = field.value[j].split(',');
    		    	var r = [];
    		    	for (var i = 0; i < option.length; i++)
    		    	{
    		    		var checked = value == option[i] ? ' checked' : '';
    		    		r.push('<label class="checkbox"><input type="checkbox" name="'+name+'" id="'+id+'"'+checked+'>'+option[i]+'</label>');
    		    	}
    		    	out = readonly == 0 ? r.join("\n") : '<span id="'+id+'">'+value+'</span>';
                    break;

    		  	case "datetime":
    		    	out = readonly == 0 ? '<input autocomplete="off" type="text" style=width:'+size+'px;" onfocus="datePicker({dateFmt:\'yyyy-MM-dd\'});" class="'+css+' popDate" value="'+value+'" name="'+name+'" id="'+id+'">' : '<span id="'+id+'">'+value+'</span>';
                    break;	    
    		}
    		return out;
    	},
    	rowUpdate: function(obj)
    	{
    		var e = obj.target.id.split('_');
    		if (e.length == 4)
    		{
    			var key = e[0]+'_'+e[1];
    			listView.rowSum(key, e[2]);
    			listView.footerSum(key);
    		};
    	},
    	footerSum: function(key)
    	{
    		var body = document.getElementById('body_' + key);
    		var field = listView.field[key];
            var sum = field.sum;

            var readonly = field.readonly;
    		for (var i = 0; i < sum.length; i++)
    		{
    			if(sum[i] == true)
    			{
    				var row = 0;
    				for (var j = 0; j < body.rows.length; j++)
    				{
    					var td = body.rows[j].cells[i+1];
                        var value = field.readonly == 0 ? $(td).find('input,select').val() : $(td).find('span').text();
    					value = parseFloat(value);
    					row += isNaN(value) ? 0 : Math.round(parseFloat(value)*10000)/10000;
    				}
    				row = row.toFixed(2);
    				$('#total_'+key+'_'+i).html(row);
    			}
    		}
    	},
    	rowSum: function(key, j) {
			
            var field = listView.field[key];
    		var type  = field.type;
    		var value = field.value;
            var readonly = field.readonly;
    		var calc = [];

    		for (var i = 0; i < type.length; i++) {

    			if(type[i] == 'calc') {

    				calc[i] = value[i];

    				for (var k = 0; k < type.length; k++) {
    					var n = k+1;
                        var td = $('#'+key+'_'+j+'_'+k);
                        var td_value = readonly== 0 ? td.val() : td.text();
                        calc[i] = calc[i].replace('['+n+']', parseFloat(td_value));
    					
    				}
    				row = isNaN(eval(calc[i])) ? 0 : Math.round(parseFloat(eval(calc[i]))*10000)/10000;
    				row = row.toFixed(2);
    				var obj = $('#'+key+'_'+j+'_'+i);
                    readonly== 0 ? obj.val(row) : obj.text(row);
    			}
    		}
    	},
    	rowAdd: function(key)
    	{
    		i = listView.total[key];
    		var tr = [];
    		tr.push('<tr><td align="center">'+(i+1)+'</td>');
    		for (var j = 0; j < listView.field[key].type.length; j++)
    		{
    			tr.push('<td>'+listView.editor(key,i,j)+'</td>');
    		}

            // 检查字段是否为只读
            if (listView.field[key].readonly == 0) {
                var option = i > 0 ? '<a class="option" href="javascript:;" onclick="listView.deleteRow(\''+key+'\',this);">删除</a>' : '<a class="option" onclick="listView.rowAdd(\''+key+'\');" href="javascript:;">添加</a>';
                tr.push('<td align="center" style="white-space:nowrap;">'+option+'</td></tr>');
            }

    		$('#body_'+key).append(tr.join("\n"));
    		// 累加行
    		listView.total[key]++;
    	},
    	deleteRow:function(key, obj) {
    		var tr = obj.parentNode.parentNode;
    	    tr.parentNode.removeChild(tr);
    	    // 总计列总数
    		listView.footerSum(key);
    	},
    	init:function(key) {
    		// 初始化列表视图
    		listView.total[key] = 0;

    		// 新建的时候默认显示一行
    		var length = listView.data[key].length > 0 ? listView.data[key].length : 1;

    		for (var i = 0; i < length; i++)
    		{
    			// 添加一行
    			listView.rowAdd(key);

    			// 计算小计行
    			listView.rowSum(key, i);

    		};

    		// 总计列总数
    		listView.footerSum(key);
    	}
    }

    window.listView = listView;

})(window);