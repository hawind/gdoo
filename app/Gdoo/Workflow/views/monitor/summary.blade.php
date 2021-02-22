<div class="panel">

    <div class="wrapper">
        @include('monitor/query')
    </div>

    <form method="post" id="myform" name="myform">
    <div class="table-responsive">
        <table class="table m-b-none table-striped b-t table-hover">
            <thead>
            <tr>
                <th align="left">姓名</th>
                <th align="right">待办数量</th>
                <th align="right">超过一天</th>
                <th align="right">超过三天</th>
                <th align="right">超过三十天</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rows as $row)
            <tr>
                <td align="left">{{$row['name']}}</td>
                <td align="right">{{$row['total']}}</td>
                <td align="right">{{$row['a']}}</td>
                <td align="right">{{$row['b']}}</td>
                <td align="right">{{$row['c']}}</td>
            </tr>
             @endforeach
            </tbody>
        </table>
    </div>
    </form>

    <footer class="panel-footer">
      <div class="row">
        <div class="col-sm-1 hidden-xs">
        </div>
        <div class="col-sm-11 text-right text-center-xs">
        </div>
      </div>
    </footer>
</div>