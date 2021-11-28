<form method="post" action="{{url()}}" id="myform" name="myform">
    <div class="panel">
    
        <div class="table-responsive">
    
        <table class="table table-form m-b-none">
    
            <tr>
                <td align="right" width="10%">项目名称</td>
                <td align="left">
                    <input type="text" name="name" id="name" class="form-control input-sm">
                </td>
            </tr>

            <tr>
                <td align="right">项目权限  <a href="javascript:;" class="fa fa-question-circle hinted" title="公开：所有人可以访问，成员编辑。<br>私有：成员访问和编辑。"></a></td>
                <td align="left">
                    <select class="form-control input-sm" name="permission">
                        <option value="1">私有</option>
                        <option value="0">公开</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td align="right">项目拥有者</td>
                <td align="left">
                    {{App\Support\Dialog::user('user','user_id', '', 0, 0)}}
                </td>
            </tr>
            <tr>
                <td align="right">项目描述</td>
                <td align="left">
                    <textarea name="description" id="description" class="form-control input-sm"></textarea>
                </td>
            </tr>
    
            <tr>
                <td align="left" colspan="2">
                    <button type="button" onclick="history.back();" class="btn btn-default">返回</button>
                    <button type="submit" class="btn btn-success btn-large"><i class="fa fa-check-circle"></i> 提交</button>
                </td>
            </tr>
    
            </table>
        </div>
    </div>
</form>