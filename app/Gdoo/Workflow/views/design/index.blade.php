<div class="panel">

    <div class="wrapper">
        @include('design/query')
    </div>

    <div class="table-responsive">
        <table class="table table-hover b-t">
        <thead>
        <tr>
            <th align="left">名称</th>
            <th align="center" width="140">类别</th>
            <th align="center" width="120">类型</th>
            <th align="center" width="80">数量</th>
            <th align="center" width="60">编号</th>
            <th align="center" width="300"></th>
        </tr>
        </thead>
        <tbody>
            @if($rows)
            @foreach($rows as $row)
            <tr>
                <td align="left">{{$row['title']}}</td>
                <td align="center">{{$categorys[$row->category_id]->title}}</td>
                <td align="center"> @if($row['type']==1) 固定 @else 自由 @endif 流程</td>
                <td align="center"><span class="badge bg-info">{{(int)$counts[$row->id]}}</span></td>
                <td align="center">{{$row['id']}}</td>
                <td align="center">
                    <a class="option" href="{{url('form/index',['work_id'=>$row['id']])}}">表单设计</a>
                    <a class="option" href="{{url('step/index',['work_id'=>$row['id']])}}">流程设计</a>
                    <a class="option" href="{{url('form/view',['id'=>$row['id']])}}">预览</a>
                    <a class="option" href="{{url('add',['id'=>$row['id']])}}">编辑</a>
                    <a class="option" href="javascript:app.confirm('{{url('delete',['id'=>$row['id']])}}','确定要删除吗？');">删除</a>
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
        </table>
    </div>

    <footer class="panel-footer">
    <div class="row">
        <div class="col-sm-1 hidden-xs">
        </div>
        <div class="col-sm-11 text-right text-center-xs">
            {{$rows->render()}}
        </div>
    </div>
    </footer>
</div>
