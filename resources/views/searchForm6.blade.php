<div class="form-group search-group">
    <select name="field_0" id="search-field-0" class="form-control input-sm">
        <option data-type="empty" value="">筛选条件</option>
        @foreach($search['columns'] as $column)
        <option data-type="{{$column[0]}}" value="{{$column[1]}}">{{$column[2]}}</option>
        @endforeach
    </select>
</div>

<div class="form-group" style="display:none;">
    <select name="condition_0" id="search-condition-0" class="form-control input-sm"></select>
</div>

<div class="form-group" id="search-value-0"></div>

<div class="btn-group">
    <button id="search-submit" type="submit" class="btn btn-sm btn-default">
        <i class="fa fa-search"></i> 筛选</button>
</div>

@if($search['params']) @foreach($search['params'] as $key => $param)
<input name="{{$key}}" type="hidden" value="{{$param}}"> 
@endforeach @endif