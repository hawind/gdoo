<table id="widget-article-index">
    <thead>
    <tr>
        <th data-field="title" data-formatter="titleFormatter" data-align="left">标题</th>
        <th data-field="created_at" data-width="200" data-formatter="datetimeFormatter" data-sortable="true" data-align="center">发布时间</th>
    </tr>
    </thead>
</table>

<script>

function datetimeFormatter(value, row) {
    return format_datetime(value);
}

function titleFormatter(value, row) {
    return '<a href="'+app.url('article/article/view', {id: row.id})+'">' + value + '</a>';
}

(function($) {
    var $table = $('#widget-article-index');
    $table.bootstrapTable({
        sidePagination: 'server',
        showColumns: false,
        showHeader: false,
        height: 200,
        pagination: false,
        url: '{{url("article/widget/index")}}',
    });

})(jQuery);
</script>