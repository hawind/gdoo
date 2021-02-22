<div class="row" style="margin-left:0;margin-right:0;">

    @if($categorys)
    @foreach($categorys as $key => $category)

    <div class="col-md-4 col-sm-6 m-b">

        @if($category)
        @foreach($category as $k => $cat)

            <div class="panel b-a">
                <div class="panel-heading text-base b-b">
                    <i class="fa fa-file-text-o"></i> {{$cat['title']}}
                </div>

                <table class="table table-hover m-b-none">
                    @if($rows[$cat['id']])
                    @foreach($rows[$cat['id']] as $row)
                    <tr>
                        <td onclick="workStart({{$row['id']}});"><a onclick="workStart({{$row['id']}});" href="javascript:;">{{$row['title']}}</a></td>
                    </tr>
                    @endforeach
                    @endif
                </table>
            </div>
        @endforeach
        @endif
    </div>

    @endforeach
    @endif

</div>

<script type="text/javascript">
var donf   = null;
var doapp  = null;
var dopage = null;

window.onDeviceOneLoaded = function() {
    donf   = sm("do_Notification");
    doapp  = sm("do_App");
    dopage = sm("do_Page");
}

function workStart(id)
{
    $.dialog({
        title: '新建工作',
        url:'{{url("add")}}?id='+id,
        dialogClass:'modal-md',
        buttons:[{
            text: '确定',
            'class': 'btn-primary',
            click: function() {
                var myform = $('#myform').serialize();
                $.post('{{url("add")}}', myform, function(res) {
                    if (res.status) {
                        donf.toast('新建成功。');
                        doapp.closePage('reload');
                    } else {
                        donf.alert(res.data);
                    }
                },'json');
            }
        },{
            text: '取消',
            'class': 'btn-default',
            click: function() {
                $(this).dialog('close');
            }
        }]
    });
}
</script>
