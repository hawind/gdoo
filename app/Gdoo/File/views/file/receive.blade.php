<div class="panel">

    <ol class="breadcrumb bg-white m-b-xs lter no-radius">
        <li><a href="{{url('file/file/index')}}">文件网盘</a></li>
        <li><a href="{{url('file/file/receive')}}">我收到的</a></li>
    </ol>

    <form method="post" id="myform" name="myform">
    <div class="table-responsive">
        <table class="table m-b-none table-hover">
            <tr>
                <th align="left">姓名</th>
            </tr>
            @foreach($rows as $row)
            <tr>
                <td align="left">
                    <a href="{{url('receivedata',['user_id'=>$row['id']])}}">
                        <i class="fa fa-folder-o"></i>
                        {{$row['name']}}
                    </a>
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