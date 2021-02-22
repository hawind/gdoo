<?php

use Illuminate\Database\Query\Builder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

// 设置 Carbon 语言
Carbon::setLocale('zh');

// 自定义模板引擎标签
Blade::extend(function ($view) {
    $view = preg_replace('/\{\{\:(.+)\}\}/', '<?php ${1}; ?>', $view);
    $view = preg_replace('/(?<!\w)(\s*)@datetime\(\s*(.*)\)/', '<?php echo format_datetime($2); ?>', $view);
    $view = preg_replace('/(?<!\w)(\s*)@date\(\s*(.*)\)/', '<?php echo format_date($2); ?>', $view);
    $view = preg_replace('/(?<!\w)(\s*)@number\(\s*(.*)\)/', '<?php echo number_format($2); ?>', $view);
    $view = preg_replace('/(?<!\w)(\s*)@age\(\s*(.*)\)/', '<?php echo date_year($2); ?>', $view);
    return $view;
});

// 数组必须大于0
Validator::extend('numeric_than', function ($attribute, $value, $params) {
    return is_numeric($value) ? $value > $params[0] : false;
});
Validator::replacer('numeric_than', function($message, $attribute, $rule, $params) {
    return str_replace('_', ' ' , $message .' 必须大于 ' .$params[0]);
});

Collection::macro('toNested', function ($text = 'name', $id = 'id') {
    $rows = [];
    $items = array_nest($this, $text);
    if (empty($id)) {
        foreach($items as $item) {
            $rows[] = $item;
        }
    } else {
        $rows = $items;
    }
    return collect($rows);
});

// 多字段排序
Collection::macro('multiSortBy', function ($params) {
    $makeComparer = function ($criteria) {
        $comparer = function ($first, $second) use ($criteria) {
            foreach ($criteria as $key => $orderType) {
                // normalize sort direction
                $orderType = strtolower($orderType);
                if ($first[$key] < $second[$key]) {
                    return $orderType === "asc" ? -1 : 1;
                } else if ($first[$key] > $second[$key]) {
                    return $orderType === "asc" ? 1 : -1;
                }
            }
            return 0;
        };
        return $comparer;
    };
    $items = $this->toArray();
    $comparer = $makeComparer($params);
    usort($items, $comparer);
    return collect($items);
});

Request::macro('module', function ($default = 'index') {
    return Request::segment(1, $default);
});

Request::macro('controller', function ($default = 'index') {
    return Request::segment(2, $default);
});

Request::macro('action', function ($default = 'index') {
    return Request::segment(3, $default);
});

/**
 * 设置by字段的筛选类型
 */
Builder::macro('setBy', function ($haeder) {
    $table = $haeder['master_table'];
    $by = Request::get('by');

    if (trim($by) == '') {
        return $this;
    }

    switch ($by) {
        // 我的客户
        case 'me':
            $this->where($table.'.created_id', auth()->id());
            break;
        // 我的下属
        case 'sub':
            break;
        // 我关注的
        case 'follow':
            break;
        // 我共享的
        case 'myshare':
            break;
        // 共享给我的
        case 'share':
            break;
        // 本日创建
        case 'day':
            $this->whereRaw(sql_year_month_day($table.'.created_at', 'ts')." = ?", [date('Y-m-d')]);
            break;
        // 本周创建
        case 'week':
            $this->whereRaw(sql_year_week($table.'.created_at', 'ts')." = ?", [date('W')]);
            break;
        // 本月创建
        case 'month':
            $this->whereRaw(sql_year_month($table.'.created_at', 'ts')." = ?", [date('Y-m')]);
            break;

        // 流程执行中
        case 'flow.todo':
            $this->whereRaw('isnull('.$table.'.status, 0) <> 1');
            break;

        // 流程已结束
        case 'flow.done':
            $this->whereRaw('isnull('.$table.'.status, 0) = 1');
            break;

        // 启用
        case 'enabled':
            $this->where($table.'.status', 1);
            break;

            // 禁用
        case 'disabled':
            $this->whereRaw('isnull('.$table.'.status, 0) = 0');
            break;
    }

    if ($haeder['trash_btn'] == 1) {
        if ($by == 'trash') {
            $this->where($table.'.deleted_id', '>', 0);
        } else {
            $this->where($table.'.deleted_id', 0);
        }
    }

    // 是草稿只自己能看
    if ($haeder['audit_type'] == 1) {
        // $this->whereRaw("(case when ".$table.".status='draft' and ".$table.".created_id=? then 1 else 0 end)", [auth()->id()]);
    }

    return $this;
});

Builder::macro('search', function ($search) {
    list($condition, $value) = search_condition($search);

    // 搜索关闭状态
    if (strpos($search['field'], '.status') !== false && $value == '-4') {
        $this->where($this->from.'.is_close', 1);
    }

    elseif (strpos($search['field'], 'tax_id') !== false) {
        if ($this->from == 'customer_order' || $this->from == 'stock_delivery' || $this->from == 'stock_direct') {
            $this->where($search['field'], $value);
        } else {
            $customer_ids = DB::table('customer_tax')->where('id', $value)->pluck('customer_id');
            $this->whereIn($this->from.'.customer_id', $customer_ids);
        }
    } elseif ($condition == 'between') {
        $this->whereBetween($search['field'], $value);

    } elseif ($search['condition'] == 'date2') {
        $this->whereBetween($search['field'], $value);

    } elseif ($search['condition'] == 'second') {
        $this->whereBetween($search['field'], strtotime($value));

    } elseif ($search['condition'] == 'second2') {
        $this->whereBetween($search['field'], $value);

    } elseif ($condition == 'not_between') {
        $this->whereNotBetween($search['field'], $value);

    } elseif ($condition == 'dialog') {
        $this->whereIn($search['field'], $value);

    } elseif ($condition == 'in') {
        $this->whereIn($search['field'], $value);
    
    // 销售团队
    } elseif ($search['field'] == 'customer.region_id') {
        if ($value[0]) {
            $this->where($search['field'], $value[0]);
        }
        if ($value[1]) {
            $this->where('customer.id', $value[1]);
        }

    } elseif ($condition == 'birthday' || $condition == 'birthbetween') {
        $this->whereRaw('DATE_FORMAT('.$search['field'].',"%m-%d") between ? and ?', $value);

    // 普通行政区域
    } elseif ($condition == 'pacs') {
        $this->where($search['field'], 'like', '%'. join("\n", $value).'%');

    // 行政区域
    } elseif ($condition == 'region') {
        list($t, $f) = explode('.', $search['field']);
        if ($value[0]) {
            $this->where($t.'.province_id', $value[0]);
        }
        if ($value[1]) {
            $this->where($t.'.city_id', $value[1]);
        }
        if ($value[2]) {
            $this->where($t.'.county_id', $value[2]);
        }
    } else {
        $this->where($search['field'], $condition, $value);
    }
    return $this;
});

/**
 * 取得指定层级集
 *
 * @var int $id 条件编号
 * $type int 0.包含自己的所有子类, 1.包含自己所有父类
 */
Builder::macro('treeById', function($id, $type = 0)
{
    $table = $this->from;
    $rows = $this->from($table)
    ->whereRaw('lft >= (select lft from '.$table.' where id=?) and rgt <= (select rgt from '.$table.' where id=?)', [$id, $id])
    ->select([$table.'.*'])
    ->get();
    
    return $rows;
});

Builder::macro('toTreeChildren', function ($select = ['node.*']) {

    // 重新定义表结构
    $this->from(DB::raw($this->from.' as node, '.$this->from.' as parent'))
    ->selectRaw('node.id, node.parent_id, node.lft, node.rgt, node.type, node.code, node.name, node.sort, node.status, (COUNT(parent.id)-1) [level]')
    ->whereRaw('node.lft BETWEEN parent.lft AND parent.rgt')
    ->groupBy('node.id', 'node.parent_id', 'node.lft', 'node.rgt', 'node.type', 'node.code', 'node.name', 'node.sort', 'node.status')
    ->orderBy('node.lft', 'asc');
    $res = $this->get();

    $rows = array();

    if ($res->count()) {
        foreach ($res as $v) {
            $v['children'][] = $v['id'];
            
            $v['layer'] = str_repeat('|&ndash;', $v['level']);

            $rows[$v['id']] = $v;

            if ($rows[$v['parent_id']]['children']) {
                $rows[$v['parent_id']]['children'] = array_merge($rows[$v['parent_id']]['children'], $v['children']);
            }
        }

        foreach ($rows as $row) {
            if ($row['parent_id'] > 0) {
                $children = array_merge((array)$rows[$row['parent_id']]['children'], $row['children']);
                $rows[$row['parent_id']]['children'] = array_unique($children);
            }
        }
    }
    return $rows;
});

Builder::macro('permission', function ($field, $user = null, $null = false, $all = true, $children = false, $created_id = '') {
    if ($user === null) {
        $user = auth()->user();
    }

    if ($null) {
        $where[] = "ifnull($field, '') = ''";
    }
    if ($all) {
        $where[] = db_instr($field, 'all');
    }
    $where[] = db_instr($field, 'u'. $user['id']);
    $where[] = db_instr($field, 'r'. $user['role_id']);
    $where[] = db_instr($field, 'd'. $user['department_id']);
    
    if ($created_id) {
        $where[] = $created_id.'='.$user['id'];
    }
    
    if ($children) {
        $dep = explode(',', $us['deptpath']);
        foreach ($dep as $deps) {
            $_deps = str_replace(['[', ']'], ['', ''], $deps);
            $where[] = db_instr($fids, 'd'.$_deps);
        }
    }
    $sql = join(' or ', $where);
    if ($sql) {
        $sql = '('.$sql.')';
    }

    $this->whereRaw($sql);
    return $this;
});
