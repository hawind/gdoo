<?php namespace Gdoo\CustomerCost\Hooks;

class CostDataHook
{
    public function onBeforeStore($params) 
    {
        $master = $params['master'];
        $row = $params['row'];
        $row['src_id'] = $master['id'];
        $row['src_sn'] = $master['sn'];
        $row['src_type_id'] = $master['type_id'];
        $params['row'] = $row;
        return $params;
    }
}
