<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">
            
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body panel-form-{{$form['action']}}">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            {{$form['tpl']}}

            @if($taxs)
            <div class="panel m-t-sm">
                <table class="table">
                    <tr>
                        <th align="left">开票名称</th>
                        <th align="center">开票编号</th>
                        <th align="center">纳税人识别号</th>
                        <th align="center">开户银行</th>
                        <th align="center">银行帐号</th>
                        <th align="center">状态</th>
                    </tr>
                    @foreach($taxs as $tax)
                    <tr>
                        <td>{{$tax['name']}}</td>
                        <td align="center">{{$tax['code']}}</td>
                        <td align="center">{{$tax['tax_number']}}</td>
                        <td align="center">{{$tax['bank_name']}}</td>
                        <td align="center">{{$tax['bank_account']}}</td>
                        <td align="center">@if($tax['status'] == '1') 生效 @else 草稿 @endif</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

        </form>

    </div>
</div>
<script>
</script>