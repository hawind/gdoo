<div class="panel">

    <div class="wrapper">

        <div class="input-group">
            <button type="button" class="btn btn-sm btn-default" data-toggle="dropdown">
                批量操作
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu text-xs">
                <li><a href="javascript:optionDelete('#myform','{{url('delete')}}');"><i class="fa fa-remove"></i> 删除</a></li>
            </ul>
        </div>

    </div>

    <ol class="breadcrumb bg-white m-b-xs lter b-t no-radius">
        <li><a href="{{url('file/file/index')}}">文件网盘</a></li>
        <li><a href="{{url('file/file/share')}}">我共享的</a></li>
        @foreach($breadcrumb as $path)
            <li @if($path['id'] == $parent_id) class="active" @endif><a href="{{url('share', ['parent_id' => $path['id']])}}">{{$path['name']}}</a></li>
        @endforeach
    </ol>

    <form method="post" id="myform" name="myform">
    <div class="table-responsive">
        <table class="table m-b-none table-hover">
            <tr>
                <th align="center">
                    <input class="select-all" type="checkbox">
                </th>
                <th align="left">名称</th>
                <th align="center">类型</th>
                <th align="center">大小</th>
                <th align="center">时间</th>
                <th align="center"></th>
            </tr>
            @foreach($rows as $row)
            <tr>
                <td align="center">
                    <input class="select-row" type="checkbox" name="id[]" value="{{$row['id']}}">
                </td>
                <td align="left">
                    @if($row['folder'] == 0)
                        <i class="fa fa-file-o"></i>
                        {{$row['name']}}
                    @else
                        <a href="{{url('share',['parent_id'=>$row['id']])}}">
                            <i class="fa fa-folder-o"></i>
                            {{$row['name']}}
                        </a>
                    @endif
                </td>
                <td align="center">
                    @if($row['folder'] == 0)
                        {{$row['type']}}
                    @else
                       文件夹
                    @endif
                </td>
                <td align="center">
                    @if($row['folder'] == 0)
                        {{human_filesize($row['size'])}}
                    @endif
                </td>
                <td align="center">@datetime($row['created_at'])</td>
                <td align='center'>

                    @if($row['folder'] == 0)
                    <a class="option" href="{{url('show',['id'=>$row['id']])}}">打开</a>
                    @else
                    <a class="option" href="{{url('share',['parent_id'=>$row['id']])}}">打开</a>
                    @endif
                    <a class="option" href="{{url('down',['id'=>$row['id']])}}">下载</a>
                    <button type="button" class="option" data-toggle="dialog-form" data-title="重命名" data-url="{{url('folder', ['folder'=>$row['folder'],'id' => $row['id'], 'parent_id' => $parent_id])}}" data-id="myfolder">重命名</button>
                    <button type="button" class="option" data-toggle="dialog-form" data-title="共享" data-url="{{url('sharing', ['id' => $row['id']])}}" data-id="myshare">共享</button>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    </form>

    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-1 hidden-xs">
            </div>
            <div class="col-sm-11 text-right text-center-xs">
                {{$rows->render()}}
            </div>
        </div>
    </div>
</div>