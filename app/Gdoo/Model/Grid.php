<?php namespace Gdoo\Model;

use Illuminate\Pagination\AbstractPaginator;

use DB;
use Auth;
use Request;
use URL;

use App\Support\Module;
use App\Support\Hook;
use App\Support\AES;

use Gdoo\User\Models\User;
use Gdoo\Index\Models\Access;
use Gdoo\Model\Models\Bill;
use Gdoo\Model\Models\Model;
use Gdoo\Model\Models\Field;
use Gdoo\Model\Models\Step;
use Gdoo\Model\Models\StepLog;
use Gdoo\Model\Services\ModelService;
use Gdoo\Model\Services\ModuleService;
use Gdoo\User\Services\UserAssetService;

class Grid
{
    public static function dataFilters($items, $header, $callback = null)
    {
        $master_ids = [];
        $region_ids = [];
        $auth_user_id = auth()->id();
        $runs = [];
        $master_prefix = $header['master_prefix'];
        $master_key = $master_prefix.'id';
        $columns = $header['columns'];
        $dialogs = $header['dialogs'];

        foreach($items as $item) { 
            foreach($columns as $column) {
                $field = $column['column'];
                if ($column['form_type'] == 'dialog') {
                    foreach ($dialogs as $i => $dialog) {
                        $dialog['value'] = array_merge((array)$dialog['value'], explode(',', $item[$field]));
                        $dialogs[$i] = $dialog;
                    }
                }
            }
            
            if ($item['province_id']) {
                $region_ids[$item['province_id']] = $item['province_id'];
                $region_ids[$item['city_id']] = $item['city_id'];
                $region_ids[$item['county_id']] = $item['county_id'];
            }

            if ($item[$master_key]) {
                $master_ids[] = $item[$master_key];
            }
        }

        // 处理流程
        if ($header['audit_type'] == 1) {
            $ids = join(',', $master_ids);
            if($ids) {
                $var1 = DB::table('model_run_log')
                ->leftJoin('model_run', 'model_run.id', '=', 'model_run_log.run_id')
                ->where('model_run_log.option', 1)
                ->where('model_run_log.status', 0)
                ->where('model_run_log.bill_id', $header['bill_id'])
                ->whereRaw('model_run.data_id in ('.$ids.')')
                ->orderBy('model_run_log.option', 'desc')
                ->orderBy('model_run_log.created_at', 'asc')
                ->get(['model_run_log.*', 'model_run.data_id']);

                foreach($var1 as $run) {
                    if ($run['user_id'] == $auth_user_id) {
                        $runs['audit'][$run['data_id']][] = $run['id'];
                    }
                    if ($run['option'] == 1) {
                        $runs['edits'][$run['data_id']] = 0;
                        if ($run['user_id'] == $auth_user_id) {
                            $runs['edits'][$run['data_id']] = 1;
                        }
                        $runs['status'][$run['data_id']][$run['run_step_id']] = $run['run_name'];
                    }
                }
            }
        }

        // 获取行政区域
        $regions = DB::table('region')->whereIn('id', $region_ids)->pluck('name', 'id')->toArray();

        // 对话框关联处理
        foreach ($dialogs as $field => $dialog) {
            $option = ModuleService::dialogs($dialog['type']);
            $ids = array_filter($dialog['value']);
            if ($option['model']) {
                $dialogs[$field]['rows'] = $option['model']($ids)->toArray();
            }
        }

        $header['runs'] = $runs;
        $header['dialogs'] = $dialogs;
        $header['regions'] = $regions;

        $rows = [];
        foreach($items as $item) {
            $rows[] = static::dataFilter($item, $header, $callback);
        }

        $ret = [];
        if ($items instanceof AbstractPaginator) {
            $ret = $items->setCollection(collect($rows))->toArray();
        } else {
            $ret = collect(['data' => $rows]);
        }

        $ret['header'] = static::getColumns($header);
        return $ret;
    }

    /**
     * 重新组合字段(主要给前端使用)
     */
    public static function getColumns($header)
    {
        $columns = [];
        foreach ($header['cols'] as $field => $col) {
            if ($field == 'action' && empty($col['events'])) {
                continue;
            }
            if ($col['field'] == 'created_by') {
                $col['formatter'] = 'created_by';
            }
            $columns[] = $col;
        }

        unset($header['cols']);
        unset($header['dialogs']);
        unset($header['join']);
        unset($header['js']);
        unset($header['raw_select']);
        unset($header['search']);
        unset($header['select']);
        unset($header['runs']);
        unset($header['regions']);

        $header['columns'] = $columns;
        
        if (empty($header['bill_uri'])) {
            $header['bill_uri'] = Request::module().'/'.Request::controller();
        }
        return $header;
    }

    public static function dataFilter($item, $header, $callback = null)
    {
        $master_prefix = $header['master_prefix'];
        $master_key = $master_prefix.'id';
        $dialogs = $header['dialogs'];
        
        foreach ($header['columns'] as $column) {
            $raw_field = $column['column'];
            $field = $column['field'];
            $setting = $column['setting'];
            $type = $setting['type'];
            $value = $item[$field];
            $raw_value = $item[$field];

            if ($column['form_type'] == 'address') {
                $value = str_replace("\n", ' ', $value);
            }

            if ($column['form_type'] == 'dialog' || $column['form_type'] == 'select2') {
                // 一对多时替换
                $dialog = $dialogs[$raw_field];
                if ($dialog) {
                    $value = $item[$raw_field];
                    $va = $dialog['rows'];
                    $vals = explode(',', $value);
                    $ret = [];
                    if ($va) {
                        foreach($vals as $_v) {
                            $ret[] = $va[$_v];
                        }
                    }
                    $value = join(',', $ret);
                }
            }

            if ($column['form_type'] == 'text') {
                if ($column['type'] == 'DECIMAL' || $column['type'] == 'INT' || $column['data_format'] == 'number') {
                    $value = floatval($value);
                    if ($value == 0) {
                        $value = '';
                    }
                }
            }

            if ($column['form_type'] == 'date') {
                if ($setting['save'] == 'u') {
                    $value = $value > 0 ? date($type, $value) : '';
                } else {
                    if ($value == '0000-00-00' || $value == '0000-00-00 00:00:00') {
                        $value = '';
                    }
                }
            }

            if ($column['form_type'] == 'option') {
                $item[$field] = $value;
                $value = $column['options'][$item[$column['raw_field']]];
            }

            if ($column['form_type'] == 'images') {
                if ($value == '') {
                    $url = '/assets/images/default_img.png';
                    $value = '<span><i class="fa fa-file-image-o"></i></span>';
                } else {
                    $url ='/uploads/'.$value;
                    $value = '<a class="image-show" data-toggle="dialog-image" href="javascript:;" data-title="图片查看" data-url="'.$url.'"><i class="fa fa-file-image-o"></i></a>';
                }
            }

            if ($column['form_type'] == 'checkbox') {
                $select = explode("\n", $setting['content']);
                $res = [];
                foreach ($select as $t) {
                    $n = $v = '';
                    list($n, $v) = explode('|', $t);
                    $v = is_null($v) ? trim($n) : trim($v);
                    if($v == $value) {
                        $res[] = $n;
                    }
                }
                $value = join(',', $res);
            }

            if ($column['form_type'] == 'select' || $column['form_type'] == 'radio') {
                if ($column['options']) {
                    $item[$field] = $value;
                    $value = $column['options'][$item[$column['raw_field']]];
                }
            }

            if ($column['form_type'] == 'region') {
                $regions = $header['regions'];
                if ($item['province_id']) {
                    $values = [$regions[$item['province_id']], $regions[$item['city_id']], $regions[$item['county_id']]];
                    $value = join(' ', $values);
                } else {
                    $value = '';
                }
            }

            if ($column['form_type'] == 'audit') {
                if ($header['audit_type'] == 1) {
                    $runs = $header['runs'];
                    $master_id = $item[$master_key];
                    $key = AES::encrypt($header['bill_id'].'.'.$master_id, config('app.key'));
                    $js = "flow.auditLog('$key');";
                    $name = $column['options'][$value];

                    $option = 'option';
                    switch($item[$master_prefix.'status']) {
                        case '0':
                            $option = 'option red';
                            break;
                        case '1':
                            $option = 'option green';
                            break;
                        case '2':
                            $name = '执行中 '.join(',', (array)$runs['status'][$master_id]);
                            break;
                        case '-2':
                            $name = '退回 '.join(',', (array)$runs['status'][$master_id]);
                            break;
                    }

                    // 关闭
                    if ($item[$master_prefix.'is_close'] == '1') {
                        $name = '关闭 ';
                    }

                    $item[$master_prefix.'status'] = '<a href="javascript:;" class="'.$option.'" onclick="'.$js.'">'.$name.'</a>';
                    $value = $item[$master_prefix.'status'];
                    $item['flow_log_id'] = $runs['audit'][$master_id][0];
                    
                    // 表单模式为OA模式
                    if ($header['form_type'] == 1) {
                        // 如果是草稿，能见者就可编辑
                        if ($raw_value == '0') {
                            $item['flow_form_edit'] = 1;
                        } else {
                            if ($runs['edits'][$master_id] == 1) {
                                $item['flow_form_edit'] = 1;
                            }
                        }
                    }
                }
                if ($header['audit_type'] == 3) {
                    $value = $column['options'][$value];
                }
            }

            $item[$field] = $value;
        }

        // 回调自定义函数
        if (is_callable($callback)) {
            $item = $callback($item);
        }

        return $item;
    }

    public static function addColumns($columns, $field, $data)
    {
        $rows = [];
        foreach ($columns as $key => $column) {
            if ($field == $key) {
                foreach ($data as $v) {
                    $rows[$v['field']] = $v;
                }
            }
            $rows[$key] = $column;
        }
        return $rows;
    }

    // 重新排序join
    public static function sortJoin($joins)
    {
        foreach ($joins as $k => $join) {
            static::recursiveJoin($joins, $k);
        }
        array_multisort(array_column($joins, 5), SORT_ASC, $joins);
        return $joins;
    }

    public static function recursiveJoin(&$joins, $parent_id = 0) {
        foreach($joins as $k => &$join) {
            if ($join[4] === $parent_id) {
                $join[5] = $joins[$parent_id][5] + 1;
                static::recursiveJoin($joins, $k);
            }
        }
    }

    public static function fieldRelated($table, $row, &$join, &$select, &$index, &$column, &$search, $setting)
    {
        if ($row['data_type']) {
            $data_type = $row['data_type'];
            $data_field = $row['data_field'];
            $data_link = $row['data_link'];
            $_table = $data_link.'_'.$data_type;

            if ($row['type']) {
                $join[$_table] = [$data_type.' as '.$_table, $_table.'.id', '=', $table.'.'.$data_link, $table, 1];
            }

            $field_count = mb_substr_count($data_field, ':');
            if ($field_count > 0) {
                $var1 = explode(':', $data_field);
                list($_v1, $_v2) = explode('.', $var1[0]);
                list($_t1, $_t2) = explode('.', $var1[1]);

                // 判断是否存在左表字段
                if ($row['type']) {
                    $_table = $data_link.'_'.$_v2.'_'.$_t1;
                } else {
                    $_table = $row['field'].'_'.$_t1;
                }

                $join[$_table] = [$_t1.' as '.$_table, $_table.'.id', '=', $data_link.'_'.$data_type.'.'.$_v2, $data_link.'_'.$data_type, 1];

                $index = $_table.'.'.$_t2;

                $search['table'] = $_t1;
                $search['field'] = $data_link.'_'.$data_type.'.'.$_v2;
                $search['name'] = $_t2;

                // 远程字段和本地字段名称一样重命名
                if ($data_link == $row['field']) {
                    $column = $column.'_'.$_v1;
                } else {
                    if ($row['type']) {
                        $column = $column.'_'.$_t2;
                    }
                    $column = $column;
                }

            } else {
                $index = $_table.'.'.$data_field;
                $search['table'] = $data_type;
                $search['field'] = $_table.'.id';
                $search['name'] = $data_field;

                // 远程字段和本地字段名称一样重命名
                if ($data_link == $row['field']) {
                    $column = $column.'_'.$data_field;
                } else {
                    $column = $column;
                }
            }
            $select[$index][] = $column;
        }
    }

    public static function header($options)
    {
        $auth = auth()->user();
    
        $res = $join = $select = $search = $dialogs = [];

        $view_type = empty($options['view_type']) ? 'list' : $options['view_type'];

        $bill = Bill::where('code', $options['code'])->first();

        // 获取主模型
        $master = Model::where('id', $bill['model_id'])->first();
        $table = $master['table'];

        if (isset($options['prefix'])) {
            $master_prefix = $options['prefix'];
        } else {
            $master_prefix = 'master_';
        }

        // 是否存在查询子表
        $exist_sub_table = 0;

        // 是否开启回收站功能
        $res['trash_btn'] = $master['is_trash'];
        if ($res['trash_btn']) {
            $res['trash_count'] = DB::table($master['table'])->where('deleted_id', '>', 0)->count();
        }

        $res['create_btn'] = isset($options['create_btn']) ? $options['create_btn'] : 1;

        if ($options['template_id'] > 0) {
            $template = DB::table('model_template')
            ->where('id', $options['template_id'])
            ->where('bill_id', $bill['id'])
            ->first();
        } else {
            $template = DB::table('model_template')
            ->permission('receive_id', null, false, false)
            ->where('type', $view_type)
            ->where('bill_id', $bill['id'])
            ->first();
        }

        // 全局模板
        if (empty($template)) {
            $template = DB::table('model_template')
            ->where('receive_id', 'all')
            ->where('type', $view_type)
            ->where('bill_id', $bill['id'])
            ->first();
        }

        // 获取全部模型
        $models = ModelService::getModelAllFields($bill['model_id']);
        $_models = [];
        $left_fields = [];
        $fields = [];

        if ($template) {
            $views = (array)json_decode($template['tpl'], true);
            foreach($views as $view) {
                $_model = $models[$view['table']];
                $_field = $_model['fields'][$view['field']];
                $_field['table'] = $_model['table'];
                $_field['is_index'] = 1;

                // 关联字段是左表字段
                $data_link = $_field['data_link'];
                if ($data_link) {
                    $_field['is_link'] = true;
                    $left_field = $_model['fields'][$data_link];
                    if ($left_field['type']) {
                        $left_field['is_link'] = true;
                        $left_field['table'] = $_model['table'];
                        $left_fields[$data_link] = $left_field;
                    }
                }

                // 按角色隐藏字段
                if ($view['role_id']) {
                    $role_ids = explode(',', $view['role_id']);
                    if (in_array($auth->role_id, $role_ids)) {
                        $_field['hidden'] = true;
                    }
                }

                if ($_model['parent_id'] == 0) {
                    $_field['is_master'] = 1;
                    $_field['column'] = $master_prefix.$_field['field'];
                } else {
                    $exist_sub_table = 1;
                    $_field['column'] = $_field['field'];
                }
                $_models[$view['table']] = $_model;
                $fields[] = $_field;
            }

            $select = [$table.'.created_by' => [], $table.'.created_at' => []];
            if ($master_prefix) {
                $select[$master['table'].'.id'][] = $master_prefix.'id';
            }

            foreach($_models as $_model) {
                // 主模型
                if ($_model['table'] == $master['table']) {
                    continue;
                }
                if ($_model['parent_id'] > 0) {
                    $select[$_model['table'].'.id'] = [];
                }
                $join[] = [$_model['table'], $_model['table'].'.'.$_model['relation'], '=', $master['table'].'.id', $master['table'], 0];
            }

        } else {
            $_fields = Field::where('model_id', $master['id'])
            ->orderBy('sort', 'asc')
            ->get()->keyBy('field')->toArray();

            foreach($_fields as $_field) {
                $_field['table'] = $table;
                $_field['column'] = $_field['field'];
                $_field['is_master'] = 1;

                // 关联字段是左表字段
                $data_link = $_field['data_link'];
                if ($data_link) {
                    $_field['is_link'] = true;
                    $left_field = $_fields[$data_link];
                    if ($left_field['type']) {
                        $left_field['is_link'] = true;
                        $left_field['table'] = $table;
                        $left_fields[$data_link] = $left_field;
                    }
                }

                $fields[] = $_field;
            }
            $select = [$table.'.created_by' => [], $table.'.created_at' => []];
            if ($master_prefix) {
                $select[$table.'.id'][] = $master_prefix.'id';
            }
        }

        foreach($fields as $index => $field) {
            if (isset($left_fields[$field['field']])) {
                unset($left_fields[$field['field']]);
            }
        }

        $fields = array_merge(array_values($left_fields), $fields);

        $res['cols']['checkbox'] = [
            'width' => 40,
            'suppressSizeToFit' => true,
            'cellClass' => 'text-center',
            'suppressMenu' => true,
            'sortable' => false,
            'editable' => false,
            'resizable' => false,
            'filter' => false,
            'checkboxSelection' => true,
            'headerCheckboxSelection' => true,
        ];

        $res['cols']['seq_sn'] = [
            'width' => 60,
            'headerName' => '序号',
            'suppressSizeToFit' => true,
            'cellClass' => 'text-center',
            'suppressMenu' => true,
            'sortable' => false,
            'resizable' => false,
            'editable' => false,
            'type' => 'sn',
            'filter' => false
        ];

        foreach ($fields as $row) {

            if ($row['is_index'] == 1 || $row['is_search'] == 1 || $row['is_link']) {
                $setting = json_decode($row['setting'], true);

                $is_master = $row['is_master'];
                $_table = $row['table'];
                $column = $row['column'];
                $field = $row['field'];
                $index = $_table.'.'.$field;
                $_search = ['field' => $index];

                if ($row['type']) {
                    if ($is_master) {
                        $select[$_table.'.'.$field][] = $column;
                    } else {
                        $select[$_table.'.'.$field] = [];
                    }
                }

                static::fieldRelated($_table, $row, $join, $select, $index, $column, $_search, $setting);
                if (isset($left_fields[$field])) {
                    continue;
                }

                $_search['field2'] = $index;

                if ($row['form_type'] == 'urd') {
                    $field = str_replace('_id', '_text', $field);
                }
                
                if ($row['form_type'] == 'autocomplete' || $row['form_type'] == 'dialog' || $row['form_type'] == 'select2') {
                    if ($setting['single'] == 0 && $is_master) {
                    }
                }

                if ($row['form_type'] == 'option') {
                    $row['raw_field'] = $column;
                    $row['options'] = option($setting['type'])->pluck('name', 'id');
                    $column = $column.'_name';
                }

                if ($row['form_type'] == 'select' || $row['form_type'] == 'radio') {
                    if ($setting['content']) {
                        $row['raw_field'] = $column;
                        $_select = explode("\n", $setting['content']);
                        $res2 = [];
                        foreach ($_select as $t) {
                            $n = $v = '';
                            list($n, $v) = explode('|', $t);
                            $v = is_null($v) ? trim($n) : trim($v);
                            $res2[$v] = $n;
                        }
                        $row['options'] = $res2;
                        $column = $column.'_name';
                    }
                }

                $children = [];

                if ($row['form_type'] == 'checkbox') {
                    // 存在子表
                    if ($row['type']) {
                    } else {
                        $checks = explode("\n", $setting['content']);
                        foreach ($checks as $check) {
                            list($n, $v) = explode('|', $check);
                            $select[$_table.'.'.$v][] = $v;
                            $cellEditorParams['values'] = [1 => '是', 0 => '否'];
                            $children[] = ['cellEditorParams' => $cellEditorParams, 'cellRenderer' => 'checkboxCellRenderer', 'suppressMenu' => true, 'cellClass' => 'text-center', 'headerName' => $n, 'width' => 80, 'field' => $v];
                        }
                    }
                }

                if ($row['form_type'] == 'audit') {
                    if ($bill['audit_type'] == 1) {
                        $row['options'] = option('flow.status')->pluck('name', 'id');
                    }
                    if ($bill['audit_type'] == 3) {
                        $row['options'] = option('audit.status')->pluck('name', 'id');
                    } 
                }

                $row['column'] = $field;
                $row['field'] = $column;
                $row['index'] = $index;
                $row['setting'] = $setting;

                $res['columns'][$column] = $row;

                $col = [];
                $col['field'] = $column;
                $col['headerName'] = $row['name'];
                $col['sortable'] = (bool)$row['is_sort'];
                $col['sortable'] = true;
                $col['suppressMenu'] = $row['is_menu'] == 1 ? false : true;
                $col['cellClass'] = 'text-'.$setting['align'];
                $col['form_type'] = $row['form_type'];
                $col['hide'] = (bool)$row['hidden'];
  
                if ($children) {
                    $col['children'] = $children;
                }

                if ($setting['cell_count']) {
                    $col['calcFooter'] = $setting['cell_count'];
                }

                if ($row['form_type'] == 'audit') {
                    $col['cellRenderer'] = 'htmlCellRenderer';
                }

                if ($row['form_type'] == 'images') {
                    $col['cellRenderer'] = 'htmlCellRenderer';
                    $col['sortable'] = false;
                }

                if ($row['data_format']) {
                    // 数据类型格式化
                    switch ($row['data_format']) {
                        case 'number':
                        case 'money':
                            list($_, $len) = explode(',', $row['length']);
                            $col['type'] = 'number';
                            $col['numberOptions'] = [
                                'separator' => '.',
                                'thousands' => ',',
                                'places' => (int)$len,
                                'default' => '',
                            ];
                            break;
                    }
                } else {
                    // 数据类型格式化
                    switch ($row['type']) {
                        case 'DECIMAL':
                            list($_, $len) = explode(',', $row['length']);
                            $col['type'] = 'number';
                            $col['numberOptions'] = [
                                'separator' => '.',
                                'thousands' => ',',
                                'places' => (int)$len,
                                'default' => '',
                            ];
                            break;
                    }
                }

                if ($setting['width']) {
                    $col['width'] = (int)$setting['width'];
                } else {
                    $col['width'] = 120;
                }

                if ($row['is_index'] == 1) {
                    $res['cols'][$row['field']] = $col;
                }

                // 搜索字段
                if ($row['is_search'] == 1) {
                    $form_type = $row['form_type'];
                    $form_type = 'text';
                    $form_field = $row['index'];
                    $form_option = [];

                    if ($row['form_type'] == 'date') {
                        $form_type = $row['type'] == 'INT' ? 'second2' : 'date2';
                    }

                    if ($row['form_type'] == 'region') {
                        $form_type = 'region';
                    }

                    if ($row['form_type'] == 'dialog') {

                        $form_type = 'dialog';
                        $form_field = $_search['field'];

                        if ($options['type'] == 'dialog') {
                            $form_type = 'text';
                            $form_field = $_search['field2'];
                        }

                        $_dialog = ModuleService::dialogs($setting['type']);
                        $_query = [];
                        if ($setting['query']) {
                            list($k, $v) = explode('=', $setting['query']);
                            if (strpos($v, '$') === 0) {
                                $v = substr($v, 1);
                                $_query[$k] = $row[$v];
                            } else {
                                $_query[$k] = $v;
                            }
                        }
                        $form_option['query'] = $_query;
                        $form_option['url'] = $_dialog['url'];
                    }

                    if ($row['form_type'] == 'select') {
                        $form_type = 'select';
                        $form_field = $_search['field'];

                        if ($row['data_type']) {
                            $query = [];
                            if ($setting['query']) {
                                list($k, $v) = explode('=', $setting['query']);
                                if (strpos($v, '$') === 0) {
                                    $v = substr($v, 1);
                                    $query[$k] = $row[$v];
                                } else {
                                    $query[$k] = explode(',', $v);
                                }
                            }
                            $_model = DB::table($_search['table'])->where('status', 1);
                            foreach ($query as $k => $v) {
                                if (is_array($v)) {
                                    $_model->whereIn($k, $v);
                                } else {
                                    $_model->where($k, $v);
                                }
                            }
                            $rows = $_model->orderBy('sort', 'asc')
                            ->pluck($_search['name'], 'id');
                            foreach ($rows as $k => $v) {
                                $form_option[] = ['id'=> $k, 'name' => $v];
                            }
                        } else {
                            $content = explode("\n", $setting['content']);
                            foreach($content as $_content) {
                                list($k, $v) = explode('|', $_content);
                                $form_option[] = ['id'=> trim($v), 'name' => trim($k)];
                            }
                        }

                        if ($field == 'tax_id') {
                            $form_type = 'dialog';
                            $form_option['query'] = $_query;
                            $form_option['url'] = 'customer/tax/dialog';
                        }
                    }

                    if ($row['form_type'] == 'option') {
                        $form_type = 'option';
                        $_options = $row['options'];
                        foreach($_options as $k => $v) {
                            $form_option[] = ['id'=> $k, 'name' => $v];
                        }
                    }

                    if ($row['form_type'] == 'select2') {
                    }

                    if ($field == 'batch_sn') {
                        $form_type = 'text';
                    }

                    if ($row['form_type'] == 'audit') {
                        $form_type = 'option';
                        $_options = $row['options'];
                        foreach($_options as $k => $v) {
                            $form_option[] = ['id'=> $k, 'name' => $v];
                        }
                    }

                    $search[] = [
                        'form_type' => $form_type,
                        'field' => $form_field,
                        'name' => $row['name'],
                        'options' => $form_option,
                    ];
                }
            }
        }

        $search_form = search_form($options['search'], $search, 'model');

        // 动作列
        $res['cols']['actions'] = [
            'headerName' => '',
            'cellRenderer' => 'actionCellRenderer',
            'options' => [],
            'width' => 80,
            'cellClass' => 'text-center',
            'suppressSizeToFit' => true,
            'suppressMenu' => true,
            'sortable' => false,
            'editable' => false,
            'resizable' => false,
            'filter' => false,
        ];

        $sort = Request::get('sort');
        $order = Request::get('order');
        if ($sort && $order) {
        } else {
            $sort = $options['sort'] ?: $table.'.id';
            $order = $options['order'] ?: 'desc';
        }
        $raw_select = [];
        $src_select = [];
        foreach($select as $k => $vv) {
            $vv = array_unique($vv);
            $raw_select[] = $k;
            if (count($vv) > 0) {
                foreach($vv as $v) {
                    $src_select[] = $k. ' as '.$v;
                }
            } else {
                $src_select[] = $k;
            }
        }

        $res['select'] = $src_select;
        $res['raw_select'] = $raw_select;
        $res['join'] = static::sortJoin($join);

        $res['search'] = $search;
        $res['search_form'] = $search_form;

        $res['dialogs'] = $dialogs;

        $res['master_model_id'] = $master['id'];
        $res['master_table'] = $master['table'];
        $res['master_prefix'] = $master_prefix;
        $res['master_name'] = $bill['name'];
        $res['audit_type'] = $bill['audit_type'];
        $res['form_type'] = $bill['form_type'];

        $res['bill_id'] = $bill['id'];
        $res['bill_uri'] = $bill['uri'];
        $res['name'] = $master['name'];
        $res['model_id'] = $master['id'];
        $res['is_sort'] = $master['is_sort'];
        $res['table'] = $table;
        $res['sort'] = $sort;
        $res['order'] = $order;

        // 获取当前权限
        $res['access'] = UserAssetService::getNowRoleAssets();

        // 是否开启简单搜索框
        $res['simple_search_form'] = 1;
        $res['exist_sub_table'] = $exist_sub_table;

        return $res;
    }

    public static function batchEdit($options)
    {
        $bill = Bill::where('code', $options['code'])->first();
        $model = Model::where('id', $bill['model_id'])->first();
        $fields = Field::where('model_id', $model['id'])
        ->whereIn('field', $options['columns'])
        ->orderBy('sort', 'asc')
        ->get()
        ->keyBy('id')
        ->toArray();

        foreach ($fields as $row) {

            $setting = json_decode($row['setting'], true);

            $_table = $row['table'];
            $column = $row['column'];
            $field = $row['field'];
            $index = $field;
            $_search = ['field' => $index];

            static::fieldRelated($_table, $row, $join, $select, $index, $column, $_search, $setting);

            if ($row['form_type'] == 'urd') {
                $field = str_replace('_id', '_text', $field);
            }
            
            if ($row['form_type'] == 'autocomplete' || $row['form_type'] == 'dialog' || $row['form_type'] == 'select2') {
            }

            if ($row['form_type'] == 'option') {
                $row['raw_field'] = $column;
                $row['options'] = option($setting['type'])->pluck('name', 'id');
                $column = $column.'_name';
            }

            if ($row['form_type'] == 'select' || $row['form_type'] == 'radio') {
                if ($setting['content']) {
                    $row['raw_field'] = $column;
                    $_select = explode("\n", $setting['content']);
                    $res1 = [];
                    foreach ($_select as $t) {
                        $n = $v = '';
                        list($n, $v) = explode('|', $t);
                        $v = is_null($v) ? trim($n) : trim($v);
                        $res1[$v] = $n;
                    }
                    $row['options'] = $res1;
                    $column = $column.'_name';
                }
            }

            if ($row['form_type'] == 'checkbox') {
                // 存在子表
                if ($row['type']) {
                } else {
                    $checks = explode("\n", $setting['content']);
                    foreach ($checks as $check) {
                        list($n, $v) = explode('|', $check);
                        $select[$_table.'.'.$v][] = $v;
                        $cellEditorParams['values'] = [1 => '是', 0 => '否'];
                    }
                }
            }

            $row['column'] = $field;
            $row['field'] = $column;
            $row['index'] = $index;
            $row['setting'] = $setting;

            $form_type = $row['form_type'];
            $form_type = 'text';
            $form_option = [];

            if ($row['form_type'] == 'date') {
                $form_type = $row['type'] == 'INT' ? 'second2' : 'date2';
            }

            if ($row['form_type'] == 'region') {
                $form_type = 'region';
            }

            if ($row['form_type'] == 'dialog') {

                $form_type = 'dialog';
                if ($options['type'] == 'dialog') {
                    $form_type = 'text';
                }

                $_dialog = ModuleService::dialogs($setting['type']);
                $_query = [];
                if ($setting['query']) {
                    list($k, $v) = explode('=', $setting['query']);
                    if (strpos($v, '$') === 0) {
                        $v = substr($v, 1);
                        $_query[$k] = $row[$v];
                    } else {
                        $_query[$k] = $v;
                    }
                }
                $_query['multi'] = $setting['single'] == 1 ? 0 : 1;
                $form_option['query'] = $_query;
                $form_option['url'] = $_dialog['url'];
            }

            if ($row['form_type'] == 'select') {
                $form_type = 'select';
                if ($row['data_type']) {
                    $query = [];
                    if ($setting['query']) {
                        list($k, $v) = explode('=', $setting['query']);
                        if (strpos($v, '$') === 0) {
                            $v = substr($v, 1);
                            $query[$k] = $row[$v];
                        } else {
                            $query[$k] = explode(',', $v);
                        }
                    }
                    $_model = DB::table($_search['table'])->where('status', 1);
                    foreach ($query as $k => $v) {
                        if (is_array($v)) {
                            $_model->whereIn($k, $v);
                        } else {
                            $_model->where($k, $v);
                        }
                    }
                    $rows = $_model->orderBy('sort', 'asc')
                    ->pluck($_search['name'], 'id');
                    foreach ($rows as $k => $v) {
                        $form_option[] = ['id'=> $k, 'name' => $v];
                    }
                } else {
                    $content = explode("\n", $setting['content']);
                    foreach($content as $_content) {
                        list($k, $v) = explode('|', $_content);
                        $form_option[] = ['id'=> trim($v), 'name' => trim($k)];
                    }
                }

                if ($field == 'tax_id') {
                    $form_type = 'dialog';
                    $form_option['query'] = $_query;
                    $form_option['url'] = 'customer/tax/dialog';
                }
            }

            if ($row['form_type'] == 'option') {
                $form_type = 'option';
                $_options = $row['options'];
                foreach($_options as $k => $v) {
                    $form_option[] = ['id'=> $k, 'name' => $v];
                }
            }

            $search[] = [
                'form_type' => $form_type,
                'field' => $field,
                'name' => $row['name'],
                'options' => $form_option,
            ];
        }

        $search_form = search_form((array)$options['search'], $search, 'model');

        $res['search_form'] = $search_form;

        $res['bill_id'] = $bill['id'];
        $res['name'] = $model['name'];

        return $res;
    }

    public static function js($header)
    {
        $table = $header['master_table'];
        $cols = [];
        foreach ($header['cols'] as $field => $col) {
            if ($field == 'action' && empty($col['events'])) {
                continue;
            }
            if ($col['field'] == 'created_by') {
                $col['formatter'] = 'created_by';
            }
            $cols[] = $col;
        }

        $mc = Request::module().'/'.Request::controller();
        $routes = [
            'create' => $mc.'/create',
            'delete' => $mc.'/delete',
            'edit' => $mc.'/edit',
            'store' => $mc.'/store',
            'audit' => $mc.'/audit',
            'show' => $mc.'/show',
            'import' => $mc.'/import',
            'todo' => $mc.'/todo',
        ];

        $search = [
            'search' => [
                'simple' => [
                    'el' => null,
                    'query' => (array)$header['search_form']["query"],
                ],
                'advanced' => [
                    'el' => null,
                    'query' => (array)$header['search_form']["query"],
                ],
                'forms' => (array)$header['search_form']["forms"],
            ],
            'cols' => $cols,
        ];
        
        $js = "gdoo.grids.$table = ".json_encode($search, JSON_UNESCAPED_UNICODE).";";
        $js .= "gdoo.grids.$table.action = new gridAction('".$table."', '".$header['master_name']."');";
        $js .= "gdoo.grids.$table.action.routes = ".json_encode($routes, JSON_UNESCAPED_UNICODE).';';
        $js .= "gdoo.grids.$table.action.bill_url = '{$mc}';";
        $res = '<script>'.$js.'</script>';
        return $res;
    }
}