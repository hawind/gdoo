<div class="wrapper-sm b-b">
    <span class="text-md">{{$project['name']}}</span> <span class="text-muted">{{$project['description']}}</span>
</div>

<div id="index-wrapper">
    <form id="{{$header['master_table']}}-search-form" class="form-inline" name="mytasksearch" method="get">

        <div class="pull-right wrapper-xs">
            <div class="btn-group">
                <a href="{{url('index', ['project_id' => $project['id'], 'tpl' => 'index'])}}" class="btn btn-sm btn-default @if($header['search_form']['query']['tpl'] == 'index') active @endif">列表</a>
                <a href="{{url('index', ['project_id' => $project['id'], 'tpl' => 'gantt'])}}" class="btn btn-sm btn-default @if($header['search_form']['query']['tpl'] == 'gantt') active @endif">甘特图</a>
            </div>
        </div>

        @section('left_buttons')
            @if(isset($access['add']))
                @if($permission['add_item'])
                <a href="javascript:addItem();" title="添加列表" class="hinted btn btn-sm btn-info"><i class="icon icon-plus"></i> 添加列表</a>
                @endif
                
                @if($permission['add_task'])
                <a href="javascript:addTask();" title="添加任务" class="hinted btn btn-sm btn-info"><i class="icon icon-plus"></i> 添加任务</a>
                @endif
            @endif
        @endsection

        @include('headers')

    </form>
</div>
<style type="text/css">
.ag-theme-balham .ag-root {
    border-bottom: 0;
}
</style>