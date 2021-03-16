<?php namespace Gdoo\Product\Models;

use Gdoo\Index\Models\BaseModel;

class ProductUnit extends BaseModel
{
    protected $table = 'product_unit';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'unit.index', 'url' => 'product/unit/index', 'name' => '计量单位'],
        ]
    ];
}
