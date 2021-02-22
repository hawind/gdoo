<div class="panel">

    <div class="wrapper">
        @if(isset($access['add']))
            <a href="{{url('add')}}" class="btn btn-sm btn-info"><i class="icon icon-plus"></i> 新建</a>
        @endif
    </div>

<form method="post" id="myform" name="myform">

<div class="table-responsive">

<table class="table m-b-none b-t table-hover">
    <thead>
        <tr>
            <th align="left">名称</th>
            <th align="left">备注</th>
            <th align="center">排序</th>
            <th align="center">编号</th>
            <th></th>
    	</tr>
    </thead>
   @if($rows)
   @foreach($rows as $v)
    <tr>
        <td align="left">{{$v['layer_html']}}{{$v['name']}}</td>
        <td align="left">{{$v['remark']}}</td>
        <td align="center">
            <input type="text" class="form-control input-sort" name="sort[{{$v['id']}}]" value="{{$v['sort']}}" />
        </td>
        <td align="center">{{$v['id']}}</td>
        <td align="center">
            <a class="option" href="{{url('add')}}?id={{$v['id']}}"> 编辑 </a>
            <a class="option" onclick="app.confirm('{{url('delete',['id'=>$v['id']])}}','确定要删除吗？');" href="javascript:;">删除</a>
        </td>
    </tr>
   @endforeach 
   @endif
</table>

<footer class="panel-footer">
      <div class="row">
        <div class="col-sm-1 hidden-xs">
            <button type="submit" class="btn btn-primary btn-sm"><i class="icon icon-sort-by-order"></i> 排序</button>
        </div>
        <div class="col-sm-11 text-right text-center-xs">
            
        </div>
      </div>
    </footer>

</div>

</div>