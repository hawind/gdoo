<script>
(function($) {
    var table = '{{$header["master_table"]}}';
    var config = gdoo.grids[table];
    var search = config.search;
    search.advanced.el = $('#' + table + '-search-form-advanced').searchForm({
        data: search.forms,
        advanced: true,
        init: search.searchInit
    });

    search.simple.el = $('#' + table + '-search-form').searchForm({
        data: search.forms,
        init: search.searchInit
    });
    search.simple.el.find('#search-submit').on('click', function() {
        var query = search.simple.el.serializeArray();
        var params = {};
        search.queryType = 'simple';
        $.map(query, function(row) {
            params[row.name] = row.value;
        });
        params['page'] = 1;
        config.grid.remoteData(params);
        return false;
    });

    var panel = $('#' + table + '-controller');
    var action = config.action;
    panel.on('click', '[data-toggle="' + table + '"]', function() {
        var data = $(this).data();
        action[data.action]();
    });
})(jQuery);
</script>