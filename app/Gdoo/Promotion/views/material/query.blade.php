<form id="search-form" class="form-inline" name="mysearch" action="{{url()}}" method="get">

    <div class="pull-right">
        @if(isset($access['delete']))
        <a class="btn btn-sm btn-danger" href="javascript:optionDelete('#myform','{{url('delete')}}');"><i class="icon icon-remove"></i> 删除</a>
        @endif
    </div>
    <!--
    @if(isset($access['audit']))
        @if($search['query']['status'] == 1)
        <a class="btn btn-sm btn-info" href="javascript:optionDelete('#myform','{{url('audit')}}', '确定要反审核吗');"><i class="icon icon-ban-circle"></i> 反审核</a>
        @else
        <a class="btn btn-sm btn-info" href="javascript:optionDelete('#myform','{{url('audit')}}', '确定要审核吗');"><i class="icon icon-ok"></i> 审核</a>
        @endif
    @endif
    -->
    @include('searchForm')

</form>

<script type="text/javascript">
$(function() {
    $('#search-form').searchForm({
        data: {{json_encode($search['forms'])}},
        init: function(e) {
            var me = this;
        }
    });
});
</script>