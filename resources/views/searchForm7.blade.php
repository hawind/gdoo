<div style="display:none;">
    <form id="{{$search['table']}}-search-form-advanced" class="search-form" action="{{url()}}" method="get">
        <div class="search-form-advanced">
            <div class="row">
                @foreach($search['columns'] as $i => $column)
                    <?php if($column['form_type'] == 'text2') { continue; } ?>
                    <div class="wrapper-xs">
                        <div class="form-group">
                            <label class="control-label col-xs-3">{{$column['name']}}</label>
                            <?php
                            if (is_array($column['form_type'])) {
                                $_type = $column['form_type'][0];
                            } else {
                                $_type = $column['form_type'];
                            }
                            ?>
                            <input type="hidden" name="field_{{$i}}" id="advanced-search-field-{{$i}}" data-title="{{$column['name']}}" data-type="{{$_type}}" value="{{$column['field']}}">
                        </div>
                        <div class="col-xs-2">
                        <div class="form-group" style="display:none;">
                                <select name="condition_{{$i}}" id="advanced-search-condition-{{$i}}" class="form-control input-sm"></select>
                            </div>
                        </div>
                        <div class="col-xs-7">
                            <div class="form-group" id="advanced-search-value-{{$i}}"></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                @endforeach
            </div>
        </div>
        <!--
        @if($header['search_form']['params'])
        @foreach($header['search_form']['params'] as $key => $param)
            <input name="{{$key}}" type="hidden" value="{{$param}}">
        @endforeach
        @endif
        -->
    </form>
</div>