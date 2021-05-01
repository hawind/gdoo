<ul class="nav nav-tabs padder m-t" id="api-dialog">
    <li class="active"><a href="#modal-department" data-toggle="tab">部门</a></li>
    <li><a href="#modal-role" data-toggle="tab">角色</a></li>
    <li><a href="#modal-user" data-toggle="tab">用户</a></li>
    <li><a href="#modal-customer" data-toggle="tab">客户</a></li>
</ul>

<div id="tab-content"></div>

<script>
(function($) {
    var params = JSON.parse('{{json_encode($gets)}}');

    var option = gdoo.formKey(params);
    var doc = getIframeDocument(params.iframe_id);
    if (doc) {
        var $option_id = $('#' + option.id, doc);
        var $option_text = $('#'+option.id + '_text', doc);
    } else {
        var $option_id = $('#' + option.id);
        var $option_text = $('#' + option.id + '_text');
    }
    var id = $option_id.val();
    var text = $option_text.val();
    var res = {};
    if (id) {
        var ids = id.split(',');
        var texts = text.split(',');
        for (var i = 0; i < ids.length; i++) {
            res[ids[i]] = texts[i];
        }
    }
    dialogCacheSelected[option.id] = res;

    var routes = {
        '#modal-user': 'user/user/dialog',
        '#modal-role': 'user/role/dialog',
        '#modal-department': 'user/department/dialog',
        '#modal-customer': 'customer/customer/dialog',
        '#modal-customer-contact': 'customer/contact/dialog',
        '#modal-supplier': 'supplier/supplier/dialog',
        '#modal-supplier-contact': 'supplier/contact/dialog'
    };

    function loadData(target) {
        params['prefix'] = 1;
        params['is_org'] = 1;
        $.get(app.url(routes[target], params), function(html) {
            $('#tab-content').html(html);
        });
    }

    loadData('#modal-department');
    $('#api-dialog a[data-toggle=tab]').click(function() {
        var target = $(this).attr('href');
        loadData(target);
    });
})(jQuery);
</script>