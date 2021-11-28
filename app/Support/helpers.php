<?php

use Illuminate\Support\Arr;

/**
 * 公共文件目录
 */
function public_path($path = '')
{
    return base_path('public').($path ? '/'.$path : $path);
}

/**
 * 文件上传目录
 */
function upload_path($path = '')
{
    return public_path('uploads').($path ? '/'.$path : $path);
}

// 序号规则生成
function make_sn($config, $update = false)
{
    if ($config['date']) {
        $time = strtotime($config['date']);
    } else {
        $time = time();
    }
    $items = [
        '{y}' => date('y', $time),
        '{Y}' => date('Y', $time),
        '{M}' => date('m', $time),
        '{D}' => date('d', $time),
        '{H}' => date('H', $time),
        '{I}' => date('i', $time),
        '{S}' => date('s', $time),
    ];

    $table = $config['table'];
    $data = $config['data'];
    $prefix = $config['prefix'];
    $length = $config['length'] > 0 ? $config['length'] : 4;
    $rule = empty($config['rule']) ? '{Y}{M}{D}' : $config['rule'];
    $bill_id = $config['bill_id'];

    // 生成序号调用插件
    $_data = App\Support\Hook::fire($table . '.onBillSeqNo', ['bill_id' => $bill_id, 'rule' => $rule]);
    extract($_data);

    if ($data) {
        $data = str_replace($prefix, '', $data);
        $new_rule = mb_substr($data, 0, mb_strlen($data) - $length);
    } else {
        $new_rule = str_replace(array_keys($items), array_values($items), $rule);
    }

    $no = DB::table('model_seq_no')
    ->where('bill_id', $bill_id)
    ->where('rule_code', $new_rule)
    ->first();

    if (empty($no)) {
        $new_sn = 1;
        if ($update == true) {
            DB::table('model_seq_no')
            ->insert([
                'bill_id' => $bill_id,
                'rule_code' => $new_rule,
                'seq_no' => $new_sn,
            ]);
        }
    } else {
        $new_sn = (int) $no['seq_no'] + 1;
        if ($update == true) {
            DB::table('model_seq_no')
            ->where('bill_id', $bill_id)
            ->where('rule_code', $new_rule)
            ->update([
                'seq_no' => $new_sn,
            ]);
        }
    }
    $new_value = $config['prefix'] . $new_rule . str_pad($new_sn, $length, '0', STR_PAD_LEFT);
    return ['new_rule' => $new_rule, 'new_sn' => $new_sn, 'new_value' => $new_value];
}

// 高级搜索生成 where 条件
function search_condition($query)
{
    $search = $query['search'];
    $type = $query['condition'];

    switch ($type) {
        case 'is':
            $condition = array('=', $search);
            break;
        case 'isnot':
            $condition = array('!=', $search);
            break;
        case 'like':
            $condition = array('like', '%' . $search . '%');
            break;
        case 'not_like':
            $condition = array('not like', '%' . $search . '%');
            break;
        case 'start_with':
            $condition = array('like', $search . '%');
            break;
        case 'not_start_with':
            $condition = array('not like', $search . '%');
            break;
        case 'end_with':
            $condition = array('like', '%' . $search);
            break;
        case 'empty':
            $condition = array('=', '');
            break;
        case 'not_empty':
            $condition = array('!=', '');
            break;
        case 'gt':
            $condition = array('>', $search);
            break;
        case 'egt':
            $condition = array('>=', $search);
            break;
        case 'lt':
            $condition = array('<', $search);
            break;
        case 'elt':
            $condition = array('<=', $search);
            break;
        case 'eq':
            $condition = array('=', $search);
            break;
        case 'neq':
            $condition = array('!=', $search);
            break;
        case 'birthday':
            $condition = array('birthday', $search);
            break;
        case 'birthbetween':
            $condition = array('birthbetween', $search);
            break;
        case 'pacs':
            $condition = array('pacs', $search);
            break;
        case 'region':
            $condition = array('region', $search);
            break;
        case 'in':
            $condition = array('in', explode(',', $search));
            break;
        case 'dialog':
            $condition = array('in', explode(',', $search));
            break;
        case 'address':
            $condition = array('!=', '');
            break;
        case 'second2':
            $condition = array('second2', [strtotime($search[0]), strtotime($search[1])]);
            break;
        case 'between':
            $search = strtotime($search);
            $condition = array('between', array($search - 1, $search + 86400));
            break;
        case 'not_between':
            $search = strtotime($search);
            $condition = array('not_between', array($search, $search + 86399));
            break;
        case 'tlt':
            $condition = array('<', strtotime($search));
            break;
        case 'tgt':
            $condition = array('>', strtotime($search) + 86400);
            break;
        default:
            $condition = array('=', $search);
    }

    return $condition;
}

// 组合搜索表单
function search_form($params = [], $columns = [], $type = 'old')
{
    if ($params['referer']) {
        $uri = join('_', Request::segments());
        Session::put('referer_' . $uri, URL::full());
    }

    $params['advanced'] = isset($params['advanced']) ? $params['advanced'] : 0;

    $gets = Request::all();

    $query = $where = [];

    foreach ($gets as $key => $get) {
        $key = str_replace('_', '.', $key);
        Arr::set($query, $key, $get);
    }

    if ($query['field']) {
        foreach ($query['field'] as $i => $field) {
            $forms['field'][$i] = $field;
            $forms['condition'][$i] = $query['condition'][$i];
            $forms['search'][$i] = $query['search'][$i];
            $forms['option'][$i] = $query['option'][$i];

            $where[$i]['field'] = $field;
            $where[$i]['condition'] = $query['condition'][$i];
            $where[$i]['search'] = $query['search'][$i];

            $active = 0;

            if ($query['condition'][$i] == 'not_empty' || $query['condition'][$i] == 'empty') {
                $active = 1;
            }

            if ($active == 0) {
                $values = is_array($query['search'][$i]) ? $query['search'][$i] : [$query['search'][$i]];

                foreach ($values as $key => $value) {
                    if ($value == '') {
                        continue;
                    }
                    $active = 1;
                }
            }
            $where[$i]['active'] = $active;
        }
    } else {
        if ($type == 'model') {
            foreach ($columns as $i => $column) {
                $forms['field'][$i] = $column['field'];
                $forms['condition'][$i] = '';
                $forms['search'][$i] = isset($column['value']) ? $column['value'] : '';
                $forms['option'][$i] = $column['options'];
            }
        } else {
            foreach ($columns as $i => $column) {
                $forms['field'][$i] = $column[1];
                $forms['condition'][$i] = '';
                $forms['search'][$i] = isset($column[3]) ? $column[3] : '';
                $forms['option'][$i] = $column['options'];
            }
        }
    }

    foreach ($params as $key => $default) {
        $params[$key] = Request::get($key, $default);
        $forms[$key] = $params[$key];
    }

    $gets['limit'] = $gets['limit'] > 0 ? $gets['limit'] : 50;
    $search['forms'] = $forms;
    $search['columns'] = $columns;
    $search['params'] = $params;
    $search['where'] = $where;
    $search['query'] = $gets + $params;
    return $search;
}

/**
 * 选择圈负责客户列表
 */
function regionCustomer($table = 'customer')
{
    $user = Auth::user();
    $role = DB::table('role')->find($user->role_id);
    $level = Gdoo\User\Services\UserService::authorise();

    $res['columns'] = [];
    $res['whereIn'] = [];
    $res['table'] = $table;

    $res['region1'] = [];
    $res['region2'] = [];
    $res['region3'] = [];
    $res['customer'] = [];
    $res['authorise'] = false;

    $query = [
        'region1_id' => 0,
        'region2_id' => 0,
        'region3_id' => 0,
        'customer_id' => 0,
    ];

    foreach ($query as $k => $v) {
        $query[$k] = Request::get($k, $v);
    }

    $regions = DB::table('customer_region')->get()->toNested();

    // 审批权限
    $owners = DB::table('customer_region')
    ->where('owner_user_id', $user->id)
    ->get()->toArray();

    // 查询权限
    $assists = DB::table('customer_region')
    ->whereRaw(db_instr('owner_assist', $user->id))
    ->get()->toArray();

    $region1 = $region2 = $region3 = [];
    $ids = [];
    foreach ($owners as $v) {
        $ids = array_merge($ids, $regions[$v['id']]['parent']);
        $ids = array_merge($ids, $regions[$v['id']]['child']);
    }
    foreach ($assists as $v) {
        $ids = array_merge($ids, $regions[$v['id']]['parent']);
        $ids = array_merge($ids, $regions[$v['id']]['child']);
    }

    $role_type = 'self';

    if (empty($ids)) {
        $role_type = 'all';
    } else {
        $role_type = 'region';
    }  

    // 销售组
    if ($level == 5) {
        $role_type = 'region';
    }
    
    // 客户
    if ($role['code'] == 'c001') {
        $role_type = 'customer';
    }

    // 客户联系人
    if ($role['code'] == 'c002') {
        $role_type = 'customer_contact';
    }

    // 登录账号类型
    switch ($role_type) {
            // 区域权限
        case 'region':
            // 查询省区权限
            $region1 = DB::table('customer_region')
            ->whereIn('id', $ids)
            ->where('layer', 1)
            ->get();

            $model = DB::table('customer_region')
            ->whereIn('id', $ids)
            ->where('layer', 2);
            if ($query['region1_id'] > 0) {
                $model->where('parent_id', $query['region1_id']);
            }
            $region2 = $model->get();

            $model = DB::table('customer_region')
            ->whereIn('id', $ids);

            if ($query['region1_id']) {
                $model->whereIn('parent_id', $region2->pluck('id'));
            }
            if ($query['region2_id'] > 0) {
                $model->where('parent_id', $query['region2_id']);
            }
            $region3 = $model->where('layer', 3)->get();

            if ($owners) {
                $res['owner_user'] = $owners;
            }
            if ($assists) {
                $res['owner_assist'] = $assists;
            }
            $res['region1'] = $region1;
            if ($query['region1_id'] > 0) {
                $res['region2'] = $region2;
            }
            if ($query['region2_id'] > 0) {
                $res['region3'] = $region3;
            }
            if ($query['region1_id'] == 0) {
                $query['region2_id'] = 0;
                $query['region3_id'] = 0;
                $query['customer_id'] = 0;
            }
            if ($query['region2_id'] == 0) {
                $query['region3_id'] = 0;
                $query['customer_id'] = 0;
            }

            if ($query['region3_id'] > 0) {
                $res['whereIn'][$table . '.region_id'] = [$query['region3_id']];
            } else {
                $res['whereIn'][$table . '.region_id'] = $region3->pluck('id')->toArray();
                $query['customer_id'] = 0;
            }
            $res['regionIn'] = $res['whereIn'][$table . '.region_id'];
            $res['authorise'] = true;
            break;
            // 客户角色
        case 'customer':
            $users[] = $user->id;
            $customerIds = DB::table('customer')->where('user_id', $user->id)->pluck('id');
            $res['whereIn'][$table . '.id'] = $customerIds;
            $res['customerIn'] = $customerIds;
            $res['regionIn'] = [];
            $res['authorise'] = true;
            break;
            // 客户联系人
        case 'customer_contact':
            $users[] = $user->id;
            $customerIds = DB::table('customer_contact')->where('user_id', $user->id)->pluck('customer_id');
            $res['whereIn'][$table . '.id'] = $customerIds;
            $res['customerIn'] = $customerIds;
            $res['regionIn'] = [];
            $res['authorise'] = true;
            break;
        case 'self':
            $users[] = $user->id;
            $res['whereIn'][$table . '.created_id'] = $users;
            $res['customerIn'] = [];
            $res['regionIn'] = [];
            $res['authorise'] = true;
            break;
        case 'all':
            if ($query['region1_id'] == 0) {
                $query['region2_id'] = 0;
                $query['region3_id'] = 0;
                $query['customer_id'] = 0;
            }
            if ($query['region2_id'] == 0) {
                $query['region3_id'] = 0;
                $query['customer_id'] = 0;
            }
            $region1 = DB::table('customer_region')
            ->where('layer', 1)
            ->get();

            $model = DB::table('customer_region');
            if ($query['region1_id'] > 0) {
                $model->where('parent_id', $query['region1_id']);
            } else {
                $model->whereIn('parent_id', $region1->pluck('id'));
            }
            $region2 = $model->where('layer', 2)->get();

            $model = DB::table('customer_region');
            if ($query['region2_id'] > 0) {
                $model->where('parent_id', $query['region2_id']);
            } else {
                $model->whereIn('parent_id', $region2->pluck('id'));
            }
            $region3 = $model->where('layer', 3)->get();

            $res['region1'] = $region1;

            if ($query['region1_id'] > 0) {
                $res['region2'] = $region2;
                $res['authorise'] = true;
            }

            if ($query['region2_id'] >  0) {
                $res['region3'] = $region3;
                $res['authorise'] = true;
            }

            if ($query['region3_id'] > 0) {
                $res['whereIn'][$table . '.region_id'] = [$query['region3_id']];
            } else {
                $res['whereIn'][$table . '.region_id'] = $region3->pluck('id')->toArray();
            }
            $res['regionIn'] = $res['whereIn'][$table . '.region_id'];
            break;
            // 默认其他角色
        default:
    }

    if ($query['region3_id'] > 0) {
        $res['customer'] = DB::table('customer')
        ->where('customer.region_id', $query['region3_id'])
        ->get(['id', 'status', 'name as customer_name'])->toArray();
    }

    if ($query['customer_id'] > 0) {
        $res['whereIn'] = [];
        $res['whereIn'][$table . '.id'] = [$query['customer_id']];
    }

    // 处理区域权限
    if ($level == 5) {
        $res['authorise'] = true;
    }

    $res['query'] = $query;
    return $res;
}

/**
 * 检查权限授权层级
 */
function authorise($action = null, $asset_name = null)
{
    return Gdoo\User\Services\UserService::authorise($action, $asset_name);
}

/**
 * 生成缩略图
 */
function image_thumb($file, $thumb_file, $width = 750)
{
    if (is_file($thumb_file)) {
        return true;
    }

    if (is_file($file)) {
        $temp = getimagesize($file);
        $srcw = $temp[0];
        $srch = $temp[1];
        $type = $temp[2];

        if ($srcw > $width) {
            switch ($type) {
                case 1:
                $image = imagecreatefromgif($file);
                break;
                case 2:
                $image = imagecreatefromjpeg($file);
                break;
                case 3:
                $image = imagecreatefrompng($file);
                break;
            }

            if (function_exists('imageantialias')) {
                imageantialias($image, true);
            }

            // 计算绽放比例
            $rate = $width / $srcw;
            // 计算出缩放后的高度
            $height = floor($srch * $rate);
            // 创建一个缩放的画布
            $dest = imagecreatetruecolor($width, $height);

            // 处理png透明
            imagealphablending($dest, false);
            imagesavealpha($dest, true);

            // 缩放
            imagecopyresampled($dest, $image, 0, 0, 0, 0, $width, $height, $srcw, $srch);

            switch ($type) {
                case 1:
                imagegif($dest, $thumb_file);
                break;
                case 2:
                imagejpeg($dest, $thumb_file, 75);
                break;
                case 3:
                imagepng($dest, $thumb_file);
                break;
            }
            if ($image) {
                imagedestroy($image);
            }
            return true;
        }
        return false;
    }
}

/**
 * 图片等比例缩放功能
 * $src_file:原文件
 * $dst_w:目标输出的宽
 * $dst_h:目标输出的高
 */
function image_resize($src_file, $dst_w, $dst_h)
{
    # 获取图片信息
    $imarr = getimagesize($src_file);

    # 获取图片类型 / 变量函数
    switch ($imarr[2]) {
        case 1:
            $imagecreatefrom = "imagecreatefromgif";
            $imageout = "imagegif";
            break;
        case 2:
            $imagecreatefrom = "imagecreatefromjpeg";
            $imageout = "imagejpeg";
            break;
        case 3:
            $imagecreatefrom = "imagecreatefrompng";
            $imageout = "imagepng";
            break;
    }

    # 大图
    $src_im = $imagecreatefrom($src_file);

    # 等比例计算目标资源的宽和高
    # 大图大小
    $src_w = imagesx($src_im);
    $src_h = imagesy($src_im);

    # 等比例缩放
    $scale = ($src_w / $dst_w) > ($src_h / $dst_h) ? ($src_w / $dst_w) : ($src_h / $dst_h);

    # 向下取整
    $dst_w = floor($src_w / $scale);
    $dst_h = floor($src_h / $scale);

    # 计算结束

    # 小图
    $dst_im = imagecreatetruecolor($dst_w, $dst_h);

    # 小图坐标
    $dst_x = 0;
    $dst_y = 0;

    # 大图坐标
    $src_x = 0;
    $src_y = 0;

    # 缩放
    imagecopyresampled($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    # 获取大图文件名并加工成小图文件名
    $t_name = 't_' . basename($src_file);

    # 获取大图文件目录
    $s_dir = dirname($src_file);

    # 定义小图保存目录，与大图一样
    $s_file = $s_dir . '/' . $t_name;

    $quality = 100;

    # 输出小图
    if ($imageout == "imagejpeg") {
        $imageout($dst_im, $src_file, $quality);
    } else {
        $imageout($dst_im, $src_file);
    }
}

// 用户头像处理
function avatar($avatar)
{
    if (is_file(upload_path('avatar') . '/' . $avatar)) {
        $src = URL::to('uploads/avatar') . '/' . $avatar;
    } else {
        $src = URL::to('assets/') . '/images/a1.jpg';
    }
    return $src;
}

/**
 * 计算年龄使用
 */
function date_year($date)
{
    if ($date == '0000-00-00') {
        return 0;
    }

    $d = new Carbon\Carbon($date);
    return $d->diffInYears();
}

/**
 * 计算剩余时间
 */
function remain_time($start, $end, $format = '%y年%m个月%d天%h小时%i分钟')
{
    if ($start == 0 || $end == 0) {
        return '';
    }
    $start = Carbon\Carbon::createFromTimeStamp($start);
    $end  = Carbon\Carbon::createFromTimeStamp($end);
    $diff = $start->diff($end);
    return $format == '' ? $diff : $diff->format($format);
}

function time_day_hour($time)
{
    $second = time() - $time;
    $day = floor($second / (3600 * 24));
    // 除去整天之后剩余的时间
    $second = $second % (3600 * 24);
    $hour = floor($second / 3600);
    return $day . '天' . $hour . '小时';
}

/**
 * 获取人性化的时间
 */
function human_time($time)
{
    return Carbon\Carbon::createFromTimeStamp($time)->diffForHumans();
}

/**
 * 人性化文件大小格式
 *
 * @param  int $bytes 文件字节
 * @return string     字符串
 */
function human_filesize($bytes)
{
    $s = ['B', 'KB', 'MB', 'GB', 'TB'];
    for ($f = 0; $bytes >= 1024 && $f < 4; $f++) {
        $bytes /= 1024;
    }
    return number_format((int) $bytes, 2) . $s[$f];
}

/**
 * 生产时间范围
 * 格式: 2012-8-20 - 2012-8-28
 */
function date_range($first, $last, $step = '+1 day', $format = 'Y-m-d')
{
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);
    while ($current <= $last) {
        $dates[] = date($format, $current);
        $current = strtotime($step, $current);
    }
    return $dates;
}

// 时间戳格式化
function format_datetime($value, $default = '', $format = 'Y-m-d H:i')
{
    if ($value instanceof Carbon\Carbon) {
        $value = $value->getTimestamp();
    }

    if ($default) {
        $data = $default;
    }
    if ($value) {
        $data = $value;
    }

    if (strlen($data) != 10) {
        return '';
    }

    return $data ? date($format, $data) : '';
}

// 时间戳格式化到日期
function format_date($value, $default = '', $format = 'Y-m-d')
{
    return format_datetime($value, $default, $format);
}

// 时间戳格式化时间
function format_time($value, $default = '', $format = 'H:i')
{
    return format_datetime($value, $default, $format);
}

/**
 * 数字金额转换成大写金额
 */
function str_rmb($money)
{
    // 四舍五入
    $money = round($money, 2);

    if ($money <= 0) {
        return '零元';
    }

    $units   = array('', '拾', '佰', '仟', '', '万', '亿', '兆');
    $amount  = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
    // 拆分小数点
    $arr     = explode('.', $money);
    // 翻转整数
    $money = strrev($arr[0]);

    // 获取数字的长度
    $length = strlen($money);

    for ($i = 0; $i < $length; $i++) {
        // 获取大写数字
        $int[$i] = $amount[$money[$i]];

        // 获取整数位
        if (!empty($money[$i])) {
            $int[$i] .= $units[$i % 4];
        }

        // 取整
        if ($i % 4 == 0) {
            $int[$i] .= $units[4 + floor($i / 4)];
        }
    }
    $con = isset($arr[1]) ? '元' . $amount[$arr[1][0]] . '角' . $amount[$arr[1][1]] . '分' : '元整';
    // 整合数组为字符串
    return implode('', array_reverse($int)) . $con;
}

/**
 * 根据参数自动获取模块控制器和方法组合 URL
 */
function url_build($path = null, $params = [])
{
    $module = Request::module();
    $controller = Request::controller();
    $action = Request::action();

    if (empty($path)) {
        $path = $module . '/' . $controller . '/' . $action;
    } else {
        $count = substr_count($path, '/');
        if ($count == 0) {
            $path = $module . '/' . $controller . '/' . $path;
        } elseif ($count == 1) {
            $path = $module . '/' . $path;
        }
    }

    if ($params) {
        $path = $path . '?' . http_build_query($params);
    }
    return URL::to($path);
}

/**
 * 组合URL使用referer
 */
function url_referer($path = null, $params = [], $referer = 1)
{
    // 模块内的跳转条件
    if ($referer) {
        $module = Request::module();
        $controller = Request::controller();
        $action = Request::action();

        if (empty($path)) {
            $uri = $module . '_' . $controller . '_' . $action;
        } else {
            $count = substr_count($path, '_');
            if ($count == 0) {
                $uri = $module . '_' . $controller . '_' . $path;
            } elseif ($count == 1) {
                $uri = $module . '_' . $path;
            }
        }
        $uri = Session::pull('referer_' . $uri);
        if ($uri) {
            return $uri;
        }
    }
    return url_build($path, $params);
}

/**
 * 复写Laravel的url
 */
function url($path = null, $params = [], $appends = [])
{
    $params = array_merge($params, $appends);
    return url_build($path, $params);
}

/**
 * 查找指定的字符串，支持逗号分隔多个字符
 */
function array_find($data, $key)
{
    $key = array_filter(explode(',', $key));
    if (empty($key)) {
        return false;
    }
    // 不是数组进行分割
    is_array($data) or $data = explode(',', $data);
    $data = array_filter($data);
    if (array_intersect($data, $key)) {
        return true;
    }
    return false;
}

/**
 * 数组重新按指定键排序
 */
function array_by($items, $key = 'id')
{
    $maps = [];
    if (empty($items)) {
        return $maps;
    }

    if ($key) {
        foreach ($items as $item) {
            $maps[$item[$key]] = $item;
        }
    } else {
        foreach ($items as $item) {
            $maps[] = $item;
        }
    }
    return $maps;
}

/** 把嵌套的数组转换到扁平化 */
function reduce_tree($arr, $level = -1)
{
    static $tree = [];
    $level++;
    foreach ($arr as $k => $v) {
        $v['level'] = $level;
        if ($v['children']) {
            $children = $v['children'];
            unset($v['children']);
            $tree[] = $v;
            reduce_tree($children, $level);
        } else {
            $tree[] = $v;
        }
    }
    return $tree;
}

/**
 * 把扁平数组格式化成嵌套数组
 */
function array_tree($items, $key = 'name', $id = 'id', $parentId = 'parent_id', $children = 'children')
{
    $items = is_array($items) ? $items : $items->toArray();
    $tree = $map = array();
    foreach ($items as $item) {
        $item['text'] = $item[$key];
        $item['folder'] = false;
        $item['isLeaf'] = true;
        $item['key'] = $item['id'];
        $map[$item[$id]] = $item;
    }

    foreach ($items as $item) {
        if (isset($map[$item[$parentId]])) {
            $map[$item[$parentId]]['folder'] = true;
            $map[$item[$parentId]]['isLeaf'] = false;
            $map[$item[$parentId]][$children][] = &$map[$item[$id]];
        } else {
            $tree[] = &$map[$item[$id]];
        }
    }
    unset($map, $items);
    return $tree;
}

/**
 * 重建树形结构的左右值
 *
 * @var $parent_id 构建的开始id
 */
function tree_rebuild($table, $parent_id = 0, $left = 0)
{
    // 左值 +1 是右值
    $right = $left + 1;

    // 获得这个节点的所有子节点
    $rows = DB::table($table)->where('parent_id', $parent_id)
        ->orderBy('sort', 'asc')
        ->get(['id', 'parent_id', 'lft', 'rgt']);

    if (sizeof($rows)) {
        foreach ($rows as $row) {
            // 这个节点的子$right是当前的右值，这是由treeRebuild函数递增
            $right = tree_rebuild($table, $row['id'], $right);
        }
    }

    // 更新左右值
    DB::table($table)->where('id', $parent_id)->orderBy('sort', 'asc')
        ->update(['lft' => $left, 'rgt' => $right]);

    // 返回此节点的右值+1
    return $right + 1;
}

function array_nest(&$items, $text = 'name')
{
    if (empty($items)) {
        return;
    }

    $tree = [];
    foreach ($items as $item) {
        $item['layer_level'] = 0;
        $item['layer_paths'] = $item['id'];
        $item['parent'] = [$item['id']];
        $item['child'] = [$item['id']];
        $item['layer_childs'] = $item['id'];
        $item['layer_html']  = '';
        $item['layer_space'] = '';

        $item['folder'] = false;
        $item['isLeaf'] = true;
        $item['expanded'] = false;
        $item['loaded'] = true;

        $item['text'] = $item[$text];
        $item['tree_path'] = [$item[$text]];
        $tree[$item['id']] = $item;
    }

    foreach ($items as $item) {
        if (isset($tree[$item['parent_id']])) {
            $tree[$item['parent_id']]['folder'] = true;
            $tree[$item['parent_id']]['isLeaf'] = false;
            $tree[$item['parent_id']]['expanded'] = true;

            $tree[$item['id']]['text'] = $tree[$item['parent_id']]['text'] . '/' . $tree[$item['id']]['text'];

            $tree[$item['id']]['layer_html'] = $tree[$item['parent_id']]['layer_html'] . '<span class="layer">|&ndash; </span>';
            $tree[$item['id']]['layer_space'] = $tree[$item['parent_id']]['layer_space'] . '　';
            $tree[$item['id']]['layer_level'] = $tree[$item['parent_id']]['layer_level'] + 1;

            $tree[$item['id']]['layer_paths'] = $tree[$item['parent_id']]['layer_paths'] . ',' . $tree[$item['id']]['layer_paths'];
            $tree[$item['parent_id']]['layer_childs'] = $tree[$item['id']]['layer_childs'] . ',' . $tree[$item['parent_id']]['layer_childs'];

            $tree[$item['id']]['tree_path'] = array_merge($tree[$item['parent_id']]['tree_path'], $tree[$item['id']]['tree_path']);

            $tree[$item['id']]['parent'] = array_merge($tree[$item['parent_id']]['parent'], $tree[$item['id']]['parent']);
            $tree[$item['parent_id']]['child'] = array_merge($tree[$item['parent_id']]['child'], $tree[$item['id']]['child']);
        }
    }
    return $tree;
}

/**
 * 百度编辑器
 */
function ueditor($name = 'content', $value = '', $config = [])
{
    static $loaded;
    if (empty($loaded)) {
        $e[] = '<script type="text/javascript">window.UEDITOR_HOME_URL = "' . URL::to('assets/vendor/ueditor') . '/";</script>';
        $e[] = '<script type="text/javascript" src="' . URL::to('assets/vendor/ueditor/ueditor.config.js') . '"></script>';
        $e[] = '<script type="text/javascript" src="' . URL::to('assets/vendor/ueditor/ueditor.all.min.js') . '"></script>';
        $loaded = true;
    }
    $e[] = '<script type="text/plain" name="' . $name . '" id="' . $name . '">' . $value . '</script>';
    $e[] = '<script type="text/javascript">var editor = UE.getEditor("' . $name . '",{initialFrameHeight:180,focus:true,initialFrameWidth:"100%"});</script>';
    return join("\n", $e);
}

/**
 * 获取选项
 */
function option($key, $value = '')
{
    static $items = [];
    static $values = [];

    if (empty($items[$key])) {
        $parent = DB::table('option')->where('value', $key)->first();
        if ($parent === null) {
            return [];
        }
        $items[$key] = DB::table('option')->where('parent_id', $parent['id'])->orderBy('sort', 'asc')->get(['name', 'value as id']);
        $values[$key] = array_by($items[$key], 'id');
    }

    if (func_num_args() == 2) {
        return $values[$key][$value]['name'];
    } else {
        return $items[$key];
    }
}

/**
 * 获取单用户数据
 */
function get_user($id = 0, $field = '', $letter = true)
{
    static $users = [];

    $args = func_num_args();

    if (empty($users)) {
        $users = DB::table('user')
            ->get(['id', 'department_id', 'role_id', 'username', 'name', 'email', 'phone', 'birthday', 'gender']);
        $users = array_by($users);
    }

    if ($args == 0) {
        return $users;
    }

    if ($args == 1) {
        return $users[$id];
    }

    if ($field == 'name' && $letter == true) {
        return '<button type="button" class="option" data-toggle="dialog-form" data-title="私信" data-url="' . url('user/message/create', ['user_id' => $id]) . '" data-id="user_message">' . $users[$id][$field] . '</button>';
    } else {
        return $users[$id][$field];
    }
}

/* 读取表格内容 */
function readExcel($filename, $extension = '')
{
    if (empty($extension)) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
    } else {
        $ext = $extension;
    }
    if ($ext == 'xlsx') {
        $type = 'Excel2007';
    } else {
        $type = 'Excel5';
    }
    // 设置以Excel5格式(Excel97-2003工作簿)
    $reader = PHPExcel_IOFactory::createReader($type);
    // 载入excel文件
    $PHPExcel = $reader->load($filename);
    // 读取第一個工作表
    $sheet = $PHPExcel->getSheet(0);
    // 取得总行数
    $highestRow = $sheet->getHighestRow();
    // 取得总列数
    $highestColumm = $sheet->getHighestColumn();
    // 转换列数
    $highestColumm = PHPExcel_Cell::columnIndexFromString($highestColumm);

    $rows = [];

    /** 循环读取每个单元格的数据 */
    // 行数是以第1行开始
    for ($i = 1; $i <= $highestRow; $i++) {
        // 列数是以A列开始
        for ($j = 0; $j < $highestColumm; $j++) {
            $rc = PHPExcel_Cell::stringFromColumnIndex($j) . $i;
            $rows[$i][] = (string) $sheet->getCell($rc)->getValue();
        }
    }
    return $rows;
}

/* 导出表格 */
function writeExcel($columns, $data, $filename)
{
    $obj = new PHPExcel();
    $obj->setActiveSheetIndex(0);
    $obj->getActiveSheet()->setTitle('sheet0');

    // 设置单元格宽度
    $obj->getActiveSheet()->getDefaultColumnDimension()->setWidth(16);

    $j = 0;
    // 设置第一行表格样式和名字
    foreach ($columns as $column) {
        $obj->getActiveSheet()->getStyleByColumnAndRow($j, 1)->getFont()->setBold(true);
        $obj->getActiveSheet()->setCellValueByColumnAndRow($j, 1, $column['label']);
        $j++;
    }

    $row = 2;
    foreach ($data as $i => $rows) {
        $col = 0;
        foreach ($columns as $key => $column) {
            $obj->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $rows[$column['name']]);
            $col++;
        }
        $row++;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . urlencode($filename) . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($obj, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

// 显示商品图片
function goodsImage($v)
{
    $image = $v['image'] ? $v['image'] : 'products/' . $v['id'] . '.jpg';
    $image_path = upload_path() . '/' . $image;
    if (is_file($image_path)) {
        $thumb = thumb($image_path, 100, 100);
        $file = pathinfo($image);
        $thumb = $public_url . '/uploads/' . $file['dirname'] . '/' . $thumb;
        return '<a class="goods-image" rel="' . $public_url . "/uploads/" . $v['image'] . '"><img class="thumbnail thumb-md goods-thumb" src="' . $thumb . '"></a>';
    } else {
        $thumb = $public_url . '/assets/images/default_img.png';
        return '<img class="thumbnail thumb-md goods-thumb" src="' . $thumb . '">';
    }
}

// 构建搜索下拉菜单数据
function search_select($data, $key = 'id', $value = 'name')
{
    $res = [];
    if (is_array($data)) {
        foreach ($data as $row) {
            $res[] = ['id' => $row[$key], 'name' => $row[$value]];
        }
    }
    return json_encode($res, JSON_UNESCAPED_UNICODE);
}

function userBrowser() { 
    $user_OSagent = $_SERVER['HTTP_USER_AGENT']; 
    if (strpos($user_OSagent, "Maxthon") && strpos($user_OSagent, "MSIE")) { 
        $visitor_browser = "Maxthon(Microsoft IE)"; 
    } elseif (strpos($user_OSagent, "Maxthon 2.0")) { 
        $visitor_browser = "Maxthon 2.0"; 
    } elseif (strpos($user_OSagent, "Maxthon")) { 
        $visitor_browser = "Maxthon"; 
    } elseif (strpos($user_OSagent, "MSIE 9.0")) { 
        $visitor_browser = "MSIE 9.0"; 
    } elseif (strpos($user_OSagent, "MSIE 8.0")) { 
        $visitor_browser = "MSIE 8.0"; 
    } elseif (strpos($user_OSagent, "MSIE 7.0")) { 
        $visitor_browser = "MSIE 7.0"; 
    } elseif (strpos($user_OSagent, "MSIE 6.0")) { 
        $visitor_browser = "MSIE 6.0"; 
    } elseif (strpos($user_OSagent, "MSIE 5.5")) { 
        $visitor_browser = "MSIE 5.5"; 
    } elseif (strpos($user_OSagent, "MSIE 5.0")) { 
        $visitor_browser = "MSIE 5.0"; 
    } elseif (strpos($user_OSagent, "MSIE 4.01")) { 
        $visitor_browser = "MSIE 4.01"; 
    } elseif (strpos($user_OSagent, "MSIE")) { 
        $visitor_browser = "MSIE 较高版本"; 
    } elseif (strpos($user_OSagent, "NetCaptor")) { 
        $visitor_browser = "NetCaptor"; 
    } elseif (strpos($user_OSagent, "Netscape")) { 
        $visitor_browser = "Netscape"; 
    } elseif (strpos($user_OSagent, "Chrome")) { 
        $visitor_browser = "Chrome"; 
    } elseif (strpos($user_OSagent, "Lynx")) { 
        $visitor_browser = "Lynx"; 
    } elseif (strpos($user_OSagent, "Opera")) { 
        $visitor_browser = "Opera"; 
    } elseif (strpos($user_OSagent, "Konqueror")) { 
        $visitor_browser = "Konqueror"; 
    } elseif (strpos($user_OSagent, "Mozilla/5.0")) { 
        $visitor_browser = "Mozilla"; 
    } elseif (strpos($user_OSagent, "Firefox")) { 
        $visitor_browser = "Firefox"; 
    } elseif (strpos($user_OSagent, "U")) { 
        $visitor_browser = "Firefox"; 
    } else { 
        $visitor_browser = "其它"; 
    } 
    return $visitor_browser;
}

function system_log($type, $name, $remark, $level = 'info') {

    $auth = auth()->user();
    DB::table('system_log')->insert([
        'type' => $type,
        'name' => $name,
        'remark' => $remark,
        'created_id' => $auth['id'],
        'created_by' => $auth['name'],
        'created_at' => time(),
        'ip' => Request::ip(),
        'error_count' => 0,
        'browser' => userBrowser(),
        'device' => '',
        'level' => $level,
    ]);
}

function db_instr($field, $str, $prefix = ',', $suffix = ',')
{
    $db_type = env('DB_CONNECTION');
    if ($db_type == 'pgsql') {
        return "strpos(concat('$prefix', $field, '$suffix'), '".$prefix.$str.$suffix."') > 0";
    } else if($db_type == 'mysql') {
        return "instr(concat('$prefix', $field, '$suffix'), '".$prefix.$str.$suffix."') > 0";
    } else if($db_type == 'sqlsrv') {
        return "charindex('" . $prefix . $str . $suffix . "', '$prefix' + $field + '$suffix') > 0";
    }
}

function get_device_type()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $type = 'other';
    if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
        $type = 'ios';
    }
    if (strpos($agent, 'android')) {
        $type = 'android';
    }
    return $type;
}

function is_weixin()
{
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

function create_token($userId, $day = 7)
{
    $payload = array(
        'sub' => $userId,
        'iat' => time(),
        // $day 天有效
        'exp' => time() + ($day * 24 * 60 * 60),
    );
    return App\Support\JWT::encode($payload, config('app.key'));
}

/**
 * 返回json数据
 */
function response_json($data, $status = false)
{
    $json = [];
    if ($status === false) {
        $json['status'] = $status;
        $json['url'] = null;
    } else {
        $json['status'] = true;
        if ($status === true) {
            $json['url'] = '';
        } else {
            $json['url'] = url_referer($status);
        }
    }
    $json['data'] = $data;
    return $json;
}

// 登录是客户
function is_customer()
{
    return auth()->user()->group_id == 2;
}

/**
 * 登录是管理员
 */
function is_admin()
{
    return auth()->user()->admin == 1;
}

// 获取生产日期
function get_batch_sn($row)
{
    if ($row['batch_sn']) {
        $batch_sn = substr($row['batch_sn'], 0, 6);
        $sn = str_split($batch_sn, 2);
        $row['batch_date'] = date("Y-m-d", mktime(0, 0, 0, $sn[1], $sn[2], $sn[0]));
    } else {
        $row['batch_date'] = '';
    }
    return $row;
}

// url编码和js encodeURIComponent保持一致
function encodeURIComponent($str)
{
    $revert = ['%7E' => '~', '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')'];
    return strtr(rawurlencode($str), $revert);
}

function plugin_sync_api($uri, $data = [])
{
    $base_url = env('PLUGIN_SYNC_API_URL');
    if (empty($base_url)) {
        return ['error_code' => 0];
    }

    $url = $base_url . '/' . $uri;
    $data_string = json_encode($data);

    //初使化init方法
    $ch = curl_init();
    //指定URL
    curl_setopt($ch, CURLOPT_URL, $url);
    //设定请求后返回结果
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (!empty($data)) {
        //声明使用POST方式来进行发送
        curl_setopt($ch, CURLOPT_POST, 1);
        //发送什么数据呢
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
    }

    //忽略证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //忽略header头信息
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //设置超时时间
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    //发送请求
    $output = curl_exec($ch);

    $error = '';

    if ($output === false) {
        $error = curl_error($ch);
    }
    //关闭curl
    curl_close($ch);

    if ($error) {
        return ['msg' => $error, 'error_code' => 1];
    }

    // 返回数据
    return json_decode($output, true);
}

function print_prince($view)
{
    $viewData = $view->getData();
    try {
        $prince = new App\Support\Prince(env('PRINCE_DIR'));
        header('Content-Type:application/pdf');
        header('Content-Disposition:inline;filename="' . $viewData['form']['template']['name'] . '.pdf"');
        $prince->convert_string_to_passthru($view);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function get_sql($row, $type = 'insert')
{
    $sqls = [];
    foreach ($row as $k => $v) {
        if ($type == 'insert') {
            if (is_string($v)) {
                $sqls[] = "'$v'";
            } else {
                $sqls[] = "$v";
            }
        } else {
            if (is_string($v)) {
                $sqls[] = "$k = '$v'";
            } else {
                $sqls[] = "$k = $v";
            }
        }
    }
    if ($type == 'insert') {
        return join(',', $sqls);
    } else {
        return join(',', $sqls);
    }
}

// 检查日期格式是否符合
function checkDateFormat($date)
{
    // 匹配日期格式
    if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
        // 检测是否为日期
        return checkdate($parts[2],$parts[3],$parts[1]);
    }
    return false;
}