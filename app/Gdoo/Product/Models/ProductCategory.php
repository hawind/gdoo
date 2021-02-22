<?php namespace Gdoo\Product\Models;

use Gdoo\Index\Models\BaseModel;

class ProductCategory extends BaseModel
{
    protected $table = 'product_category';

    public static $tabs = [
        'name'  => 'tab',
        'items' => [
            ['value' => 'category.index', 'url' => 'product/category/index', 'name' => '产品类别'],
        ]
    ];

    public static $bys = [
        'name'  => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
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
        return $query->where('type', $types[$type]);
    }

    public function products()
    {
        return $this->hasMany('Gdoo\Product\Models\Product', 'category_id');
    }

    public function scopeDialog($q, $value)
    {
        return $q->whereIn('id', $value)->get()->toNested()->pluck('text', 'id');
    }
}
