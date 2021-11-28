<?php namespace Gdoo\Produce\Models;

use Gdoo\Index\Models\BaseModel;

class Material extends BaseModel
{
    protected $table = 'product_material';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['value' => 'material.index', 'url' => 'produce/material/index', 'name' => '原辅料档案'],
        ]
    ];
}
