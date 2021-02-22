 <div class="hbox hbox-auto-xs hbox-auto-sm" ng-controller="MailCtrl">
  <div class="col w-md bg-light dk b-r bg-auto">

    <div class="wrapper b-b bg">

      <a class="btn btn-sm btn-info w-xs font-bold">文件夹列表</a>
    
    </div>

    <div class="wrapper hidden-sm hidden-xs" id="email-menu">

      <ul class="nav nav-pills nav-stacked">

        @if($folders)
        @foreach($folders as $menuKey => $menuValue)
            <li @if(Request::action() == $menuValue['id']) class="active" @endif ><a class="text-base text-info" href="{{url($menuValue['id'])}}">{{$menuValue['name']}}</a></li>
        @endforeach
        @endif
      </ul>
   
      <!--
      <div class="wrapper">Labels</div>
      <ul class="nav nav-pills nav-stacked nav-sm">
        <li>
          <a><i class="fa fa-fw fa-circle text-info"></i>Angular</a>
        </li>
        <li>
          <a><i class="fa fa-fw fa-circle text-primary"></i>Bootstrap</a>
        </li>
        <li>
          <a><i class="fa fa-fw fa-circle text-success"></i>Work</a>
        </li>
        <li>
          <a><i class="fa fa-fw fa-circle text-muted"></i>Client</a>
        </li>
      </ul>
      <div class="wrapper">
        <form name="label">
          <div class="input-group">
            <input type="text" class="form-control input-sm" placeholder="New label">
            <span class="input-group-btn">
              <button class="btn btn-sm btn-default" type="button">Add</button>
            </span>
          </div>
        </form>
      </div>
      -->
    </div>
  </div>
  <div class="col">
    
    <div>
      <!-- header -->
      <div class="wrapper bg-light lter b-b">

        <div class="pull-right">
            <button type="button" data-toggle="dialog-form" data-title="新建文件夹" data-url="{{url('folder', ['folder'=>1,'parent_id'=>$parent_id])}}" data-id="myfolder" class="btn btn-default btn-sm"><i class="fa fa-folder-o"></i> 新建</button>
            <button type="button" data-toggle="dialog-form" data-title="上传文件" data-url="{{url('upload', ['parent_id' => $parent_id])}}" data-id="myupload" class="btn btn-info btn-sm"><i class="fa fa-file-o"></i> 上传文件</button>
        </div>

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
      <!-- / header -->

       <div class="panel">
      <form method="post" id="myform" name="myform">
                    <div class="table-responsive ">
                        <table class="table m-b-none table-hover">
                            <thead>
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
                            </thead>
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
                                        <a href="{{url('',['parent_id'=>$row['id']])}}">
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
                                    <a class="option" href="{{url('personal',['parent_id'=>$row['id']])}}">打开</a>
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



</div>
</div>

