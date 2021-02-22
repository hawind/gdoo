 <div class="panel">

     @include('query')

    <table class="table table-hover b-t" id="table-sortable" url="{{url()}}">
        <tr>
            <th align="left">节点名称</th>
            <th align="left">转入节点</th>
            <th align="center">执行目标</th>
            <th align="center">节点类型</th>
            <th align="center">节点颜色</th>
            <th align="center">ID</th>
            <th align="center"></th>
        </tr>
        @foreach($rows as $row)
        <tr id="{{$row->id}}">
            <td align="left" @if($row->type != "end" && $row->type != "start") class="move" @endif>
                @if($row->sn > 1)
                <span class="badge badge-bg">{{$row->sn}}</span>
                @endif
                {{$row->name}}
            </td>
            <td align="left">
                <?php $joins = explode(',', $row->join); ?>
                @foreach($joins as $id)
                    {{$rows[$id]['name']}}
                @endforeach
            </td>
            <td align="center">{{$row->type}}</td>

            <td align="center">@if($row->option == 1) 审核 @else 知会 @endif</td>

            <td align="center"><span class="label label-{{$row->color}}">{{$row->color}}</span></td>
            <td align="center">{{$row->id}}</td>
            <td align="center">
                @if($row->type != 'end')
                <?php $condition = json_decode($row->condition, true); ?>
                <a class="option" href="{{url('condition',['model_id'=>$model->id,'id'=>$row->id])}}">条件({{count($condition)}})</a>
                <a class="option" href="{{url('create',['model_id'=>$model->id,'id'=>$row->id])}}">编辑</a>
                @endif
                @if($row->sn > 1)
                <a class="option" onclick="app.confirm('{{url('delete',['id'=>$row->id])}}','确定要删除吗？');" href="javascript:;">删除</a>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    </form>
</div>