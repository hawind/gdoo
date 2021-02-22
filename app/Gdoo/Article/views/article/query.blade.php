<form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">
    <div class="pull-right">
        @if(isset($access['delete']))
            <a class="btn btn-sm btn-danger" href="javascript:optionDelete('#myform','{{url('delete')}}');"><i class="icon icon-remove"></i> 删除</a>
        @endif
    </div>
    @if(isset($access['create']))
        <a href="{{url('create')}}" class="btn btn-sm btn-info"><i class="icon icon-plus"></i> 新建</a>
    @endif
    @include('searchForm')
</form>

<script type="text/javascript">

$(function() {
    $('#search-form').searchForm({
        data: {{json_encode($search['forms'])}},
        init: function(e) {
            var self = this;
        }
    });
});
</script>