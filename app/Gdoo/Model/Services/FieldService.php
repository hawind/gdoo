<?php namespace Gdoo\Model\Services;

use DB;
use URL;
use Request;

use App\Support\Dialog;
use App\Support\Module;
use Gdoo\Model\Services\ModuleService;

use Gdoo\Index\Services\AttachmentService;
use Gdoo\System\Models\Setting;

class FieldService
{
    public static function title()
    {
        return [
            'text' => 'text(单行文本)',
            'textarea' => 'textarea(多行文本)',
            'password' => 'password(密码文本)',
            'option' => 'enum(枚举)',
            'radio' => 'radio(单选按钮)',
            'select' => 'select(下拉菜单)',
            'select2' => 'select2(下拉菜单)',
            'checkbox' => 'checkbox(复选框)',
            'dialog' => 'dialog(对话框)',
            'urd' => 'urd(权限对话框)',
            'auto' => 'auto(宏控件)',
            'calc' => 'calc(计算控件)',
            'editor' => 'editor(编辑器)',
            'date' => 'date(日期时间)',
            'image' => 'image(单图上传)',
            'images' => 'images(多图上传)',
            'file' => 'file(文件上传)',
            'files' => 'files(多文件上传)',
            'address' => 'address(地址)',
            'region' => 'region(行政区域)',
            'location' => 'location(位置)',
            'notification' => 'notification(通知)',
            'sn' => 'sn(单据编号)',
            'audit' => 'audit(审核状态)',
            'custom' => 'custom(自定义)',
        ];
    }

    public static function tr_text($setting, $param)
    {
        $title = $param['title'];
        $name = $param['name'];
        $tips = $param['tips'];
        $type = $param['type'] == '' ? 'text' : $param['type'];
        $items = $param['items'];
        $value = isset($setting[$name]) ?  $setting[$name] : $param['value'];

        $str = '<div class="form-group">
        <div class="col-sm-3 control-label" for="'.$name.'">
            '.$title.'
            <a class="hinted" href="javascript:;" title="'.$tips.'"><i class="fa fa-question-circle"></i></a>
        </div>
        <div class="col-sm-9 control-text">';
        if ($type == 'text') {
            $str .= '<input type="text" class="form-control input-sm" value="' . $value . '" name="setting['.$name.']">';
        }
        if ($type == 'textarea') {
            $str .= '<textarea class="form-control input-sm" name="setting['.$name.']" rows="5">' . $value . '</textarea>';
        }
        if ($type == 'radio') {
            foreach ($items as $item) {
                $str .= '<label class="radio-inline"><input type="radio" value="'.$item['value'].'" name="setting['.$name.']" ' . ($value == $item['value'] ? 'checked' : '') . '> '.$item['name'].'</label>';
            }
        }
        if ($type == 'select') {
            $str .= '<select class="form-control input-sm" id="setting_'.$name.'" name="setting['.$name.']">
            <option value=""> - </option>';
            foreach ($items as $k => $v) {
                $selected = $value == $k ? ' selected' : '';
                $str .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
            }
            $str .= '</select>';
        }
        if ($type == 'align') {
            $items = ['left' => '左', 'center' => '中', 'right' => '右'];
            $str .= '<select class="form-control input-sm" name="setting['.$name.']">
            <option value=""> - </option>';
            foreach ($items as $k => $v) {
                $selected = $value == $k ? ' selected' : '';
                $str .= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
            }
            $str .= '</select>';
        }
        $str .= '
        </div>
        </div>';
        return $str;
    }

    public static function tr_texts($setting, $params)
    {
        $str = '';
        foreach ($params as $param) {
            $str .= static::tr_text($setting, $param);
        }
        return $str;
    }

    /**
     * 以下函数作用于字段添加/修改部分
     */
    public static function form_text($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '行表达式', 'name' => 'row_count', 'type' => 'textarea', 'tips' => '计算表达式，支持js语句'],
            ['title' => '列表达式', 'name' => 'cell_count', 'tips' => '列合计函数，支持: sum'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_calc($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_auto($setting = [])
    {
        $types = [
            'sys_date'                 => '当前日期，形如 1999-01-01',
            'sys_date_cn'              => '当前日期，形如 2009年1月1日',
            'sys_date_cn_s1'           => '当前日期，形如 2009年',
            'sys_date_cn_s2'           => '当前年份，形如 2009',
            'sys_date_cn_s3'           => '当前日期，形如 2009年1月',
            'sys_date_cn_s4'           => '当前日期，形如 1月1日',
            'sys_time'                 => '当前时间',
            'sys_datetime'             => '当前日期+时间',
            'sys_week'                 => '当前星期中的第几天，形如 星期一',
            'sys_user_id'              => '当前用户ID',
            'sys_user_name'            => '当前用户姓名',
            'sys_user_name_date'       => '当前用户姓名+日期',
            'sys_user_name_datetime'   => '当前用户姓名+日期+时间',
            'sys_department_name'      => '当前用户部门',
            'sys_user_post'            => '当前用户职位',
            'sys_user_post_assist'     => '当前用户辅助职位',
            'sys_sql'                  => '来自sql查询语句',
        ];

        $params = [
            ['title' => '类型', 'name' => 'type', 'items' => $types, 'tips' => '', 'type' => 'select'],
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_password($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_textarea($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '高度', 'name' => 'height', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_editor($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '高度', 'name' => 'height', 'tips' => 'px'],
            ['title' => '类型', 'name' => 'type', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '完整模式'], ['value' => 0, 'name' => '简洁模式']], 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => '', 'type' => 'textarea'],
        ];
        return static::tr_texts($setting, $params);
    }

    public static function form_select($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '选项列表', 'name' => 'content', 'tips' => '格式：选项名称1|选项值1(回车换行)', 'type' => 'textarea'],
            ['title' => '查询键', 'name' => 'query_key', 'tips' => ''],
            ['title' => '查询值', 'name' => 'query_value', 'tips' => ''],
            ['title' => '查询参数', 'name' => 'query', 'tips' => '格式：name={name}', 'type' => 'textarea'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
            ['title' => '其他选项', 'name' => 'single', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '单选'], ['value' => 0, 'name' => '多选']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // 自定义
    public static function form_custom($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '方法', 'name' => 'method', 'tips' => ''],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    // 地址选项
    public static function form_address($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    // 行政区域
    public static function form_region($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    // 流程状态
    public static function form_audit($setting = [])
    {
        return static::form_region($setting);
    }

    // 单据编号
    public static function form_sn($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            //['title' => '前缀', 'name' => 'prefix', 'tips' => '格式: DHD'],
            //['title' => '规则', 'name' => 'rule', 'tips' => '格式: {Y}{M}{D}'],
            //['title' => '序号', 'name' => 'length', 'tips' => '格式: 4'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    // 单选按钮
    public static function form_radio($setting = [])
    {
        return static::form_select($setting);
    }

    // 多选按钮
    public static function form_checkbox($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '选项列表', 'name' => 'content', 'tips' => '格式：选项名称1|选项值1(回车换行)', 'type' => 'textarea'],
            ['title' => '默认值', 'name' => 'default', 'tips' => ''],
        ];
        return static::tr_texts($setting, $params);
    }

    // 图片上传
    public static function form_image($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            //['title' => '路径', 'name' => 'path', 'tips' => '例如: calendar'],
            //['title' => '大小', 'name' => 'size', 'tips' => 'MB'],
        ];
        return static::tr_texts($setting, $params);
    }

    // 多文件上传
    public static function form_images($setting = [])
    {
        return static::form_image($setting);
    }

    // 文件上传
    public static function form_file($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            //['title' => '格式', 'name' => 'type', 'tips' => '多个格式以,号分开，如：zip,rar,tar'],
            //['title' => '路径', 'name' => 'path', 'tips' => '例如: calendar'],
            //['title' => '大小', 'name' => 'size', 'tips' => 'MB'],
        ];
        return static::tr_texts($setting, $params);
    }

    // 多文件上传
    public static function form_files($setting = [])
    {
        return static::form_file($setting);
    }

    // 通知
    public static function form_notification($setting = [])
    {
        $options = DB::table('option')->where('parent_id', 0)->pluck('name', 'value');
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '数据源', 'name' => 'type', 'items' => $options, 'tips' => '', 'type' => 'select'],
            ['title' => '默认值', 'name' => 'default', 'tips' => '格式：选中值1,选中值2'],
            ['title' => '其他选项', 'name' => 'single', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '单选'], ['value' => 0, 'name' => '多选']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // 选项菜单
    public static function form_option($setting = [])
    {
        $options = DB::table('option')->where('parent_id', 0)->pluck('name', 'value');
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '数据源', 'name' => 'type', 'items' => $options, 'tips' => '', 'type' => 'select'],
            ['title' => '默认值', 'name' => 'default', 'tips' => "格式：选中值1,选中值2"],
            ['title' => '其他选项', 'name' => 'single', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '单选'], ['value' => 0, 'name' => '多选']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // 对话框
    public static function form_dialog($setting = [])
    {
        $dialogs = ModuleService::dialogs();
        $items = [];
        foreach($dialogs as $k => $v) {
            $items[$k] = $v['name'];
        }
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'css 样式: input-inline'],
            ['title' => '数据源', 'name' => 'type', 'items' => $items, 'tips' => '', 'type' => 'select'],
            ['title' => '查询键', 'name' => 'query_key', 'tips' => ''],
            ['title' => '查询值', 'name' => 'query_value', 'tips' => ''],
            ['title' => '查询参数', 'name' => 'query', 'tips' => '格式：name={name}', 'type' => 'textarea'],
            ['title' => '默认值', 'name' => 'default', 'tips' => "格式：选中值1,选中值2"],
            ['title' => '其他选项', 'name' => 'single', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '单选'], ['value' => 0, 'name' => '多选']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // select2插件
    public static function form_select2($setting = [])
    {
        return static::form_select($setting);
    }

    // autocomplete插件
    public static function form_autocomplete($setting = [])
    {
        return static::form_select($setting);
    }

    // 权限对话框
    public static function form_urd($setting = [])
    {
        $params = [
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => '格式：选中值1,选中值2'],
            ['title' => '其他选项', 'name' => 'single', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '单选'], ['value' => 0, 'name' => '多选']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // 日期
    public static function form_date($setting = [])
    {
        $params = [
            ['title' => '显示格式', 'name' => 'type', 'value' => 'Y-m-d H:i:s', 'tips' => '格式: Y-m-d H:i:s 表示: 2001-02-13 11:20:20'],
            ['title' => '数据格式', 'name' => 'save', 'items' => ['date' => '日期', 'u' => '时间戳'], 'tips' => '', 'type' => 'select'],
            ['title' => '最小日期', 'name' => 'min_date', 'tips' => '格式：2001-01-01，表示开始日期，或者以#开头的字段值'],
            ['title' => '最大日期', 'name' => 'max_date', 'tips' => '格式：2038-01-01，表示结束日期，或者以#开头的字段值'],
            ['title' => '对齐', 'name' => 'align', 'tips' => '', 'type' => 'align'],
            ['title' => '宽度', 'name' => 'width', 'tips' => 'px'],
            ['title' => '样式', 'name' => 'css', 'tips' => 'input-inline'],
            ['title' => '默认值', 'name' => 'default', 'tips' => '', 'type' => 'radio', 'items' => [['value' => 1, 'name' => '当前时间'], ['value' => 0, 'name' => '空']]],
        ];
        return static::tr_texts($setting, $params);
    }

    // 字段扩展设置
    public static function content_field($field)
    {
        // 配置
        if (is_string($field['setting'])) {
            $setting = empty($field['setting']) ? [] : json_decode($field['setting'], true);
        } else {
            $setting = $field['setting'];
        }

        $attribute = $field['attribute'];

        if ($field['data_type']) {
            if ($field['type']) {
                $name = $field['table'].'['.$field['field'].']';
                $id = $field['table'].'_'.$field['field'];
            } else {
                $id = $field['table'].'_'.$field['field'];
            }
        } else {
            if ($field['type']) {
                $name = $field['table'].'['.$field['field'].']';
                $id = $field['table'].'_'.$field['field'];
            } else {
                $id = $field['table'].'_'.$field['field'];
            }
        }

        $attribute['key'] = $field['table'].'.'.$field['field'];

        $attribute['class'][] = 'form-control';
        $attribute['class'][] = 'input-sm';

        if ($setting['css']) {
            $attribute['class'][] = $setting['css'];
        }

        $view = $field['view'];
        if ($view['width']) {
            $attribute['style'][] = 'width:'.$view['width'].'px';
            $attribute['class'][] = 'input-inline';
        }

        if ($setting['height']) {
            $attribute['style'][] = 'height:'.$setting['height'].'px';
        }

        if ($field['validate']) {
            $attribute['validate'] = $field['validate'];
        }

        $attribute['autocomplete'] = 'off';

        $attribute['id'] = $id;
        $attribute['name'] = $name;

        $field['attribute'] = $attribute;
        $field['setting'] = $setting;
        $field['auth_id'] = auth()->id();

        return $field;
    }

    // 字段属性组合
    public static function content_attribute($attributes)
    {
        foreach ($attributes as $k => $v) {
            if ($k == 'class') {
                $attributes[$k] = $k.'="'.join(' ', $v).'"';
            } elseif ($k == 'style') {
                $attributes[$k] = $k.'="'.join(';', $v).'"';
            } else {
                $attributes[$k] = $k.'="'.$v.'"';
            }
        }
        return join(' ', $attributes);
    }

    /**
     * 以下函数作用于发布内容部分
     */
    public static function content_custom($field, $content = '')
    {
        if ($field['is_show'] == 0) {
            $content = '<div id="'.$field['table'].'_'.$field['field'].'" data-content="'.$content.'"></div>';
        } else {
            $content = '<div id="'.$field['table'].'_'.$field['field'].'">'.$content.'</div>';
        }
        
        return $content;
    }

    public static function content_text($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $type = $field['is_hide'] == 0 ? 'text' : 'hidden';

        if ($field['is_read'] == 1 && $field['is_hide'] == 1) {
            return '';
        }

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        }

        if ($field['is_write']) {
            $content = $content == '' ? $setting['default'] : $content;
        }

        if ($field['type'] == 'DECIMAL') {
            // 显示时才才格式化金额
            if ($field['is_show']) {
                list($_, $len) = explode(',', $field['length']);
                $content = number_format(floatval($content), $len);
            } else {
                $content = floatval($content);
            }
        }

        return $field['is_show'] ? $content : '<input type="'.$type.'" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
    }

    public static function content_audit($field, $content = '', $row = [])
    {
        $field = static::content_field($field);

        $content = empty($content) ? '0' : $content;
        $option = option('flow.status', $content);

        if ($content == 2) {
            if ($row['id'] > 0) {
                $logs = DB::table('model_run_log')
                ->leftJoin('model_run', 'model_run.id', '=', 'model_run_log.run_id')
                ->where('model_run_log.option', 1)
                ->where('model_run_log.updated_id', 0)
                ->where('model_run.bill_id', $field['bill']['id'])
                ->where('model_run.data_id', $row['id'])
                ->pluck('model_run_log.run_name')->implode(',');
                $content = $option.' '.$logs;
            }
        } else {
            $content = $option;
        }

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        }

        unset($field['attribute']['name']);
        return $field['is_show'] ? $content : '<input type="text" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
    }

    public static function content_sn($field, $content = '')
    {
        $field = static::content_field($field);
        $bill = $field['bill'];
        if ($field['is_write']) {
            if ($content == '') {
                $make_sn = make_sn([
                    'table' => $field['table'],
                    'bill_id' => $bill['id'],
                    'data' => $content,
                    'prefix' => $bill['sn_prefix'],
                    'rule' => $bill['sn_rule'],
                    'length' => $bill['sn_length'],
                ]);
                $content = $make_sn['new_value'];
            }
        }
        
        if ($field['is_auto'] || $field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        }

        return $field['is_show'] ? $content : '<input type="text" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
    }

    public static function content_auto($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $t = isset($setting['type']) ? $setting['type'] : '';

        $time = time();
        $user = auth()->user();

        $items = [
            '{Y}' => date('Y', $time),
            '{M}' => date('m', $time),
            '{D}' => date('d', $time),
            '{H}' => date('H', $time),
            '{I}' => date('i', $time),
            '{S}' => date('s', $time),
            'sys_date' => date('Y-m-d'),
            'sys_user_name' => $user['name'],
            'sys_user_name_datetime' => $user['name'].' '.date('Y-m-d H:i'),
            'sys_department_name' => $user->department['name'],
            '{UR}' => $user->role['name'],
            '{UP}' => $user->post['name'],
        ];

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        } else {
            if ($field['is_show'] == 0) {
                $content = $items[$t];
                //$content = $content == '' ? $items[$t] : $content;
            }

            if ($field['is_auto']) {
                $field['attribute']['readonly'] = 'readonly';
            }
        }

        return $field['is_show'] ? $content : '<input type="text" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
    }

    public static function content_address($field, $content = '')
    {
        $field = static::content_field($field);

        if ($field['is_show']) {
            return $content;
        }

        $_content = explode("\n", $content);

        $attribute = $field['attribute'];
        $id = $attribute['id'];
        $name = $attribute['name'];

        $class = ['form-control','input-sm'];
        $attr = 'class="'. join(' ', $class).'"';

        $readonly = '';
        if ($field['is_read']) {
            $readonly = 'readonly="readonly"';
        }

        $str = '<div class="form-inline"><select '.$attr.' id="'.$id.'_0" name="'.$name.'[0]" '.$readonly.'></select>';
        $str .= '&nbsp;<select '.$attr.' id="'.$id.'_1" name="'.$name.'[1]" '.$readonly.'></select>';
        $str .= '&nbsp;<select '.$attr.' id="'.$id.'_2" name="'.$name.'[2]" '.$readonly.'></select>';
        $str .= '&nbsp;<input '.$attr.' type="text" id="'.$id.'_3" name="'.$name.'[3]" placeholder="街道" value="' . $_content[3] . '" '.$readonly.' />';
        $str .= '</div>';
        
        if ($readonly == '') {
            $pcas = 'new pcas("'.$id.'_0", "'.$id.'_1", "'.$id.'_2", "'.$_content[0].'", "'.$_content[1].'", "'.$_content[2].'");';
            $str .= '<script type="text/javascript">'.$pcas.'</script>';
        }
        return $str;
    }

    public static function content_region($field, $content = '', $row)
    {
        $field = static::content_field($field);
        $attribute = $field['attribute'];
        $id = $attribute['id'];

        if ($field['is_show']) {
            $ids = [$row['province_id'], $row['city_id'], $row['county_id']];
            $content = DB::table('region')->whereIn('id', $ids)->pluck('name')->toArray();
            return join(' ', $content);
        }

        $attr = 'class="'. join(' ', $attribute['class']).'"';
        $readonly = '';
        if ($field['is_read']) {
            $readonly = 'readonly="readonly"';
        }

        $str = '<div class="form-inline"><select '.$attr.' id="'.$id.'_0" name="'.$field['table'].'[province_id]" '.$readonly.'></select>';
        $str .= '&nbsp;<select '.$attr.' id="'.$id.'_1" name="'.$field['table'].'[city_id]" '.$readonly.'></select>';
        $str .= '&nbsp;<select '.$attr.' id="'.$id.'_2" name="'.$field['table'].'[county_id]" '.$readonly.'></select>';
        $str .= '</div>';
        
        if ($readonly == '') {
            $pcas = 'new regionSelect("'.$id.'_0", "'.$id.'_1", "'.$id.'_2", "'.$row['province_id'].'", "'.$row['city_id'].'", "'.$row['county_id'].'");';
            $str .= '<script type="text/javascript">'.$pcas.'</script>';
        }
        return $str;
    }

    public static function content_password($field, $content = '')
    {
        $field = static::content_field($field);

        if ($field['is_show']) {
            return '';
        }

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        }

        return '<input type="password" ' . static::content_attribute($field['attribute']) . ' />';
    }

    public static function content_textarea($field, $content = '')
    {
        $field = static::content_field($field);

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
        }
        return $field['is_show'] ? nl2br($content) : '<textarea ' . static::content_attribute($field['attribute']) . '>' . $content . '</textarea>';
    }

    public static function content_editor($field, $content = '')
    {
        $field = static::content_field($field);
        $attribute = $field['attribute'];
        
        if ($field['is_show']) {
            return $content;
        }

        return ueditor($attribute['name'], $content);
    }

    public static function content_notification($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $str = '<label class="checkbox-inline i-checks i-checks-sm">
            <input name="notify[site]" type="checkbox" value="1" checked>
            <i></i>站内消息
        </label>
        <label class="checkbox-inline i-checks i-checks-sm">
            <input name="notify[mail]" type="checkbox" value="1">
            <i></i>邮件
        </label>
        <label class="checkbox-inline i-checks i-checks-sm">
            <input name="notify[sms]" type="checkbox" value="1">
            <i></i>短信
        </label>';
        
        return $str;
    }

    public static function content_select($field, $content = '', $row = [])
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $query = [];
        if ($setting['query']) {
            list($k, $v) = explode('=', $setting['query']);
            if (strpos($v, '$') === 0) {
                $v = substr($v, 1);
                if ($v == 'auth_id') {
                    $query[$k] = $row[$v];
                } else {
                    $query[$k] = $row[$v];
                }
            } else {
                $query[$k] = explode(',', $v);
            }
        }

        if ($field['is_show'] || $field['is_print']) {
            $str = [];
            if ($field['data_type']) {
                $model = DB::table($field['data_type'])->where('status', 1);
                foreach ($query as $k => $v) {
                    if (is_array($v)) {
                        $model->whereIn($k, $v);
                    } else {
                        $model->where($k, $v);
                    }
                }
                $rows = $model->orderBy('sort', 'asc')
                ->pluck($field['data_field'], 'id');

                foreach ($rows as $k => $v) {
                    if ($k == $content) {
                        $str[] = $v;
                    }
                }
                return join(',', $str);

            } else {
                if (empty($setting['content'])) {
                    return $content;
                } else {
                    $select = explode("\n", $setting['content']);
                    foreach ($select as $t) {
                        $n = $v = $selected = '';
                        list($n, $v) = explode('|', $t);
                        $v = is_null($v) ? trim($n) : trim($v);
                        if ($v == $content) {
                            $str[] = $n;
                        }
                    }
                    return join(',', $str);
                }
            }
        }

        $hidden = '';
        if ($field['is_read']) {
            $field['attribute']['disabled'] = 'disabled';
            $hidden = '<input name="'.$field['attribute']['name'].'" id="'.$field['attribute']['id'].'" type="hidden" value="' . $content . '" />';
            $field['attribute']['id'] = $field['attribute']['id'].'_select';
            unset($field['attribute']['name']);
        }

        $str = $hidden.'<select ' . static::content_attribute($field['attribute']) . '>';
        $str.= "<option value=''> - </option>";
        $content = $content == '' ? $setting['default'] : $content;

        if ($field['data_type']) {
            $model = DB::table($field['data_type'])->where($field['data_type'].'.status', 1);

            // 是仓库只显示授权仓库
            if ($field['data_type'] == 'warehouse') {
                $model->leftJoin('user_warehouse', 'user_warehouse.warehouse_id', '=', 'warehouse.id')
                ->where('user_warehouse.user_id', auth()->id());
            }
            
            foreach ($query as $k => $v) {
                if (is_array($v)) {
                    $model->whereIn($k, $v);
                } else {
                    $model->where($k, $v);
                }
            }
            $rows = $model->orderBy('sort', 'asc')
            ->pluck($field['data_field'], $field['data_type'].'.id');

            foreach ($rows as $k => $v) {
                $selected = $k == $content ? ' selected="selected"' : '';
                $str.= "<option value='" . $k . "'" . $selected . ">" . $v . "</option>";
            }
        } else {
            if (empty($setting['content'])) {
            } else {
                $select = explode("\n", $setting['content']);
                foreach ($select as $t) {
                    $n = $v = $selected = '';
                    list($n, $v) = explode('|', $t);
                    $v = is_null($v) ? trim($n) : trim($v);
                    $selected = $v == $content ? ' selected="selected"' : '';
                    $str.= "<option value='" . $v . "'" . $selected . ">" . $n . "</option>";
                }
            }
        }
        return $str . '</select>';
    }

    public static function content_select2($field, $content = '', $row = [])
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $related = $field['related'];

        $value = $content == 0 ? '' : $content;

        $dialog = ModuleService::dialogs($field['data_type']);

        $rows = [];
        $ids = explode(',', $value);
        if ($value && $dialog['model']) {
            $rows = $dialog['model']($ids)->pluck('name', 'id')->toArray();
        } else {
            $rows = [$content => $content];
        }

        if ($field['is_show']) {
            return join(',', $rows);
        }

        $options = [];
        foreach ($rows as $k => $v) {
            if ($related) {
                $options[] = '<option value="'.$k.'" selected>'.$v.'</option>';
            } else {
                $options[] = '<option value="'.$v.'" selected>'.$v.'</option>';
            }
        }

        $relations = explode(',', $setting['query']);
        $query = ['select2' => 'true'];
        $query['related'] = empty($related) ? 0 : 1;

        if ($relations) {
            foreach ($relations as $relation) {
                if ($relation) {
                    list($k, $v) = explode('=', $relation);
                    $query[$v] = $row[$k];
                }
            }
        }

        $attribute = $field['attribute'];

        $css = '';
        if($attribute['required']) {
            $css = 'input-select2-required';
        }

        $attribute['class'][] = 'input-select2-custom';

        $multi = (int)!$setting['single'];

        $id = $attribute['id'];
        $name = $attribute['name'];

        if ($field['is_read']) {
            $html[] = '<select name="'.$name.'" class="form-control input-sm" disabled="disabled" id="'.$id.'">'.join('', $options).'</select>';
            return join("\n", $html);
        } else {
            $html[] = '<select ' . static::content_attribute($attribute) . '>'.join('', $options).'</select>';
            $select2_options = [
                'placeholder' => '请选择'.$field['name'],
                'width' => '100%',
                'allowClear' => true,
                'search_key' => $field['data_type'],
                'containerCssClass' => $css,
                'tags' => true,
                'multiple' => $multi,
                'ajaxParams' => $query,
                'ajax' => [
                    'url' => url($dialog['url']),
                ],
            ];
            $html[] = '<script type="text/javascript">$(function($) { $("#'.$id.'").select2Field('.json_encode($select2_options, JSON_UNESCAPED_UNICODE).'); });</script>';
            return join("\n", $html);
        }
    }

    public static function content_dialog($field, $content = '', $row = [])
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $related = $field['related'];

        $value = $content == 0 ? '' : $content;

        if ($field['is_sub'] && $field['is_print']) {
            if (isset($field['_column'])) {
                return $row[$field['_column']];
            } else {
                return $row[$field['field']];
            }
        }

        $dialog = ModuleService::dialogs($setting['type']);
        $multi = (int)!$setting['single'];
        if ($related) {
        } else {
            $related['field'] = $field['field'];
            $related['table'] = $field['table'];
        }

        $attribute = $field['attribute'];
        $id = $attribute['id'];
        $name = $attribute['name'];

        $rows = '';
        if ($value) {
            if ($field['type']) {
                $ids = explode(',', $value);
                $rows = $dialog['model']($ids)->implode(',');
            } else {
                $rows = $value;
            }
        }

        if ($field['is_print']) {
            return $rows;
        }

        if ($field['is_show']) {
            return '<input type="hidden" id="'.$id.'" value="'.$content.'">'.$rows;
        }

        if ($field['is_hide'] == 1) {
            return '<input type="hidden" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
        } else {
            $width = '100%';

            if ($field['is_read']) {
                if ($setting['css'] == 'input-inline') {
                    $width = '153px';
                }
                if ($setting['width']) {
                    // $width = $setting['width'].'px';
                }
                $html[] = '<div class="select-group" style="width:'.$width.';"><input class="form-control input-sm select-readonly" readonly="readonly" value="'.$rows.'" id="'.$id.'_text">';
            } else {
                if ($setting['css'] == 'input-inline') {
                    $width = '225px';
                }
                if ($setting['width']) {
                    // $width = $setting['width'].'px';
                }

                $attribute = $field['attribute'];
                $css = $css2 = '';
                if($attribute['required']) {
                    $css = ' input-required';
                    $css2 = ' input-search-required';
                }

                $query = [];
                if ($setting['query']) {
                    list($k, $v) = explode('=', $setting['query']);
                    if (strpos($v, '$') === 0) {
                        $v = substr($v, 1);
                        $query[$k] = $row[$v];
                    } else {
                        $query[$k] = $v;
                    }
                }

                $query['url'] = $dialog['url'];
                $query['title'] = $dialog['name'];
                $query['id'] = $related['field'];
                $query['form_id'] = $related['table'];
                $query['multi'] = $multi;
                $jq = '';
                foreach ($query as $k => $v) {
                    $jq .= ' data-'.$k.'="'. $v.'"';
                }
                $html[] = '<div class="select-group input-group" style="width:'.$width.';"><input autocomplete="off" class="gdoo-dialog-input form-control input-inline input-sm'.$css.'" '.$jq.' value="'.$rows.'" id="'.$id.'_text" />';
                $html[] = '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$value.'">';
                $html[] = '<div class="input-search">';
                $html[] = '<a class="input-search-btn'.$css2.'" data-toggle="dialog-view" '.$jq.' data-id="'.$id.'"><i class="fa fa-search"></i></a>';
                $html[] = '</div>';
            }
            $html[] = '</div>';
            return join("\n", $html);
        }
    }

    public static function content_urd($field, $content = '', $row = [])
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $attribute = $field['attribute'];

        $attr_id = $attribute['id'];
        $attr_name = $attribute['name'];
        $name = str_replace('_id', '_name', $attr_name);
        $key_name = str_replace('_id', '_name', $attr_id);
        $v_id = $field['field'];
        $v_name = str_replace('_id', '_name', $v_id);

        $multi = (int)!$setting['single'];
        $params = [
            'multi' => $multi, 
            'prefix' => 1, 
            'name' => $key_name, 
            'title' => '组织架构',
            'url' => 'index/api/dialog',
            'id' => $attr_id,
            'toggle' => 'dialog-view'
        ];

        $jq = '';
        foreach ($params as $key => $value) {
            $jq .= ' data-'.$key.'="'. $value.'"';
        }

        if ($field['is_show']) {
            return $row[$v_name];
        } else {
            if ($field['is_hide'] == 1) {
                return '<input type="hidden" value="' . $content . '" ' . static::content_attribute($field['attribute']) . ' />';
            } else {
                $width = '100%';
                if ($field['is_read']) {
                    if ($setting['css'] == 'input-inline') {
                        $width = '153px';
                    }
                    if ($setting['width']) {
                        $width = $setting['width'].'px';
                    }
                    $html[] = '<div class="select-group" style="width:'.$width.';"><input class="form-control input-sm select-readonly" readonly="readonly" id="'.$attr_id.'_text" value="'.$row[$v_name].'" />';
                } else {
                    if ($setting['css'] == 'input-inline') {
                        $width = '225px';
                    }
                    if ($setting['width']) {
                        $width = $setting['width'].'px';
                    }
                    $html[] = '<div class="select-group input-group" style="width:'.$width.';"><input class="form-control input-inline input-sm" name="'.$name.'" style="cursor:pointer;" '.$jq.' readonly="readonly" value="'.$row[$v_name].'" id="'.$attr_id.'_text" />';
                    $html[] = '<div class="input-group-btn">';
                    $html[] = '<a data-toggle="dialog-clear" data-id="'.$attr_id.'" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>';
                    $html[] = '</div>';
                }
                $html[] = '<input type="hidden" id="'.$attr_id.'" name="'.$attr_name.'" value="'.$row[$v_id].'">';
                $html[] = '</div>';

                return join("\n", $html);
            }
        }
    }

    public static function content_option($field, $content = '', $row = [])
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        // 子表
        if ($field['is_show']) {
            return option($setting['type'], $content);
        }

        // 新增时设置默认值
        if (empty($row['id'])) {
            $content = $setting['default'];
        }

        if ($setting['single'] == 0) {
            $field['attribute']['multiple'] = 'multiple';
        }

        if ($field['is_read']) {
            $field['attribute']['readonly'] = 'readonly';
            $field['attribute']['onfocus'] = 'this.defaultIndex=this.selectedIndex;';
            $field['attribute']['onchange'] = 'this.selectedIndex=this.defaultIndex;';
        }

        $width = '100%';
        if ($setting['css'] == 'input-inline') {
            $width = '153px';
        }
        if ($setting['width']) {
            $width = $setting['width'].'px';
        }

        $str = '<select ' . static::content_attribute($field['attribute']) . '>';
        $str .= "<option value=''> - </option>";

        $options = option($setting['type']);
        foreach ($options as $option) {
            $selected = $option['id'] == $content ? ' selected' : '';
            $str.= "<option value='" . $option['id'] . "'" . $selected . ">" . $option['name'] . "</option>";
        }
        return $str . '</select>';
    }

    public static function content_date($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $type = isset($setting['type']) ? $setting['type'] : 'Y-m-d H:i:s';
        $save = isset($setting['save']) ? $setting['save'] : 'date';

        $time_sign = array(
            'Y'  => 'yyyy',
            'y'  => 'yy',
            'm'  => 'mm',
            'm'  => 'MM',
            'M'  => 'M',
            'n'  => 'm',
            'd'  => 'dd',
            'j'  => 'd',
            'l'  => 'DD',
            'jS' => 'D',
            'W'  => 'W',
            'H'  => 'HH',
            'h'  => 'hh',
            'G'  => 'H',
            'g'  => 'h',
            'i'  => 'mm',
            's'  => 'ss',
            'z'  => 'z',
            'c'  => 'c',
            'r'  => 'r',
            'a'  => 'a',
            't'  => 't',
            'A'  => 'A'
        );
        $time_format = strtr($type, $time_sign);

        $content = empty($content) ? ($setting['default'] == 1 ? date($type): '') : ($save == 'date' ? $content : date($type, $content));

        if ($content == '0000-00-00' || $content == '0000-00-00 00:00:00') {
            $content = '';
        }

        if ($content == '1900-01-01') {
            $content = '';
        }

        if ($field['is_show']) {
            return $content;
        }

        $attribute = $field['attribute'];

        if ($field['is_read']) {
            $attribute['readonly'] = 'readonly';
        } else {
            // 宏锁定
            if ($field['is_auto']) {
                $attribute['readonly'] = 'readonly';
            } else {
                $attribute['data-toggle'] = 'date';
                $attribute['data-format'] = $time_format;
                $attribute['data-min_date'] = $setting['min_date'];
                $attribute['data-max_date'] = $setting['max_date'];
            }
        }
        return '<input type="text" value="' .$content. '" ' . static::content_attribute($attribute) . ' />';
    }

    public static function content_radio($field, $content = '', $row, $permission)
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $select = explode("\n", $setting['content']);
        $str = [];
        $w = $permission[$field['field']]['w'] == 1 ? '' : 'disabled="disabled"';
        $disabled = $permission[$field['field']]['w'] == 1 ? '' : 'i-checks-disabled';

        if ($field['is_print']) {
            foreach ($select as $t) {
                $n = $v = '';
                list($n, $v) = explode('|', $t);
                $v = is_null($v) ? trim($n) : trim($v);
                if ($v == $content) {
                    $str[] = $n;
                }
            }
            return join(',', $str);
        }

        $attribute = $field['attribute'];
        unset($attribute['class']);
        $id = $attribute['id'];
        $name = $attribute['name'];
        
        foreach ($select as $i => $t) {
            $n = $v = '';
            list($n, $v) = explode('|', $t);
            $v = is_null($v) ? trim($n) : trim($v);
            $checked = $content == $v ? 'checked="checked"' : '';
            $attribute['id'] = $id.'_'.$i;
            if ($field['is_show']) {
                $str[] = '<div class="radio radio-inline" style="padding-left:0;"><label class="i-checks i-checks-sm"><input type="radio" disabled="disabled" name="'. $name . '" '.$w.' value="' . $v . '" '.$checked.'"><i></i>'. $n. '</label></div>';
            } else {
                $str[] = '<div class="radio radio-inline" style="padding-left:0;"><label class="i-checks i-checks-sm '.$disabled.'"><input type="radio" name="'. $name . '" '.$w.' value="' . $v . '"' . static::content_attribute($attribute).' '.$checked.'><i></i>'. $n. '</label></div>';
            }
        }
        return join('', $str);
    }

    public static function content_checkbox($field, $content = '', $row = [], $permission = [])
    {
        $field = static::content_field($field);
        // 配置
        $setting = $field['setting'];
        $default = $setting['default'];
        $content = is_null($content) ? $default : $content;
        $checkeds = [];
        $items = [];
        $values = explode(",", $content);

        if ($setting['type']) {
            $items = DB::table($setting['type'])->where('status', 1)->orderBy('sort', 'asc')->get();
        } else {
            if (not_empty($setting['content'])) {
                $selects = explode("\n", $setting['content']);
                foreach ($selects as $select) {
                    $n = $v = '';
                    list($n, $v) = explode('|', $select);
                    $v = is_null($v) ? trim($n) : trim($v);
                    $items[] = ['id' => $v, 'name' => $n];
                }
            } else {
                $items[] = ['id' => 1, 'name' => $field['name']];
            }
        }

        // 打印模式直接返回选中的值名称
        if ($field['is_print']) {
            foreach ($items as $item) {
                if ($field['type']) {
                    if (in_array($item['id'], $values)) {
                        $checkeds[] = $item['name'];
                    }
                } else {
                    if ($row[$item['id']] == 1) {
                        $checkeds[] = $item['name'];
                    }
                }
            }
            return join(',', $checkeds);
        }

        $str = [];
        foreach ($items as $item) {

            // 存在字段
            if ($field['type']) {
                $value = $v;
                $checked = in_array($v, $values) ? 'checked="checked"' : '';
                $name = $field['table'].'['.$field['field'].']['.$v.']';
                $key = $field['field'];
            } else {
                $value = 1;
                $checked = $row[$item['id']] == $value ? 'checked="checked"' : '';
                $name = $field['table'].'['.$item['id'].']';
                $key = $item['id'];
            }

            if ($field['is_show']) {
                $str[] = '<label class="i-checks i-checks-sm m-r-xs m-b-none"><input type="checkbox" id="'. $field['table'].'_'.$item['id'].'" disabled="disabled" '.$checked.'><i></i>'.$item['name'].'</label>';
            } else {
                $w = $permission[$key]['w'] == 1 ? '' : 'disabled="disabled"';
                $disabled = $permission[$key]['w'] == 1 ? '' : 'i-checks-disabled';
                $str[] = '<label class="i-checks '.$disabled.' i-checks-sm m-t-xs m-r-xs m-b-none"><input type="checkbox" '.$w.' id="'. $field['table'].'_'.$item['id'].'" name="'.$name.'" value="'.$value.'" '.$checked.'><i></i>'.$item['name'].'</label>';
            }
        }
        return join(' ', $str);
    }

    public static function content_location($field, $content = '', $row)
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $attribute = $field['attribute'];
        $id = $attribute['id'];
        $name = $attribute['name'];

        $attribute['readonly'] = 'readonly';

        if ($field['is_show']) {
            return '
            <div id="'.$id.'-media" class="media-controller">
                <i class="icon icon-map-marker text-info text-sm"></i><a href="javascript:;" data-location="'.$row['location'].'" data-longitude="'.$row['longitude'].'" data-latitude="'.$row['latitude'].'" data-toggle="map-show">'.$content.'</a>
            </div>';
        } else {
            $str = '
            <div class="input-group">
                <div class="input-group-btn">
                    <a href="javascript:;" data-location="'.$row['location'].'" data-name="'.$name.'" data-id="'.$id.'" data-longitude="'.$row['longitude'].'" data-latitude="'.$row['latitude'].'" data-toggle="map-select" class="btn btn-sm btn-info"><i class="fa fa-map"></i> 选择位置</a>
                </div>
            </div>
            <div id="'.$id.'-media" class="media-controller media-input">
                <input type="text" class="form-control input-sm" autocomplete="off" id="'.$id.'" value="' .$content. '" name="' .$name. '">
                <input type="hidden" id="'.$id.'_longitude" value="'.$row['longitude'].'" name="' .$field['table']. '[longitude]" />
                <input type="hidden" id="'.$id.'_latitude" value="'.$row['latitude'].'" name="' .$field['table']. '[latitude]" />
            </div>';
        }
        return $str;
    }

    public static function content_image($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $attribute = $field['attribute'];
        $attribute['readonly'] = 'readonly';

        $id = $attribute['id'];
        $name = $attribute['name'];

        if ($field['is_show']) {
            if (empty($content)) {
                $src = 'assets/images/nopic.jpg';
            } else {
                $src = 'uploads/'.$content;
            }
            return '
            <div id="'.$id.'-media" class="media-controller">
                <div class="media-item">
                    <img class="img-responsive img-thumbnail" src="'.url($src).'" />
                </div>
            </div>';
        } else {
            if (empty($content)) {
                $src = 'assets/images/nopic.jpg';
                $close = '';
            } else {
                $src = 'uploads/'.$content;
                $close = '<a class="close" title="删除这张图片" data-toggle="media-delete">×</a>';
            }
            $dialog = "mediaDialog('system/media/dialog', '".$name."', '".$id."', 0)";
            $str = '
            <div class="input-group">
                <div class="input-group-btn">
                    <button type="button" onclick="'.$dialog.'" class="btn btn-sm btn-info"><i class="fa fa-image"></i> 选择图片</button>
                </div>
            </div>
            <div id="'.$id.'-media" class="media-controller media-input">
                <div class="media-item">
                    <input type="hidden" value="' .$content. '" name="' .$name. '" />
                    <img class="img-responsive img-thumbnail" src="'.url($src).'" />
                    '.$close.'
                </div>
            </div>';
        }
        return $str;
    }

    public static function content_images($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];

        $attribute = $field['attribute'];
        $id = $attribute['id'];
        $name = $attribute['name'];

        $attribute['readonly'] = 'readonly';

        if ($field['is_show']) {
            $html = '<div id="'.$id.'-media" class="media-controller">';
            if (empty($content)) {
                $src = 'assets/images/nopic.jpg';
                $html .= '<div class="media-item">
                        <img class="img-responsive img-thumbnail" src="'.url($src).'" />
                    </div>';
            } else {
                $srcs = explode(',', $content);
                foreach($srcs as $src) {
                    $html .= '<div class="media-item">
                        <img class="img-responsive img-thumbnail" src="'.url('uploads/'.$src).'" />
                    </div>';
                }
            }
            $html .= '</div>';
            return $html;
        } else {
            $items = '';
            if (empty($content)) {
                $src = 'assets/images/nopic.jpg';
                $items .= '<div class="media-item">
                    <img class="img-responsive img-thumbnail" src="'.url($src).'" />
                    <input type="hidden" value="" name="' .$name. '[]" />
                    </div>';
            } else {
                $srcs = explode(",", $content);
                foreach($srcs as $src) {
                    $items .= '<div class="media-item">
                    <img class="img-responsive img-thumbnail" src="'.url('uploads/'.$src).'" />
                    <input type="hidden" value="' .$src. '" name="' .$name. '[]" />
                    <a class="close" title="删除这张图片" data-toggle="media-delete"></a>
                    </div>';
                }
            }
            $dialog = "mediaDialog('system/media/dialog', '".$name."', '".$id."', 1)";
            $html = '
            <div class="input-group">
                <div class="input-group-btn">
                    <button type="button" onclick="'.$dialog.'" class="btn btn-sm btn-info"><i class="fa fa-image"></i> 选择图片</button>
                </div>
            </div>';

            $html .= '<div id="'.$id.'-media" class="media-controller media-input">';
            $html .= $items;
            $html .= '</div>';
        }
        return $html;
    }

    public static function content_file($name, $content = '', $field = '')
    {
        // 配置
        $setting = isset($field['setting']) ? json_decode($field['setting'], true) : $field;
        // 必填字段
        $required = isset($field['not_null']) && $field['not_null'] ? ' required' : '';
        $type = base64_encode($setting['type']);
        $size = (int)$setting['size'];
        return '<input type="text" class="input-text" size="50" value="' . $content . '" name="data[' . $name . ']" id="fc_' . $name . '" ' . $required . ' />
	    <input type="button" style="width:66px;cursor:pointer;" class="button" onClick="file_info(\'fc_' . $name . '\')" value="' . trans('a-mod-164') . '" />
	    <input type="button" style="width:66px;cursor:pointer;" class="button" onClick="uploadFile(\'fc_' . $name . '\',\'' . $type . '\',\'' . $size . '\')" value="' . trans('a-mod-120') . '" />';
    }

    public static function content_files($field, $content = '')
    {
        $field = static::content_field($field);
        $setting = $field['setting'];
        $attribute = $field['attribute'];
        $name = $attribute['name'];
        $input_id = $attribute['id'];

        $config = Setting::where('type', 'system')->pluck('value', 'key');

        $attachment = AttachmentService::edit($content, $field['table'], $field['field']);

        $str = '<div id="file_'.$input_id.'" class="uploadify-queue">';

        if ($field['is_write']) {
            $str .= '<a class="btn btn-sm btn-info hinted" title="文件大小限制: '.$config['upload_max'].'MB" href="javascript:viewBox(\'attachment\', \'上传\', \''.url('index/attachment/upload', ['path' => Request::module(), 'table' => $field['table'], 'field' => $field['field']]).'\');"><i class="fa fa-cloud-upload"></i> 文件上传</a>';
            $str .= '<div class="clear"></div>';
        }

        if (count((array)$attachment['rows'])) {
            foreach ($attachment['rows'] as $file) {
                $str .= '<div id="file_queue_'.$file['id'].'" class="uploadify-queue-item">
                <span class="file-name"><i class="icon icon-paperclip"></i> <a class="option" title="下载" download="'.$file['name'].'" href="'.URL::to('uploads').'/'.$file['path'].'">'.$file['name'].'</a></span>
                <input type="hidden" class="'.$input_id.' id" name="'. $name . '[]" value="'.$file['id'].'">';
                    
                // pdf
                if (in_array($file['type'], ['pdf'])) {
                    $str .= '<a href="'.URL::to('uploads').'/'.$file['path'].'" class="btn btn-xs btn-default" target="_blank">[预览]</a>';
                } 

                // 图片
                if (in_array($file['type'], ['jpg','png','gif','bmp'])) {
                    $str .= '<img data-original="'.URL::to('uploads').'/'.$file['path'].'" /><a data-toggle="image-show" class="option">[预览]</a>';
                }

                // 删除
                if ($field['is_write']) {
                    $str .= '<span class="cancel"><a class="option gray hinted" title="删除文件" href="javascript:uploader.cancel(\'file_queue_'.$file['id'].'\');"><i class="fa fa-times-circle"></i></a></span>';
                }

                $str .= '<span class="file-size">('.human_filesize($file['size']).')</span> <span class="file-created_by"> '.$file['created_by'].' </span>';
                $str .= '</div><div class="clear"></div>';
            }
        }
        
        if ($field['is_write']) {
            $str .= '<script id="upload-item-tpl" type="text/html">
                <div id="file_draft_<%=id%>" class="uploadify-queue-item">
                    <span class="file-name"><span class="text-danger hinted" title="草稿状态">!</span> <a class="option" href="javascript:uploader.file(\'file_draft_<%=id%>\', \''.URL::to('uploads').'/<%=path%>\');"><%=name%></a></span>
                    <span class="file-size">(<%=size%>)</span>
                    
                    <img data-original="'.URL::to('uploads').'/<%=path%>" /><a data-toggle="image-show" data-title="附件预览" class="option">[预览]</a>

                    <span class="cancel"><a class="option gray hinted" title="删除文件" href="javascript:uploader.cancel(\'file_draft_<%=id%>\');"><i class="fa fa-times-circle"></i></a></span>
                    <input type="hidden" class="'.$input_id.' id" name="'. $name . '[]" value="<%=id%>" />
                </div>
                <div class="clear"></div>
            </script>';

            $str .= '<div id="fileDraft_'.$input_id.'">';
            if (count($attachment['draft'])) {
                foreach ($attachment['draft'] as $file) {
                    $str .= '<div id="queue_draft_'.$file['id'].'" class="uploadify-queue-item">
                        <span class="file-name"><span class="text-danger hinted" title="草稿附件">!</span> <a class="option" href="javascript:uploader.file(\'queue_draft_'.$file['id'].'\', \''.URL::to('uploads').'/'.$file['path'].'\');">'.$file['name'].'</a></span>
                        <span class="file-size">('.human_filesize($file['size']).')</span>';

                        // pdf
                        if (in_array($file['type'], ['pdf'])) {
                            $str .= '<a href="'.URL::to('uploads').'/'.$file['path'].'" class="btn btn-xs btn-default" target="_blank">[预览]</a>';
                        } 

                        // 图片
                        if (in_array($file['type'], ['jpg','png','gif','bmp'])) {
                            $str .= '<img data-original="'.URL::to('uploads').'/'.$file['path'].'" /><a data-toggle="image-show" class="option">[预览]</a>';
                        }

                        $str .= '<span class="cancel"><a class="option gray hinted" title="删除文件" href="javascript:uploader.cancel(\'queue_draft_'.$file['id'].'\');"><i class="fa fa-times-circle"></i></a></span>
                        <input type="hidden" class="'.$input_id.' id" name="'. $name . '[]" value="'.$file['id'].'">
                    </div>
                    <div class="clear"></div>';
                }
            }
            $str .= '</div>';
        }

        $str .= '</div><script type="text/javascript">
        (function($) {
            var galley_id = "file_'.$input_id.'";
            var galley = document.getElementById(galley_id);
            var viewer = new Viewer(galley, {
                navbar: false,
                url: "data-original",
            });
            $("#" + galley_id).on("click", \'[data-toggle="image-show"]\', function() {
                $(this).prev().click();
            });
        })(jQuery);
        </script>';
        return $str;
    }
}
