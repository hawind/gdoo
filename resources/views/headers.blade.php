@if($header['tabs'])
@if(count($header['tabs']['items']))
<div class="panel-heading tabs-box">
    <ul class="nav nav-tabs">
        <?php 
            $tabs = $header['tabs'];
            $params = $header['search_form']['params'];
            $tab_active = isset($params['tab']) ? $params['tab'] : $tabs['items'][0]['value'];
        ?>
        @foreach($tabs['items'] as $tab)
        <?php 
            $params['tab'] = $tab['value'];
        ?>
        <li class="@if($tab_active == $tab['value']) active @endif">
            <a class="text-sm" href="{{url($tab['url'], $params)}}">{{$tab['name']}}</a>
        </li>
        @endforeach
    </ul>
</div>
@endif
@endif

<div class="wrapper-xs">
    <div class="pull-right">

        @if($header['right_buttons'])
            @foreach($header['right_buttons'] as $button)
                @if($button['display'])
                <a @if($button['type'] == 'a') href="{{url($button['action'])}}" @else data-toggle="{{$header['master_table']}}" data-action="{{$button['action']}}" href="javascript:;" @endif class="btn btn-sm btn-{{$button['color']}}"><i class="fa {{$button['icon']}}"></i> {{$button['name']}}</a> 
                @endif
            @endforeach
        @endif

        <?php
            $params = $header['search_form']['params'];
        ?>
        @if($header['trash_btn'])
            <?php $params['by'] = 'trash'; ?>
            <a href="{{url('', $params)}}" class="btn btn-sm btn-default @if($header['search_form']['query']['by'] == 'trash') active @endif"><i class="fa fa-trash"></i>回收站 ({{$header['trash_count']}})</a>
        @endif
        
    </div>

    @if(isset($access['create']) && $header['create_btn'])
        <a href="javascript:;" data-toggle="{{$header['master_table']}}" data-action="create" class="btn btn-sm btn-success hinted" title="新建{{$header['name']}}"><i class="icon icon-plus"></i> 新建</a>
    @endif
    
    {{:$button_count = 0}}
    @foreach($header['buttons'] as $button)
    @if($button['display'])
        {{:$button_count++}}
    @endif
    @endforeach

    @if($button_count)
    
    <div class="btn-group">
        <a class="btn btn-info btn-sm" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bars"></i> 操作 <span class="caret"></span></a>
        <ul class="dropdown-menu">
            @foreach($header['buttons'] as $button)
                @if($button['action'] == 'divider')
                    <li class="divider"></li>
                @else
                    @if($button['display'])
                    <!-- 此处跳过查询子表的删除按钮 -->
                    @if($header['exist_sub_table'] == 1 && $button['action'] == 'delete')
                    <?php continue; ?>
                    @endif
                    <li><a data-toggle="{{$header['master_table']}}" data-action="{{$button['action']}}" href="javascript:;"><i class="fa {{$button['icon']}}"></i> {{$button['name']}}</a></li>
                    @endif
                @endif
            @endforeach
        </ul>
    </div> 
    @endif

    <span class="visible-xs">
        <a href="javascript:;" data-toggle="{{$header['master_table']}}" data-action="filter" class="btn btn-sm btn-default"><i class="fa fa-search"></i> 搜索</a>
    </span>

    <!-- 简单搜索表单 -->
    @if($header['simple_search_form'] == 1)
    <form id="{{$header['master_table']}}-search-form" class="search-inline-form form-inline hidden-xs" name="mysearch" action="{{url()}}" method="get">
        <div class="form-group search-group">
            <select name="field_0" id="search-field-0" class="form-control input-sm">
                <option data-type="empty" value="">筛选条件</option>
                @foreach($header['search_form']['columns'] as $column)
                <option data-type="{{$column['form_type']}}" data-title="{{$column['name']}}" value="{{$column['field']}}">{{$column['name']}}</option>
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
            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu text-xs" role="menu">
                <li>
                    <a data-toggle="{{$header['master_table']}}" data-action="filter" href="javascript:;">
                        <i class="fa fa-search"></i> 高级搜索</a>
                </li>
            </ul>
        </div>
        <!--
        @if($header['search_form']['params'])
            @foreach($header['search_form']['params'] as $key => $param)
            <input name="{{$key}}" type="hidden" value="{{$param}}"> 
            @endforeach
        @endif
        -->
    </form>
    @else
        <a class="btn btn-sm btn-default" data-toggle="{{$header['master_table']}}" data-action="filter" href="javascript:;"><i class="fa fa-search"></i> 筛选</a>
    @endif
    
    @if($header['bys'])
    <?php $by_name = '筛选'; ?>
    @foreach($header['bys']['items'] as $item)
        @if($header['search_form']['query'][$header['bys']['name']] == $item['value'])
        <?php $by_name = $item['name']; ?>
        @endif
    @endforeach

    <div class="btn-group" role="group">
        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="fa fa-filter"></span> {{$by_name}}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <?php
                $params = $header['search_form']['params'];
            ?>
            @foreach($header['bys']['items'] as $item)
                @if($item['value'] == 'divider')
                    <li class="divider"></li>
                @else
                    <?php $params[$header['bys']['name']] = $item['value']; ?>
                    <li class="@if($header['search_form']['query'][$header['bys']['name']] == $item['value']) active @endif"><a href="{{url('', $params)}}">{{$item['name']}}</a></li>
                @endif
            @endforeach
            <!--
            <li>
                <a href="javascript:;">添加自定义筛选</a>
            </li>
            -->
        </ul>
    </div>
    @endif

    @if($header['left_buttons'])
        @foreach($header['left_buttons'] as $button)
            @if($button['display'])
            <a @if($button['type'] == 'a') @if($button['target']) target="{{$button['target']}}" @endif href="{{$button['url'] ? url($button['url']) : url($button['action'])}}" @else data-toggle="{{$header['master_table']}}" data-action="{{$button['action']}}" href="javascript:;" @endif class="btn btn-sm btn-{{$button['color']}}"><i class="fa {{$button['icon']}}"></i> {{$button['name']}}</a> 
            @endif
        @endforeach
    @endif

    <div style="display:none;">
        <form id="{{$header['master_table']}}-search-form-advanced" class="search-form" action="{{url()}}" method="get">
            <div class="wrapper-xs1 search-form-advanced">
                <div class="row">
                    @foreach($header['search_form']['columns'] as $i => $column)
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

</div>