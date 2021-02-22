<div class="wrapper-xs">
<form id="batch-edit-form" action="{{url()}}" method="post">
    <div class="form-group search-group m-b-xs">
        <select name="field" id="search-field-0" class="form-control input-sm">
            <option value=""> - </option>
            @foreach($header['search_form']['columns'] as $column)
            <option data-type="{{$column['form_type']}}" data-title="{{$column['name']}}" value="{{$column['field']}}">{{$column['name']}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" style="display:none;">
        <select name="condition_0" id="search-condition-0" class="form-control input-sm"></select>
    </div>
    <div class="form-group m-b-xs" id="search-value-0"></div>
    <input name="ids" type="hidden" value="{{$gets['ids']}}">
</form>
</div>
<script>
    var forms = JSON.parse('{{json_encode($header["search_form"]["forms"], JSON_UNESCAPED_UNICODE)}}');
    (function($) {
        var el = $('#batch-edit-form').searchForm({
            data: forms
        });
        el.find('#search-submit').on('click', function() {
            var query = el.serializeArray();
            var params = {};
            $.map(query, function(row) {
                params[row.name] = row.value;
            });
            config.grid.remoteData(params);
            return false;
        });
    })(jQuery);
</script>