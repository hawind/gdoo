@verbatim
<template v-if="header.tabs && header.tabs.items.length > 0">
<div class="panel-heading tabs-box">
    <ul class="nav nav-tabs">
        <template v-for="tab in header.tabs.items">
        <li :class="header.tab_active == tab.value ? 'active' : ''">
            <a class="text-sm" :href="url(tab.url,{tab:tab.value})">{{tab.name}}</a>
        </li>
        </template>
    </ul>
</div>
</template>

<div class="wrapper-xs">
    <div class="pull-right">
        <template v-for="button in header.right_buttons">
            <template v-if="button.display">
            <a v-if="button.type == 'a'" :href="url(button.action)" :class="'btn btn-sm btn-' + button.color"><i :class="'fa ' + button.icon"></i> {{button.name}}</a>
            <a v-else :data-toggle="header.master_table" :data-action="button.action" href="javascript:;" :class="'btn btn-sm btn-' + button.color"><i :class="'fa ' + button.icon"></i> {{button.name}}</a> 
            </template>
        </template>

        <template v-if="header.trash_btn">
            <a href="{{url('',{by:'trash'})}}" :class="'btn btn-sm btn-default ' + (header.search_form.query.by == 'trash' ? 'active' : '')"><i class="fa fa-trash"></i>回收站 ({{header.trash_count}})</a>
        </template>
        
    </div>

    <a v-if="access.create && header.create_btn" href="javascript:;" :data-toggle="header.master_table" data-action="create" class="btn btn-sm btn-success hinted" :title="'新建' + header.name"><i class="icon icon-plus"></i> 新建</a>
    
    <div class="btn-group">
        <a class="btn btn-info btn-sm" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-bars"></i> 操作 <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <template v-for="button in header.buttons">
                <li v-if="button.action == 'divider'" class="divider"></li>
                <li v-elseif="button.display && (header.exist_sub_table == 1 && button.action != 'delete')"><a :data-toggle="header.master_table" :data-action="button.action" href="javascript:;"><i class="'fa ' + button.icon"></i> {{button.name}}</a></li>
            </template>
        </ul>
    </div> 

    <span class="visible-xs">
        <div class="btn-group">
        <a href="javascript:;" :data-toggle="header.master_table" data-action="filter" class="btn btn-sm btn-default"><i class="fa fa-search"></i> 搜索</a>
        </div>
    </span>

    <!-- 简单搜索表单 -->
    <template v-if="header.simple_search_form">
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
    </form>
    </template>
    <template v-else>
        <a class="btn btn-sm btn-default" :data-toggle="header.master_table" data-action="filter" href="javascript:;"><i class="fa fa-search"></i> 筛选</a>
    </template>

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
                        @if($column['form_type'] == 'text2') { continue; }
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
        </form>
    </div>

</div>

@endverbatim