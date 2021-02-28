<form class="form-horizontal form-controller" method="post" action="{{url()}}" id="flow-field" name="flow_field">

    <div class="form-group">
        <div class="col-sm-3 control-label" for="model_id"><span style="color:red;">*</span> 模型名</div>
        <div class="col-sm-9 control-text b-t">
            <select class="form-control input-sm" name="model_id" id="model_id">
                @foreach($models as $v)
                    <option value="{{$v['id']}}" @if($row['model_id'] == $v['id']) selected @endif>{{$v['name']}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="name">
            <span style="color:red;">*</span>
            字段别名
            <a class="hinted" href="javascript:;" title="例如客户名称"><i class="fa fa-question-circle"></i></a>
        </label>
        <div class="col-sm-9 control-text">
            <input class="form-control input-sm" type="text" name="name" value="{{$row['name']}}" id="name" onblur="app.pinyin('name','field');" required="required" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="field"><span style="color:red;">*</span> 
            字段名
            <a class="hinted" href="javascript:;" title="只能由英文字母、数字和下划线组成，并且仅能字母开头"><i class="fa fa-question-circle"></i></a>
        </label>
        <div class="col-sm-9 control-text">
            <input class="form-control input-sm" type="text" id="field" name="field" value="{{$row['field']}}" required="required" />
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="form_type"><span style="color:red;">*</span> 编辑类别</label>
        <div class="col-sm-9 control-text">
            <select class="form-control input-sm" name="form_type" id="form_type" required="required">
                <option value=""> - </option>
                {{:$titles = Gdoo\Model\Services\FieldService::title()}}
                @foreach($titles as $id => $title)
                    <option value="{{$id}}" @if($row['form_type'] == $id) selected @endif>{{$title}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div id="content" class="b-t">
        @if($row['id'])
        {{Gdoo\Model\Services\FieldService::{'form_'.$row['form_type']}($row['setting'], $row['model_id'])}}
        @endif
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="data_format">格式数据</label>
        <div class="col-sm-9 control-text">
            <select class="form-control input-sm" name="data_format">
                <option value=""> - </option>
                <option value="text" @if($row['data_format'] == 'text') selected @endif>文本</option>
                <option value="number" @if($row['data_format'] == 'number') selected @endif>数字</option>
                <option value="money" @if($row['data_format'] == 'money') selected @endif>金额</option>
            </select>
        </div>
    </div>

    @verbatim
        <div id="app"> 
            <div class="form-group">
                <label class="col-sm-3 control-label" for="data_type">绑定数据</label>
                <div class="col-sm-9 control-text">
                    <select class="form-control input-sm" v-model="data_type" name="data_type" id="data_type" @change="changeDataType">
                        <option value=""> - </option>
                        <option v-for="item in dataType" :value="item.table">{{item.name}}</option>
                    </select>
                </div>
            </div>

        <div v-show="data_type">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="data_field">映射字段</label>
                <div class="col-sm-9 control-text">
                    <select class="form-control input-sm" v-model="data_field" name="data_field" id="data_field">
                        <option value=""> - </option>
                        <option v-for="item in dataField" :value="item.key">{{item.name}}({{item.field}})</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="data_link">关联字段</label>
                <div class="col-sm-9 control-text">
                    <select class="form-control input-sm" v-model="data_link" name="data_link" id="data_link">
                        <option value=""> - </option>
                        <option v-for="item in dataLink" :value="item.field">{{item.name}}({{item.field}})</option>
                    </select>
                </div>
            </div>

            <!--
            <div class="form-group">
                <label class="col-sm-3 control-label" for="data_relation">关联关系</label>
                <div class="col-sm-9 control-text">
                    <select class="form-control input-sm" v-model="data_relation" name="data_relation" id="data_relation">
                        <option value=""> - </option>
                        <option v-for="item in dataRelation" :value="item.field">{{item.name}}({{item.field}})</option>
                    </select>
                </div>
            </div>
            -->
        </div>
        @endverbatim

        <div class="form-group">
            <label class="col-sm-3 control-label" for="type">
                字段类型
                <a class="hinted" href="javascript:;" title="注意修改类型可能导致数据丢失，如果不建立字段请留空"><i class="fa fa-question-circle"></i></a>
            </label>
            <div class="col-sm-9 control-text">
                <select class="form-control input-sm" name="type" onchange="setlength(this.value)" id="type">
                    <option value="">-</option>
                    @foreach($templates as $type => $template)
                        <option value="{{$type}}" @if($row['type'] == $type) selected @endif>{{$type}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    
        <div class="form-group">
            <label class="col-sm-3 control-label" for="length">
                字段长度
                <a class="hinted" href="javascript:;" title="注意长度值不能超界"><i class="fa fa-question-circle"></i></a>
            </label>
            <div class="col-sm-9 control-text">
                <input class="form-control input-sm" type="text" id="length" name="length" value="{{$row['length']}}">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label" for="length">不是NULL</label>
            <div class="col-sm-9 control-text">
                <select class="form-control input-sm" name="not_null">
                    <option value="0" @if($row['not_null'] == '0') selected @endif>否</option>
                    <option value="1" @if($row['not_null'] == '1') selected @endif>是</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label" for="length">字段默认值</label>
            <div class="col-sm-9 control-text">
                <div class="input-group input-group-sm">
                    <div class="input-group-btn">
                        <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
                            选择 <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" id="default-menu">
                            <li><a href="javascript:;">&nbsp;</a></li>
                            <li><a href="javascript:;">NULL</a></li>
                            <li><a href="javascript:;">Empty String</a></li>
                        </ul>
                    </div>
                    <input type="text" id="default" class="form-control input-sm" name="default" value="{{$row['default']}}" @if($row['default'] == 'NULL' || $row['default'] == 'Empty String') readonly="readonly" @endif />
                </div>
            </div>
        </div>
    
        <div class="form-group">
            <label class="col-sm-3 control-label" for="index">
                字段索引
                <a class="hinted" href="javascript:;" title="注意必须理解索引的概念"><i class="fa fa-question-circle"></i></a>
            </label>
            <div class="col-sm-9 control-text">
                <select class="form-control input-sm" name="index">
                    <option value=""> - </option>
                    <option value="INDEX" @if($row['index'] == 'INDEX') selected @endif>普通</option>
                    <option value="UNIQUE" @if($row['index'] == 'UNIQUE') selected @endif>唯一</option>
                    <option value="PRIMARY" @if($row['index'] == 'PRIMARY') selected @endif>主键</option>
                </select>
            </div>
        </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="tips">
            编辑提示
            <a class="hinted" href="javascript:;" title="显示在字段别名下方作为表单输入提示"><i class="fa fa-question-circle"></i></a>
        </label>
        <div class="col-sm-9 control-text">
            <input class="form-control input-sm" type="text" name="tips" value="{{$row['tips']}}">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="is_index">列表显示</label>
        <div class="col-sm-9 control-text">
            <label class="radio-inline"><input type="radio" @if($row['is_index'] == '0') checked @endif value="0" name="is_index">否</label>
            <label class="radio-inline"><input type="radio" @if($row['is_index'] == '1') checked @endif value="1" name="is_index">是</label>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-3 control-label" for="is_search">列表搜索</label>
        <div class="col-sm-9 control-text">
            <label class="radio-inline"><input type="radio" @if($row['is_search'] == '0') checked @endif value="0" name="is_search">否</label>
            <label class="radio-inline"><input type="radio" @if($row['is_search'] == '1') checked @endif value="1" name="is_search">是</label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="is_sort">字段菜单</label>
        <div class="col-sm-9 control-text">
            <label class="radio-inline"><input type="radio" @if($row['is_menu'] == '0') checked @endif value="0" name="is_menu">否</label>
            <label class="radio-inline"><input type="radio" @if($row['is_menu'] == '1') checked @endif value="1" name="is_menu">是</label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="is_sort">列表排序</label>
        <div class="col-sm-9 control-text">
            <label class="radio-inline"><input type="radio" @if($row['is_sort'] == '0') checked @endif value="0" name="is_sort">否</label>
            <label class="radio-inline"><input type="radio" @if($row['is_sort'] == '1') checked @endif value="1" name="is_sort">是</label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="is_import">允许导入</label>
        <div class="col-sm-9 control-text">
            <label class="radio-inline"><input type="radio" @if($row['is_import'] == '0') checked @endif value="0" name="is_import">否</label>
            <label class="radio-inline"><input type="radio" @if($row['is_import'] == '1') checked @endif value="1" name="is_import">是</label>
        </div>
    </div>

    <input name="id" type="hidden" value="{{$row['id']}}">
    <input name="parent_id" type="hidden" value="{{$parent_id}}">

</form>
</div>

<script type="text/javascript">
var types = JSON.parse('<?php echo json_encode($types, JSON_UNESCAPED_UNICODE); ?>') || {};
var fields = JSON.parse('<?php echo json_encode($fields, JSON_UNESCAPED_UNICODE); ?>') || {};

var model_id = '{{$row["model_id"]}}';
var data_link = '{{$row["data_link"]}}';
var data_type = '{{$row["data_type"]}}';
var data_field = '{{$row["data_field"]}}';

const vueData = {
  data() {
    return {
        data_relation: '',
        dataLink: fields,
        dataField: [],
        dataRelation: [
            {field: 'one2one', name: '一对一'},
            {field: 'one2many', name: '一对多'},
            {field: 'many2many', name: '多对多'}
        ],
        dataType: types,
        data_link: data_link,
        data_type: data_type,
        data_field: data_field,
    }
  },mounted() {
    this.changeDataType();
    $('#form_type').off('change').on('change', function() {
        var loading = layer.msg('数据提交中...', {
            icon: 16, 
            shade: 0.1,
            time: 1000 * 30
        });
        $.get("{{url('type')}}?type=" + this.value + '&model_id=' + model_id, function(data) {
            $("#content").html(data);
        }).complete(function() {
            layer.close(loading);
        });
    });
    $('#default-menu').on('click', 'a', function() {
        let value = $(this).text();
        $('#default').val(value);
        defaultReadonly(value);
    });
    },
    methods: {
        changeDataType: function() {
            var me = this;
            $.getJSON(url('model/field/getColumns'), {table: me.data_type}, function(res) {
                me.dataField = res.data;
            });
        }
    }
}
Vue.createApp(vueData).mount('#app');

function setlength(value) {
    var types = {{json_encode($templates)}};
    if (value) {
        var type = types[value];
        $('#length').val(types[value]['length']);
        $('#default').val(types[value]['default']);
        defaultReadonly(types[value]['default']);
    }
}

function defaultReadonly(value) {
    if (value == 'NULL' || value == 'Empty String') {
        $('#default').prop('readonly', true);
    } else {
        $('#default').prop('readonly', false);
    }
}

ajaxSubmit('flow-field');
</script>