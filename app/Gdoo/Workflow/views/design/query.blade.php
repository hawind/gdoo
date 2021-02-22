<form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">

    @if(isset($access['add']))
        <a href="{{url('add')}}" class="btn btn-sm btn-info"><i class="icon icon-plus"></i> 新建</a>
    @endif

    @include('searchForm')

</form>
<script type="text/javascript">
$(function() {
    $('#search-form').searchForm({
        data:{{json_encode($search['forms'])}},
        init:function(e) {
            var self = this;
            e.category = function(i) {
                var rows = [];
                var categorys = {{json_encode($categorys)}};
                $.map(categorys, function(category) {
                    rows.push({id:category.id,name:category.title});
                });
                self._select(rows, i);
            }
        }
    });
});
</script>