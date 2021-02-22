<form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">

    @if(isset($access['add']))
        <a href="{{url('add')}}" class="btn btn-sm btn-info"><i class="fa fa-plus"></i> 添加项目</a>
    @endif

    @include('searchForm')

</form>

<script type="text/javascript">
$(function() {
    $('#search-form').searchForm({
        data: {{json_encode($search['forms'])}},
        init:function(e) {
            var self = this;
        }
    });
});
</script>