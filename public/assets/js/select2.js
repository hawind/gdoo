var select2List = {};

(function($) {

    $.fn.select2Field = function(options) {
        $this = $(this);
        var key = $this.attr('key');
        var event = gdoo.event.get(key);

        var defaults = {
            width: '100%',
            placeholder:' - ',
            allowClear: true,
            minimumInputLength: 0,
            // 不需要每次都获取数据
            resultCache: true,
            ajax: {
                type: 'POST',
                url: '',
                dataType: 'json',
                delay: 250,
                cache: false,
                data: function (params) {
                    var query = options.ajaxParams || {};
                    query.field_0 = options.search_key + '.name';
                    query.condition_0 = 'like';
                    query.search_0 = (params.term || '');
                    query.page = (params.page || 1);
                    query.resultCache = true;
                    event.trigger('query', select2, query);
                    return query;
                },
                processResults: function (res, params) {
                    return {
                        results: res.data,
                        pagination: {
                            more: res.current_page < res.last_page
                        }
                    };
                }
            },
            escapeMarkup: function(markup) {
                return markup;
            }, 
            templateResult: function(m) {
                return m.text;
            }, 
            // 函数用来渲染结果
            templateSelection: function(m) {
                return m.text;
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: 'draft_' + term,
                    text: term
                }
            },
            initSelection: function(element, callback) {
                var data = {id: element.val(), text: element.text()};
                callback(data);
            }
        };
        
        options = $.extend(true, {}, defaults, options);

        event.trigger('init', options);

        var select2 = $this.select2(options);

        select2.on('select2:select', function(e) {
            event.trigger('onSelect', e.params.data);
        });

        select2.on('select2:opening', function() {
        });

        select2.on('select2:open', function() {
            //select2.select2();
            /*
            var data = $(this).data('select2');
            $('.select2-link').remove();
            data.$results.parents('.select2-results')
            .append('<div class="select2-link"><a> <i class="fa fa-plus-square"></i> 更多</a></div>')
            .on('click', function () {
                data.trigger('close');
            });
            */
        });
        
        return select2;
    }
})(jQuery);