<div class="panel">

    <div class="wrapper-sm">
        <a class="btn btn-default btn-sm" href="{{url('index',['bill_id'=>$bill_id])}}"><i class="fa fa-reply"></i> 返回</a>
        <a id="add-form-group" class="btn btn-sm btn-info">
            <i class="fa fa-plus"></i>
            添加表单组
        </a>
        <!--
        <a onclick="preview()" class="btn btn-default">预览</a>
        -->
        <a onclick="submit()" class="btn btn-sm btn-success">
            <i class="fa fa-check"></i> 提交
        </a>

    </div>

    <div class="wrapper-sm b-t">

    <div class="col-sm-2 col-left m-b-xs">
        <div class="panel b-a panel-default tabs-box">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#1" data-toggle="tab">字段列表</a></li>
                <li class=""><a href="#2" data-toggle="tab">组件列表</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="1">
                    <ul class="list-group">
                        <!-- 多行子表 -->
                        @foreach($models as $model)
                            @if($model['parent_id'])
                                <div class="list-group-item fld field" data-col="12" data-hidden="0" data-readonly="0" data-title="" data-role_name="{{$model['role_name']}}" data-role_id="{{$model['role_id']}}" data-type="{{$model['type']}}" data-table="" data-field="{{$model['table']}}">
                                    <div class="title">
                                        <b>{{$model['name']}}</b>
                                    </div>
                                    <i class="move fa fa-w fa-arrows"></i>
                                    <i class="fa fa-w fa-remove" title="删除"></i>
                                    <div class="desc-sublist"></div>
                                </div>
                            @endif
                            @foreach($model['fields'] as $field)
                                <div class="list-group-item fld field" data-col="12" data-hidden="0" data-readonly="0" data-title="" data-role_name="{{$field['role_name']}}" data-role_id="{{$field['role_id']}}" data-type="@if($model['parent_id'] == 0) 0 @else {{$field['type']}} @endif" data-table="{{$model['table']}}" data-field="{{$field['field']}}">
                                    <div class="title">
                                        @if($model['parent_id'] > 0)
                                            <span class="model-title">[{{$model['name']}}]</span>
                                        @endif
                                        {{$field['name']}}
                                    </div>
                                    <i class="move fa fa-w fa-arrows"></i>
                                    <i class="remove fa fa-w fa-remove" title="删除"></i>
                                    @if($model['parent_id'] == 0)
                                    <div class="desc"></div>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
                <div class="tab-pane" id="2">
                    <ul class="list-group">
                        <div class="list-group-item fld field" data-col="12" data-hidden="0" data-readonly="0" data-role_name="" data-role_id="" data-id="0" data-type="0" data-custom="1" data-field="{flowlog}">
                            <div class="title">流程记录</div>
                            <i class="move fa fa-w fa-arrows"></i>
                            <i class="remove fa fa-w fa-remove" title="删除"></i>
                            <div class="desc"></div>
                        </div>
                        <div class="list-group-item fld field" data-col="12" data-title="单行文本" data-hidden="0" data-readonly="0" data-role_name="" data-role_id="" data-id="0" data-type="0" data-custom="1" data-field="{text}">
                            <div class="title">单行文本</div>
                            <i class="move fa fa-w fa-arrows"></i>
                            <i class="remove fa fa-w fa-remove" title="删除"></i>
                            <div class="desc"></div>
                        </div>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="droppedFieldsBox" id="droppedFieldsBox"></div>
    </div>

    <div class="col-sm-2 col-right">
        <div class="panel b-a panel-default tabs-box">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#11" data-toggle="tab">字段属性</a></li>
            <li class=""><a href="#22" data-toggle="tab">模板属性</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="11">
                <div class="panel-body field-attr" id="fieldAttribute">

                    <div class="form-group">
                        <label for="set_col">占用行比例</label>
                        <div class="form-text">
                            <select class="form-control input-sm" id="set_col" onchange="setCol(this.value)" required="required">
                                <option value="12">12</option>
                                <option value="11">11</option>
                                <option value="10">10</option>
                                <option value="9">9</option>
                                <option value="8">8</option>
                                <option value="7">7</option>
                                <option value="6">6</option>
                                <option value="5">5</option>
                                <option value="4">4</option>
                                <option value="3">3</option>
                                <option value="2">2</option>
                                <option value="1">1</option>
                            </select>
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="set_readonly">字段只读</label>
                        <div class="form-text">
                            <select class="form-control input-sm" id="set_readonly" onchange="setField('readonly', this.value)" required="required">
                                <option value="0">否</option>
                                <option value="1">是</option>
                            </select>
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="set_hide_title">标题</label>
                        <div class="form-text">
                            <input class="form-control input-sm" id="set_title" onblur="setField('title', this.value)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="set_content">内容</label>
                        <div class="form-text">
                            <input class="form-control input-sm" id="set_content" onblur="setField('content', this.value)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="set_hide_title">标题隐藏</label>
                        <div class="form-text">
                            <select class="form-control input-sm" id="set_hide_title" onchange="setField('hide_title', this.value)" required="required">
                                <option value="0">否</option>
                                <option value="1">是</option>
                            </select>
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="set_readonly">字段宽度</label>
                        <div class="form-text">
                            <input class="form-control input-sm" id="set_width" onchange="setField('width', this.value)">
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="set_hidden">字段可见</label>
                        <div class="form-text">
                            <select class="form-control input-sm" id="set_hidden" onchange="setField('hidden', this.value)" required="required">
                                <option value="0">是</option>
                                <option value="1">否</option>
                            </select>
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="set_hidden">字段隐藏</label>
                        <div class="form-text">
                            <div class="select-group input-group" style="width:100%;"><input class="form-control input-inline input-sm" style="cursor:pointer;" readonly="readonly" data-multi="1" data-name="role_name" data-title="角色" data-url="user/role/dialog" data-id="role_id" data-toggle="dialog-view" id="role_name" />
                                <div class="input-group-btn">
                                    <a data-toggle="dialog-clear" data-id="role_id" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>
                                </div>
                                <input type="hidden" id="role_id">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="22">
                    <div class="panel-body field-attr">
                        <form method="post" id="myform" name="myform">
                        <div class="form-group">
                            <label for="set_col"><span class="red">*</span> 名称</label>
                            <div class="form-text">
                                <input type="text" id="name" name="name" value="{{$template['name']}}" class="form-control input-sm">
                            </div>
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
                            <label for="set_col"><span class="red">*</span> 类型</label>
                            <div class="form-text">
                                <select multiple="multiple" class="input-select2 form-control input-sm" id="type" name="type[]" data-width="100%">
                                    <option value="create" @if(in_array('create', $template['type'])) selected @endif>新增</option>
                                    <option value="edit" @if(in_array('edit', $template['type'])) selected @endif>编辑</option>
                                    <option value="show" @if(in_array('show', $template['type'])) selected @endif>显示</option>
                                    <option value="print" @if(in_array('print', $template['type'])) selected @endif>打印</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="set_col">权限</label>
                            <div class="form-text">
                                {{App\Support\Dialog::search($template, 'id=receive_id&name=receive_name&multi=1')}}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="set_col">打印方式</label>
                            <div class="form-text">
                                <select class="input-select2 form-control input-sm" id="print_type" name="print_type" data-width="100%">   
                                    <option value="html" @if($template['print_type'] == 'html') selected @endif>html</option>
                                    <option value="pdf" @if($template['print_type'] == 'pdf') selected @endif>pdf</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="set_col">客户端</label>
                            <div class="form-text">
                                <select multiple="multiple" class="input-select2 form-control input-sm" id="client" name="client[]" data-width="100%">
                                    <option value="web" @if(in_array('web', $template['client'])) selected @endif>web</option>
                                    <option value="app" @if(in_array('app', $template['client'])) selected @endif>app</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="set_col">宽度</label>
                            <div class="form-text">
                                <input type="text" id="width" name="width" value="{{$template['width']}}" class="form-control input-sm">
                            </div>
                        </div>

                        <input type="hidden" name="bill_id" id="bill_id" value="{{$bill_id}}">
                        <input type="hidden" name="id" id="template_id" value="{{$template['id']}}">
                    </form>
                </div>
            </div>

        </div>

    </div>
    
</div>
<div class="clearfix"></div>

</div>
</div>

@verbatim
@endverbatim

<style>
@media (min-width: 768px) {
    .col-sm-8 {
        padding-left: 5px;
        padding-right: 5px;
    }
    .col-left {
        padding-left: 0;
        padding-right: 5px;
    }
    .col-right {
        padding-left: 5px;
        padding-right: 0;
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

.title {
    padding: 10px;
    text-align: left;
}

.desc {
    display: none;
    color: #999;
}

.list-group-item {
    padding: 0;
    cursor: pointer;
    border-left: 0;
    border-right: 0;
}
.list-group-item:first-child {
    border-top: 0;
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

.droppedFields > .remove {
    display: none;
}
.droppedFields .active > .remove {
    background-color: #ddd;
    display: block;
    color: #fff;
    position: absolute;
    right: 5px;
    top: 5px;
    width: 19px;
    height: 19px;
    padding-left: 4px;
    line-height: 18px;
}
.droppedFields .active > .remove:hover {
    background-color: #ff0000;
}

.droppedFields .title {
    padding: 8px;
    padding-left: 29px;
}

.droppedFields .model-title {
    display: none;
}

.droppedFields > .move {
    display: none;
}

.droppedFields .active > .move {
    cursor: move;
    background-color: #23b7e5;
    display: block;
    color: #fff;
    position: absolute;
    left: 5px;
    top: 5px;
    width: 22px;
    height: 22px;
    padding-left: 4px;
    line-height: 20px;
}

.droppedFields .field {
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

.droppedFields .field {
    position: relative;
    min-width: 120px;
}

.droppedFields > .field:hover {
    border: 1px dashed #f6c483;
}

.droppedFields > .field.active {
    border: 1px dashed #23b7e5;
}

.droppedFields .fa {
    cursor: pointer;
    display: none;
}

.droppedFields > .field.active .fa {
    position: absolute;
    right: 5px;
    top: 5px;
}

.droppedFields .desc {
    display: none;
}

.droppedFields .desc-sublist {
    width: 100%;
    padding: 10px;
    min-height: 30px;
    border-top: #ddd solid 1px;
    white-space: nowrap;
    overflow-x: auto;
}

.droppedFields .desc-sublist .fld {
    white-space: normal;
    width: auto;
}

.droppedFields {
    min-height: 60px;
    background-color: #fff;
    padding: 10px;
}

.highlightDroppable {
    border: 1px dashed #f6c483;
    background: #fffdfa;
    text-align: center;
    color: #ccc;
    display: inline-block;
    width: 100%;
    margin-top: -1px;
    position: relative;
    margin: 1px;
    padding: 8px;
}

.field-attr .form-group label {
    width: 70px;
    float: left;
    vertical-align: middle;
    text-align: right;
    font-weight: normal;
    line-height: 28px;
}
.field-attr .form-group .form-text {
    margin-left: 80px;
}

</style>

<!--
@verbatim
<div id="app">
    <div class="dragDemo">
        <gdoo-draggable class="list-group"
            element="div"
            v-model="listLeft"
            :move="onMove"
            group="people"
            @start="isDragging=true"
            @end="isDragging=false">
            <div v-for="(item, key) in listLeft" :key="key">
                {{item.name}}-{{item.value}}
            </div>
        </gdoo-draggable>
        <gdoo-draggable class="list-group"
            element="div"
            v-model="listLeft1"
            :move="onMove"
            group="people"
            @start="isDragging=true"
            @end="isDragging=false">
            <div v-for="(item, key) in listLeft1" :key="key">
                {{item.name}}-{{item.value}}
            </div>
        </gdoo-draggable>
        <gdoo-draggable class="list-group"
            element="div"
            v-model="listRight"
            :move="onMove"
            group="people"
            @start="isDragging=true"
            @end="isDragging=false">
            <div v-for="(item,key) in listRight" :key="key">
                {{item.name}}-{{item.value}}
            </div>
        </gdooDraggable>
    </div>
</div>
@endverbatim

<script>
new Vue({
    el: '#app',
    data() {
        return {
            isDragging:false,
            listLeft:[{
                name:'数据一',
                value:'1'
            }, {
                name:'数据二',
                value:'2'
            }, {
                name:'数据三',
                value:'3'
            }, {
                name:'数据四',
                value:'4'
            }, {
                name:'数据五',
                value:'5'
            }],
            listLeft1:[{
                name:'布局',
                value:'1'
            }, {
                name:'数据二',
                value:'2'
            }],
            listRight:[]
        }
    },
    methods: {
        onMove({relatedContext, draggedContext}) {
            const relatedElement = relatedContext.element;
            const draggedElement = draggedContext.element;
            return (
                (!relatedElement || !relatedElement.fixed) && !draggedElement.fixed
            );
        },
    }
});
</script>
 
<style scoped>
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
-->

<script>

var fields = '{{$template["tpl"]}}';
fields = fields ? JSON.parse(fields) : {};

var activeField = null;

var droppedFieldsBox = null;

function setCol(v) {
    activeField.attr('data-col', v);
    activeField.css({ width: 'calc(' + (v / 12 * 100) + '% - 2px'});
}

function setField(f, v) {
    if (f == 'title') {
        if (v) {
            activeField.find('.title').text(v);
        }
    }
    activeField.attr('data-' + f, v);
}

// 子表对话框
gdoo.event.set('role_id', {
    onSelect(row) {
        var role_id = $('#role_id').val();
        var role_name = $('#role_id_text').val();
        setField('role_id', role_id);
        setField('role_name', role_name);
        return true;
    }
});

var sortableOptions = {
    opacity: 0.4,
    delay: 50,
    cursor: "move",
    handle: ".move",
    placeholder: "highlightDroppable ui-corner-all",
    forcePlaceholderSize: true,
    connectWith: '.droppedFields,.desc-sublist',
    stop: function (event, ui) {
        var type = ui.item.hasClass('component');
        if (type == undefined) {
            ui.item.attr('component', 1);
            ui.item.css({width:'100%'});
        }
        ui.item.trigger("click");

        if(ui.item.data('type') == 1) {
            droppedFieldsBox.find('.desc-sublist:not(.ui-sortable)').sortable(sortableOptions);
        }
    }, 
    start: function (event, ui) {
        ui.item.removeClass('list-group-item');
        var h = $(this).find(".highlightDroppable");
        h.outerWidth(ui.item[0].style.width);
        h.outerHeight(ui.item[0].style.height);
        h.html('拖放控件到这里');
    }, out: function (event, ui) {
    }
}

function editDialog(options) {
    var defaultOptions = {
        title: '',
        html: '<div class="panel-body"><div class="form-group"><label>名字</label><input type="text" class="form-control input-sm" id="group_title"></div><div class="form-group"><label>类型</label><select class="form-control input-sm" id="group_type"><option value="panel">panel</option><option value="tabs">tabs</option></select></div><div class="form-group"><label>边框</label><select class="form-control input-sm" id="group_border"><option value="1">有</option><option value="0">无</option></select></div></div>',
        buttons: [{
            class: 'btn-default',
            text: '<i class="fa fa-remove"></i> 取消',
            click: function () {
                $(this).dialog('close');
            }
        },{
            class: 'btn-info',
            text: '<i class="fa fa-check"></i> 提交',
            click: function () {
                if(typeof options.onSubmit === 'function') {
                    options.onSubmit.call(this);
                }
            }
        }]
    };

    options = $.extend(defaultOptions, options);
    $.dialog(options);
}

$(function() {

    droppedFieldsBox = $('#droppedFieldsBox');

    // 添加表单组
    $("#add-form-group").on('click', function() {
        editDialog({
            title: '添加表单组', 
            onSubmit: function() {
                var title = $(this).find('#group_title').val();
                var type = $(this).find('#group_type').val();
                var border = $(this).find('#group_border').val();
                
                var size = $('.droppedFields').size() + 1;
                
                droppedFieldsBox.append('<div class="panel b-a"><div class="panel-heading b-b"><span class="pull-right"><i class="fa fa-fw fa-pencil"></i><i class="fa fa-fw fa-remove"></i></span><span class="label bg-light panel-type">' + type + '</span> <span class="panel-title">' + title + '</span></div><div data-type="' + type + '" data-border="'+border+'" data-title="' + title + '" data-column="' + size + '" id="selected-column-' + size + '" class="droppedFields"></div></div>');
                $('.droppedFields:not(.ui-sortable)').sortable(sortableOptions);
                (this).dialog('close');
            }
        });
    });

    // 编辑表单组
    droppedFieldsBox.on('click', '.panel-heading .fa-pencil', function() {

        var heading = $(this).closest('.panel');
        var dropped = heading.find('.droppedFields');

        editDialog({
            title: '编辑表单组',
            onShow: function() {
                var me = this;
                me.html(me.options['html']);
                $(this).find('#group_title').val(dropped.attr('data-title'));
                $(this).find('#group_type').val(dropped.attr('data-type'));
                $(this).find('#group_border').val(dropped.attr('data-border'));
            },
            onSubmit: function() {
                var title = $(this).find('#group_title').val();
                var type = $(this).find('#group_type').val();
                var border = $(this).find('#group_border').val();
                dropped.attr('data-title', title);
                dropped.attr('data-type', type);
                dropped.attr('data-border', border);

                heading.find('.panel-title').text(title);
                heading.find('.panel-type').text(type);
                heading.find('.panel-border').text(border);

                $(this).dialog('close');
            }
        });

    });

    // 删除表单组
    droppedFieldsBox.on('click', '.panel-heading .fa-remove', function() {
        $(this).closest('.panel').remove();
    });

    // 删除字段
    droppedFieldsBox.on('click', '.droppedFields .fa-remove', function() {
        $(this).closest('.field').remove();
    });

    // 点击字段
    droppedFieldsBox.on('click', '.droppedFields .field', function(e) {

        e.stopPropagation();

        var me = $(this);
        activeField = me;

        $(document).find('.droppedFields .field').removeClass('active');
        me.addClass('active');
        var data = me.data();

        for (const key in data) {
            if (key == 'sortableItem') {
                continue;
            }
            var v = me.attr('data-' + key) || '';
            var $attr = $('#fieldAttribute');
            $attr.find('#set_' + key).val(v);
            if (key == 'role_name') {
                $attr.find('#role_id_text').val(v || '');
            } else {
                $attr.find('#' + key).val(v || '');
            }
        }
    });

    $('.fld').draggable({
        connectToSortable: '.droppedFields',
        helper: "clone",
        revert: "invalid",
        start: function (event, ui) {
            var width = ui.helper.parent().outerWidth();
            ui.helper.outerWidth(width);
        }
    });

    // 初始化字段
    $.each(fields, function(k, form_group) {

        var type = form_group.type;
        var size = form_group.column;
        var title = form_group.title || '';
        var border = form_group.border;
        var _fields = Array();

        form_group.fields = form_group.fields || [];

        $.each(form_group.fields, function(k, v) {
            var w = 'calc(' + (v.col / 12 * 100) + '% - 2px)';
            if (v.type == 0) {
                _fields.push('<div class="fld field" ' + attrJoin(v) + ' style="display:inline-block;width: ' + w + ';" title="'+ v.name +'"><div class="title">' + v.name + '</div><div class="desc"></div><i class="move fa fa-w fa-arrows"></i><i class="remove fa fa-w fa-remove" title="删除"></i></div>');
            } else {
                var _subs = [];
                v.fields = v.fields || [];
                $.each(v.fields, function(k, vv) {
                    _subs.push('<div class="fld field" ' + attrJoin(vv, true) + ' title="'+ vv.name +'"><div class="title">' + vv.name + '</div><i class="move fa fa-w fa-arrows"></i><i class="remove fa fa-w fa-remove" title="删除"></i></div>');
                });
                _fields.push('<div class="fld field" ' + attrJoin(v) + ' style="display:inline-block;width: ' + w + ';" title="'+ v.name +'"><div class="title"> ' + v.name + '</div><i class="move fa fa-w fa-arrows"></i><i class="remove fa fa-w fa-remove" title="删除"></i><div class="desc-sublist">' + _subs.join('') + '</div></div>');
                
            }
        
        });

        droppedFieldsBox.append('<div class="panel b-a"><div class="panel-heading b-b"><span class="pull-right"><i class="fa fa-fw fa-pencil"></i><i class="fa fa-fw fa-remove"></i></span><span class="label bg-light panel-type">' + type + '</span> <span class="panel-title">' + title + '</span></div><div data-type="' + type + '" data-border="'+border+'" data-title="' + title + '" data-column="' + size + '" id="selected-column-' + size + '" class="droppedFields">' + _fields.join('') + '</div></div>');

    });

    $('.droppedFields:not(.ui-sortable)').sortable(sortableOptions);
    droppedFieldsBox.find('.desc-sublist:not(.ui-sortable)').sortable(sortableOptions);

    $('.droppedFieldsBox').sortable({
        handle: '.panel-heading',
        opacity: 0.6,
        delay: 50,
        cursor: 'move',
        start: function (event, ui) {
        },
        update: function(event, ui) {
        }
    });

});

function attrJoin(data, subfld) {
    var fields = ['col', 'css', 'table', 'field', 'content', 'hidden', 'custom', 'title', 'readonly', 'type', 'width', 'hide_title', 'role_id', 'role_name'];
    var attr = [];
    for (let i = 0; i < fields.length; i++) {
        var field = fields[i];
        if (subfld == true && field == 'type') {
            continue;
        }

        var v = data[field];
        if (field == 'hidden' || field == 'readonly' || field == 'type' || field == 'hide_title' || field == 'custom') {
            v = v == undefined ? 0 : v;
        }

        if (field == 'content' || field == 'role_id' || field == 'role_name' || field == 'title') {
            v = v == undefined ? '' : v;
        }

        if (field == 'width') {
            v = setInt(v);
        }
        attr.push('data-' + field + '="' + v + '"');
    }
    return attr.join(' ');
}

function setInt(v) {
    var v = parseInt(v);
    return v > 0 ? v : '';
}

function preview() {

    console.log(getColumns());
    return;

    var dialogContent, i, j;
    if (columns.length > 0) {
        var divWidth = 100 / columns.length;
        dialogContent = "<div>";
        for (i = 0; i < columns.length; i++) {
            dialogContent += "<div style='float:left;width=" + divWidth + "%;'>";
            dialogContent += "<ul><li><b>Column " + (i + 1) + "</b></li>";
            for (j = 0; j < columns[i].length; j++) {
                var obj = columns[i][j];
                dialogContent += "<li>" + obj.label + "</li>";
            }
            dialogContent += "</ul></div>";
        }
        dialogContent += "</div>";
    } else {
        dialogContent = '<div>Nothing to preview</div>';
    }

    $(dialogContent).dialog({
        modal: true,
        width: 500,
        height: 400,
        buttons: {
            Ok: function () {
                $(this).dialog("close");
            }
        }
    });
}

function getColumns() {
    var res = [];
    $.each($(".droppedFields"), function(i, v) {

        var groupTitle = $(v).attr('data-title');
        var groupType = $(v).attr('data-type');
        var groupBorder = $(v).attr('data-border');
        var groupColumn = $(v).attr('data-column');

        var fields = $(v).children('.field');

        var columns = [];

        if (fields.length > 0) {

            $.each(fields, function(k, v) {

                var me = $(v);
                var type = me.data('type');

                var _column = getColumn(me);

                if(type == 1) {

                    var _fields = $(v).children('.desc-sublist').children('.field');

                    if (_fields.length > 0) {
                        var __column = [];
                        $.each(_fields, function(k, _v) {
                            var _me = $(_v);
                            __column.push(getColumn(_me));
                        });
                        _column.fields = __column;
                    }
                }

                columns.push(_column);
                
            });
            res.push({title:groupTitle, border: groupBorder, type: groupType, column:groupColumn, fields:columns});
        }
    });
    return res;
}

function getColumn(me) {
    var column = {};
    me.find('.model-title').remove();

    column.field = me.attr('data-field');
    column.css = me.attr('data-css');
    column.hidden = me.attr('data-hidden');
    column.width = me.attr('data-width');
    column.readonly = me.attr('data-readonly');
    column.hide_title = me.attr('data-hide_title');
    column.type = me.attr('data-type');
    column.table = me.attr('data-table');
    column.title = me.attr('data-title');
    column.role_id = me.attr('data-role_id');
    column.role_name = me.attr('data-role_name');
    column.custom = me.attr('data-custom');
    column.col = me.attr('data-col');
    column.content = me.attr('data-content');
    column.name = me.children(".title").text().trim();
    return column;
}

function submit() {
    var columns = getColumns();
    var data = $('#myform').serialize() + '&' + $.param({columns: columns});
    $.post('{{url()}}', data, function (res) {
        if (res.status) {
            toastrSuccess(res.data);
            location.href = res.url;
        } else {
            toastrError(res.data);
        }
    }, 'json');
}
</script>
