<?php namespace Gdoo\Product\Models;

use DB;
use Gdoo\Index\Models\BaseModel;

class Product extends BaseModel
{
    protected $table = 'product';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'product.index', 'url' => 'product/product/index', 'name' => '产品档案'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'enabled', 'name' => '启用'],
            ['value' => 'disabled', 'name' => '禁用'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    public function scopeType($query, $type = 1)
    {
        $types['sale'] = 1;
        $types['supplier'] = 2;
        return $query->LeftJoin('product_category', 'product_category.id', '=', 'product.category_id')
        ->where('product_category.type', $types[$type]);
    }

    public function warehouse($query)
    {
        return $this->belongsTo('Gdoo\Product\Models\Warehouse');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->pluck('name', 'id');
    }
}
