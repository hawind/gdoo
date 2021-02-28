<?php namespace App\Support;

use DB;

class Dialog
{
    public static $items = [
        'user' => [
            'title' => '用户',
            'table' => 'user',
            'field' => 'name',
            'url'   => 'user/user/dialog',
        ],
        'role' => [
            'title' => '角色',
            'table' => 'role',
            'field' => 'name',
            'url'   => 'user/role/dialog',
        ],
        'department' => [
            'title' => '部门',
            'table' => 'department',
            'field' => 'name',
            'url'   => 'user/department/dialog',
        ],
        'supplier' => [
            'title' => '供应商',
            'table' => 'supplier',
            'join'  => 'user',
            'field' => 'user.name',
            'url'   => 'supplier/supplier/dialog',
        ],
        'customer' => [
            'title' => '客户',
            'table' => 'customer',
            'join'  => 'user',
            'field' => 'user.name',
            'url'   => 'customer/customer/dialog',
        ],
        'customer_contact' => [
            'title' => '客户联系人',
            'table' => 'customer_contact',
            'join'  => 'user',
            'field' => 'user.name',
            'url'   => 'customer/contact/dialog',
        ],
        'supplier_product' => [
            'title' => '商品',
            'table' => 'product',
            'field' => 'name',
            'url'   => 'supplier/product/dialog',
        ],
        'product' => [
            'title' => '产品',
            'table' => 'product',
            'field' => 'name',
            'url'   => 'product/product/dialog',
        ],
        'promotion' => [
            'title' => '促销',
            'table' => 'promotion',
            'field' => 'id',
            'url'   => 'promotion/promotion/dialog',
        ],
        'region' => [
            'title' => '销售组',
            'table' => 'customer_region',
            'field' => 'name',
            'url'   => 'customer/region/dialog',
        ],
        'hr' => [
            'title' => '人事档案',
            'table' => 'hr',
            'field' => 'name',
            'url'   => 'hr/hr/dialog',
        ],
        'logistics' => [
            'title' => '物流',
            'table' => 'logistics',
            'field' => 'name',
            'url'   => 'order/logistics/dialog',
        ],
    ];

    public static function text($item, $value)
    {
        $dialog = self::$items[$item];

        $rows = '';
        
        if ($value) {
            $ids = explode(',', $value);

            $table = $dialog['table'];
            $join = $dialog['join'];

            if ($join) {
                $rows = DB::table($table)
                ->LeftJoin('user', 'user.id', '=', $table.'.user_id')
                ->whereIn($table.'.id', $ids)
                ->pluck($dialog['field'])->implode(',');
            } else {
                $rows = DB::table($table)
                ->whereIn('id', $ids)
                ->pluck($dialog['field'])->implode(',');
            }
        }
        return $rows;
    }

    public static function show($key, $data, $multi = 0, $readonly = 0)
    {
        $id = $key.'_id';
        $name = $key.'_name';

        if ($readonly == 0) {
            $html[] = '<div class="select-group input-group">';
        } else {
            $html[] = '<div class="select-group">';
        }
        
        if ($readonly == 0) {
            $arrow  = $multi == 1 ? 'icon-group' : 'icon-user';
            $option = "dialogShow('$id','$name','$multi');";
            $html[] = '<div class="form-control input-sm" onclick="'.$option .'" id="'.$name.'">'.$data[$name].'</div>';

            $html[] = '<div class="input-group-btn">';
            $html[] = '<button type="button" onclick="'.$option .'" class="btn btn-sm btn-default"><i class="icon '.$arrow.'"></i></button>';
            $html[] = '</div>';
        } else {
            $html[] = '<div class="form-control input-sm" id="'.$name.'">'.$data[$name].'</div>';
        }
        $html[] = '<input type="hidden" id="'.$id.'" name="'.$id.'" value="'.$data[$id].'">';
        $html[] = '</div>';
        return join("\n", $html);
    }

    public static function user($item, $name, $value = '', $multi = 0, $readonly = 0, $width = 'auto')
    {
        $rows = '';

        $dialog = self::$items[$item];

        if ($value) {
            $ids = explode(',', $value);

            $table = $dialog['table'];
            $join = $dialog['join'];

            if ($join) {
                $rows = DB::table($table)
                ->LeftJoin('user', 'user.id', '=', $table.'.user_id')
                ->whereIn($table.'.id', $ids)
                ->pluck($dialog['field'])->implode(',');
            } else {
                $rows = DB::table($table)
                ->whereIn('id', $ids)
                ->pluck($dialog['field'])->implode(',');
            }
        }

        $id = str_replace(['[',']'], ['_',''], $name);
        
        if ($readonly == 0) {
            $width = is_numeric($width) ? 'width:'.$width.'px;' : '';
            $html[] = '<div class="select-group input-group">';
        } else {
            $html[] = '<div class="select-group">';
        }

        if ($readonly == 0) {
            $html[] = '<input class="form-control input-sm" data-toggle="dialog-view" data-title="'.$dialog['title'].'" readonly="readonly" data-url="'.$dialog['url'].'" data-id="'.$id.'" data-multi="'.$multi.'" value="'.$rows.'" style="'.$width.'cursor:pointer;" id="'.$id.'_text" />';
            $html[] = '<div class="input-group-btn">';
            $html[] = '<a data-toggle="dialog-clear" data-id="'.$id.'" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>';
            $html[] = '</div>';
        } else {
            $html[] = '<div class="form-control input-sm" id="'.$id.'_text">'.$rows.'</div>';
        }
        $html[] = '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$value.'">';
        $html[] = '</div>';
        return join("\n", $html);
    }

    public static function search($data, $query)
    {
        $params = [];
        parse_str($query, $params);

        $defaultParams = [
            'prefix' => 1,
            'multi' => 0,
            'readonly' => 0,
            'width' => '100%',
            'title' => '',
            'url' => 'index/api/dialog',
        ];

        $params = array_merge($defaultParams, $params);
        extract($params);
        
        $_id = str_replace(['[',']'], ['_',''], $id);
        $_name = str_replace(['[',']'], ['_',''], $name);

        $jq = '';
        foreach ($params as $key => $value) {
            $jq .= ' data-'.$key.'="'. $value.'"';
        }

        $params['id'] = str_replace(['[',']'], ['_',''], $id);
        $params['name'] = str_replace(['[',']'], ['_',''], $name);

        $e[] = '<div class="select-group input-group">';
        $e[] = '<input class="form-control input-sm" style="width:'.$params['width'].';cursor:pointer;" readonly="readonly" data-toggle="dialog-view"'.$jq.' name="'.$name.'" value="'.$data[$name].'" id="'.$params['id'].'_text" />';
        $e[] = '<div class="input-group-btn">';
        $e[] = '<a data-toggle="dialog-clear" data-id="'.$params['id'].'" class="btn btn-sm btn-default"><i class="fa fa-times"></i></a>';
        $e[] = '</div>';
        $e[] = '<input type="hidden" id="'.$params['id'].'" name="'.$id.'" value="'.$data[$id].'">';
        $e[] = '</div>';
        return join("\n", $e);
    }
}
