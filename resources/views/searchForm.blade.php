@if($search['query']['advanced'])

<div class="panel panel-default m-t-sm m-b-none">
    <div class="wrapper-xs">
        <div class="row">
            @foreach($search['columns'] as $i => $column)
            <div class="col-sm-6">
                <div class="wrapper-xs">
                    <div class="form-inline">
                        <div class="form-group">
                            {{$column[2]}}
                            <input type="hidden" name="field_{{$i}}" id="search-field-{{$i}}" data-type="{{$column[0]}}" value="{{$column[1]}}">
                        </div>

                        <div class="form-group" style="display:none;">
                            <select name="condition_{{$i}}" id="search-condition-{{$i}}" class="form-control input-sm"></select>
                        </div>

                        <div class="form-group" id="search-value-{{$i}}">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="panel-footer">

        <div class="btn-group">
            <button id="search-submit" type="submit" class="btn btn-sm btn-default">
                <i class="fa fa-search"></i> 搜索</button>
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu text-xs" role="menu">
                <li>
                    <a href="{{url('')}}"><i class="fa fa-search"></i> 简单搜索</a>
                </li>
            </ul>
        </div>

    </div>
</div>

@else

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
        <i class="fa fa-search"></i> 搜索</button>
    @if(is_numeric($search['query']['advanced']))
    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu text-xs" role="menu">
        <li>
            <a href="{{url('', ['advanced' => 1])}}">
                <i class="fa fa-search"></i> 高级搜索</a>
        </li>
    </ul>
    @endif
</div>

@endif @if($search['params']) @foreach($search['params'] as $key => $param)
<input name="{{$key}}" type="hidden" value="{{$param}}"> @endforeach @endif