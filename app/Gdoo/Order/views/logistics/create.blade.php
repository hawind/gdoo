<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right">>
        </div>
        {{$form['btn']}}
    </div>
    <div class="form-panel-body">
        <form class="form-horizontal form-controller" method="post" id="{{$form['table']}}" name="{{$form['table']}}">
            <div class="panel">
                {{$form['tpl']}}
            </div>
        </form>
    </div>
</div>