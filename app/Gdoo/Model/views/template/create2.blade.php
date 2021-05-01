<div class="panel">
    
    <div class="wrapper-sm">

        <a class="btn btn-default btn-sm" href="{{url('index',['bill_id'=>$bill_id])}}"><i class="fa fa-reply"></i> 返回</a>
        <a onclick="submit()" class="btn btn-sm btn-success">
            <i class="fa fa-check"></i> 提交
        </a>
        
    </div>

    <div class="wrapper-sm b-t">

    @verbatim
    <div id="app">
        
    <div class="col-sm-2 m-b">
        <div class="panel b-a panel-default">
            <div class="panel-heading">
                <div>字段列表</div>
            </div>
            <gdoo-draggable class="list-group"
                v-model="listLeft"
                item-key="name"
                group="people"
                :sort="false"
                @start="isDragging=true"
                @end="isDragging=false">
                <template #item="{element}">
                    <a class="list-group-item">
                        {{element.name}}
                    </a>
                </template>
            </gdoo-draggable>

        </div>
    </div>
    <div class="col-sm-2 m-b">
        <div class="panel b-a panel-default">
            <div class="panel-heading">
                <div>字段列表</div>
            </div>

            <gdoo-draggable class="list-group"
                v-model="listRight"
                item-key="name"
                group="people"
                @start="isDragging=true"
                @end="isDragging=false">
                <template #item="{element, index}">
                    <a class="list-group-item" @click="clickItem(index, element)">
                        {{element.name}}
                    </a>
                </template>
            </gdoo-draggable>
        </div>
    </div>
    </div>
    @endverbatim
    
    <div class="col-sm-2 m-b">
        <div class="panel b-a panel-default">
            <div class="panel-heading">
                <div>字段列表</div>
            </div>
            
            <form method="post" id="myform" name="myform">

                    <div class="panel-body">
                        <div class="form-group">
                            <label><span class="red">*</span> 名称</label>
                            <input type="text" id="name" name="name" value="{{$template['name']}}" class="form-control input-sm">
                        </div>

                        <div class="form-group">
                            <label for="set_col">
                            <span class="red">*</span> 编码
                            <a class="hinted" href="javascript:;" title="视图前缀:{{$master_model['table']}}_"><i class="fa fa-question-circle"></i></a>
                            </label>
                            <div class="form-text">
                                <input type="text" id="code" name="code" value="{{$template['code']}}" class="form-control input-sm">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>类型</label>
                            <select multiple="multiple" class="input-select2 form-control input-sm" id="type" name="type[]" data-width="100%">
                                <option value="list" @if(in_array('list', $template['type'])) selected @endif>列表</option>
                                <option value="dialog" @if(in_array('dialog', $template['type'])) selected @endif>对话框</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>权限<span class="red">*</span></label>
                            {{App\Support\Dialog::search($template, 'id=receive_id&name=receive_name&multi=1')}}
                        </div>
                        <div class="form-group">
                            <label>客户端 <span class="red">*</span></label>
                            <select multiple="multiple" class="input-select2 form-control input-sm" data-width="100%" id="client" name="client[]">
                                <option value="web" @if(in_array('web', $template['client'])) selected @endif>web</option>
                                <option value="app" @if(in_array('app', $template['client'])) selected @endif>app</option>
                            </select>
                        </div>
                        <input type="hidden" name="bill_id" id="bill_id" value="{{$bill_id}}">
                        <input type="hidden" name="id" id="id" value="{{$template['id']}}">
                        <input type="hidden" name="table" id="table" value="{{$model['table']}}_">
                </form>
        </div>
    </div>

    </div>

    <div class="col-sm-2 m-b">
        <div class="panel b-a panel-default">
            <div class="panel-heading">
                <div>字段属性</div>
            </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="set_hidden">字段隐藏</label>
                        <div class="select-group input-group" style="width:100%;">
                            <input class="form-control input-inline input-sm" style="cursor:pointer;" readonly="readonly" data-multi="1" data-name="role_name" data-title="角色" data-url="user/role/dialog" data-id="role_id" data-toggle="dialog-view" id="role_id_text" />
                            <div class="input-group-btn">
                                <a data-toggle="dialog-clear" data-id="role_id" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>
                            </div>
                            <input type="hidden" id="role_id">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

<style>
.col-sm-2 {
    padding: 0;
    padding-right: 10px;
}
</style>
<script>
var template = JSON.parse('{{json_encode($template, JSON_UNESCAPED_UNICODE)}}');
var rightIndex = -1;
var vueData = {
    components: {
        gdooDraggable,
    },
    data() {
        return {
            isDragging: false,
            listLeft: template.leftFields,
            listRight: template.rightFields
        }
    },
    methods: {
        clickItem(index, item) {
            rightIndex = index;
            $('#role_id').val(item.role_id || '');
            $('#role_id_text').val(item.role_name || '');
        }
    }
}
var vm = Vue.createApp(vueData).mount('#app');

// 子表对话框
gdoo.event.set('role_id', {
    clear() {
        vm.listRight[rightIndex]['role_id'] = '';
        vm.listRight[rightIndex]['role_name'] = '';
    },
    onSelect(row) {
        var role_id = $('#role_id').val();
        var role_name = $('#role_id_text').val();
        if (rightIndex >= 0) {
            vm.listRight[rightIndex]['role_id'] = role_id;
            vm.listRight[rightIndex]['role_name'] = role_name;
        }
        return true;
    }
});

function submit() {
    var data = vm.listRight;
    var data = $('#myform').serialize() + '&' + $.param({columns: data});
    $.post('{{url()}}', data, function (res) {
        if (res.status) {
            toastrSuccess(res.data);
            location.reload();
        } else {
            toastrError(res.data);
        }
    }, 'json');
}

</script>

<style>

@media (min-width: 768px) {
    .col-sm-8 {
        padding-left: 0px;
        padding-right: 0px;
    }
}

.panel-heading .panel-title {
    font-size: 12px;
}

.panel-heading .fa {
    vertical-align: middle;
    color: #999;
    cursor: pointer;
}

.panel-heading .fa:hover {
    color: #666;
}

.desc {
    display: none;
    color: #999;
}

.field {
    cursor: pointer;
}

.droppedFields .title > .fa {
    display: none;
    float: right;
    color: #999;
}

.droppedFields .active .title > .fa {
    display: none;
    float: right;
    color: #fff;
}

.droppedFields .title:hover > .fa {
    display: block;
}

.list-group {
    position: relative;
    height: 500px;
    overflow-x: hidden;
    overflow-y: auto;
}

.list-group .fa {
    display: none;
}

.list-group .field:hover {
    background-color: #eee;
    color: #666;
}

.droppedFields .field {
    cursor: move;
    border-top: #ddd solid 1px;
    border-bottom: #ddd solid 1px;
    border-right: #ddd solid 1px;
    border-left: #ddd solid 1px;
    background-color: #fff;
    display: inline-block;
    width: 100%;
    margin-top: -1px;
    white-space: nowrap;
    margin: 1px;
}

.title {
    padding: 10px;
    display: inline-block;
    text-align: left;
}

.droppedFields .title {
    background-color: #eee;
    width: 160px;
}

.droppedFields .desc-sublist .title {
    width: 120px;
}

.droppedFields .active > .title {
    background-color: #0e90d2;
    color: #fff;
}

.droppedFields .fa {
    cursor: pointer;
}

.droppedFields .desc {
    display: inline-block;
}

.droppedFields .desc-sublist {
    width: 100%;
    padding: 10px;
    min-height: 30px;
    border-top: #ddd solid 1px;
    white-space: nowrap;
    overflow-x: auto;
}

.droppedFields .subfld {
    white-space: normal;
    width: auto;
}

.droppedFields {
    min-height: 60px;
    background-color: #fff;
    padding: 10px;
}

.highlightDroppable {
    padding: 10px;
    border: 1px dashed #f6c483;
    background: #fffdfa;
    text-align: center;
    color: #ccc;
    display: inline-block;
    width: 100%;
    margin-top: -1px;
    position: relative;
    margin: 1px;
}

.desc-sublist .highlightDroppable {
    width: 120px;
}

</style>
 
<style>
.dragDemo {
margin-top:50px;
display: flex;
justify-content: center;
color: #555;
}
.dragDemo .list-group {
    width: 300px;
    border: 1px solid #ddd;
    text-align: center;
    margin-right: 50px;
}
.dragDemo .list-group > div {
    padding:10px;
    border-bottom:1px dashed #ddd;
}
.dragDemo .list-group > div img {
    width:25px;
    height:25px;
    vertical-align: middle;
    padding-right:10px;
}
</style>