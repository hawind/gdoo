<div class="panel">

    <ol class="breadcrumb bg-white m-b-xs lter no-radius">
        <li><a href="{{url('file/file/index')}}">文件网盘</a></li>
        <li><a href="{{url('file/file/receive')}}">我收到的</a></li>
        <li><a href="{{url('file/file/receivedata', ['user_id' => $user['id']])}}">{{$user['name']}}</a></li>
        @foreach($breadcrumb as $path)
            <li @if($path['id'] == $parent_id) class="active" @endif><a href="{{url('receivedata', ['parent_id' => $path['id'], 'user_id' => $user['id']])}}">{{$path['name']}}</a></li>
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
                        <a href="{{url('receivedata',['parent_id'=>$row['id'], 'user_id' => $user['id']])}}">
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
                    <a class="option" href="{{url('receivedata',['parent_id'=>$row['id'], 'user_id' => $user['id']])}}">打开</a>
                    @endif
                    <a class="option" href="{{url('down',['id'=>$row['id']])}}">下载</a>
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