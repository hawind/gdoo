<?php namespace Gdoo\Article\Models;

use Gdoo\Index\Models\BaseModel;

class Article extends BaseModel
{
    protected $table = 'article';

    public static $tabs = [
        'name' => 'tab',
        'items' => [
            ['url' => 'article/article/index', 'name' => '未读', 'value' => 'unread'],
            ['url' => 'article/article/index', 'name' => '已读', 'value' => 'done'],
            ['url' => 'article/article/index', 'name' => '全部', 'value' => 'all'],
        ]
    ];

    public static $bys = [
        'name' => 'by',
        'items' => [
            ['value' => '', 'name' => '全部'],
            ['value' => 'divider'],
            ['value' => 'day', 'name' => '今日创建'],
            ['value' => 'week', 'name' => '本周创建'],
            ['value' => 'month', 'name' => '本月创建'],
        ]
    ];

    public function user()
    {
        return $this->belongsTo('Gdoo\User\Models\User', 'created_id');
    }

    public function category()
    {
        return $this->belongsTo('Gdoo\Article\Models\ArticleCategory');
    }
}
