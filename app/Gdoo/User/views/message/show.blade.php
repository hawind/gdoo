<div class="panel m-b-none">
    <div class="panel-heading b-b b-light">
        <small class="text-muted">
            来自 : {{get_user($row['created_id'], 'name')}} &nbsp;创建时间 : @datetime($row['created_at'])
        </small>
    </div>
    <div class="panel-body">
        {{$row['content']}}
    </div>
</div>