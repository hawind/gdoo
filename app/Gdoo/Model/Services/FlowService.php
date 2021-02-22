<?php namespace Gdoo\Model\Services;

class FlowService
{
    public static function regulars()
    {
        return [
            'required' => '必填',
            'numeric' => '数字',
            'numeric_than:0' => '数字大于0',
            'alpha' => '字母',
            'date' => '日期',
            'alpha_num' => '数字+字母',
            'email' => '邮箱',
            'active_url' => '链接',
            'unique' => '唯一',
            'regex:/^[0-9]{5,20}$/' => 'QQ',
            'regex:/^(1)[0-9]{10}$/' => '手机',
            'regex:/^[0-9-]{6,13}$/' => '电话',
            'regex:/^[0-9]{6}$/' => '邮编',
        ];
    }
}
