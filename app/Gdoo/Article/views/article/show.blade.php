<style>
    .content-body {
        margin: 0;
    }
</style>
<div class="form-panel">
        <div class="form-panel-header">
        <div class="pull-right"></div>
        {{$form['btn']}}
        @if(isset($access['reader']))
            <button type="button" onclick="viewBox('reader', '阅读记录', '{{url('reader',['id' => $res['id']])}}')" class="btn btn-sm btn-default"><i class="icon icon-eye-open"></i> 阅读记录</button>
        @endif
    </div>
    <div class="form-panel-body">
        <div class="wrapper-sm">
            <div class="panel">
                <div class="panel-heading b-b b-light text-center">
                    <h3 class="m-xs m-l-none">
                        {{$res['title']}}
                    </h3>
                    <small class="text-muted">
                        发布人: {{get_user($res['created_id'], 'name')}}
                        &nbsp;
                        发布时间: @datetime($res['created_at'])
                    </small>
                </div>

                <div class="panel-body text-base">
                    {{$res['content']}}
                </div>

                <div class="wrapper-sm">
                    @include('attachment/view')
                </div>
            </div>
        </div>
    </div>
</div>