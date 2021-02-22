 <style>
     .content-body {
         margin: 0;
     }

     .form-panel-body {
         overflow: hidden;
     }

     .left-group {
         overflow: hidden;
         overflow-y: auto;
         height: calc(100vh - 185px);
     }

     .right-group {
         overflow: hidden;
         overflow-y: auto;
         height: calc(100vh - 177px);
     }
 </style>
 <div class="form-panel">
     <div class="form-panel-header">
         <div class="pull-right">
         </div>
         <button type="button" class="btn btn-sm btn-info" id="role-form-submit"><i class="fa fa-check"></i> 保存</button>
         <a class="btn btn-sm btn-default" data-toggle="closetab" data-id="role_config"><i class="fa fa-sign-out"></i> 退出</a>
     </div>
     <div class="form-panel-body">

         <div class="panel no-border">

             <div class="padder padder-t padder-r-n">
                 <form id="myfilter" role="form" class="form-inline" name="myfilter" action="{{url()}}" method="get">

                     <div class="form-group">
                         <label>授权角色</label>
                         <select class="form-control input-sm" data-toggle="redirect" id='role_id' name='role_id' data-url="{{url($atcion, $query)}}">
                             @foreach($roles as $key => $role)
                             <option value="{{$role->id}}" @if($query['role_id']==$role->id) selected @endif>{{$role->layer_space}}{{$role->name}}</option>
                             @endforeach
                         </select>
                     </div>
                     <span class="hidden-xs">&nbsp;</span>
                     <div class="form-group">
                         <label>参照角色</label>
                         <select class="form-control input-sm" data-toggle="redirect" id='clone_id' name='clone_id' data-url="{{url($atcion, $query)}}">
                             <option value="">无</option>
                             @foreach($roles as $key => $role)
                             <option value="{{$role->id}}" @if($query['clone_id']==$role->id) selected @endif>{{$role->layer_space}}{{$role->name}}</option>
                             @endforeach
                         </select>
                     </div>

                 </form>
             </div>

             <div class="wrapper m-t b-t" style="background-color:#f0f3f4;">

                 <div class="row">

                     <div class="col-sm-2 m-b padder-r-n">

                         <div class="panel panel-info">
                             <div class="panel-heading b-b b-light">
                                 <div class="h5 m-t-xs m-b-xs"><i class="fa fa-cubes"></i> 模块列表</div>
                             </div>
                             <div class="list-group left-group">
                                 @if(count($modules))
                                 @foreach($modules as $menuKey => $menuValue)
                                 <a class="list-group-item" onclick="module('{{$menuKey}}');" href="javascript:;">
                                    <span class="h5">{{$menuValue['name']}}</span>
                                 </a>
                                 @endforeach
                                 @endif
                             </div>
                         </div>
                     </div>

                     <div class="col-sm-10">

                         <form method="post" class="form-inline" action="{{url()}}" id="role" name="role">

                             @if(count($modules))
                             @foreach($modules as $menuKey => $menuValue)

                             <div class="modules" style="display:none;" id="{{$menuKey}}">

                                 <div class="panel m-b-sm b-a">

                                     <div class="panel-heading b-b b-light">
                                         <label class="checkbox-inline"><input type="checkbox" class="menu-check">{{$menuValue['name']}}</label>
                                     </div>

                                     <div class="panel-body right-group">

                                         @if(count($menuValue['controllers']))
                                         @foreach($menuValue['controllers'] as $groupKey => $groupValue)

                                         <div class="panel m-b-sm b-a">

                                             <div class="panel-heading b-b b-light">
                                                <label class="checkbox-inline" title="{{$groupKey}}"><input class="group-check" type="checkbox">{{$groupValue['name']}}</label>
                                             </div>

                                             <div class="panel-body">
                                                @if(count($groupValue['actions']))
                                                @foreach($groupValue['actions'] as $childKey => $childValue)
                                                <div class="col-md-3 col-sm-6 wrapper-xs">
                                                    {{'';$selected = $assets[$menuKey][$groupKey.'.'.$childKey]}}
                                                    <label title="{{$childKey}}" class="checkbox-inline">
                                                        <input type="checkbox" class="action-check" name="assets[{{$menuKey}}][{{$groupKey}}.{{$childKey}}][action]" value="1" @if(isset($selected)) checked @endif>
                                                        {{$childValue['name']}}
                                                    </label>
                                                </div>
                                                @endforeach
                                                @endif
                                             </div>

                                         </div>

                                         @endforeach
                                         @endif

                                         <div class="clearfix"></div>
                                     </div>

                                 </div>
                             </div>

                             @endforeach

                             @endif

                             <input type="hidden" name="role_id" value="{{$query['role_id']}}">
                             <input type="hidden" name="key" id="key" value="{{$query['key']}}">
                         </form>
                     </div>
                 </div>
             </div>
         </div>
     </div>
</div>

<script type="text/javascript">
     $(function() {

        $('.menu-check,.group-check').on('click', function() {
            if ($(this).prop("checked") == true) {
                $(this).parent().parent().parent().find(':checkbox').prop('checked', true);
            } else {
                $(this).parent().parent().parent().find(':checkbox').prop('checked', false);
            }
        });

        ajaxSubmit('role', function(res) {
             if (res.status) {
                 toastrSuccess(res.data);
             } else {
                 toastrError(res.data);
             }
         });

         var e = $('#role');
         var key = e.find('#key').val();
         if (key == '') {
             var list = e.find('.modules');
             key = list.eq(0).attr('id');
             e.find('#key').val(key);
         }
         e.find('#' + key).fadeIn();

     });

     function module(key) {
         var e = $('#role');
         e.find('#key').val(key);
         e.find('.modules').hide();
         e.find('#' + key).fadeIn();
    }
</script>