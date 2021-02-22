<table id="widget-file-file">
    <thead>
    <tr>
        <th data-field="title" data-align="left">标题</th>
        <th data-field="date" data-width="160" data-align="center">日期</th>
        <th data-field="option" data-width="200" data-align="center">下载</th>
    </tr>
    </thead>
</table>

<script>
(function($) {
    var $table = $('#widget-file-file');
    $table.bootstrapTable({
        sidePagination: 'server',
        showColumns: false,
        showHeader: false,
        height: 200,
        pagination: false,
        url: '{{url("file/widget/index")}}',
    });

})(jQuery);
</script>