<div class="panel">

    <div class="wrapper">
        文件网盘
    </div>

    <div class="table-responsive">
        <table class="table m-b-none table-hover">
            <tr>
                <th align="left">名称</th>
            </tr>
            @foreach($rows as $row)
            <tr>
                <td align="left">
                    <a href="{{url($row['id'])}}"><i class="fa fa-folder-o"></i> {{$row['name']}}</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-1 hidden-xs">
            </div>
            <div class="col-sm-11 text-right text-center-xs">
                
            </div>
        </div>
    </div>
</div>