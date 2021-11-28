<?php namespace Gdoo\Model\Controllers;

use DB;
use Auth;
use Request;
use Validator;

use App\Support\Module;

use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Form;

use Gdoo\Model\Services\FlowService;
use Gdoo\Model\Services\FieldService;
use Gdoo\Model\Services\ModuleService;

use Gdoo\Index\Controllers\DefaultController;
use Gdoo\System\Models\Option;

class FieldController extends DefaultController
{
    public $permission = ['getColumns', 'getEnums'];

    public function index()
    {
        $model_id = Request::get('model_id');

        if (Request::method() == 'POST') {
            $sorts = Request::get('sort');
            $i = 0;
            foreach ($sorts as $id) {
                Field::where('id', $id)->update(['sort' => $i]);
                $i ++;
            }
            return $this->json('恭喜你，操作成功。', true);
        }

        $master = Model::with(['fields' => function ($q) {
            $q->orderBy('sort', 'asc')
            ->orderBy('id', 'asc');
        }])->find($model_id);

        $sublist = Model::with(['fields' => function ($q) {
            $q->orderBy('sort', 'asc')
            ->orderBy('id', 'asc');
        }])->where('parent_id', $master->id)->get();

        $models = DB::table('model')->where('parent_id', 0)->orderBy('lft', 'asc')->get();
        $model = Model::find($model_id);

        $sets = $models->keyBy('table');

        return $this->display([
            'master' => $master,
            'sublist' => $sublist,
            'model_id' => $model_id,
            'model' => $model,
            'models' => $models,
            'sets' => $sets,
        ]);
    }

    public function create()
    {
        $model_id = (int)Request::get('model_id');
        $id = (int)Request::get('id');

        $row = Field::find($id);
        $model = Model::find($model_id);

        $templates = [];

        switch ($this->dbType) {
            case 'sqlsrv':
                $templates = [
                    'BIGINT' => ['type' => 'bigint', 'length' => '', 'default' => 'NULL'],
                    'INT' => ['type' => 'int', 'length' => '', 'default' => 'NULL'],
                    'MEDIUMINT' => ['type' => 'int', 'length' => '', 'default' => 'NULL'],
                    'SMALLINT' => ['type' => 'smallint', 'length' => '', 'default' => 'NULL'],
                    'TINYINT' => ['type' => 'tinyint', 'length' => '', 'default' => 'NULL', ],
                    'DECIMAL' => ['type' => 'decimal', 'length' => '20,2', 'default' => 'NULL'],
                    'DATE' => ['type' => 'date', 'length' => '', 'default' => 'NULL'],
                    'DATETIME' => ['type' => 'datetime', 'length' => '', 'default' => 'NULL'],
                    'NCHAR' => ['type' => 'nchar', 'length' => '50', 'default' => 'NULL'],
                    'NVARCHAR' => ['type' => 'nvarchar', 'length' => '255', 'default' => 'NULL'],
                    'NVARCHAR(MAX)' => ['type' => 'nvarchar(max)', 'length' => '', 'default' => 'NULL'],
                    'VARCHAR' => ['type' => 'nvarchar', 'length' => '255', 'default' => 'NULL'],
                    'TEXT' => ['type' => 'nvarchar(max)', 'length' => '', 'default' => 'NULL']
                ];
            break;
            case 'mysql':
                $templates = [
                    'BIGINT' => ['type' => 'bigint', 'length' => '20', 'default' => 'NULL'],
                    'INT' => ['type' => 'integer',  'length' => '11', 'default' => 'NULL'],
                    'SMALLINT' => ['type' => 'smallint', 'length' => '5', 'default' => 'NULL'],
                    'TINYINT' => ['type' => 'smallint', 'length' => '3', 'default' => 'NULL'],
                    'DECIMAL' => ['type' => 'decimal', 'length' => '20,2', 'default' => 'NULL'],
                    'DATE' => ['type' => 'date',  'length' => '', 'default' => 'NULL'],
                    'DATETIME' => ['type' => 'datetime', 'length' => '', 'default' => 'NULL'],
                    'TIME' => ['type' => 'time', 'length' => '', 'default' => 'NULL'],
                    'CHAR' => ['type' => 'char', 'length' => '50', 'default' => 'NULL'],
                    'VARCHAR' => ['type' => 'varchar', 'length' => '255', 'default' => 'NULL'],
                    'TEXT' => ['type' => 'text', 'length' => '', 'default' => 'NULL']
                ];
                break;    
        }

        if (Request::method() == 'POST') {

            $gets = Request::all();

            $rules = [
                'name' => 'required',
                'field' => 'required|unique:model_field,field,'.$id.',id,model_id,'.$model_id,
                'form_type' => 'required',
            ];

            if ($gets['validate']) {
                $gets['validate'] = join('|', $gets['validate']);
            }
            
            $v = Validator::make($gets, $rules);
            if ($v->fails()) {
                return $this->json(join('<br>',$v->errors()->all()));
            }
            
            if (trim($gets['type'])) {
                $type = $templates[$gets['type']]['type'];
                $table = $model->getAttribute('table');
                $sql = [];

                try {
                    $columns = DB::select('select column_name as column_name from information_schema.columns where table_name=?', [$table]);
                    $columns = array_by($columns, 'column_name');

                    switch ($this->dbType) { 
                        case 'sqlsrv':
                            if ($gets['id'] > 0 && isset($columns[$row['field']])) {

                                // 修改字段名
                                if ($row['field'] != $gets['field']) {
                                    $sql[] = "EXEC sp_rename '{$table}.{$row['field']}', '{$gets['field']}', 'column'";
                                    
                                    // 删除旧索引
                                    if ($row['index']) {
                                        if ($row['index'] == 'PRIMARY') {
                                            $index = "pk_{$table}_{$row['field']}";
                                        } else {
                                            $index = "idx_{$table}_{$row['field']}";
                                        }
                                        $sql[] = "if exists (select 1 from sys.indexes where object_id = object_id(N'{$table}') and name = '{$index}')
                                        begin
                                            drop index {$index} on {$table}
                                        end";
                                    }
                                    
                                    // 添加新索引
                                    if ($gets['index']) {
                                        if ($gets['index'] == 'INDEX') {
                                            $sql[] = "create INDEX idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'UNIQUE') {
                                            $sql[] = "create UNIQUE INDEX idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'PRIMARY') {
                                            $sql[] = "create PRIMARY KEY INDEX pk_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        }
                                    }
                                }
                                
                                // 修改字段类型
                                if ($row['type'] != $gets['type'] || $row['not_null'] != $gets['not_null'] || $row['length'] != $gets['length']) {
                                    $not_null = $gets['not_null'] == 1 ? 'not null' : 'null';
                                    $type = $gets['length'] == '' ? $type : $type.'('.$gets['length'].')';
                                    $sql[] = "alter table {$table} alter column {$gets['field']} {$type} $not_null";
                                }
        
                                // 默认值
                                if ($row['default'] != $gets['default']) {

                                    // 删除旧默认值
                                    $sql[] = "if exists (select 1 from sysobjects as t where id = (select cdefault from syscolumns where id = object_id(N'{$table}') and name = 'df_{$table}_{$gets['field']}'))
                                    begin
                                        alter table $table drop constraint df_{$table}_{$gets['field']}
                                    end";

                                    $default = trim($gets['default']);
                                    if ($gets['default'] == 'NULL') {
                                        $default = 'NULL';
                                    } else if($gets['default'] == 'Empty String') {
                                        $default = "''";
                                    }
                                    if ($default != '') {
                                        $sql[] = "alter table $table add constraint df_{$table}_{$gets['field']} default {$default} for {$gets['field']} with values";
                                    }
                                }

                                // 修改字段注释
                                if ($row['name'] != $gets['name']) {
                                    $sql[] = "EXECUTE sp_updateextendedproperty 'MS_Description', '{$gets['name']}', 'user', 'dbo', 'table', '{$table}', 'column', '{$gets['field']}'";
                                }

                            } else {
                                // 字段不存在
                                if (!isset($columns[$gets['field']])) {

                                    $not_null = $gets['not_null'] == 1 ? 'not null' : 'null';
                                    $type = $gets['length'] == '' ? $type : $type.'('.$gets['length'].')';

                                    // 添加字段
                                    $sql[] = "alter table {$table} add {$gets['field']} {$type} $not_null";
                                    // 添加字段索引
                                    if ($gets['index']) {
                                        if ($gets['index'] == 'INDEX') {
                                            $sql[] = "create INDEX idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'UNIQUE') {
                                            $sql[] = "create UNIQUE INDEX idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        }
                                        else if($gets['index'] == 'PRIMARY') {
                                            $sql[] = "create PRIMARY KEY INDEX pk_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        }
                                    }

                                    $default = trim($gets['default']);
                                    if ($default == '') {
                                        $default = '';
                                    } else if ($default == 'NULL') {
                                        $default = 'NULL';
                                    } else if($gets['default'] == 'Empty String') {
                                        $default = "''";
                                    }
                                    if ($default != '') {
                                        $sql[] = "alter table $table add constraint df_{$table}_{$gets['field']} default {$gets['default']} for {$gets['field']} with values";
                                    }

                                    $sql[] = "EXECUTE sp_addextendedproperty 'MS_Description', '{$gets['name']}', 'user', 'dbo', 'table', '{$table}', 'column', '{$gets['field']}'";
                                }
                            }
                        break;
                        case 'mysql':
                            $type = $gets['type'].($gets['length'] == '' ? '' : '('.$gets['length'].')');
                            $not_null = $gets['not_null'] == 1 ? 'not null' : 'null';

                            // 处理默认值
                            $default = trim($gets['default']);
                            if ($default == '' || $default == 'NULL') {
                                $default = 'default NULL';
                            } else if($default == 'Empty String') {
                                $default = "default ''";
                            } else {
                                $default = "default '{$default}'";
                            }
                            // 处理注释
                            $comment = "COMMENT '{$gets['name']}'";
                            // 字段位置
                            $after = '';

                            if ($gets['id'] > 0 && isset($columns[$row['field']])) {
                                
                                // 修改字段数据
                                if ($row['field'] != $gets['field'] || $row['type'] != $gets['type'] || $row['not_null'] != $gets['not_null'] || $row['length'] != $gets['length']) {
                                    $sql[] = "alter table {$table} change `{$row['field']}` `{$gets['field']}` $type $not_null $default $comment $after";
                                }

                                // 修改字段名
                                if ($row['field'] != $gets['field']) {
                                    // 删除旧索引
                                    if ($row['index']) {
                                        if ($row['index'] == 'PRIMARY') {
                                            $index = "pk_{$table}_{$row['field']}";
                                        } else {
                                            $index = "idx_{$table}_{$row['field']}";
                                        }
                                        $sql[] = "drop index {$index} on {$table}";
                                    }
                                    
                                    // 添加新索引
                                    if ($gets['index']) {
                                        if ($gets['index'] == 'INDEX') {
                                            $sql[] = "alter table {$table} add index idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'UNIQUE') {
                                            $sql[] = "alter table {$table} add unique idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'PRIMARY') {
                                            $sql[] = "alter table {$table} add primary key pk_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        }
                                    }
                                }
                            } else {
                                // 字段不存在
                                if (!isset($columns[$gets['field']])) {
                                    // 添加字段
                                    $sql[] = "alter table {$table} add `{$gets['field']}` $type $not_null $default $comment $after";
                                    
                                    // 添加新索引
                                    if ($gets['index']) {
                                        if ($gets['index'] == 'INDEX') {
                                            $sql[] = "alter table {$table} add index idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'UNIQUE') {
                                            $sql[] = "alter table {$table} add unique idx_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        } else if($gets['index'] == 'PRIMARY') {
                                            $sql[] = "alter table {$table} add primary key pk_{$table}_{$gets['field']} on $table({$gets['field']})";
                                        }
                                    }
                                }
                            }
                            break;
                    }

                    // 操作数据表
                    foreach ($sql as $_sql) {
                        DB::statement($_sql);
                    }

                } catch(\Exception $e) {
                    return $this->json($e->getMessage());
                }
            }

            // 写入模型数据
            $gets['setting'] = json_encode($gets['setting'], JSON_UNESCAPED_UNICODE);

            // 去掉关联字段
            if (empty($gets['data_type'])) {
                $gets['data_type'] = '';
                $gets['data_field'] = '';
                $gets['data_link'] = '';
            }

            $model = Field::findOrNew((int)$gets['id']);
            $model->fill($gets);
            $model->save();

            /*
            $_model = DB::table('model')->where('id', $model_id)->first();
            $_model['fields'] = DB::table('model_field')->where('model_id', $model_id)->get();
            $abc = json_encode($_model, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            file_put_contents('model_'.$_model['table'].'.json', $abc);
            */

            return $this->json('恭喜你，操作成功。', url('index', ['model_id' => $gets['model_id']]));
        }

        if ($row['validate']) {
            $row['validate'] = explode('|', $row['validate']);
        }

        if ($model['parent_id'] > 0) {
            // 获取子表目录
            $_model = DB::table('model')->where('id', $model['parent_id'])->first();
            $_models = DB::table('model')->where('parent_id', $_model['id'])->get();
        } else {
            // 获取子表目录
            $_model = DB::table('model')->where('id', $model['id'])->first();
            $_models = DB::table('model')->where('parent_id', $_model['id'])->get();
        }

        $models[] = $_model;
        foreach ($_models as $_model) {
            $models[] = $_model;
        }

        $row['model_id'] = $model_id;

        $setting = json_decode($row['setting'], true);
        $row['setting'] = $setting;

        $types = [];
        $dialogs = ModuleService::dialogs();
        foreach ($dialogs as $table => $dialog) {
            $types[] = ['table' => $table, 'name' => $dialog['name']];
        }

        $_types = DB::table('model')->orderBy('lft')->get()->keyBy('id');
        $types = [];
        foreach ($_types as $type) {
            if ($type['parent_id']) {
                $name = $_types[$type['parent_id']]['name'].'->'.$type['name'];
            } else {
                $name = $type['name'];
            }
            $types[] = ['table' => $type['table'], 'name' => $name];
        }

        $fields = DB::table('model_field')->where('model_id', $model_id)->get(['field', 'name']);

        $regulars = FlowService::regulars();
        return $this->render([
            'types' => $types,
            'fields' => $fields,
            'row' => $row,
            'model' => $model,
            'models'  => $models,
            'model_id' => $model_id,
            'regulars' => $regulars,
            'templates' => $templates,
        ]);
    }

    /**
     * 获取字段类型
     */
    public function type()
    {
        $type = Request::get('type');
        $model_id = Request::get('model_id');
        if ($type) {
            return FieldService::{'form_'.$type}([], $model_id);
        }
    }

    /**
     * 字段关联对象
     */
    public function getColumns()
    {
        $table = Request::get('table');

        $flow = DB::table('model')->where('table', $table)->first();
        $fields = DB::table('model_field')->where('model_id', $flow['id'])->get();
        $rows = [];
        foreach($fields as $field) {
            if ($field['type']) {
                if ($field['data_type'] && $field['data_field']) {
                    $f = $field['data_type'].'.'.$field['data_field'];
                    $rows[] = ['field' => $f, 'key' => $field['field'].'.'.$field['data_link'].':'.$f, 'name' => $field['name']];
                } else {
                    $rows[] = ['field' => $field['field'], 'key' => $field['field'], 'name' => $field['name']];
                }
            }
        }
        return $this->json($rows, true);
    }

    public function getEnums()
    {
        $enums = Option::where('parent_id', 0)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return $enums;
    }

    public function delete()
    {
        $model_id = Request::get('model_id');
        $id = Request::get('id');
        $id = is_array($id) ? $id : [$id];
        Field::whereIn('id', $id)->delete();
        return $this->json('恭喜你，操作成功。', url('index', ['model_id' => $model_id]));
    }
}
