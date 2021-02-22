<div class="panel">

    <div class="wrapper-sm">
        <a class="btn btn-default btn-sm" href="{{url('index',['bill_id'=>$bill_id])}}"><i class="fa fa-reply"></i> 返回</a>
        <!--
        <a onclick="preview()" class="btn btn-default">预览</a>
        -->
        <a onclick="submit()" class="btn btn-sm btn-success">
            <i class="fa fa-check"></i> 提交
        </a>

    </div>

    <div class="wrapper-sm b-t gdoo-form-designer">

        @verbatim
        <div id="vueApp">

            <div class="col-sm-2 col-left m-b-sm">
                <div class="panel b-a panel-default tabs-box">

                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab1" data-toggle="tab">组件列表</a></li>
                        <li class=""><a href="#tab2" data-toggle="tab">字段列表</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">

                            <!--
                            <draggable :list="fields" class="list-group" :sort="false" :group="{name:'gdooComponent', pull:true, put:false}" ghost-class="ghostClass">
                                <div class="list-group-item" v-for="(item, key) in fields" :key="key">
                                    <div class="title">{{item.name}}</div>
                                </div>
                            </draggable>
                            -->
                            
                            <div class="af-left-group-controller af-over-y">
                                <draggable :list="items" class="af-left-group"
                                    :sort="false"
                                    item-key="name"
                                    :group="{name:'gdooComponent', pull:'clone', put:false}"
                                    :clone="onClone"
                                    :move="onMove"
                                    ghost-class="ghostClass">
                                    <template #item="{element}">
                                        <div class="af-left-group-item">
                                            <div class="title">{{element.name}}</div>
                                        </div>
                                    </template>
                                </draggable>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab2">
                            <div class="af-left-group-controller af-over-y">
                                <ul class="af-left-group">
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
            </div>

            <div class="col-sm-8 col-center m-b-sm">
                <div class="droppedFieldsBox" id="droppedFieldsBox">
                    <gdoo-form-designer :parent_id="0" :items="state.views" class="af-items af-over-y"></gdoo-form-designer>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="col-sm-2 col-right">
                <div class="panel b-a panel-default tabs-box">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#model-attr" data-toggle="tab">字段属性</a></li>
                        <li class=""><a href="#model-template" data-toggle="tab">模板属性</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="model-attr">

                            <div class="field-attr af-over-y" id="field_attr">

                                <div class="form-group" v-show="state.activeItem.name != undefined">
                                    <label>标签名称</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.name">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field != undefined">
                                    <label>字段名</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.field">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field_type != undefined">
                                    <label>字段类型</label>
                                    <div class="form-text">
                                        <select @change="changeFieldType" class="form-control input-sm" v-model="state.activeItem.field_type">
                                            <option value=""></option>
                                            <option :value="item.name" v-for="item in state.fieldTypes">{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field_type != '' && state.activeItem.field_length != undefined">
                                    <label>字段长度</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.field_length">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field_type != '' && state.activeItem.field_not_null != undefined">
                                    <label>字段not null</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.field_not_null">
                                            <option value="0">否</option>
                                            <option value="1">是</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field_not_null == 1 && state.activeItem.field_type != '' && state.activeItem.field_value != undefined">
                                    <label>字段默认值</label>
                                    <div class="form-text">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-btn">
                                                <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu" id="default-menu" @click="fieldValueClick">
                                                    <li><a href="javascript:;">&nbsp;</a></li>
                                                    <li><a href="javascript:;">NULL</a></li>
                                                    <li><a href="javascript:;">Empty String</a></li>
                                                </ul>
                                            </div>
                                            <input type="text" class="form-control input-sm" v-model="state.activeItem.field_value" :readonly="(state.activeItem.field_value == 'NULL' || state.activeItem.field_value == 'Empty String') ? 'readonly' : false" />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_url == '' && state.activeItem.data_enum != undefined">
                                    <label>数据枚举</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.data_enum">
                                            <option value=""></option>
                                            <option :value="item.value" v-for="item in state.dataEnums">{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_enum == '' && state.activeItem.data_url == '' && state.activeItem.data_option != undefined">
                                    <label>
                                        数据选项
                                        <a class="hinted" href="javascript:;" title="格式：名称1|值1(回车换行)"><i class="fa fa-question-circle"></i></a>
                                    </label>
                                    <div class="form-text">
                                        <textarea class="form-control input-sm" v-model="state.activeItem.data_option"></textarea>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_url != undefined">
                                    <label>数据源</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.data_url">
                                            <option value=""></option>
                                            <option :value="item.table" v-for="item in state.dataUrls">{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_url != '' && state.activeItem.data_query != undefined">
                                    <label>
                                        查询参数
                                        <a class="hinted" href="javascript:;" title="格式：name={name}"><i class="fa fa-question-circle"></i></a>
                                    </label>
                                    <div class="form-text">
                                        <textarea class="form-control input-sm" v-model="state.activeItem.data_query"></textarea>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_multiple != undefined">
                                    <label>数据多选</label>
                                    <div class="form-text">
                                        <label style="text-align:left;float:none;"><input type="checkbox" value="1" v-model="state.activeItem.data_multiple" style="vertical-align:middle;margin-top:0 !important;"> 是 </label>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_type != undefined">
                                    <label>映射数据</label>
                                    <div class="form-text">
                                        <select @change="changeDataType" class="form-control input-sm" v-model="state.activeItem.data_type">
                                            <option value=""></option>
                                            <option :value="item.table" v-for="item in state.dataTypes">{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_type != '' && state.activeItem.data_field != undefined">
                                    <label>映射字段</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.data_field">
                                            <option :value="item.field" v-for="item in state.dataFields">{{item.name}}({{item.field}})</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_type != '' && state.activeItem.data_link != undefined">
                                    <label>关联字段</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.data_link">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.data_type != '' && state.activeItem.data_relation != undefined"> 
                                    <label>关联关系</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.data_relation">
                                            <option value=""></option>
                                            <option value="one2one">一对一</option>
                                            <option value="one2many">一对多</option>
                                            <option value="many2many">多对多</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.value != undefined">
                                    <label>默认值</label>
                                    <div class="form-text">
                                        <textarea class="form-control input-sm" v-model="state.activeItem.value"></textarea>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.grid != undefined">
                                    <label>布局栅格</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.grid">
                                            <option :value="item" v-for="item in [12,11,10,9,8,7,6,5,4,3,2,1]">{{item}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.width != undefined">
                                    <label>布局宽度</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.width">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.label_width != undefined">
                                    <label>标签宽度</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.label_width">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.field_width != undefined">
                                    <label>组件宽度</label>
                                    <div class="form-text">
                                        <input class="form-control input-sm" v-model="state.activeItem.field_width">
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.hide_name != undefined">
                                    <label>隐藏标签</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.hide_name">
                                            <option value="0">否</option>
                                            <option value="1">是</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.hide_field != undefined">
                                    <label>隐藏组件</label>
                                    <div class="form-text">
                                        <select class="form-control input-sm" v-model="state.activeItem.hide_field">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-show="state.activeItem.exclude_auth_id != undefined">
                                    <label>排除权限</label>
                                    <div class="form-text">
                                        <div class="select-group input-group" style="width:100%;">
                                            <input class="form-control input-inline input-sm" style="cursor:pointer;" readonly="readonly" data-multi="1" data-name="exclude_auth_name" data-title="角色" data-url="user/role/dialog" data-id="exclude_auth_id" data-toggle="dialog-view" id="exclude_auth_id_text" v-model="state.activeItem.exclude_auth_name" />
                                            <div class="input-group-btn">
                                                <a data-toggle="dialog-clear" data-id="exclude_auth_id" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>
                                            </div>
                                            <input type="hidden" id="exclude_auth_id" v-model="state.activeItem.exclude_auth_id">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" v-model="state.activeItem.item_id">
                            </div>

                        </div>

                        <div class="tab-pane" id="model-template">

                            <div class="field-attr af-over-y">
                                <form method="post" id="myform" name="myform">
                                    <div class="form-group">
                                        <label for="set_col">视图名称 <span class="red">*</span></label>
                                        <div class="form-text">
                                            <input type="text" id="name" name="name" v-model="state.template.name" class="form-control input-sm">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="set_col">视图类型 <span class="red">*</span></label>
                                        <div class="form-text">
                                            <select multiple="multiple" class="input-select2 form-control input-sm" id="type" v-model="state.template.type" name="type[]" data-width="100%">
                                                <option value="create">新增</option>
                                                <option value="edit">编辑</option>
                                                <option value="show">显示</option>
                                                <option value="print">打印</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="set_col">权限范围 <span class="red">*</span></label>
                                        <div class="form-text">
                                            <div class="select-group input-group">
                                                <input class="form-control input-sm" style="width:100%;cursor:pointer;" readonly="readonly" data-toggle="dialog-view" data-prefix="1" data-multi="1" data-readonly="0" data-width="100%" data-title="" data-url="index/api/dialog" data-id="receive_id" data-name="receive_name" name="receive_name" value="全体人员" id="receive_id_text">
                                                <div class="input-group-btn">
                                                    <a data-toggle="dialog-clear" data-id="receive_id" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>
                                                </div>
                                                <input type="hidden" id="receive_id" name="receive_id" value="all">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="set_col">客户端 <span class="red">*</span></label>
                                        <div class="form-text">
                                            <select multiple="multiple" class="input-select2 form-control input-sm" v-model="state.template.client" id="client" name="client[]" data-width="100%">
                                                <option value="web">web</option>
                                                <option value="app">app</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="set_col">宽度</label>
                                        <div class="form-text">
                                            <input type="text" id="width" name="width" v-model="state.template.width" class="form-control input-sm">
                                        </div>
                                    </div>

                                    <input type="hidden" name="bill_id" id="bill_id" v-model="state.template.bill_id">
                                    <input type="hidden" name="id" id="template_id" v-model="state.template.id">

                                </form>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
            @endverbatim

        </div>
        <div class="clearfix"></div>

    </div>
</div>

<script>
let template = JSON.parse('{{json_encode($template, JSON_UNESCAPED_UNICODE)}}');
let views = JSON.parse('{{json_encode($views, JSON_UNESCAPED_UNICODE)}}');
let dataTypes = JSON.parse('{{json_encode($types, JSON_UNESCAPED_UNICODE)}}');
let itemMaxId = 0;
let fieldMaxId = 0;

function getItemMaxId() {
    return ++itemMaxId;
}

function getFieldMaxId() {
    return 'item' + strLeftPad(++fieldMaxId, 3);
}

function strLeftPad(num, n) {
    var len = num.toString().length;
    while(len < n) {
        num = "0" + num;
        len ++;
    }
    return num;
}

const vueApp = Vue.createApp({
    components: {
        draggable: GdooVueComponents.draggable,
        'gdoo-form-designer': GdooVueComponents.gdooFormDesigner,
    },
    setup() {
        let fieldTypes = [
            {name:'BIGINT', langth:'20'},
            {name:'INT', langth:'11'},
            {name:'SMALLINT', langth:'5'},
            {name:'TINYINT', langth:'3'},
            {name:'DECIMAL', langth:'10,2'},
            {name:'DATE', langth:''},
            {name:'DATETIME', langth:''},
            {name:'TIME', langth:''},
            {name:'CHAR', langth:'30'},
            {name:'VARCHAR', langth:'100'},
            {name:'TEXT', langth:''},
        ];

        let views = [{
            name: '子表',
            grid: 12,
            table: 1,
            type: 'component',
            children: []
        },{
            name: '布局',
            grid: 12,
            type: 'component',
            children: [{
                name: '文本1',
                grid: 12,
                type: 'text',
                value: '',

                field: '',
                field_type: 'VARCHAR',
                field_length: '255',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',
                
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '文本2',
                grid: 12,
                type: 'text',
                value: '',

                field: '',
                field_type: 'VARCHAR',
                field_length: '255',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',
                
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            }],
        }];

        // 初始化视图itemId
        function setupViews(views, item_id) {
            views.forEach(view => {
                view.item_id = getItemMaxId();
                view.parent_id = item_id;
                if (view.children && view.children.length > 0) {
                    setupViews(view.children, view.item_id);
                }
            });
        }
        setupViews(views, 0);

        let state = Vue.reactive({
            activeItem: {},
            fieldTypes: fieldTypes,
            dataTypes: dataTypes,
            dataFields: [],
            dataEnums: [],
            template: template,
            views: views,
            fields: {},
        });
        Vue.provide('activeItem', state.activeItem);

        function changeFieldType() {
            fieldTypes.forEach(fieldType => {
                if (state.activeItem.field_type == fieldType.name) {
                    state.activeItem.field_length = fieldType.langth;
                }
            });
        }

        // 获取映射字段列表
        function changeDataType() {
            $.getJSON(url('model/field/getColumns'), {table: state.activeItem.data_type}, function(res) {
                state.dataFields = res.data;
            });
        }

        // 获取枚举列表
        function getEnumList() {
            $.getJSON(url('model/field/getEnums'), function(data) {
                state.dataEnums = data;
            });
        }
        getEnumList();

        // 监听模板改变事件
        function watchViews(views) {
            views.forEach(view => {
                view = Vue.toRaw(view);
                if (view.type == "component") {
                    if (view.table == 1) {
                        state.fields.tables.push(view);
                    } else {
                        if (view.children && view.children.length > 0) {
                            watchViews(view.children);
                        }
                    }
                } else {
                    let master = {};
                    for (var key in view) {
                        if (key == 'children') {
                            continue;
                        }
                        master[key] = view[key];
                    }
                    state.fields.master.push(master);
                }
            });
        }

        Vue.watch(state.views, (oldViews, newViews) => {
            state.fields.master = [];
            state.fields.tables = [];
            watchViews(newViews);
        });

        return {state, changeFieldType, changeDataType};
    },
    data() {
        return {
            items: [{
                name: '布局',
                type: 'component',
                grid: 12,
                children: []
            },{
                name: '子表',
                type: 'component',
                table: 1,
                grid: 12,
                children: []
            },{
                name: '单行文本',
                grid: 12,
                type: 'text',
                value: '',

                field: '',
                field_type: 'VARCHAR',
                field_length: '100',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            }, {
                name: '多行文本',
                grid: 12,
                type: 'textarea',
                value: '',
                
                field: '',
                field_type: 'VARCHAR',
                field_length: '255',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            }, {
                name: '密码',

                field: '',
                field_type: 'VARCHAR',
                field_length: '60',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                value: '',
                type: 'password',
                grid: 12,
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            }, {
                name: '编辑器',
                
                field: '',
                field_type: 'TEXT',
                field_length: '',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'editor',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '下拉菜单',

                field: '',
                field_type: 'VARCHAR',
                field_length: '100',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_enum: '',
                data_url: '',
                data_query: '',
                data_option: '',
                data_multiple: false,

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'select',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '对话框',

                field: '',
                field_type: 'VARCHAR',
                field_length: '100',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_url: '',
                data_query: '',
                data_option: '',
                data_multiple: false,

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'dialog',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '单选框组',

                field: '',
                field_type: 'VARCHAR',
                field_length: '100',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_enum: '',
                data_url: '',
                data_query: '',
                data_option: '',
                data_multiple: false,

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'select',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '多选框组',

                field: '',
                field_type: 'VARCHAR',
                field_length: '100',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_enum: '',
                data_url: '',
                data_query: '',
                data_option: '',
                data_multiple: false,

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'select',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '日期选择',

                field: '',
                field_type: 'DATE',
                field_length: '',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'date',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '日期时间选择',

                field: '',
                field_type: 'DATATIME',
                field_length: '',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'date',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '时间选择',

                field: '',
                field_type: 'VARCHAR',
                field_length: '30',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',

                type: 'date',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '上传',
                
                field: '',
                field_type: 'VARCHAR',
                field_length: '255',
                field_not_null: 0,
                field_index: '',
                field_value: '',

                data_type: '',
                data_field: '',
                data_link: '',
                data_relation: '',
                
                type: 'upload',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '审核意见',
                field: '',
                type: 'audit',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '会签意见',
                field: '',
                type: 'feedback',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            },{
                name: '流程记录',
                field: '',
                type: 'flowlog',
                grid: 12,
                value: '',
                label_width: '',
                field_width: '100%',
                hide_field: 0,
                hide_name: 0,
                exclude_auth_id:'',
                exclude_auth_name:'',
            }]
        }
    },
    methods: {
        fieldValueClick(e) {
            vm.state.activeItem.field_value = e.target.innerText;
        },
        onMove(item) {
            let me = this;
            let oldItem = item.draggedContext.element;
            let newItem = item.relatedContext.element;
            // 获取容器dom
            let node = item.to.offsetParent;
            if (oldItem.type == 'component') {
                var item_table = node.getAttribute('item-table');
                // 不能把容器拖入子表容器
                if (item_table == 1) {
                    return false;
                }
            } else {
                return true;
            }
        },
        onClone(item) {
            var me = this;
            var data = $.extend(true, {}, Vue.toRaw(item));
            return data;
        }
    }
});
var vm = vueApp.mount('#vueApp');

$(function() {

    // 子表对话框
    gdoo.event.set('exclude_auth_id', {
        clear(row) {
            vm.state.activeItem.exclude_auth_id = '';
            vm.state.activeItem.exclude_auth_name = '';
        },
        onSelect(row) {
            var auth_id = $('#exclude_auth_id').val();
            var auth_name = $('#exclude_auth_id_text').val();
            vm.state.activeItem.exclude_auth_id = auth_id;
            vm.state.activeItem.exclude_auth_name = auth_name;
            return true;
        }
    });

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

})
</script>

<style>
.gdoo-form-designer {
    padding: 10px 5px;
    padding-bottom: 0;
}
.col-left {
    padding-left: 5px;
    padding-right: 5px;
}
.col-center {
    padding-left: 5px;
    padding-right: 5px;
}
.col-right {
    padding-left: 5px;
    padding-right: 5px;
}
@media (min-width: 768px) {
    .af-left-group-controller,
    .field-attr {
        height:calc(100vh - 135px);
    }
}

.af-over-y {
    overflow-x: hidden;
    overflow-y: auto;
}
.af-over-y::-webkit-scrollbar {
    width: 7px;
    height: 4px;
    background-color: #F5F5F5;  
}
.af-over-y::-webkit-scrollbar-track {
    box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    background: #fff;
}
.af-over-y::-webkit-scrollbar-thumb {
    border-radius: 3px;
    box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color:rgba(158, 158, 158, 0.7);
}  
.af-over-y::-webkit-scrollbar-thumb:hover {
    border-radius: 3px;
    box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color:rgba(158, 158, 158, 1);
}

.af-over-x {
    overflow-y: hidden;
    overflow-x: auto;
}
.af-over-x::-webkit-scrollbar {
    width: 4px;
    height: 7px;
    background-color: #F5F5F5;  
} 
.af-over-x::-webkit-scrollbar-track {
    box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    background: #fff;
}
.af-over-x::-webkit-scrollbar-thumb {
    border-radius: 3px;
    box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color:rgba(158, 158, 158, 0.7);
}  
.af-over-x::-webkit-scrollbar-thumb:hover {
    border-radius: 3px;
    box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color:rgba(158, 158, 158, 1);
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

.af-left-group {
    padding: 10px 5px 5px 10px;
}

.af-left-group-item {
    background: #f6f7ff;
    border: 1px solid transparent;
    width:calc(50% - 5px);
    float: left;
    margin-right: 5px;
    margin-bottom: 5px;
    cursor: move;
    padding: 6px;
}

.af-left-group-item:hover {
    color: #409eff;
    border: 1px dashed #409eff;
}

.af-items {
    padding: 10px;
    padding-right: 5px;
    padding-bottom: 5px;
    border: 1px solid #dee5e7;
    height:calc(100vh - 91px);
    box-shadow: 0 1px 1px rgba(0, 0, 0, .05);
    width: 100%;
}

.field-attr {
    padding: 10px;
    padding-bottom: 0;
}
.field-attr .form-group {
    margin-bottom: 10px;
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

.input-group-btn:first-child > .btn,
.input-group-btn:first-child > .btn-group {
    margin-right: -2px;
}

</style>